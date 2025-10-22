<?php

namespace App\Helpers;

use App\Models\Notification;
use App\Models\User;

class NotificationHelper
{
    /**
     * สร้างแจ้งเตือนสำหรับ Admin เมื่อมีผู้ใช้ลงทะเบียนใหม่
     */
    public static function notifyAdminsNewUserRegistration(User $newUser)
    {
        // หา Admin ทั้งหมดที่มีสิทธิ์ manage_users
        $admins = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();

        foreach ($admins as $admin) {
            Notification::create([
                'type' => 'user_registered',
                'user_id' => $admin->id,
                'related_user_id' => $newUser->id,
                'title' => 'ผู้ใช้ใหม่ลงทะเบียน',
                'message' => "ผู้ใช้ {$newUser->name} ({$newUser->email}) ลงทะเบียนเข้าระบบและรอการอนุมัติ",
                'url' => route('user_management.index'),
                'is_read' => false,
            ]);
        }
    }

    /**
     * สร้างแจ้งเตือนเมื่อผู้ใช้ได้รับการอนุมัติ
     */
    public static function notifyUserApproved(User $user, User $approvedBy)
    {
        Notification::create([
            'type' => 'user_approved',
            'user_id' => $user->id,
            'related_user_id' => $approvedBy->id,
            'title' => 'บัญชีของคุณได้รับการอนุมัติแล้ว',
            'message' => "บัญชีของคุณได้รับการอนุมัติโดย {$approvedBy->name} คุณสามารถเข้าสู่ระบบได้แล้ว",
            'url' => route('dashboard'),
            'is_read' => false,
        ]);
    }

    /**
     * สร้างแจ้งเตือนเมื่อผู้ใช้ถูกปฏิเสธ
     */
    public static function notifyUserRejected(User $user, User $rejectedBy, $reason)
    {
        Notification::create([
            'type' => 'user_rejected',
            'user_id' => $user->id,
            'related_user_id' => $rejectedBy->id,
            'title' => 'บัญชีของคุณถูกปฏิเสธ',
            'message' => "บัญชีของคุณถูกปฏิเสธโดย {$rejectedBy->name}\nเหตุผล: {$reason}",
            'url' => null,
            'is_read' => false,
        ]);
    }

    /**
     * แจ้งเตือน Admin เมื่อมีหมูตาย
     */
    public static function notifyAdminsPigDeath($pigDeath, User $reportedBy)
    {
        $admins = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();

        // Load relationships ถ้ายังไม่ได้ load
        if (!$pigDeath->relationLoaded('batch')) {
            $pigDeath->load('batch', 'barn', 'pen');
        }

        $batch = $pigDeath->batch;
        $barn = $pigDeath->barn;
        $pen = $pigDeath->pen;

        foreach ($admins as $admin) {
            Notification::create([
                'type' => 'pig_death',
                'user_id' => $admin->id,
                'related_user_id' => $reportedBy->id,
                'title' => 'รายงานหมูตาย',
                'message' => "มีหมูตาย {$pigDeath->amount} ตัว\nรุ่น: {$batch->batch_code}\nเล้า: {$barn->barn_code}\nคอก: {$pen->pen_code}\nสาเหตุ: " . ($pigDeath->cause ?? 'ไม่ระบุ'),
                'url' => url('view_pig_death'),
                'is_read' => false,
            ]);
        }
    }

    /**
     * แจ้งเตือน Admin เมื่อมีการรักษาหมูป่วย
     */
    public static function notifyAdminsBatchTreatment($batchTreatment, User $reportedBy)
    {
        $admins = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();

        // Load relationships ถ้ายังไม่ได้ load
        if (!$batchTreatment->relationLoaded('batch')) {
            $batchTreatment->load('batch', 'barn', 'pen');
        }

        $batch = $batchTreatment->batch;
        $barn = $batchTreatment->barn;
        $pen = $batchTreatment->pen;

        foreach ($admins as $admin) {
            Notification::create([
                'type' => 'batch_treatment',
                'user_id' => $admin->id,
                'related_user_id' => $reportedBy->id,
                'title' => 'บันทึกการรักษาหมูป่วย',
                'message' => "มีการบันทึกการรักษา\nรุ่น: {$batch->batch_code}\nเล้า: {$barn->barn_code}\nคอก: {$pen->pen_code}\nยา: {$batchTreatment->medicine_name}\nจำนวน: {$batchTreatment->dosage} {$batchTreatment->unit}",
                'url' => url('view_batch_treatment'),
                'is_read' => false,
            ]);
        }
    }

