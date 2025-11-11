<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Batch, App\Models\Cost, App\Models\DairyRecord, App\Models\PigSale, App\Models\Profit;

$batch = Batch::where('status','closed')->orderBy('id','desc')->first();

echo "=== COMPLETED BATCH SUMMARY ===\n";
echo "Batch Code: " . $batch->batch_code . " (ID: {$batch->id})\n";
echo "Status: " . $batch->status . "\n";
echo "Total Pigs: {$batch->total_pig_amount}\n";
echo "Start Weight: {$batch->starting_avg_weight}kg\n";
echo "End Weight: {$batch->average_weight_per_pig}kg\n";
echo "Period: " . $batch->start_date->format('Y-m-d') . " → " . $batch->end_date->format('Y-m-d') . "\n";
echo "\n=== RECORDS ===\n";
echo "Costs: " . Cost::where('batch_id',$batch->id)->count() . "\n";
echo "Dairy Records: " . DairyRecord::where('batch_id',$batch->id)->count() . "\n";
echo "Pig Sales: " . PigSale::where('batch_id',$batch->id)->count() . "\n";
echo "Profits: " . Profit::where('batch_id',$batch->id)->count() . "\n";

$profit = Profit::where('batch_id',$batch->id)->first();
if($profit) {
    echo "\n=== PROFIT RECORD ===\n";
    echo "  Revenue: ฿" . number_format($profit->total_revenue, 0) . "\n";
    echo "  Cost: ฿" . number_format($profit->total_cost, 0) . "\n";
    echo "  Gross Profit: ฿" . number_format($profit->gross_profit, 0) . "\n";
    echo "  Margin: " . round($profit->profit_margin_percent, 2) . "%\n";
    echo "  ADG: " . round($profit->adg, 2) . " kg/day\n";
    echo "  FCR: " . round($profit->fcr, 2) . "\n";
    echo "  FCG: ฿" . round($profit->fcg, 2) . "\n";
} else {
    echo "\n⚠️ No profit record found!\n";
}
