<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Simulate uploading dairy record without PigDeath (เพื่อ test batch undefined)
use Illuminate\Http\Request;
use App\Http\Controllers\DairyController;

$controller = new DairyController();

// สร้าง mock request
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
            'item_code' => 'FEED001',
            'item_name' => 'Feed Test',
            'item_type' => 'feed',
            'quantity' => 10,
            'note' => 'Test'
        ]
    ],
    'medicine_use' => [],
    'dead_pig' => []
]);

try {
    $response = $controller->uploadDairy($request);
    echo "✅ Success: Upload without PigDeath works!\n";
    echo "Response: " . $response->getTargetUrl() . "\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>
