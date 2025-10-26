<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InventoryMovement;
use App\Models\Cost;
use App\Models\CostPayment;
use Illuminate\Support\Facades\DB;

class ClearBatch53Command extends Command
{
    protected $signature = 'batch:clear-53';
    protected $description = 'ลบ InventoryMovement, Cost, CostPayment ของ Batch 53';

    public function handle()
    {
        $this->warn('⚠️ คำเตือน: ลบ Batch 53 ทั้งหมด!');

        if (!$this->confirm('ยืนยันหรือไม่?')) {
            $this->info('ยกเลิก');
            return;
        }

        $batchId = 53;

        DB::beginTransaction();
        try {
            // ดึง InventoryMovement ทั้งหมดของ Batch 53
            $inventories = InventoryMovement::where('batch_id', $batchId)->get();
            $this->info('InventoryMovement: ' . $inventories->count());

            // ดึง Cost ทั้งหมดของ Batch 53
            $costs = Cost::where('batch_id', $batchId)->get();
            $this->info('Cost: ' . $costs->count());

            // ลบ CostPayment ที่เกี่ยวกับ Cost เหล่านี้
            CostPayment::whereIn('cost_id', $costs->pluck('id'))->delete();
            $this->info('✅ ลบ CostPayment เรียบร้อย');

            // ลบ Cost
            $costs->each->delete();
            $this->info('✅ ลบ Cost เรียบร้อย');

            // ลบ InventoryMovement
            $inventories->each->delete();
            $this->info('✅ ลบ InventoryMovement เรียบร้อย');

            DB::commit();
            $this->info('✅ เรียบร้อย - Batch 53 ล้างเรียบร้อย');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }
}
