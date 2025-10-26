<?php

namespace App\Observers;

use App\Models\PigDeath;
use App\Models\Notification;
use App\Helpers\RevenueHelper;
use Illuminate\Support\Facades\Log;

class PigDeathObserver
{
    /**
     * Handle the PigDeath "created" event.
     */
    public function created(PigDeath $pigDeath): void
    {
        try {
            Log::info('PigDeathObserver::created triggered', ['pig_death_id' => $pigDeath->id]);

            // ✅ NEW: ส่ง notification ให้ admin
            if ($pigDeath->batch_id) {
                $batch = $pigDeath->batch;
                $recordedByName = $pigDeath->recordedBy?->name ?? 'ไม่ระบุ';

                Log::info('Creating notification', [
                    'batch_id' => $pigDeath->batch_id,
                    'batch_code' => $batch->batch_code ?? 'N/A',
                    'recorded_by_name' => $recordedByName,
                    'quantity' => $pigDeath->quantity,
                ]);

                $notification = Notification::create([
                    'user_id'   => null, // ส่งให้ admin ทั้งหมด
                    'title'     => 'บันทึกหมูตาย',
                    'message'   => "บันทึกหมูตาย {$pigDeath->quantity} ตัว (รุ่น: {$batch->batch_code}, ผู้บันทึก: {$recordedByName})",
                    'type'      => 'pig_death',
                    'related_model_id' => $pigDeath->id,  // ✅ FIX: ใช้ related_model_id ไม่ใช่ related_id
                    'is_read'    => false,  // ✅ FIX: ใช้ is_read ไม่ใช่ status
                ]);

                Log::info('Notification created successfully', [
                    'notification_id' => $notification->id,
                    'type' => $notification->type,
                ]);
            }

            // 🔴 BUG FIX: ไม่ควรคำนวณ profit ตรงนี้
            // Profit จะถูกคำนวณเฉพาะตอน approve Payment (PaymentApprovalController)
        } catch (\Exception $e) {
            // Log error แต่ไม่ให้ระงับการบันทึก PigDeath
            Log::error('PigDeathObserver Error', [
                'message' => $e->getMessage(),
                'pig_death_id' => $pigDeath->id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Handle the PigDeath "updated" event.
     */
    public function updated(PigDeath $pigDeath): void
    {
        // 🔴 BUG FIX: ไม่ควรคำนวณ profit ตรงนี้
        // Profit จะถูกคำนวณเฉพาะตอน approve Payment (PaymentApprovalController)
    }

    /**
     * Handle the PigDeath "deleted" event.
     */
    public function deleted(PigDeath $pigDeath): void
    {
        // 🔴 BUG FIX: ไม่ควรคำนวณ profit ตรงนี้
        // Profit จะถูกคำนวณเฉพาะตอน approve Payment (PaymentApprovalController)
    }
}

