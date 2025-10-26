<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Batch;
use App\Models\Profit;

$batch = Batch::find(53);

echo "=== Batch 53 Data ===\n";
echo "starting_avg_weight: " . ($batch->starting_avg_weight ?? 'NULL') . "\n";
echo "average_weight_per_pig: " . ($batch->average_weight_per_pig ?? 'NULL') . "\n";
echo "current_quantity: " . ($batch->current_quantity ?? 'NULL') . "\n";
echo "total_pig_amount: " . ($batch->total_pig_amount ?? 'NULL') . "\n";
echo "status: " . ($batch->status ?? 'NULL') . "\n";

$profit = Profit::where('batch_id', 53)->first();
echo "\n=== Profit 53 Data ===\n";
echo "total_pig_sold: " . ($profit->total_pig_sold ?? 'NULL') . "\n";
echo "days_in_farm: " . ($profit->days_in_farm ?? 'NULL') . "\n";
echo "total_revenue: " . ($profit->total_revenue ?? 'NULL') . "\n";

// ตรวจสอบ quantity ใน DairyStorehouseUse
echo "\n=== DairyStorehouseUse Data ===\n";
$dairyIds = \App\Models\DairyRecord::where('batch_id', 53)->pluck('id');
$uses = \App\Models\DairyStorehouseUse::whereIn('dairy_record_id', $dairyIds)->get();
echo "Total Uses: " . $uses->count() . "\n";
foreach ($uses as $use) {
    $storehouseName = $use->storehouse ? $use->storehouse->item_name : 'NULL';
    echo "Quantity: {$use->quantity}, Storehouse ID: {$use->storehouse_id}, Storehouse: {$storehouseName}\n";
}
?>
