<?php

namespace App\Observers;

use App\Models\InventoryMovement;
use App\Helpers\RevenueHelper;

class InventoryMovementObserver
{
    /**
     * Handle the InventoryMovement "created" event.
     * เมื่อสินค้าเข้าคลัง (change_type = 'in') จะสร้าง Cost record อัตโนมัติ
     *
     * @param  \App\Models\InventoryMovement  $inventoryMovement
     * @return void
     */
    public function created(InventoryMovement $inventoryMovement)
    {
        // บันทึกต้นทุนจากการใช้สินค้า storehouse
        RevenueHelper::recordStorehouseCost($inventoryMovement);
    }

    /**
     * Handle the InventoryMovement "updated" event.
     *
     * @param  \App\Models\InventoryMovement  $inventoryMovement
     * @return void
     */
    public function updated(InventoryMovement $inventoryMovement)
    {
        //
    }

    /**
     * Handle the InventoryMovement "deleted" event.
     *
     * @param  \App\Models\InventoryMovement  $inventoryMovement
     * @return void
     */
    public function deleted(InventoryMovement $inventoryMovement)
    {
        //
    }

    /**
     * Handle the InventoryMovement "restored" event.
     *
     * @param  \App\Models\InventoryMovement  $inventoryMovement
     * @return void
     */
    public function restored(InventoryMovement $inventoryMovement)
    {
        //
    }

    /**
     * Handle the InventoryMovement "force deleted" event.
     *
     * @param  \App\Models\InventoryMovement  $inventoryMovement
     * @return void
     */
    public function forceDeleted(InventoryMovement $inventoryMovement)
    {
        //
    }
}
