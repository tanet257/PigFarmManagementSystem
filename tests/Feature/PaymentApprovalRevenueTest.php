<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Cost;
use App\Models\Farm;
use App\Models\Payment;
use App\Models\PigDeath;
use App\Models\PigEntry;
use App\Models\PigEntryRecord;
use App\Models\PigSale;
use App\Models\Profit;
use App\Models\Revenue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentApprovalRevenueTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $farm;
    protected $batch;
    protected $pigEntry;
    protected $pigSale;
    protected $payment;

    protected function setUp(): void
    {
        parent::setUp();

        // สร้าง user
        $this->user = User::factory()->create(['role' => 'admin']);

        // สร้าง farm
        $this->farm = Farm::factory()->create();

        // สร้าง batch
        $this->batch = Batch::factory()->create([
            'farm_id' => $this->farm->id,
            'status' => 'active',
        ]);

        // สร้าง pig entry
        $this->pigEntry = PigEntryRecord::factory()->create([
            'farm_id' => $this->farm->id,
            'batch_id' => $this->batch->id,
        ]);

        // สร้าง pig entry record
        $pigEntryRecord = PigEntryRecord::factory()->create([
            'pig_entry_id' => $this->pigEntry->id,
            'batch_id' => $this->batch->id,
            'farm_id' => $this->farm->id,
        ]);

        // สร้าง pig sale
        $this->pigSale = PigSale::factory()->create([
            'batch_id' => $this->batch->id,
            'farm_id' => $this->farm->id,
            'quantity' => 10,
            'price_per_pig' => 1000,
            'total_price' => 10000,
            'discount' => 0,
            'net_total' => 10000,
            'status' => 'approved',
            'payment_status' => 'รอการชำระ',
        ]);

        // สร้าง payment
        $this->payment = Payment::create([
            'pig_sale_id' => $this->pigSale->id,
            'amount' => 10000,
            'payment_method' => 'transfer',
            'status' => 'pending',
            'recorded_by' => $this->user->name,
            'created_at' => now(),
        ]);
    }

    /**
     * Test: เมื่ออนุมัติการชำระเงิน Revenue record ควรถูกสร้าง/อัปเดท
     */
    public function test_approve_payment_creates_revenue_record()
    {
        // ตรวจสอบ: ยังไม่มี Revenue record ในตอนนี้
        $this->assertDatabaseMissing('revenues', [
            'pig_sale_id' => $this->pigSale->id,
        ]);

        // Act: อนุมัติการชำระเงิน
        $this->actingAs($this->user)
            ->patchJson(
                route('payment_approvals.approve_payment', $this->payment->id),
                ['approval_notes' => 'อนุมัติเรียบร้อย']
            );

        // Assert: Revenue record ควรถูกสร้าง
        $this->assertDatabaseHas('revenues', [
            'pig_sale_id' => $this->pigSale->id,
            'batch_id' => $this->batch->id,
            'farm_id' => $this->farm->id,
            'revenue_type' => 'pig_sale',
            'quantity' => 10,
            'unit_price' => 1000,
            'total_revenue' => 10000,
            'net_revenue' => 10000,
            'payment_status' => 'ชำระแล้ว', // ✅ ได้รับการชำระแล้ว
        ]);

        // ตรวจสอบ: Revenue model สามารถดึงได้
        $revenue = Revenue::where('pig_sale_id', $this->pigSale->id)->first();
        $this->assertNotNull($revenue);
        $this->assertEquals(10000, $revenue->net_revenue);
    }

    /**
     * Test: เมื่ออนุมัติการชำระเงิน Profit record ควรถูก recalculate
     */
    public function test_approve_payment_recalculates_profit()
    {
        // Arrange: สร้าง costs
        $feedCost = Cost::factory()->create([
            'batch_id' => $this->batch->id,
            'farm_id' => $this->farm->id,
            'cost_type' => 'feed',
            'item_name' => 'feed cost',
            'quantity' => 100,
            'unit_price' => 50,
            'total_price' => 5000,
            'payment_status' => 'pending',
        ]);

        // สร้าง payment สำหรับ cost นี้
        $costPayment = Payment::create([
            'cost_id' => $feedCost->id,
            'amount' => 5000,
            'payment_method' => 'transfer',
            'status' => 'pending',
            'recorded_by' => $this->user->name,
        ]);

        // Act: อนุมัติการชำระเงิน (PigSale)
        $this->actingAs($this->user)
            ->patchJson(
                route('payment_approvals.approve_payment', $this->payment->id),
                ['approval_notes' => 'อนุมัติ']
            );

        // Assert: Profit record ควรถูกสร้าง/อัปเดท
        $profit = Profit::where('batch_id', $this->batch->id)->first();
        $this->assertNotNull($profit, 'Profit record ควรถูกสร้าง');

        // ตรวจสอบ: Revenue ควรรวมอยู่ใน profit
        $this->assertEquals(10000, $profit->total_revenue, 'Total revenue ควรเป็น 10000');
    }

    /**
     * Test: Revenue payment_status ควรอัปเดทตามการชำระเงิน
     */
    public function test_revenue_payment_status_updates_on_full_payment()
    {
        // ตรวจสอบ: payment_status ของ PigSale ตอนนี้
        $this->assertEquals('รอการชำระ', $this->pigSale->payment_status);

        // Act: อนุมัติการชำระเงินเต็มจำนวน
        $this->actingAs($this->user)
            ->patchJson(
                route('payment_approvals.approve_payment', $this->payment->id),
                ['approval_notes' => 'อนุมัติ']
            );

        // Assert: PigSale payment_status ควรเป็น 'ชำระแล้ว'
        $this->pigSale->refresh();
        $this->assertEquals('ชำระแล้ว', $this->pigSale->payment_status);

        // Assert: Revenue payment_status ควรเป็น 'ชำระแล้ว'
        $revenue = Revenue::where('pig_sale_id', $this->pigSale->id)->first();
        $this->assertNotNull($revenue);
        $this->assertEquals('ชำระแล้ว', $revenue->payment_status);
        $this->assertNotNull($revenue->payment_received_date);
    }

    /**
     * Test: เมื่ออนุมัติการชำระเงิน Payment status ควรเป็น 'approved'
     */
    public function test_approve_payment_updates_payment_status()
    {
        // ตรวจสอบ: payment status ตอนนี้
        $this->assertEquals('pending', $this->payment->status);

        // Act: อนุมัติ
        $this->actingAs($this->user)
            ->patchJson(
                route('payment_approvals.approve_payment', $this->payment->id),
                ['approval_notes' => 'อนุมัติ']
            );

        // Assert
        $this->payment->refresh();
        $this->assertEquals('approved', $this->payment->status);
        $this->assertNotNull($this->payment->approved_at);
        $this->assertEquals($this->user->name, $this->payment->approved_by);
    }

    /**
     * Test: ตรวจสอบ Revenue record มี batch_id ที่ถูกต้อง
     */
    public function test_revenue_has_correct_batch_and_farm_info()
    {
        // Act: อนุมัติ
        $this->actingAs($this->user)
            ->patchJson(
                route('payment_approvals.approve_payment', $this->payment->id),
                ['approval_notes' => 'อนุมัติ']
            );

        // Assert
        $revenue = Revenue::where('pig_sale_id', $this->pigSale->id)->first();
        $this->assertEquals($this->batch->id, $revenue->batch_id);
        $this->assertEquals($this->farm->id, $revenue->farm_id);
        $this->assertEquals($this->pigSale->id, $revenue->pig_sale_id);
        $this->assertEquals('pig_sale', $revenue->revenue_type);
    }

    /**
     * Test: เมื่อมี Revenue record แล้ว ต้องอัปเดท ไม่ใช่สร้างใหม่
     */
    public function test_approve_payment_updates_existing_revenue()
    {
        // Arrange: สร้าง Revenue record ก่อน
        $originalRevenue = Revenue::create([
            'farm_id' => $this->farm->id,
            'batch_id' => $this->batch->id,
            'pig_sale_id' => $this->pigSale->id,
            'revenue_type' => 'pig_sale',
            'quantity' => 10,
            'unit_price' => 1000,
            'total_revenue' => 10000,
            'net_revenue' => 10000,
            'payment_status' => 'รอการอนุมัติ',
            'revenue_date' => $this->pigSale->date,
        ]);

        $originalId = $originalRevenue->id;

        // Act: อนุมัติ
        $this->actingAs($this->user)
            ->patchJson(
                route('payment_approvals.approve_payment', $this->payment->id),
                ['approval_notes' => 'อนุมัติ']
            );

        // Assert: Revenue record ID ยังเหมือนเดิม (ไม่ได้สร้างใหม่)
        $updatedRevenue = Revenue::where('pig_sale_id', $this->pigSale->id)->first();
        $this->assertEquals($originalId, $updatedRevenue->id);
        $this->assertEquals('ชำระแล้ว', $updatedRevenue->payment_status);
    }
}
