<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Batch;
use App\Models\BatchMetric;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RecalcBatchMetrics extends Command
{
    protected $signature = 'batch:recalc-metrics {--batch=} {--since=} {--until=} {--chunk=100}';

    protected $description = 'Recalculate batch metrics (current_quantity, averages, and batch_metrics including FCR/FCG/ADG)';

    public function handle()
    {
        $batchId = $this->option('batch');
        $since = $this->option('since') ? Carbon::parse($this->option('since')) : null;
        $until = $this->option('until') ? Carbon::parse($this->option('until')) : null;
        $chunk = (int)$this->option('chunk');

        $query = Batch::query();
        if ($batchId) $query->where('id', $batchId);

        $this->info('Starting batch metrics recalculation');

        $processed = 0;

        $query->chunkById($chunk, function($batches) use (&$processed, $since, $until) {
            foreach ($batches as $batch) {
                DB::transaction(function() use ($batch, $since, $until, &$processed) {
                    // --- 1. Current quantity ---
                    $entries = DB::table('pig_entry_records')->where('batch_id', $batch->id)
                        ->when($since, fn($q) => $q->where('date', '>=', $since))
                        ->when($until, fn($q) => $q->where('date', '<=', $until))
                        ->sum('quantity');

                    $sells = DB::table('pig_sells')->where('batch_id', $batch->id)
                        ->when($since, fn($q) => $q->where('date', '>=', $since))
                        ->when($until, fn($q) => $q->where('date', '<=', $until))
                        ->sum('quantity');

                    $deaths = DB::table('pig_deaths')->where('batch_id', $batch->id)
                        ->when($since, fn($q) => $q->where('date', '>=', $since))
                        ->when($until, fn($q) => $q->where('date', '<=', $until))
                        ->sum('quantity');

                    $starting = $batch->starting_quantity ?? 0;
                    $current = max($starting + $entries - $sells - $deaths, 0);

                    // --- 2. Average weight & price ---
                    $avgWeight = DB::table('pig_sells')->where('batch_id', $batch->id)
                        ->whereNotNull('weight')
                        ->when($since, fn($q) => $q->where('date', '>=', $since))
                        ->when($until, fn($q) => $q->where('date', '<=', $until))
                        ->avg('weight');

                    if ($avgWeight === null && isset($batch->average_weight_per_pig)) {
                        $avgWeight = $batch->average_weight_per_pig;
                    }

                    $soldValue = DB::table('pig_sells')->where('batch_id', $batch->id)
                        ->when($since, fn($q) => $q->where('date', '>=', $since))
                        ->when($until, fn($q) => $q->where('date', '<=', $until))
                        ->select(DB::raw('COALESCE(SUM(price * quantity),0) as total_value'))
                        ->value('total_value');

                    $avgPrice = ($sells > 0 && $soldValue > 0) ? ($soldValue / $sells) : null;

                    // --- 3. Total feed used & total feed cost ---
                    $feedData = DB::table('dairy_storehouse_uses')
                        ->join('store_houses', 'dairy_storehouse_uses.storehouse_id', '=', 'store_houses.id')
                        ->where('dairy_storehouse_uses.batch_id', $batch->id)
                        ->when($since, fn($q) => $q->where('dairy_storehouse_uses.date', '>=', $since))
                        ->when($until, fn($q) => $q->where('dairy_storehouse_uses.date', '<=', $until))
                        ->where('store_houses.item_type', 'feed')
                        ->select(
                            DB::raw('SUM(dairy_storehouse_uses.quantity) as total_quantity'),
                            DB::raw('SUM(dairy_storehouse_uses.quantity * store_houses.price) as total_cost')
                        )
                        ->first();

                    $totalFeed = $feedData->total_quantity ?? 0;
                    $totalFeedCost = $feedData->total_cost ?? 0;

                    // --- 4. Total gain, ADG, FCR, FCG ---
                    $initial_qty = $batch->initial_quantity ?? null;
                    $initial_avg_weight = $batch->initial_average_weight ?? null;
                    $final_qty = $current;
                    $final_avg_weight = $avgWeight;

                    $total_gain = null;
                    if ($initial_qty !== null && $initial_avg_weight !== null && $final_avg_weight !== null) {
                        $initial_total_weight = $initial_qty * $initial_avg_weight;
                        $final_total_weight = $final_qty * $final_avg_weight;
                        $total_gain = $final_total_weight - $initial_total_weight;
                    }

                    $startDate = $batch->start_date ? Carbon::parse($batch->start_date) : null;
                    $days = $startDate ? max(1, Carbon::now()->diffInDays($startDate)) : null;

                    $adg = ($total_gain !== null && $days) ? $total_gain / $days : null;
                    $fcr = ($total_gain !== null && $total_gain > 0) ? $totalFeed / $total_gain : null;
                    $fcg = ($total_gain !== null && $total_gain > 0) ? $totalFeedCost / $total_gain : null;

                    // --- 5. Total mortality ---
                    $totalMortality = $deaths;

                    // --- 6. Update batch ---
                    $batch->current_quantity = $current;
                    if ($avgWeight !== null) $batch->average_weight_per_pig = $avgWeight;
                    if ($avgPrice !== null) $batch->average_price_per_pig = $avgPrice;
                    $batch->save();

                    // --- 7. Update or create metric row ---
                    BatchMetric::updateOrCreate(
                        ['batch_id' => $batch->id],
                        [
                            'adg' => $adg,
                            'fcr' => $fcr,
                            'fcg' => $fcg,
                            'total_feed_used' => $totalFeed,
                            'total_feed_cost' => $totalFeedCost,
                            'total_mortality' => $totalMortality,
                        ]
                    );

                    $processed++;
                    $this->info("Processed batch {$batch->id} (current={$current}, feed={$totalFeed}, FCR={$fcr}, FCG={$fcg})");
                });
            }
        });

        $this->info('Recalculation complete. Processed: ' . $processed . ' batches.');
        return 0;
    }
}
