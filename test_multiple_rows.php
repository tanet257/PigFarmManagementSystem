<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\DairyController;
use Illuminate\Support\Facades\Log;

$controller = new DairyController();

// Test: Upload หลาย row feed พร้อมกัน
$request = new Request([
    'farm_id' => 2,
    'batch_id' => 53,  // ใช้ batch 53
    'feed_use' => [
        // Row 1
        [
            'farm_id' => 2,
            'batch_id' => 53,
            'date' => date('d/m/Y H:i', strtotime('-1 day')),
            'barn_id' => 1,
            'barn_pen' => json_encode([['barn_id' => 1, 'pen_id' => 1]]),
            'item_code' => 'F931L-2',        // ✅ Farm 2 Feed
            'item_name' => 'อาหารหมูเล็ก',
            'item_type' => 'feed',
            'quantity' => 5,
            'note' => 'Row 1: 5 kg'
        ],
        // Row 2
        [
            'farm_id' => 2,
            'batch_id' => 53,
            'date' => date('d/m/Y H:i'),
            'barn_id' => 1,
            'barn_pen' => json_encode([['barn_id' => 1, 'pen_id' => 2]]),
            'item_code' => 'F992-2',        // ✅ Farm 2 Feed
            'item_name' => 'อาหารหมูกลาง',
            'item_type' => 'feed',
            'quantity' => 3,
            'note' => 'Row 2: 3 kg'
        ],
        // Row 3
        [
            'farm_id' => 2,
            'batch_id' => 53,
            'date' => date('d/m/Y H:i', strtotime('+1 day')),
            'barn_id' => 2,
            'barn_pen' => json_encode([['barn_id' => 2, 'pen_id' => 3]]),
            'item_code' => 'F993-2',        // ✅ Farm 2 Feed
            'item_name' => 'อาหารหมูใหญ่',
            'item_type' => 'feed',
            'quantity' => 7,
            'note' => 'Row 3: 7 kg'
        ]
    ],
    'medicine_use' => [],
    'dead_pig' => []
]);

echo "=== Test: Upload 3 rows feed พร้อมกัน ===\n\n";

try {
    $response = $controller->uploadDairy($request);
    echo "✅ Success: All 3 rows uploaded\n\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}

// ตรวจสอบผลลัพธ์
echo "=== ตรวจสอบผลลัพธ์ ===\n";
echo shell_exec('php << \'EOF\'
<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Batch;
use App\Models\DairyRecord;
use App\Models\DairyStorehouseUse;
use App\Models\InventoryMovement;
use App\Models\Profit;

$batchId = 53;

echo "Batch 53 Data:\n";
echo "  DairyRecord Count: " . DairyRecord::where("batch_id", $batchId)->count() . "\n";

$dairyIds = DairyRecord::where("batch_id", $batchId)->pluck("id");
echo "  DairyStorehouseUse Count: " . DairyStorehouseUse::whereIn("dairy_record_id", $dairyIds)->count() . "\n";
echo "  InventoryMovement (OUT) Count: " . InventoryMovement::where("batch_id", $batchId)->where("change_type", "out")->count() . "\n";

$profit = Profit::where("batch_id", $batchId)->first();
if ($profit) {
    echo "\nProfit KPI:\n";
    echo "  ADG: {$profit->adg}\n";
    echo "  FCR: {$profit->fcr}\n";
    echo "  FCG: {$profit->fcg}\n";
    echo "  Total Feed KG: {$profit->total_feed_kg}\n";
} else {
    echo "  ❌ No Profit record!\n";
}
?>
EOF
');
?>
