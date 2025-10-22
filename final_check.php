<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PigEntryRecord;
use App\Models\Batch;

echo "=== สถานะ PigEntry + Batch ===\n\n";

$batch = Batch::find(18);
echo "Batch 18:\n";
echo "  - total_pig_amount: {$batch->total_pig_amount}\n";
echo "  - current_quantity: {$batch->current_quantity}\n\n";

$entries = PigEntryRecord::where('batch_id', 18)->orderBy('id')->get();
foreach ($entries as $entry) {
    $details = \App\Models\PigEntryDetail::where('pig_entry_id', $entry->id)->sum('quantity');
    $status = $entry->status === 'cancelled' ? '❌ CANCELLED' : '✓ ACTIVE';
    echo "{$status} Entry ID: {$entry->id}, Qty: {$details}, Cancelled At: {$entry->cancelled_at}\n";
}

echo "\n=== ข้อความเชิญหาเหตุผล ===\n";
echo "Expected after all cancellations: 0 pigs (6000 - 6000 cancelled)\n";
echo "But with 200 sales: 200 pigs (if 1 sale before cancel)\n";
echo "Actual: " . $batch->current_quantity . " pigs\n";
echo "\nDifference: " . ($batch->current_quantity - 200) . " pigs\n";