    /**
     * แจ้งเตือน Admin เมื่อมีการขายหมู
     */
    public static function notifyAdminsPigSale($pigSale, User $reportedBy)
    {
        $admins = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();

        // Load relationship ถ้ายังไม่ได้ load
        if (!$pigSale->relationLoaded('batch')) {
            $pigSale->load('batch');
        }

        $batch = $pigSale->batch;

        foreach ($admins as $admin) {
            Notification::create([
                'type' => 'pig_sale',
                'user_id' => $admin->id,
                'related_user_id' => $reportedBy->id,
                'title' => 'บันทึกการขายหมู',
                'message' => "มีการขายหมู {$pigSale->quantity} ตัว\nรุ่น: {$batch->batch_code}\nราคารวม: " . number_format($pigSale->total_price, 2) . " บาท\nวันที่ขาย: {$pigSale->date}",
                'url' => route('pig_sales.index'),
                'is_read' => false,
            ]);
        }
    }

    /**
     * แจ้งเตือน Admin เมื่อมีการเพิ่มสินค้าเข้าคลัง
     */
    public static function notifyAdminsInventoryMovement($inventoryMovement, User $reportedBy)
    {
        $admins = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();

        // Load relationship ถ้ายังไม่ได้ load
        if (!$inventoryMovement->relationLoaded('storehouse')) {
            $inventoryMovement->load('storehouse');
        }

        $storehouse = $inventoryMovement->storehouse;
        $movementType = $inventoryMovement->change_type ?? 'in';

        // แปลประเภทการเคลื่อนไหว
        $typeText = match ($movementType) {
            'in' => 'เพิ่มสินค้าเข้า',
            'out' => 'เบิกสินค้าออก',
            default => 'ปรับปรุง'
        };

        foreach ($admins as $admin) {
            Notification::create([
                'type' => 'inventory_movement',
                'user_id' => $admin->id,
                'related_user_id' => $reportedBy->id,
                'title' => "{$typeText}คลัง",
                'message' => "รหัสสินค้า: {$storehouse->item_code}\nประเภท: {$storehouse->item_type}\nจำนวน: {$inventoryMovement->quantity} {$storehouse->unit}\nคงเหลือ: {$storehouse->stock} {$storehouse->unit}",
                'url' => route('inventory_movements.index'),
                'is_read' => false,
            ]);
        }
    }

    /**
     * แจ้งเตือน Admin เมื่อมีการบันทึกการชำระเงิน (Pig Entry Payment)
     */
    public static function notifyAdminsPigEntryPaymentRecorded($pigEntryRecord, User $recordedBy)
    {
        $admins = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();

        // Load relationship ถ้ายังไม่ได้ load
        if (!$pigEntryRecord->relationLoaded('batch')) {
            $pigEntryRecord->load('batch', 'farm');
        }

        $batch = $pigEntryRecord->batch;
        $farm = $pigEntryRecord->farm;

        foreach ($admins as $admin) {
            Notification::create([
                'type' => 'payment_recorded_pig_entry',
                'user_id' => $admin->id,
                'related_user_id' => $recordedBy->id,
                'title' => 'บันทึกการชำระเงินการรับเข้าหมู',
                'message' => "บันทึกการชำระเงิน\nฟาร์ม: {$farm->farm_name}\nรุ่น: {$batch->batch_code}\nวันที่รับเข้า: {$pigEntryRecord->pig_entry_date}\nรอการอนุมัติ",
                'url' => route('payment_approvals.index'),
                'is_read' => false,
                'related_model' => 'PigEntryRecord',
                'related_model_id' => $pigEntryRecord->id,
                'approval_status' => 'pending',
            ]);
        }
    }

