<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PigEntryRecord;
use App\Models\Cost;
use App\Models\CostPayment;
use App\Models\Batch;

echo "=== CHECK BATCH 53 PAYMENT STATUS ===\n\n";

// ค้นหา batch 53
$batch = Batch::where('batch_code', 'f1-b2505')->first();

if (!$batch) {
    echo "❌ ไม่พบ batch 53\n";
    exit;
}

echo "✅ พบ Batch: " . $batch->batch_code . " (ID: " . $batch->id . ")\n\n";

// ค้นหา PigEntryRecord ของ batch นี้
$pigEntries = PigEntryRecord::where('batch_id', $batch->id)->get();

echo "📊 Pig Entry Records สำหรับ batch นี้: " . $pigEntries->count() . "\n";

foreach ($pigEntries as $entry) {
    echo "\n--- PigEntryRecord ID: " . $entry->id . " ---\n";
    echo "วันที่: " . $entry->pig_entry_date . "\n";
    echo "จำนวนหมู: " . $entry->total_pig_amount . " ตัว\n";
    echo "ราคารวม: ฿" . number_format($entry->total_pig_price, 2) . "\n";

    // ค้นหา Cost record ของ entry นี้
    $costs = Cost::where('pig_entry_record_id', $entry->id)->get();

    echo "  ↳ Cost records: " . $costs->count() . "\n";

    foreach ($costs as $cost) {
        echo "    └─ Cost ID: " . $cost->id . " | Type: " . $cost->cost_type . " | Amount: ฿" . number_format($cost->total_price, 2) . "\n";

        // ค้นหา CostPayment
        $payments = CostPayment::where('cost_id', $cost->id)->get();
        echo "       CostPayment records: " . $payments->count() . "\n";

        foreach ($payments as $payment) {
            echo "       ├─ ID: " . $payment->id . " | Status: " . $payment->status . " | Amount: ฿" . number_format((float)$payment->amount, 2) . "\n";
            echo "       ├─ Approved By: " . ($payment->approved_by ?? 'N/A') . "\n";
            echo "       └─ Approved Date: " . ($payment->approved_date ? $payment->approved_date->format('d/m/Y H:i') : 'N/A') . "\n";
        }
    }
}

// ค้นหา Cost จาก batch_id ด้วย (alternative)
echo "\n\n--- Alternative: ค้นหา Cost โดยใช้ batch_id ---\n";
$costsFromBatch = Cost::where('batch_id', $batch->id)->get();
echo "Cost records จาก batch_id: " . $costsFromBatch->count() . "\n";

foreach ($costsFromBatch as $cost) {
    echo "\nCost ID: " . $cost->id . " | Type: " . $cost->cost_type . " | Amount: ฿" . number_format($cost->total_price, 2) . "\n";

    $payment = CostPayment::where('cost_id', $cost->id)->latest()->first();

    if ($payment) {
        echo "✅ CostPayment Found:\n";
        echo "   Status: " . $payment->status . "\n";
        echo "   Amount: ฿" . number_format((float)$payment->amount, 2) . "\n";
        echo "   Approved By: " . ($payment->approved_by ?? 'N/A') . "\n";
        echo "   Approved Date: " . ($payment->approved_date ? $payment->approved_date->format('d/m/Y H:i') : 'N/A') . "\n";
    } else {
        echo "❌ ไม่มี CostPayment\n";
    }
}

?>
