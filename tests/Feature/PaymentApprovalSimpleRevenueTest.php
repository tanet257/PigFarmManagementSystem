<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Farm;
use App\Models\Payment;
use App\Models\PigSale;
use App\Models\Revenue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentApprovalSimpleRevenueTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: เมื่ออนุมัติการชำระเงิน Revenue record ควรถูกสร้าง
     */
    public function test_payment_approval_records_revenue()
    {
        // Arrange
        $user = User::factory()->create(['role' => 'admin']);
        $farm = Farm::factory()->create();
        $batch = Batch::factory()->create(['farm_id' => $farm->id]);

        $pigSale = PigSale::factory()->create([
            'batch_id' => $batch->id,
            'farm_id' => $farm->id,
            'quantity' => 10,
            'price_per_pig' => 1000,
            'total_price' => 10000,
            'net_total' => 10000,
            'status' => 'approved',
            'payment_status' => 'รอการชำระ',
        ]);

        $payment = Payment::create([
            'pig_sale_id' => $pigSale->id,
            'amount' => 10000,
            'payment_method' => 'transfer',
            'status' => 'pending',
            'recorded_by' => $user->name,
        ]);

        // ตรวจสอบ: ยังไม่มี Revenue record
        $this->assertDatabaseMissing('revenues', ['pig_sale_id' => $pigSale->id]);

        // Act: อนุมัติ Payment
        $this->actingAs($user)
            ->patchJson(
                route('payment_approvals.approve_payment', $payment->id),
                ['approval_notes' => 'OK']
            );

        // Assert: Revenue record ควรถูกสร้าง
        $this->assertDatabaseHas('revenues', [
            'pig_sale_id' => $pigSale->id,
            'batch_id' => $batch->id,
            'farm_id' => $farm->id,
            'revenue_type' => 'pig_sale',
            'quantity' => 10,
            'net_revenue' => 10000,
        ]);

        // ตรวจสอบ: Payment status ควรเป็น 'approved'
        $payment->refresh();
        $this->assertEquals('approved', $payment->status);

        // ตรวจสอบ: PigSale payment_status ควรอัปเดท
        $pigSale->refresh();
        $this->assertEquals('ชำระแล้ว', $pigSale->payment_status);

        // ตรวจสอบ: Revenue payment_status ควรเป็น 'ชำระแล้ว'
        $revenue = Revenue::where('pig_sale_id', $pigSale->id)->first();
        $this->assertNotNull($revenue);
        $this->assertEquals('ชำระแล้ว', $revenue->payment_status);
    }
}