    /**
     * แจ้งเตือน Admin เมื่อมีการบันทึกหมูเข้าใหม่
     */
    public static function notifyAdminsPigEntryRecorded($pigEntryRecord, User $recordedBy)
    {
        $admins = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();

        // Load relationship ถ้ายังไม่ได้ load
        if (!$pigEntryRecord->relationLoaded('batch')) {
            $pigEntryRecord->load('batch', 'farm');
        }

        $batch = $pigEntryRecord->batch;
        $farm = $pigEntryRecord->farm;

        foreach ($admins as $admin) {
            Notification::create([
                'type' => 'pig_entry_recorded',
                'user_id' => $admin->id,
                'related_user_id' => $recordedBy->id,
                'title' => 'บันทึกการรับเข้าหมูใหม่',
                'message' => "มีการบันทึกการรับเข้าหมูใหม่\nฟาร์ม: {$farm->farm_name}\nรุ่น: {$batch->batch_code}\nจำนวน: {$pigEntryRecord->total_pig_amount} ตัว\nวันที่รับเข้า: {$pigEntryRecord->pig_entry_date}\nรอการบันทึกการชำระเงิน",
                'url' => route('pig_entry_records.index'),
                'is_read' => false,
                'related_model' => 'PigEntryRecord',
                'related_model_id' => $pigEntryRecord->id,
            ]);
        }
    }

    /**
     * แจ้งเตือน Admin เมื่อมีการบันทึกการชำระเงิน (Pig Sale Payment)
     */
    public static function notifyAdminsPigSalePaymentRecorded($pigSale, User $recordedBy)
    {
        $admins = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();

        // Load relationship ถ้ายังไม่ได้ load
        if (!$pigSale->relationLoaded('batch')) {
            $pigSale->load('batch', 'farm', 'customer');
        }

        $batch = $pigSale->batch;
        $farm = $pigSale->farm;
        $customer = $pigSale->customer;

        foreach ($admins as $admin) {
            Notification::create([
                'type' => 'payment_recorded_pig_sale',
                'user_id' => $admin->id,
                'related_user_id' => $recordedBy->id,
                'title' => 'บันทึกการชำระเงินการขายหมู',
                'message' => "บันทึกการชำระเงิน\nฟาร์ม: {$farm->farm_name}\nรุ่น: {$batch->batch_code}\nผู้ซื้อ: {$pigSale->buyer_name}\nวันที่ขาย: {$pigSale->date}\nรอการอนุมัติ",
                'url' => route('payment_approvals.index'),
                'is_read' => false,
                'related_model' => 'PigSale',
                'related_model_id' => $pigSale->id,
                'approval_status' => 'pending',
            ]);
        }
    }

    /**
     * แจ้งเตือนผู้สร้างการขายเมื่อสถานะการชำระเปลี่ยน
     * ใช้เมื่อ payment_status เปลี่ยน (รอชำระ -> ชำระบางส่วน -> ชำระแล้ว)
     */
    public static function notifyUserPigSalePaymentStatusChanged($pigSale, $oldStatus, $newStatus)
    {
        // หาผู้สร้างการขาย
        $creator = User::where('name', $pigSale->created_by)->first();
        if (!$creator) {
            return;
        }

        // Load relationships ถ้ายังไม่ได้ load
        if (!$pigSale->relationLoaded('batch')) {
            $pigSale->load('batch', 'farm');
        }

        $batch = $pigSale->batch;
        $farm = $pigSale->farm;

        // กำหนดข้อความแจ้งเตือนตามสถานะ
        $statusMessage = '';
        $statusBadge = '';
        switch ($newStatus) {
            case 'ชำระแล้ว':
                $statusMessage = "การขายของคุณได้รับการชำระเงินครบแล้ว ✅";
                $statusBadge = 'ชำระแล้ว';
                break;
            case 'ชำระบางส่วน':
                $statusMessage = "การขายของคุณได้รับการชำระเงินบางส่วน (คงเหลือ " . number_format($pigSale->balance, 2) . " บาท)";
                $statusBadge = 'ชำระบางส่วน';
                break;
            case 'รอชำระ':
                $statusMessage = "การขายของคุณรอการชำระเงิน";
                $statusBadge = 'รอชำระ';
                break;
            case 'ยกเลิกการขาย':
                $statusMessage = "การขายของคุณถูกยกเลิกแล้ว ❌";
                $statusBadge = 'ยกเลิก';
                break;
            default:
                $statusMessage = "สถานะการขายเปลี่ยนเป็น: {$newStatus}";
                $statusBadge = $newStatus;
        }

        Notification::create([
            'type' => 'pig_sale_status_changed',
            'user_id' => $creator->id,
            'title' => 'สถานะการขายหมูของคุณเปลี่ยนแปลง',
            'message' => "{$statusMessage}\n\nรายละเอียด:\nฟาร์ม: {$farm->farm_name}\nรุ่น: {$batch->batch_code}\nจำนวน: {$pigSale->quantity} ตัว\nราคารวม: " . number_format($pigSale->net_total, 2) . " บาท\nสถานะ: {$statusBadge}",
            'url' => route('pig_sales.index'),
            'is_read' => false,
            'related_model' => 'PigSale',
            'related_model_id' => $pigSale->id,
        ]);
    }

