<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\PigSale;
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
     * แสดงรายการ payment ที่รอการอนุมัติ
     */
    public function index()
    {
        // ดึง pending payments จาก Payment table
        $pendingPayments = Payment::where('status', 'pending')
            ->with(['pigSale.farm', 'pigSale.batch', 'recordedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // ดึง approved payments
        $approvedPayments = Payment::where('status', 'approved')
            ->with(['pigSale.farm', 'pigSale.batch', 'recordedBy'])
            ->orderBy('approved_at', 'desc')
            ->paginate(15);

        // ดึง rejected payments
        $rejectedPayments = Payment::where('status', 'rejected')
            ->with(['pigSale.farm', 'pigSale.batch', 'recordedBy'])
            ->orderBy('rejected_at', 'desc')
            ->paginate(15);

        return view('admin.payment_approvals.index', compact(
            'pendingPayments',
            'approvedPayments',
            'rejectedPayments'
        ));
    }

    /**
     * อนุมัติการชำระเงิน (Payment table)
     */
    public function approvePayment($paymentId)
    {
        DB::beginTransaction();
        try {
            $payment = Payment::findOrFail($paymentId);

            // ตรวจสอบว่า pending หรือไม่
            if ($payment->status !== 'pending') {
                return redirect()->back()->with('error', 'สามารถอนุมัติได้เฉพาะการชำระที่รอการอนุมัติเท่านั้น');
            }

            // อนุมัติ
            $payment->update([
                'status' => 'approved',
                'approved_by' => auth()->user()->name,
                'approved_at' => now(),
            ]);

            // อัปเดท Revenue และ PigSale
            $pigSale = $payment->pigSale;
            if ($pigSale) {
                // ✅ ก่อนอื่น บันทึก/อัปเดท Revenue ของ PigSale
                RevenueHelper::recordPigSaleRevenue($pigSale);

                $oldPaymentStatus = $pigSale->payment_status;
                $totalPaid = Payment::where('pig_sale_id', $pigSale->id)
                    ->where('status', 'approved')
                    ->sum('amount');

                if ($totalPaid >= $pigSale->net_total) {
                    $pigSale->update([
                        'payment_status' => 'ชำระแล้ว',
                        'paid_amount' => $totalPaid,
                        'balance' => 0,
                    ]);

                    Revenue::where('pig_sale_id', $pigSale->id)->update([
                        'payment_status' => 'ชำระแล้ว',
                        'payment_received_date' => now(),
                    ]);

                    $newPaymentStatus = 'ชำระแล้ว';
                } else {
                    $pigSale->update([
                        'payment_status' => 'ชำระบางส่วน',
                        'paid_amount' => $totalPaid,
                        'balance' => $pigSale->net_total - $totalPaid,
                    ]);

                    $newPaymentStatus = 'ชำระบางส่วน';
                }

                // ✅ ส่งแจ้งเตือนให้ผู้สร้างการขายเมื่อสถานะการชำระเปลี่ยน
                if ($oldPaymentStatus !== $newPaymentStatus) {
                    \App\Helpers\NotificationHelper::notifyUserPigSalePaymentStatusChanged($pigSale, $oldPaymentStatus, $newPaymentStatus);
                }

                // 🔥 Recalculate profit
                $profitResult = RevenueHelper::calculateAndRecordProfit($pigSale->batch_id);
                if (!$profitResult['success']) {
                    Log::warning('Payment Approval - Profit recalculation failed: ' . $profitResult['message']);
                }
            }

            DB::commit();

            return redirect()->route('payment_approvals.index')
                ->with('success', 'อนุมัติการชำระเงินสำเร็จ (Profit ปรับปรุงแล้ว)');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PaymentApprovalController - approvePayment Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }


    /**
     * ปฏิเสธการชำระเงิน (Payment table)
     */
    public function rejectPayment(Request $request, $paymentId)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'reject_reason' => 'required|string|max:500',
            ]);

            $payment = Payment::findOrFail($paymentId);

            // ตรวจสอบว่า pending หรือไม่
            if ($payment->status !== 'pending') {
                return redirect()->back()->with('error', 'สามารถปฏิเสธได้เฉพาะการชำระที่รอการอนุมัติเท่านั้น');
            }

            // ปฏิเสธ
            $payment->update([
                'status' => 'rejected',
                'rejected_by' => auth()->user()->name,
                'rejected_at' => now(),
                'reject_reason' => $validated['reject_reason'],
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'ปฏิเสธการชำระเงินสำเร็จ');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PaymentApprovalController - rejectPayment Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }



    /**
     * ดูรายละเอียดการชำระเงิน
     */
    public function detail($notificationId)
    {
        $notification = Notification::findOrFail($notificationId);

        // ตรวจสอบว่าเป็นการแจ้งเตือนเกี่ยวกับการขายหมู
        if ($notification->type !== 'payment_recorded_pig_sale') {
            return redirect()->back()->with('error', 'การแจ้งเตือนนี้ไม่ใช่การอนุมัติการชำระเงิน');
        }

        $paymentData = PigSale::findOrFail($notification->related_model_id);
        $type = 'pig_sale';

        return view('admin.payment_approvals.detail', compact('notification', 'paymentData', 'type'));
    }
}
