<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';

use App\Helpers\PigInventoryHelper;
use App\Models\PigDeath;

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// สมมติว่า batch 2503 มี dead pigs ในคอก 46
$batchId = 2503;
echo "=== Testing Dead Pigs Calculation ===\n";
echo "Batch ID: $batchId\n\n";

// ดึงข้อมูล dead pigs ทั้งหมด
$allDeadPigs = PigDeath::where('batch_id', $batchId)->get();
echo "ทั้งหมด dead pigs ใน batch นี้:\n";
foreach ($allDeadPigs as $death) {
    echo "- ID: {$death->id}, Pen: {$death->pen_id}, Qty: {$death->quantity}, Status: {$death->status}\n";
}

echo "\nStatus breakdown:\n";
echo "- recorded: " . PigDeath::where('batch_id', $batchId)->where('status', 'recorded')->sum('quantity') . " ตัว\n";
echo "- sold: " . PigDeath::where('batch_id', $batchId)->where('status', 'sold')->sum('quantity') . " ตัว\n";

// ดึงจาก helper
echo "\n--- From PigInventoryHelper::getPigsByBatch() ---\n";
$result = PigInventoryHelper::getPigsByBatch($batchId);

echo "Dead pigs ที่มี is_dead = true:\n";
foreach ($result['pigs'] as $pig) {
    if ($pig['is_dead']) {
        echo "- Pen: {$pig['pen_id']}, Available: {$pig['available']}, Barn: {$pig['barn_name']}, PenCode: {$pig['pen_name']}\n";
    }
}

echo "\nทั้งหมด available: " . $result['total_available'] . " ตัว\n";
