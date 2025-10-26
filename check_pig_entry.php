<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Batch;
use App\Models\PigEntryRecord;

$batch = Batch::find(53);

echo "=== Batch 53 ===\n";
echo "Batch ID: {$batch->id}\n";

// ดึง PigEntryRecord ที่เกี่ยวข้อง
$pigEntry = PigEntryRecord::where('batch_id', 53)->first();

if ($pigEntry) {
    echo "✅ PigEntryRecord found:\n";
    echo "  Entry Date: " . $pigEntry->pig_entry_date . "\n";
    echo "  Starting Weight: " . $pigEntry->average_weight_per_pig . " kg\n";
    echo "  Total Pigs: " . $pigEntry->total_pig_amount . "\n";
} else {
    echo "❌ No PigEntryRecord found for batch 53\n";
}
?>
