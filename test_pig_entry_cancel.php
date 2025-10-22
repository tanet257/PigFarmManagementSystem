<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Batch;
use App\Models\PigEntryRecord;
use App\Models\Cost;

echo "=== ทดสอบการยกเลิก PigEntry + Cost ===\n\n";

$batch = Batch::where('batch_code', 'ฺฺฺf1-b2501')->first();
if (!$batch) {
    echo "✗ ไม่พบ Batch\n";
    exit;
}

// ดึง PigEntry record
$pigEntry = PigEntryRecord::where('batch_id', $batch->id)->first();
if (!$pigEntry) {
    echo "✗ ไม่พบ PigEntry\n";
    exit;
}

echo "Batch: {$batch->batch_code}\n";
echo "PigEntry ID: {$pigEntry->id}\n";
echo "Current Status: {$pigEntry->status}\n\n";

// ดึง Cost ที่เกี่ยวข้อง
$costs = Cost::where('pig_entry_record_id', $pigEntry->id)->get();
echo "Cost records ก่อนยกเลิก: {$costs->count()}\n";
foreach ($costs as $cost) {
    echo "  - Cost ID: {$cost->id}, Type: {$cost->cost_type}, Status: {$cost->payment_status}, Amount: ฿{$cost->total_price}\n";
}

echo "\n✓ ยกเลิก PigEntry (simulate delete)...\n\n";

// ยกเลิก Cost
foreach ($costs as $cost) {
    if ($cost->payment_status !== 'ยกเลิก') {
        $cost->update([
            'payment_status' => 'ยกเลิก',
        ]);
        $cost->payments()->update([
            'status' => 'cancelled',
        ]);
        echo "✓ Cost ID {$cost->id} cancelled\n";
    }
}

// ยกเลิก PigEntry
$pigEntry->update([
    'status' => 'cancelled',
    'cancellation_reason' => 'Test cancel',
    'cancelled_at' => now(),
    'cancelled_by' => 'System',
]);

echo "\n✓ Recalculate Profit...\n";
$profitResult = \App\Helpers\RevenueHelper::calculateAndRecordProfit($batch->id);
echo "Result: " . ($profitResult['success'] ? 'SUCCESS' : 'FAILED') . "\n";

// ดู Profit records
$profit = \App\Models\Profit::where('batch_id', $batch->id)->first();
if ($profit) {
    echo "\n✓ Profit record:\n";
    echo "  - Revenue: ฿{$profit->total_revenue}\n";
    echo "  - Cost: ฿{$profit->total_cost}\n";
    echo "  - Gross Profit: ฿{$profit->gross_profit}\n";
}

echo "\nเสร็จ!\n";
