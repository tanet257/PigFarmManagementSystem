<?php
// Test delete scenario

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Before Delete ===\n";
$batches = \App\Models\Batch::where('batch_code', 'ฺฺฺb2501')->get(['id', 'farm_id', 'batch_code', 'status']);
foreach($batches as $b) {
    echo "ID: {$b->id}, Farm: {$b->farm_id}, Code: {$b->batch_code}, Status: {$b->status}\n";
}

echo "\n=== Deleting Batch ID 16 ===\n";
$result = \App\Helpers\PigInventoryHelper::deleteBatchWithAllocations(16);
echo "Result: " . ($result['success'] ? 'Success' : 'Failed') . "\n";
echo "Message: {$result['message']}\n";

echo "\n=== After Delete ===\n";
$batches = \App\Models\Batch::where('batch_code', 'ฺฺฺb2501')->get(['id', 'farm_id', 'batch_code', 'status']);
foreach($batches as $b) {
    echo "ID: {$b->id}, Farm: {$b->farm_id}, Code: {$b->batch_code}, Status: {$b->status}\n";
}
?>