    /**
     * แจ้งเตือนผู้สร้างการขายเมื่อการขายถูกยกเลิก
     */
    public static function notifyUserPigSaleCancelled($pigSale)
    {
        // หาผู้สร้างการขาย
        $creator = User::where('name', $pigSale->created_by)->first();
        if (!$creator) {
            return;
        }

        // Load relationships ถ้ายังไม่ได้ load
        if (!$pigSale->relationLoaded('batch')) {
            $pigSale->load('batch', 'farm');
        }

        $batch = $pigSale->batch;
        $farm = $pigSale->farm;

        Notification::create([
            'type' => 'pig_sale_cancelled',
            'user_id' => $creator->id,
            'title' => 'การขายหมูของคุณถูกยกเลิก',
            'message' => "❌ การขายของคุณถูกยกเลิกแล้ว\n\nรายละเอียด:\nฟาร์ม: {$farm->farm_name}\nรุ่น: {$batch->batch_code}\nจำนวน: {$pigSale->quantity} ตัว\nราคารวม: " . number_format($pigSale->net_total, 2) . " บาท",
            'url' => route('pig_sales.index'),
            'is_read' => false,
            'related_model' => 'PigSale',
            'related_model_id' => $pigSale->id,
        ]);
    }

    /**
     * แจ้งเตือนผู้สร้างการขายเมื่อการขายได้รับการอนุมัติ
     */
    public static function notifyUserPigSaleApproved($pigSale, $approvedBy)
    {
        // หาผู้สร้างการขาย
        $creator = User::where('name', $pigSale->created_by)->first();
        if (!$creator) {
            return;
        }

        // Load relationships ถ้ายังไม่ได้ load
        if (!$pigSale->relationLoaded('batch')) {
            $pigSale->load('batch', 'farm');
        }

        $batch = $pigSale->batch;
        $farm = $pigSale->farm;

        Notification::create([
            'type' => 'pig_sale_approved',
            'user_id' => $creator->id,
            'related_user_id' => $approvedBy->id ?? null,
            'title' => 'การขายหมูของคุณได้รับการอนุมัติ ✅',
            'message' => "✅ การขายของคุณได้รับการอนุมัติแล้ว\n\nอนุมัติโดย: {$approvedBy->name}\n\nรายละเอียด:\nฟาร์ม: {$farm->farm_name}\nรุ่น: {$batch->batch_code}\nจำนวน: {$pigSale->quantity} ตัว\nราคารวม: " . number_format($pigSale->net_total, 2) . " บาท",
            'url' => route('pig_sales.index'),
            'is_read' => false,
            'related_model' => 'PigSale',
            'related_model_id' => $pigSale->id,
        ]);
    }

    /**
     * หา Admin ทั้งหมด
     */
    private static function getAdmins()
    {
        return User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();
    }

    /**
     * ส่งอีเมล เมื่อผู้ใช้ได้รับการอนุมัติ
     */
    public static function sendUserApprovedEmail(User $user, User $approvedBy)
    {
        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(
                new \App\Mail\UserRegistrationApproved($user, $approvedBy)
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Send User Approved Email Error: ' . $e->getMessage());
        }
    }

    /**
     * ส่งอีเมล เมื่อผู้ใช้ถูกปฏิเสธ
     */
    public static function sendUserRejectedEmail(User $user, User $rejectedBy, $reason)
    {
        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(
                new \App\Mail\UserRegistrationRejected($user, $rejectedBy, $reason)
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Send User Rejected Email Error: ' . $e->getMessage());
        }
    }

    /**
     * ส่งอีเมล เมื่อผู้ใช้ได้รับการยกเลิกลงทะเบียน
     */
    public static function sendUserCancelledEmail(User $user, $reason = null)
    {
        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(
                new \App\Mail\UserRegistrationCancelled($user, $reason)
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Send User Cancelled Email Error: ' . $e->getMessage());
        }
    }

    /**
     * ส่งอีเมล เมื่อ Role ของผู้ใช้ถูกอัปเดท
     */
    public static function sendUserRoleUpdatedEmail(User $user, User $updatedBy, $newRole, $oldRole = null)
    {
        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(
                new \App\Mail\UserRoleUpdated($user, $updatedBy, $newRole, $oldRole)
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Send User Role Updated Email Error: ' . $e->getMessage());
        }
    }

