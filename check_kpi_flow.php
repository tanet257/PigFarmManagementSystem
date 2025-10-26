<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Batch;
use App\Models\DairyRecord;
use App\Models\DairyStorehouseUse;
use App\Models\InventoryMovement;
use App\Models\Profit;

$batchId = 51; // ใช้ batch ที่เพิ่งบันทึก

echo "=== ตรวจสอบข้อมูล Batch 51 ===\n\n";

// Batch info
$batch = Batch::find($batchId);
if ($batch) {
    echo "✅ Batch: {$batch->batch_code}\n";
    echo "   Status: {$batch->status}\n";
    echo "   Current Quantity: {$batch->current_quantity}\n";
}

// DairyRecord count
$dairyCount = DairyRecord::where('batch_id', $batchId)->count();
echo "\n📋 DairyRecord Count: $dairyCount\n";

if ($dairyCount > 0) {
    // DairyStorehouseUse (Feed/Medicine)
    $dairyRecordIds = DairyRecord::where('batch_id', $batchId)->pluck('id');
    $dairyStorehouseUseCount = DairyStorehouseUse::whereIn('dairy_record_id', $dairyRecordIds)->count();
    echo "   └─ DairyStorehouseUse (Feed/Medicine) Count: $dairyStorehouseUseCount\n";

    if ($dairyStorehouseUseCount > 0) {
        $uses = DairyStorehouseUse::whereIn('dairy_record_id', $dairyRecordIds)->with('storehouse')->get();
        foreach ($uses as $use) {
            echo "      • {$use->storehouse->item_name} (Code: {$use->storehouse->item_code}): {$use->quantity} {$use->storehouse->unit}\n";
        }
    }
}

// InventoryMovement (out)
$inventoryOut = InventoryMovement::where('batch_id', $batchId)
    ->where('change_type', 'out')
    ->get();

echo "\n📦 InventoryMovement (OUT) Count: " . $inventoryOut->count() . "\n";
if ($inventoryOut->count() > 0) {
    foreach ($inventoryOut as $inv) {
        echo "   • Quantity: {$inv->quantity}, Date: {$inv->date}\n";
    }
}

// Profit record
$profit = Profit::where('batch_id', $batchId)->first();
echo "\n📊 Profit Record:\n";
if ($profit) {
    echo "   ADG: {$profit->adg}\n";
    echo "   FCR: {$profit->fcr}\n";
    echo "   FCG: {$profit->fcg}\n";
    echo "   Feed Cost: {$profit->feed_cost}\n";
    echo "   Total Feed KG: {$profit->total_feed_kg}\n";
} else {
    echo "   ❌ Profit record not found!\n";
}

echo "\n✅ Check complete\n";
?>
