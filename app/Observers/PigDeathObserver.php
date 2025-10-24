<?php

namespace App\Observers;

use App\Models\PigDeath;
use App\Helpers\RevenueHelper;

class PigDeathObserver
{
    /**
     * Handle the PigDeath "created" event.
     */
    public function created(PigDeath $pigDeath): void
    {
        // อัปเดท profit เมื่อมีการเพิ่มหมูตาย
        if ($pigDeath->batch_id) {
            RevenueHelper::calculateAndRecordProfit($pigDeath->batch_id);
        }
    }

    /**
     * Handle the PigDeath "updated" event.
     */
    public function updated(PigDeath $pigDeath): void
    {
        // อัปเดท profit เมื่อมีการแก้ไขจำนวนหมูตาย
        if ($pigDeath->batch_id) {
            RevenueHelper::calculateAndRecordProfit($pigDeath->batch_id);
        }
    }

    /**
     * Handle the PigDeath "deleted" event.
     */
    public function deleted(PigDeath $pigDeath): void
    {
        // อัปเดท profit เมื่อมีการลบหมูตาย
        if ($pigDeath->batch_id) {
            RevenueHelper::calculateAndRecordProfit($pigDeath->batch_id);
        }
    }
}
