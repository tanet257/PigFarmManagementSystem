<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\PigEntryRecord;
use App\Models\PigSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentApprovalController extends Controller
{
    /**
     * แสดงรายการ payment ที่รอการอนุมัติ
     */
    public function index()
    {
        // ดึงการแจ้งเตือนที่เป็นประเภท payment ที่รอการอนุมัติ
        $pendingNotifications = Notification::where('approval_status', 'pending')
            ->whereIn('type', ['payment_recorded_pig_entry', 'payment_recorded_pig_sale'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // ดึงการแจ้งเตือนที่อนุมัติแล้ว
        $approvedNotifications = Notification::where('approval_status', 'approved')
            ->whereIn('type', ['payment_recorded_pig_entry', 'payment_recorded_pig_sale'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // ดึงการแจ้งเตือนที่ถูกปฏิเสธ
        $rejectedNotifications = Notification::where('approval_status', 'rejected')
            ->whereIn('type', ['payment_recorded_pig_entry', 'payment_recorded_pig_sale'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.payment_approvals.index', compact('pendingNotifications', 'approvedNotifications', 'rejectedNotifications'));
    }

    /**
     * อนุมัติการชำระเงิน
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
            } elseif ($relatedModel === 'PigSale') {
                $pigSale = PigSale::findOrFail($relatedModelId);
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
}
