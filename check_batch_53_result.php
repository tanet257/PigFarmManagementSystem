<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Batch;
use App\Models\DairyRecord;
use App\Models\DairyStorehouseUse;
use App\Models\InventoryMovement;
use App\Models\Profit;

$batchId = 53;

echo "=== ตรวจสอบผลลัพธ์ Batch 53 ===\n\n";

echo "DairyRecord Count: " . DairyRecord::where("batch_id", $batchId)->count() . "\n";

$dairyIds = DairyRecord::where("batch_id", $batchId)->pluck("id");
$dairyStorehouseCount = DairyStorehouseUse::whereIn("dairy_record_id", $dairyIds)->count();
echo "DairyStorehouseUse Count: " . $dairyStorehouseCount . "\n";

$inventoryOutCount = InventoryMovement::where("batch_id", $batchId)->where("change_type", "out")->count();
echo "InventoryMovement (OUT) Count: " . $inventoryOutCount . "\n";

$profit = Profit::where("batch_id", $batchId)->first();
if ($profit) {
    echo "\n✅ Profit KPI:\n";
    echo "  ADG: " . round($profit->adg, 3) . "\n";
    echo "  FCR: " . round($profit->fcr, 3) . "\n";
    echo "  FCG: " . round($profit->fcg, 2) . "\n";
    echo "  Total Feed KG: " . round($profit->total_feed_kg, 2) . "\n";
    echo "  Feed Cost: ฿" . number_format($profit->feed_cost, 2) . "\n";
} else {
    echo "  ❌ No Profit record!\n";
}
?>
