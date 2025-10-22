<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Batch;
use App\Models\PigEntryRecord;
use App\Models\PigEntryDetail;
use App\Models\PigDeath;
use Illuminate\Support\Facades\DB;

echo "=== ตรวจสอบจำนวนหมูทั้งหมด Batch f1-b2501 ===\n\n";

$batch = Batch::where('batch_code', 'ฺฺฺf1-b2501')->first();
if (!$batch) {
    echo "✗ ไม่พบ Batch\n";
    exit;
}

echo "Batch: {$batch->batch_code} (ID: {$batch->id})\n";
echo "Status: {$batch->status}\n";
echo "Initial Quantity: {$batch->initial_quantity}\n\n";

// 1. ดู PigEntry ทั้งหมด (รวม cancelled)
$entries = PigEntryRecord::where('batch_id', $batch->id)->get();
echo "=== PigEntry Records ({$entries->count()}) ===\n";

$totalEntryPigs = 0;
$activePigs = 0;
foreach ($entries as $entry) {
    $details = PigEntryDetail::where('pig_entry_id', $entry->id)->get();
    $entryQuantity = $details->sum('quantity');
    $totalEntryPigs += $entryQuantity;

    $cancelled = $entry->status === 'cancelled' ? '[CANCELLED]' : '[ACTIVE]';
    echo "{$cancelled} Entry ID: {$entry->id}, Quantity: {$entryQuantity}, Status: {$entry->status}\n";

    if ($entry->status !== 'cancelled') {
        $activePigs += $entryQuantity;
    }
}

echo "\nTotal Pigs from PigEntry: {$totalEntryPigs}\n";
echo "Active Pigs: {$activePigs}\n";

// 2. ดู PigDeath
$deaths = PigDeath::where('batch_id', $batch->id)->get();
$totalDeaths = $deaths->sum('quantity');
echo "\n=== PigDeath ===\n";
echo "Total Deaths: {$totalDeaths} pigs ({$deaths->count()} records)\n";

// 3. ดู PigSale
$sales = \App\Models\PigSale::where('batch_id', $batch->id)->get();
$totalSales = $sales->sum('quantity');
echo "\n=== PigSale ===\n";
echo "Total Sales: {$totalSales} pigs ({$sales->count()} records)\n";
foreach ($sales as $sale) {
    echo "  - Sale ID: {$sale->id}, Qty: {$sale->quantity}, Status: {$sale->status}, Payment: {$sale->payment_status}\n";
}

// 4. ดู batch_pen_allocations (current inventory)
$allocations = DB::table('batch_pen_allocations')
    ->where('batch_id', $batch->id)
    ->get();

$currentInventory = $allocations->sum('current_quantity');
echo "\n=== Current Inventory (batch_pen_allocations) ===\n";
echo "Total Current Quantity: {$currentInventory} pigs ({$allocations->count()} records)\n";

foreach ($allocations as $alloc) {
    $pen = \App\Models\Pen::find($alloc->pen_id);
    $barn = \App\Models\Barn::find($alloc->barn_id);
    echo "  - Barn: {$barn->barn_code}, Pen: {$pen->pen_code}, Current: {$alloc->current_quantity}\n";
}

// 5. คำนวณเชค
echo "\n=== การคำนวณเชค ===\n";
echo "Initial: {$batch->initial_quantity}\n";
echo "- Deaths: {$totalDeaths}\n";
echo "- Sales: {$totalSales}\n";
echo "= Expected Current: " . ($batch->initial_quantity - $totalDeaths - $totalSales) . "\n";
echo "Actual Current: {$currentInventory}\n";

if ($batch->initial_quantity - $totalDeaths - $totalSales == $currentInventory) {
    echo "✓ Balance OK\n";
} else {
    echo "✗ MISMATCH! Difference: " . (($batch->initial_quantity - $totalDeaths - $totalSales) - $currentInventory) . "\n";
}
