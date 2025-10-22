<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PigEntryRecord;
use App\Models\Cost;

echo "=== ยกเลิก PigEntry 21 ===\n\n";

$entry = PigEntryRecord::find(21);
if (!$entry) {
    echo "✗ ไม่พบ PigEntry 21\n";
    exit;
}

echo "Before:\n";
echo "  Status: {$entry->status}\n";

// ยกเลิก Cost
$costs = Cost::where('pig_entry_record_id', 21)->get();
echo "\nCancelling {$costs->count()} costs...\n";
foreach ($costs as $cost) {
    $cost->update(['payment_status' => 'ยกเลิก']);
    $cost->payments()->update(['status' => 'rejected']);  // ใช้ rejected เพราะ enum
    echo "✓ Cost ID {$cost->id} cancelled\n";
}

// ยกเลิก PigEntry
$entry->update([
    'status' => 'cancelled',
    'cancellation_reason' => 'Test cancel',
    'cancelled_at' => now(),
    'cancelled_by' => 'System',
]);

echo "\nAfter:\n";
echo "  Status: {$entry->status}\n";

// Recalculate profit
echo "\n✓ Recalculate Profit...\n";
$result = \App\Helpers\RevenueHelper::calculateAndRecordProfit($entry->batch_id);
echo "Result: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "\n";

$profit = \App\Models\Profit::where('batch_id', $entry->batch_id)->first();
if ($profit) {
    echo "\n✓ Profit Updated:\n";
    echo "  - Revenue: ฿{$profit->total_revenue}\n";
    echo "  - Cost: ฿{$profit->total_cost}\n";
    echo "  - Gross Profit: ฿{$profit->gross_profit}\n";
}
