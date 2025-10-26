<?php

namespace App\Observers;

use App\Models\Cost;
use App\Models\CostPayment;
use App\Helpers\RevenueHelper;

class CostObserver
{
    /**
     * Handle the Cost "created" event.
     * เมื่อสร้าง Cost ใหม่:
     * 1. Auto-approve เฉพาะ feed/medicine จาก inventory movements (change_type = in)
     * 2. Auto-approve wage, electric_bill, water_bill, other (สำหรับ PigEntry)
     * 3. Auto-approve shipping (ค่าขนส่งพร้อม piglet)
     * 4. piglet ต้อง manual approval
     * 5. อัปเดท Profit ของ Batch
     *
     * @param  \App\Models\Cost  $cost
     * @return void
     */
    public function created(Cost $cost)
    {
        // ✅ Auto-approve certain cost types:
        // - feed/medicine (จาก inventory movements - สินค้าเข้าคลัง)
        // - wage/electric_bill/water_bill/other (สำหรับ PigEntry)
        // - shipping (ค่าขนส่งพร้อม piglet - ไม่ต้องอนุมัติแยก)
        // ❌ piglet ต้อง manual approval (ค่อนข้างแพง)
        $autoApproveCostTypes = ['feed', 'medicine', 'wage', 'electric_bill', 'water_bill', 'other', 'shipping'];

        if (in_array($cost->cost_type, $autoApproveCostTypes)) {
            // สร้าง CostPayment ที่ approved เลย
            CostPayment::create([
                'cost_id' => $cost->id,
                'cost_type' => $cost->cost_type,
                'status' => 'approved',
                'amount' => $cost->total_price,
                'approved_by' => auth()->id() ?? 1, // Default admin user
                'approved_date' => now(),
            ]);
        }

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
