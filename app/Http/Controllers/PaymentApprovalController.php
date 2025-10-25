<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\PigSale;
use App\Models\PigDeath;  // ✅ NEW
use App\Models\Payment;
use App\Models\Revenue;
use App\Helpers\RevenueHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentApprovalController extends Controller
{
    /**
     * แสดงรายการการขายหมูที่รอการอนุมัติ (ลบ Payment approval เพื่อความเรียบง่าย)
     */
    public function index()
    {
        // ดึง pending PigSale approvals (การขายหมูที่รอการอนุมัติ)
        $pendingPigSales = PigSale::where('status', 'pending')
            ->with(['farm', 'batch', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // ดึง cancel sale requests (ขอยกเลิกการขายที่รอการอนุมัติ)
        $pendingCancelSales = PigSale::where('status', 'ยกเลิกการขาย_รอการอนุมัติ')
            ->with(['farm', 'batch', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // ดึง approved PigSales
        $approvedPigSales = PigSale::where('status', 'approved')
            ->with(['farm', 'batch', 'approvedBy'])
            ->orderBy('approved_at', 'desc')
            ->paginate(15);

        // ดึง rejected PigSales
        $rejectedPigSales = PigSale::where('status', 'rejected')
            ->with(['farm', 'batch'])
            ->orderBy('rejected_at', 'desc')
            ->paginate(15);

        return view('admin.payment_approvals.index', compact(
            'pendingPigSales',
            'pendingCancelSales',
            'approvedPigSales',
            'rejectedPigSales'
        ));
    }

    /**
     * ดูรายละเอียดการขายหมู
     */
    public function detail($notificationId)
    {
        $notification = Notification::findOrFail($notificationId);

        // ตรวจสอบว่าเป็นการแจ้งเตือนเกี่ยวกับการขายหมู
        if ($notification->type !== 'payment_recorded_pig_sale') {
            return redirect()->back()->with('error', 'การแจ้งเตือนนี้ไม่ใช่การอนุมัติการขายหมู');
        }

        $paymentData = PigSale::findOrFail($notification->related_model_id);
        $type = 'pig_sale';

        return view('admin.payment_approvals.detail', compact('notification', 'paymentData', 'type'));
    }

    /**
     * ✅ อนุมัติการขายหมู (PigSale table)
     * บันทึก Profit และ Revenue ตอนอนุมัติ
     */
    public function approvePigSale($pigSaleId)
    {
        DB::beginTransaction();
        try {
            $pigSale = PigSale::findOrFail($pigSaleId);

            // ตรวจสอบว่า pending หรือไม่
            if ($pigSale->status !== 'pending') {
                return redirect()->back()->with('error', 'สามารถอนุมัติได้เฉพาะการขายที่รอการอนุมัติเท่านั้น');
            }

            // อนุมัติ (only update status - approval details are in Payment table)
            $pigSale->update([
                'status' => 'approved',
            ]);

            // ✅ บันทึก Profit และ Revenue ตอนอนุมัติเท่านั้น (ไม่ใช่ตอนบันทึก)
            RevenueHelper::recordPigSaleRevenue($pigSale);
            RevenueHelper::calculateAndRecordProfit($pigSale->batch_id);

            // ✅ สร้าง Notification ให้ผู้บันทึก
            Notification::create([
                'user_id' => $pigSale->created_by,
                'title' => '✅ การขายหมูของคุณได้รับการอนุมัติ',
                'message' => "การขายหมู {$pigSale->quantity} ตัว (รุ่น: {$pigSale->batch->batch_code})\nได้รับการอนุมัติโดย: " . auth()->user()->name,
                'type' => 'pig_sale_approved',
                'related_model' => 'PigSale',
                'related_model_id' => $pigSale->id,
                'is_read' => false,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'อนุมัติการขายหมูสำเร็จ (บันทึก Profit แล้ว)');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PaymentApprovalController - approvePigSale Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }    /**
     * ✅ NEW: ปฏิเสธการขายหมู (PigSale table)
     */
    public function rejectPigSale(Request $request, $pigSaleId)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'rejection_reason' => 'required|string|max:500',
            ]);

            $pigSale = PigSale::findOrFail($pigSaleId);

            // ตรวจสอบว่า pending หรือไม่
            if ($pigSale->status !== 'pending') {
                return redirect()->back()->with('error', 'สามารถปฏิเสธได้เฉพาะการขายที่รอการอนุมัติเท่านั้น');
            }

            // ปฏิเสธ
            $pigSale->update([
                'status' => 'rejected',
                'rejected_by' => auth()->id(),
                'rejected_at' => now(),
                'rejection_reason' => $validated['rejection_reason'],
            ]);

            // ✅ NEW: คืน dead pigs status เป็น recorded ถ้าเป็นการขายหมูตาย
            if ($pigSale->sell_type === 'หมูตาย') {
                PigDeath::whereIn('id', function ($query) use ($pigSaleId) {
                    $query->selectRaw('pig_deaths.id')
                        ->from('pig_deaths')
                        ->join('pig_sale_details', 'pig_deaths.pen_id', '=', 'pig_sale_details.pen_id')
                        ->where('pig_sale_details.pig_sale_id', $pigSaleId);
                })->where('status', 'sold')->update(['status' => 'recorded']);
            }

            // ✅ NEW: สร้าง Notification ให้ผู้บันทึก
            Notification::create([
                'user_id' => $pigSale->created_by,
                'title' => '❌ การขายหมูของคุณถูกปฏิเสธ',
                'message' => "การขายหมู {$pigSale->quantity} ตัว (รุ่น: {$pigSale->batch->batch_code})\nถูกปฏิเสธโดย: " . auth()->user()->name . "\nเหตุผล: " . $validated['rejection_reason'],
                'type' => 'pig_sale_rejected',
                'related_model' => 'PigSale',
                'related_model_id' => $pigSale->id,
                'is_read' => false,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'ปฏิเสธการขายหมูสำเร็จ');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PaymentApprovalController - rejectPigSale Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * ✅ NEW: อนุมัติการยกเลิกการขายหมู (Cancel Sale)
     * เรียกใช้ PigSaleController::confirmCancel() สำหรับ pig inventory restoration
     */
    public function approveCancelSale(Request $request, $pigSaleId)
    {
        DB::beginTransaction();
        try {
            $pigSale = PigSale::findOrFail($pigSaleId);
            $batchId = $pigSale->batch_id;

            // ตรวจสอบว่า cancel_requested หรือไม่
            if ($pigSale->status !== 'ยกเลิกการขาย_รอการอนุมัติ') {
                return redirect()->back()->with('error', 'สามารถอนุมัติได้เฉพาะคำขอยกเลิกเท่านั้น');
            }

            // ✅ เรียกใช้ฟังก์ชั่น confirmCancel() ของ PigSaleController เพื่อจัดการ inventory restoration
            $pigSaleController = new \App\Http\Controllers\PigSaleController();

            // Call confirmCancel() directly (return value doesn't matter, we'll handle it)
            $pigSaleController->confirmCancel($pigSaleId);

            // ✅ คืน dead pigs status เป็น recorded ถ้าเป็นการขายหมูตาย
            if ($pigSale->sell_type === 'หมูตาย') {
                PigDeath::whereIn('id', function ($query) use ($pigSale) {
                    $query->selectRaw('pig_deaths.id')
                        ->from('pig_deaths')
                        ->join('pig_sale_details', 'pig_deaths.pen_id', '=', 'pig_sale_details.pen_id')
                        ->join('pig_sales', 'pig_sale_details.pig_sale_id', '=', 'pig_sales.id')
                        ->where('pig_sales.id', $pigSale->id);
                })->where('status', 'sold')->update(['status' => 'recorded']);
            }

            // ✅ สร้าง Notification ให้ผู้บันทึก
            Notification::create([
                'user_id' => $pigSale->created_by,
                'title' => '✅ คำขอยกเลิกการขายหมูของคุณถูกอนุมัติ',
                'message' => "การยกเลิกการขายหมู {$pigSale->quantity} ตัว (รุ่น: {$pigSale->batch->batch_code})\nถูกอนุมัติโดย: " . auth()->user()->name,
                'type' => 'pig_sale_cancel_approved',
                'related_model' => 'PigSale',
                'related_model_id' => $pigSale->id,
                'is_read' => false,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'อนุมัติการยกเลิกการขายหมูสำเร็จ (คืนหมูกลับเล้า-คอกแล้ว)');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PaymentApprovalController - approveCancelSale Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * ✅ NEW: ปฏิเสธการยกเลิกการขายหมู (Cancel Sale)
     */
    public function rejectCancelSale(Request $request, $pigSaleId)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'reject_reason' => 'required|string|max:500',
            ]);

            $pigSale = PigSale::findOrFail($pigSaleId);

            // ตรวจสอบว่า cancel_requested หรือไม่
            if ($pigSale->status !== 'ยกเลิกการขาย_รอการอนุมัติ') {
                return redirect()->back()->with('error', 'สามารถปฏิเสธได้เฉพาะคำขอยกเลิกเท่านั้น');
            }

            // อัปเดท status กลับไปเป็น approved (ยกเลิกคำขอ)
            $pigSale->update([
                'status' => 'approved',
            ]);

            // ✅ สร้าง Notification ให้ผู้บันทึก
            Notification::create([
                'user_id' => $pigSale->created_by,
                'title' => '❌ คำขอยกเลิกการขายหมูของคุณถูกปฏิเสธ',
                'message' => "การยกเลิกการขายหมู {$pigSale->quantity} ตัว (รุ่น: {$pigSale->batch->batch_code})\nถูกปฏิเสธโดย: " . auth()->user()->name . "\nเหตุผล: " . $validated['reject_reason'],
                'type' => 'pig_sale_cancel_rejected',
                'related_model' => 'PigSale',
                'related_model_id' => $pigSale->id,
                'is_read' => false,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'ปฏิเสธการยกเลิกการขายหมูสำเร็จ');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PaymentApprovalController - rejectCancelSale Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

}
