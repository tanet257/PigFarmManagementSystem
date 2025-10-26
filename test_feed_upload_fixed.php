<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\DairyController;
use Illuminate\Support\Facades\Log;

$controller = new DairyController();

// สร้าง mock request - feed use ต้องมี item_code AND barn_id
$request = new Request([
    'farm_id' => 2,
    'batch_id' => 51,
    'feed_use' => [
        [
            'farm_id' => 2,
            'batch_id' => 51,
            'date' => date('d/m/Y H:i'),
            'barn_id' => 1,
            'barn_pen' => json_encode([['barn_id' => 1, 'pen_id' => 1]]),
            'item_code' => 'FEED001',        // ✅ มี item_code
            'item_name' => 'Feed A',
            'item_type' => 'feed',
            'quantity' => 10,
            'note' => 'Test feed upload'
        ]
    ],
    'medicine_use' => [],
    'dead_pig' => []
]);

echo "=== ทำการ Upload Feed Use ===\n\n";
Log::info('TEST: Uploading feed', ['request' => $request->all()]);

try {
    $response = $controller->uploadDairy($request);
    echo "✅ Success: Feed uploaded\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== ตรวจสอบผลลัพธ์ ===\n";
echo shell_exec('php check_kpi_flow.php');
?>
