<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Batch;
use App\Models\PigEntryRecord;
use App\Models\PigEntryDetail;

echo "=== Reset Data และ Test Cancel ===\n\n";

// Reset batch
$batch = Batch::where('batch_code', 'ฺฺฺf1-b2501')->first();

// Delete PigEntryDetail
PigEntryDetail::where('batch_id', $batch->id)->delete();

// Reset PigEntry
$entries = PigEntryRecord::where('batch_id', $batch->id)->get();
foreach ($entries as $entry) {
    $entry->update([
        'status' => 'active',
        'cancelled_at' => null,
        'cancelled_by' => null,
    ]);
}

// Reset batch
$batch->total_pig_amount = 6000;
$batch->current_quantity = 5800;
$batch->save();

echo "✓ Data Reset\n\n";

echo "Before Cancel:\n";
echo "  - total_pig_amount: {$batch->total_pig_amount}\n";
echo "  - current_quantity: {$batch->current_quantity}\n";

// ยกเลิก entry แรก
$entry = $entries->first();
if ($entry) {
    $entryQty = $entry->total_pig_amount ?? 1500;

    echo "\n✓ Cancelling Entry ID: {$entry->id} (Qty: {$entryQty})\n";

    // ยกเลิก
    $entry->status = 'cancelled';
    $entry->save();

    // ลด batch
    $batch->total_pig_amount = max(0, $batch->total_pig_amount - $entryQty);
    $batch->current_quantity = max(0, $batch->current_quantity - $entryQty);
    $batch->save();

    echo "\nAfter Cancel:\n";
    echo "  - total_pig_amount: {$batch->total_pig_amount}\n";
    echo "  - current_quantity: {$batch->current_quantity}\n";
    echo "\n✓ หมูรวมลดลง {$entryQty} ตัว\n";
}

echo "\nเสร็จ!\n";
