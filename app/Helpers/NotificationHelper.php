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
                'title' => '🆕 ผู้ใช้ใหม่ลงทะเบียน',
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
            'title' => '✅ บัญชีของคุณได้รับการอนุมัติแล้ว',
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
            'title' => '❌ บัญชีของคุณถูกปฏิเสธ',
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
                'title' => '💀 รายงานหมูตาย',
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
                'title' => '💊 บันทึกการรักษาหมูป่วย',
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
                'title' => '💰 บันทึกการขายหมู',
                'message' => "มีการขายหมู {$pigSale->quantity} ตัว\nรุ่น: {$batch->batch_code}\nราคารวม: " . number_format($pigSale->total_price, 2) . " บาท\nวันที่ขาย: {$pigSale->date}",
                'url' => route('pig_sale.index'),
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
            'in' => '📥 เพิ่มสินค้าเข้า',
            'out' => '📤 เบิกสินค้าออก',
            default => '🔄 ปรับปรุง'
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
     * หา Admin ทั้งหมด
     */
    private static function getAdmins()
    {
        return User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();
    }
}
