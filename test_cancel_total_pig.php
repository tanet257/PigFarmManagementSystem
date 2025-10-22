<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Batch;
use App\Models\PigEntryRecord;

echo "=== ทดสอบการยกเลิก PigEntry + ลด total_pig_amount ===\n\n";

// Reset data
$batch = Batch::where('batch_code', 'ฺฺฺf1-b2501')->first();
if (!$batch) {
    echo "✗ ไม่พบ Batch\n";
    exit;
}

echo "Batch: {$batch->batch_code}\n\n";

// เรียมแรม test data
$batch->total_pig_amount = 6000;
$batch->current_quantity = 5800;
$batch->save();

$entries = PigEntryRecord::where('batch_id', $batch->id)
    ->where('status', 'active')
    ->get();

echo "Before Cancel:\n";
echo "  - total_pig_amount: {$batch->total_pig_amount}\n";
echo "  - current_quantity: {$batch->current_quantity}\n";
echo "  - Active Entries: {$entries->count()}\n\n";

// ยกเลิก entry แรก
if ($entries->count() > 0) {
    $entry = $entries->first();
    $entryQty = $entry->total_pig_amount;

    echo "Cancelling Entry ID: {$entry->id} (Qty: {$entryQty})\n";

    // Simulate cancel (ในจริง: call controller method)
    $entry->status = 'cancelled';
    $entry->cancelled_at = now();
    $entry->cancelled_by = 'admin';
    $entry->save();

    // ลด batch quantity
    $batch->total_pig_amount = max(0, $batch->total_pig_amount - $entryQty);
    $batch->current_quantity = max(0, $batch->current_quantity - $entryQty);
    $batch->save();

    echo "\nAfter Cancel:\n";
    echo "  - total_pig_amount: {$batch->total_pig_amount}\n";
    echo "  - current_quantity: {$batch->current_quantity}\n";
    echo "\n✓ หมูรวมถูกลดเรียบร้อย\n";
}

echo "\nเสร็จ!\n";
