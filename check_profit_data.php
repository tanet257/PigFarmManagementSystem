<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Database Check ===\n";
echo "Total batches: " . \App\Models\Batch::count() . "\n";
echo "Total pig entries: " . \App\Models\PigEntryRecord::count() . "\n";
echo "Total pig sales: " . \App\Models\PigSale::count() . "\n";
echo "Total costs: " . \App\Models\Cost::count() . "\n";
echo "Total revenues: " . \App\Models\Revenue::count() . "\n";
echo "Total profits: " . \App\Models\Profit::count() . "\n";

// ดึงข้อมูล batch ทั้งหมด
$allBatches = \App\Models\Batch::all();
echo "\n=== All Batches ===\n";
foreach ($allBatches as $batch) {
    echo "Batch ID: {$batch->id}, Code: {$batch->batch_code}, Status: {$batch->status}\n";

    $pigEntries = \App\Models\PigEntryRecord::where('batch_id', $batch->id)->count();
    $pigSales = \App\Models\PigSale::where('batch_id', $batch->id)->count();
    $costs = \App\Models\Cost::where('batch_id', $batch->id)->sum('total_price');
    $revenues = \App\Models\Revenue::where('batch_id', $batch->id)->sum('net_revenue');
    $profit = \App\Models\Profit::where('batch_id', $batch->id)->first();

    echo "  - Pig Entries: {$pigEntries}\n";
    echo "  - Pig Sales: {$pigSales}\n";
    echo "  - Total Costs: {$costs}\n";
    echo "  - Total Revenues: {$revenues}\n";
    echo "  - Profit Record: " . ($profit ? "Yes (ID: {$profit->id})" : "NO") . "\n";
}

// ดึงข้อมูล batch ที่มีข้อมูล
$batches = \App\Models\Batch::where('status', 'กำลังเลี้ยง')->get();
echo "\n=== Active Batches ===\n";
foreach ($batches as $batch) {
    echo "Batch ID: {$batch->id}, Code: {$batch->batch_code}, Status: {$batch->status}\n";

    $costs = \App\Models\Cost::where('batch_id', $batch->id)->sum('total_price');
    $revenues = \App\Models\Revenue::where('batch_id', $batch->id)->sum('net_revenue');
    $profit = \App\Models\Profit::where('batch_id', $batch->id)->first();

    echo "  - Total Costs: {$costs}\n";
    echo "  - Total Revenues: {$revenues}\n";
    echo "  - Profit Record: " . ($profit ? "Yes (ID: {$profit->id})" : "NO") . "\n";
}
?>
