<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\PigEntryRecord;
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

        // ดึง pending cancel requests
        $pendingCancelRequests = Notification::where('approval_status', 'pending')
            ->where('type', 'cancel_pig_sale')
            ->with('relatedUser')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // ดึง approved cancel requests
        $approvedCancelRequests = Notification::where('approval_status', 'approved')
            ->where('type', 'cancel_pig_sale')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // ดึง rejected cancel requests
        $rejectedCancelRequests = Notification::where('approval_status', 'rejected')
            ->where('type', 'cancel_pig_sale')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // ดึง notification ประเภท payment ทั้งหมด 
        // ❌ ไม่ดึง payment_recorded_pig_entry (ไปแสดงบน Cost Payment Approvals แทน - Phase 7I)
        // ✅ ดึงเฉพาะ payment_recorded_pig_sale
        $pendingNotifications = Notification::where('approval_status', 'pending')
            ->where('type', 'payment_recorded_pig_sale')  // Only PigSale payments
            ->with(['relatedUser', 'pigSale'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $approvedNotifications = Notification::where('approval_status', 'approved')
            ->where('type', 'payment_recorded_pig_sale')  // Only PigSale payments
            ->with(['relatedUser', 'pigSale'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $rejectedNotifications = Notification::where('approval_status', 'rejected')
            ->where('type', 'payment_recorded_pig_sale')  // Only PigSale payments
            ->with(['relatedUser', 'pigSale'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.payment_approvals.index', compact(
            'pendingPayments',
            'approvedPayments',
            'rejectedPayments',
            'pendingCancelRequests',
            'approvedCancelRequests',
            'rejectedCancelRequests',
            'pendingNotifications',
            'approvedNotifications',
            'rejectedNotifications'
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
     * อนุมัติการชำระเงิน (Notification table - PigEntry)
     */
    public function approve(Request $request, $notificationId)
    {
        DB::beginTransaction();
        try {
            $notification = Notification::findOrFail($notificationId);

            // ตรวจสอบว่าเป็นการแจ้งเตือนเกี่ยวกับการชำระเงิน
            if (!in_array($notification->type, ['payment_recorded_pig_entry', 'payment_recorded_pig_sale'])) {
                return redirect()->back()->with('error', 'การแจ้งเตือนนี้ไม่ใช่การอนุมัติการชำระเงิน');
            }

            // ตรวจสอบว่าอนุมัติแล้วหรือไม่
            if ($notification->approval_status !== 'pending') {
                return redirect()->back()->with('error', 'การแจ้งเตือนนี้ได้รับการอนุมัติแล้ว');
            }

            // ระบุหนักสั่ง request
            $validated = $request->validate([
                'approval_notes' => 'nullable|string|max:500',
            ]);

            // ดึงข้อมูล payment ที่เกี่ยวข้อง
            $relatedModel = $notification->related_model;
            $relatedModelId = $notification->related_model_id;

            if ($relatedModel === 'PigEntryRecord') {
                $pigEntry = PigEntryRecord::findOrFail($relatedModelId);

                // ✅ อัปเดท PigEntryRecord payment status
                $pigEntry->update([
                    'payment_approved_at' => now(),
                    'payment_approved_by' => auth()->user()->name,
                    'payment_status' => 'approved',
                ]);

                // 🔥 Recalculate profit เมื่อ payment อนุมัติ
                if ($pigEntry->batch_id) {
                    RevenueHelper::calculateAndRecordProfit($pigEntry->batch_id);
                }
            } elseif ($relatedModel === 'PigSale') {
                $pigSale = PigSale::findOrFail($relatedModelId);

                // ✅ For PigSale, just mark notification as approved
                // (The payment approval is already handled in approvePayment() method)
            } else {
                throw new \Exception('ไม่รู้จักประเภท model นี้');
            }

            // อัปเดท notification status
            $notification->update([
                'approval_status' => 'approved',
                'approval_notes' => $validated['approval_notes'] ?? '',
                'is_read' => true,
                'read_at' => now(),
            ]);

            // ✅ ส่งแจ้งเตือนให้ผู้ที่เกี่ยวข้องว่าการชำระได้รับการอนุมัติ
            if ($relatedModel === 'PigEntryRecord') {
                \App\Helpers\NotificationHelper::notifyUserPigEntryPaymentApproved($pigEntry);
            }

            DB::commit();

            $modelName = $relatedModel === 'PigEntryRecord' ? 'การรับเข้าหมู' : 'การขายหมู';
            return redirect()->route('payment_approvals.index')
                ->with('success', "อนุมัติการชำระเงิน ($modelName) เรียบร้อยแล้ว");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * ปฏิเสธการชำระเงิน
     */
    public function reject(Request $request, $notificationId)
    {
        DB::beginTransaction();
        try {
            $notification = Notification::findOrFail($notificationId);

            // ตรวจสอบว่าเป็นการแจ้งเตือนเกี่ยวกับการชำระเงิน
            if (!in_array($notification->type, ['payment_recorded_pig_entry', 'payment_recorded_pig_sale'])) {
                return redirect()->back()->with('error', 'การแจ้งเตือนนี้ไม่ใช่การอนุมัติการชำระเงิน');
            }

            // ตรวจสอบว่าปฏิเสธแล้วหรือไม่
            if ($notification->approval_status !== 'pending') {
                return redirect()->back()->with('error', 'การแจ้งเตือนนี้ได้รับการอนุมัติแล้ว');
            }

            // ตรวจสอบว่ามีเหตุผลในการปฏิเสธหรือไม่
            $validated = $request->validate([
                'rejection_reason' => 'required|string|max:500',
            ]);

            // อัปเดท notification status
            $notification->update([
                'approval_status' => 'rejected',
                'approval_notes' => $validated['rejection_reason'],
                'is_read' => true,
                'read_at' => now(),
            ]);

            DB::commit();

            $modelName = $notification->related_model === 'PigEntryRecord' ? 'การรับเข้าหมู' : 'การขายหมู';
            return redirect()->route('payment_approvals.index')
                ->with('success', "ปฏิเสธการชำระเงิน ($modelName) เรียบร้อยแล้ว");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * ดูรายละเอียดการชำระเงิน
     */
    public function detail($notificationId)
    {
        $notification = Notification::findOrFail($notificationId);

        // ตรวจสอบว่าเป็นการแจ้งเตือนเกี่ยวกับการชำระเงิน
        if (!in_array($notification->type, ['payment_recorded_pig_entry', 'payment_recorded_pig_sale'])) {
            return redirect()->back()->with('error', 'การแจ้งเตือนนี้ไม่ใช่การอนุมัติการชำระเงิน');
        }

        // ดึงข้อมูล payment ที่เกี่ยวข้อง
        if ($notification->related_model === 'PigEntryRecord') {
            $paymentData = PigEntryRecord::findOrFail($notification->related_model_id);
            $type = 'pig_entry';
        } elseif ($notification->related_model === 'PigSale') {
            $paymentData = PigSale::findOrFail($notification->related_model_id);
            $type = 'pig_sale';
        } else {
            return redirect()->back()->with('error', 'ไม่รู้จักประเภท model นี้');
        }

        return view('admin.payment_approvals.detail', compact('notification', 'paymentData', 'type'));
    }

    /**
     * อนุมัติยกเลิกการขาย (Notification table - cancel_pig_sale)
     */
    public function approveCancelSale($notificationId)
    {
        DB::beginTransaction();
        try {
            $notification = Notification::findOrFail($notificationId);

            if ($notification->type !== 'cancel_pig_sale') {
                return redirect()->back()->with('error', 'ไม่ใช่การขอยกเลิกการขายหมู');
            }

            if ($notification->approval_status !== 'pending') {
                return redirect()->back()->with('error', 'คำขอนี้ได้รับการอนุมัติแล้ว');
            }

            // Call PigSaleController::confirmCancel()
            $pigSaleController = new PigSaleController();
            $result = $pigSaleController->confirmCancel($notification->related_model_id);

            // อัปเดท notification status
            $notification->update([
                'approval_status' => 'approved',
                'is_read' => true,
                'read_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('payment_approvals.index')
                ->with('success', 'อนุมัติยกเลิกการขายสำเร็จ');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PaymentApprovalController - approveCancelSale Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * ปฏิเสธยกเลิกการขาย
     */
    public function rejectCancelSale(Request $request, $notificationId)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'rejection_reason' => 'required|string|max:500',
            ]);

            $notification = Notification::findOrFail($notificationId);

            if ($notification->type !== 'cancel_pig_sale') {
                return redirect()->back()->with('error', 'ไม่ใช่การขอยกเลิกการขายหมู');
            }

            if ($notification->approval_status !== 'pending') {
                return redirect()->back()->with('error', 'คำขอนี้ได้รับการอนุมัติแล้ว');
            }

            $notification->update([
                'approval_status' => 'rejected',
                'approval_notes' => $validated['rejection_reason'],
                'is_read' => true,
                'read_at' => now(),
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'ปฏิเสธการขอยกเลิกการขายสำเร็จ');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PaymentApprovalController - rejectCancelSale Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }
}
