<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Profit;

class CheckProfitDetailCommand extends Command
{
    protected $signature = 'profit:check-detail {batch_id?}';
    protected $description = 'ตรวจสอบ Profit detail - ทั้งหมด cost type';

    public function handle()
    {
        $batchId = $this->argument('batch_id');

        $query = Profit::query();
        if ($batchId) {
            $query->where('batch_id', $batchId);
        }

        $profits = $query->get();

        if ($profits->isEmpty()) {
            $this->warn('ไม่มี Profit');
            return;
        }

        foreach ($profits as $profit) {
            $this->info('=== Batch ID: ' . $profit->batch_id . ' ===');
            $this->table(
                ['Cost Type', 'Amount'],
                [
                    ['Feed', $profit->feed_cost],
                    ['Medicine', $profit->medicine_cost],
                    ['Transport', $profit->transport_cost],
                    ['Labor', $profit->labor_cost],
                    ['Utility', $profit->utility_cost],
                    ['Other', $profit->other_cost],
                    ['Total Cost', $profit->total_cost],
                    ['Total Revenue', $profit->total_revenue],
                    ['Gross Profit', $profit->gross_profit],
                ]
            );

            // ตรวจสอบ ProfitDetail
            $details = \App\Models\ProfitDetail::where('profit_id', $profit->id)->get();
            $this->info('ProfitDetail count: ' . $details->count());

            if ($details->count() > 0) {
                $this->table(
                    ['ID', 'Category', 'Item', 'Amount'],
                    $details->map(fn($d) => [$d->id, $d->cost_category, $d->item_name, $d->amount])->toArray()
                );
            }
        }
    }
}