    /**
     * แจ้งเตือนผู้สร้างการรับเข้าเมื่อการชำระได้รับการอนุมัติ
     */
    public static function notifyUserPigEntryPaymentApproved($pigEntry)
    {
        try {
            // หาผู้สร้าง
            $creator = User::where('name', $pigEntry->created_by)->first();
            if (!$creator) {
                return;
            }

            // Load relationships ถ้ายังไม่ได้ load
            if (!$pigEntry->relationLoaded('batch')) {
                $pigEntry->load('batch', 'farm');
            }

            $batch = $pigEntry->batch;
            $farm = $pigEntry->farm;

            Notification::create([
                'type' => 'pig_entry_payment_approved',
                'user_id' => $creator->id,
                'title' => '✅ การชำระเงินการรับเข้าได้รับการอนุมัติ',
                'message' => "✅ การชำระเงินของคุณได้รับการอนุมัติแล้ว\n\nรายละเอียด:\nฟาร์ม: {$farm->farm_name}\nรุ่น: {$batch->batch_code}\nวันที่รับเข้า: {$pigEntry->pig_entry_date}",
                'url' => route('pig_entry_records.index'),
                'is_read' => false,
                'related_model' => 'PigEntryRecord',
                'related_model_id' => $pigEntry->id,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Notify User Pig Entry Payment Approved Error: ' . $e->getMessage());
        }
    }

    /**
     * อัปเดตแจ้งเตือนของการแจ้งเตือนการรับเข้า เมื่อถูกลบ
     * (ไม่ลบ แต่เปลี่ยน title ให้เป็น "[ลบแล้ว]")
     */
    public static function markPigEntryNotificationsAsDeleted($pigEntryRecordId)
    {
        try {
            // หาแจ้งเตือนทั้งหมดที่เกี่ยวข้องกับ PigEntryRecord นี้
            $notifications = Notification::where('related_model', 'PigEntryRecord')
                ->where('related_model_id', $pigEntryRecordId)
                ->get();

            foreach ($notifications as $notification) {
                // เพิ่ม "[ลบแล้ว]" ในหน้า title
                if (!str_contains($notification->title, '[ลบแล้ว]')) {
                    $notification->update([
                        'title' => '[ลบแล้ว] ' . $notification->title,
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Mark Pig Entry Notifications As Deleted Error: ' . $e->getMessage());
        }
    }

    /**
     * อัปเดตแจ้งเตือนของการขายหมู เมื่อถูกยกเลิก
     * (ไม่ลบ แต่เปลี่ยน title/message ให้เป็น "ยกเลิกแล้ว")
     */
    public static function markPigSaleNotificationsAsCancelled($pigSaleId)
    {
        try {
            // หาแจ้งเตือนทั้งหมดที่เกี่ยวข้องกับ PigSale นี้
            $notifications = Notification::where('related_model', 'PigSale')
                ->where('related_model_id', $pigSaleId)
                ->where('type', '!=', 'pig_sale_cancelled') // ไม่อัปเดตแจ้งเตือนการยกเลิก
                ->get();

            foreach ($notifications as $notification) {
                // เพิ่ม "[ยกเลิกแล้ว]" ในหน้า title
                if (!str_contains($notification->title, '[ยกเลิกแล้ว]')) {
                    $notification->update([
                        'title' => '[ยกเลิกแล้ว] ' . $notification->title,
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Mark Pig Sale Notifications As Cancelled Error: ' . $e->getMessage());
        }
    }

    /**
     * อัปเดตแจ้งเตือนของ Batch เมื่อถูกยกเลิก
     * (ไม่ลบ แต่เปลี่ยน title/message ให้เป็น "[ยกเลิกแล้ว]")
     */
    public static function markBatchNotificationsAsCancelled($batchId)
    {
        try {
            // หาแจ้งเตือนทั้งหมดที่เกี่ยวข้องกับ Batch นี้
            $notifications = Notification::where('related_model', 'Batch')
                ->where('related_model_id', $batchId)
                ->get();

            foreach ($notifications as $notification) {
                // เพิ่ม "[ยกเลิกแล้ว]" ในหน้า title
                if (!str_contains($notification->title, '[ยกเลิกแล้ว]')) {
                    $notification->update([
                        'title' => '[ยกเลิกแล้ว] ' . $notification->title,
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Mark Batch Notifications As Cancelled Error: ' . $e->getMessage());
        }
    }
}
