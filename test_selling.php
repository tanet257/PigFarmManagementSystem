<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';

use App\Models\PigDeath;
use App\Helpers\PigInventoryHelper;

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Before Selling ===\n";
$deaths = PigDeath::where('batch_id', 50)->get();
foreach ($deaths as $death) {
    echo "ID: {$death->id}, Qty: {$death->quantity}, Status: {$death->status}\n";
}

// Simulate selling first death record (4 ตัว)
echo "\nSimulating selling 4 pigs from first record...\n";
$firstDeath = $deaths->first();
$firstDeath->quantity -= 4;
if ($firstDeath->quantity == 0) {
    $firstDeath->quantity = 0;  // Set to 0 if needed
}
$firstDeath->status = 'sold';
$firstDeath->save();

echo "\n=== After Selling ===\n";
$deaths = PigDeath::where('batch_id', 50)->get();
foreach ($deaths as $death) {
    echo "ID: {$death->id}, Qty: {$death->quantity}, Status: {$death->status}\n";
}

echo "\n=== From Helper ===\n";
$result = PigInventoryHelper::getPigsByBatch(50);
echo "Total available (recorded): " . $result['total_available'] . " ตัว\n";
foreach ($result['pigs'] as $pig) {
    if ($pig['is_dead']) {
        echo "Pen {$pig['pen_id']}: available = {$pig['available']}\n";
    }
}
