<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InventoryMovement;
use App\Models\Cost;

class CheckInventoryCommand extends Command
{
    protected $signature = 'inventory:check {batch_id?}';
    protected $description = 'ตรวจสอบ InventoryMovement - หา duplicate';

    public function handle()
    {
        $batchId = $this->argument('batch_id');

        $query = InventoryMovement::where('change_type', 'in');
        if ($batchId) {
            $query->where('batch_id', $batchId);
        }

        $inventories = $query->get();

        if ($inventories->isEmpty()) {
            $this->warn('ไม่มี InventoryMovement');
            return;
        }

        $this->info('Total InventoryMovement (in): ' . $inventories->count());

        // หา duplicate (same batch_id + storehouse_id)
        $grouped = $inventories->groupBy(fn($i) => $i->batch_id . '-' . $i->storehouse_id);
        $duplicates = $grouped->filter(fn($g) => $g->count() > 1);

        if ($duplicates->count() > 0) {
            $this->warn('⚠️ พบ Duplicate InventoryMovement: ' . $duplicates->count() . ' groups');

            foreach ($duplicates as $key => $items) {
                $this->line('Group: ' . $key . ' (' . $items->count() . ' records)');
                foreach ($items as $item) {
                    $this->line('  - ID: ' . $item->id . ', Qty: ' . $item->quantity . ', Date: ' . $item->created_at);
                }
            }
        } else {
            $this->info('✅ ไม่มี Duplicate');
        }

        // ตรวจสอบ Cost จำนวน
        $costCount = Cost::where('cost_type', 'feed')->orWhere('cost_type', 'medicine')->count();
        $this->info('Cost (feed + medicine): ' . $costCount);
    }
}
