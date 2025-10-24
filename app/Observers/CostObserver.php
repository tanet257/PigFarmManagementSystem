<?php

namespace App\Observers;

use App\Models\Cost;
use App\Helpers\RevenueHelper;

class CostObserver
{
    /**
     * Handle the Cost "created" event.
     * เมื่อสร้าง Cost ใหม่ให้อัปเดท Profit ของ Batch
     *
     * @param  \App\Models\Cost  $cost
     * @return void
     */
    public function created(Cost $cost)
    {
        // ถ้ามี batch_id ให้อัปเดท profit
        if ($cost->batch_id) {
            RevenueHelper::calculateAndRecordProfit($cost->batch_id);
        }
    }

    /**
     * Handle the Cost "updated" event.
     * เมื่อแก้ไข Cost (เช่น payment_status เปลี่ยน) ให้อัปเดท Profit
     *
     * @param  \App\Models\Cost  $cost
     * @return void
     */
    public function updated(Cost $cost)
    {
        // ถ้ามี batch_id ให้อัปเดท profit
        if ($cost->batch_id) {
            RevenueHelper::calculateAndRecordProfit($cost->batch_id);
        }
    }

    /**
     * Handle the Cost "deleted" event.
     *
     * @param  \App\Models\Cost  $cost
     * @return void
     */
    public function deleted(Cost $cost)
    {
        //
    }

    /**
     * Handle the Cost "restored" event.
     *
     * @param  \App\Models\Cost  $cost
     * @return void
     */
    public function restored(Cost $cost)
    {
        //
    }

    /**
     * Handle the Cost "force deleted" event.
     *
     * @param  \App\Models\Cost  $cost
     * @return void
     */
    public function forceDeleted(Cost $cost)
    {
        //
    }
}
