<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cost;

class CheckCostCommand extends Command
{
    protected $signature = 'cost:check {batch_id?}';
    protected $description = 'ตรวจสอบ Cost โดย cost_type';

    public function handle()
    {
        $batchId = $this->argument('batch_id');

        $query = Cost::query();
        if ($batchId) {
            $query->where('batch_id', $batchId);
        }

        $costs = $query->get();

        if ($costs->isEmpty()) {
            $this->warn('ไม่มี Cost');
            return;
        }

        // จัดกลุ่มตาม batch
        $grouped = $costs->groupBy('batch_id');

        foreach ($grouped as $batch => $costGroup) {
            $this->info('=== Batch ID: ' . $batch . ' ===');

            // จัดกลุ่มตาม cost_type
            $byType = $costGroup->groupBy('cost_type');

            foreach ($byType as $type => $items) {
                $subtotal = $items->sum('total_price');
                $this->line($type . ': ' . count($items) . ' items, Total: ฿' . number_format($subtotal, 2));
            }

            $total = $costGroup->sum('total_price');
            $this->info('Grand Total: ฿' . number_format($total, 2));

            $this->table(
                ['ID', 'Type', 'Item', 'Qty', 'Unit', 'Price/Unit', 'Total'],
                $costGroup->map(fn($c) => [$c->id, $c->cost_type, $c->item_code, $c->quantity, $c->unit, number_format($c->price_per_unit, 2), number_format($c->total_price, 2)])->toArray()
            );
        }
    }
}
