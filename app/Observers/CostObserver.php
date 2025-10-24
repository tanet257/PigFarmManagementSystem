<?php

namespace App\Observers;

use App\Models\Cost;
use App\Helpers\RevenueHelper;

class CostObserver
{
    /**
     * Handle the Cost "created" event.
     * ❌ ลบ: ไม่ควรบันทึก Profit ทุกครั้งเพิ่มต้นทุน (ต้องบันทึกเมื่อ Batch สิ้นสุดเท่านั้น)
     *
     * @param  \App\Models\Cost  $cost
     * @return void
     */
    public function created(Cost $cost)
    {
        // ❌ ไม่ทำอะไร
    }

    /**
     * Handle the Cost "updated" event.
     * ❌ ลบ: ไม่ควรบันทึก Profit ทุกครั้งแก้ไขต้นทุน (ต้องบันทึกเมื่อ Batch สิ้นสุดเท่านั้น)
     *
     * @param  \App\Models\Cost  $cost
     * @return void
     */
    public function updated(Cost $cost)
    {
        // ❌ ไม่ทำอะไร
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
