<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Batch;
use App\Models\PigEntryRecord;
use App\Models\Cost;
use App\Models\CostPayment;
use App\Models\Payment;
use App\Models\PigSale;

echo "=== Test: ยกเลิก Batch และ Pig Entry ===\n\n";

// ============================================================
// PART 1: ยกเลิก PigEntry
// ============================================================
echo "📌 PART 1: ยกเลิก PigEntry\n";
echo str_repeat("-", 60) . "\n\n";

$entry = PigEntryRecord::find(21);
if (!$entry) {
    echo "❌ ไม่พบ PigEntry 21\n";
} else {
    echo "✅ พบ PigEntry 21\n";
    echo "  - Status ก่อน: {$entry->status}\n";
    echo "  - Batch ID: {$entry->batch_id}\n\n";

    // Cancel via Controller method (simulated)
    $entry->update([
        'status' => 'cancelled',
        'cancellation_reason' => 'Test cancel',
        'cancelled_at' => now(),
        'cancelled_by' => 'System',
    ]);

    echo "✅ ยกเลิก PigEntry เสร็จ\n";
    echo "  - Status หลัง: {$entry->status}\n";
    echo "  - Cancelled by: {$entry->cancelled_by}\n";
    echo "  - Cancelled at: {$entry->cancelled_at}\n\n";

    // Cancel related CostPayments
    $costs = Cost::where('pig_entry_record_id', 21)->get();
    echo "  📦 ยกเลิก Cost Payments ({$costs->count()} costs):\n";

    foreach ($costs as $cost) {
        $paymentCount = $cost->payments()->count();
        $cost->payments()->update(['status' => 'rejected']);
        echo "    ✓ Cost ID {$cost->id}: ยกเลิก {$paymentCount} payments\n";
    }
    echo "\n";
}

// ============================================================
// PART 2: ยกเลิก Batch
// ============================================================
echo "📌 PART 2: ยกเลิก Batch\n";
echo str_repeat("-", 60) . "\n\n";

$batch = Batch::find(52);
if (!$batch) {
    echo "❌ ไม่พบ Batch 52\n";
} else {
    echo "✅ พบ Batch 52\n";
    echo "  - Batch Code: {$batch->batch_code}\n";
    echo "  - Status ก่อน: {$batch->status}\n\n";

    // Count related records
    $pigEntries = PigEntryRecord::where('batch_id', 52)->count();
    $pigSales = PigSale::where('batch_id', 52)->count();
    $costs = Cost::where('batch_id', 52)->count();
    $payments = Payment::whereIn('pig_sale_id', PigSale::where('batch_id', 52)->pluck('id'))->count();
    $costPayments = CostPayment::whereIn('cost_id', Cost::where('batch_id', 52)->pluck('id'))->count();

    echo "  📊 Related records:\n";
    echo "    - PigEntry: {$pigEntries}\n";
    echo "    - PigSale: {$pigSales}\n";
    echo "    - Cost: {$costs}\n";
    echo "    - Payment (PigSale): {$payments}\n";
    echo "    - CostPayment: {$costPayments}\n\n";

    // Use helper to cancel batch
    $result = \App\Helpers\PigInventoryHelper::deleteBatchWithAllocations(52);

    if ($result['success']) {
        echo "✅ ยกเลิก Batch เสร็จ\n";
        echo "  - Message: {$result['message']}\n\n";

        // Check after cancel
        $batch->refresh();
        echo "  📊 หลังจากยกเลิก:\n";
        echo "    - Batch Status: {$batch->status}\n";

        $cancelledPigEntries = PigEntryRecord::where('batch_id', 52)
            ->where('status', 'cancelled')->count();
        echo "    - Cancelled PigEntries: {$cancelledPigEntries}\n";

        $cancelledPigSales = PigSale::where('batch_id', 52)
            ->where('status', 'ยกเลิกการขาย')->count();
        echo "    - Cancelled PigSales: {$cancelledPigSales}\n";

        $rejectedPayments = Payment::whereIn('pig_sale_id', PigSale::where('batch_id', 52)->pluck('id'))
            ->where('status', 'rejected')->count();
        echo "    - Rejected Payments: {$rejectedPayments}\n";

        $rejectedCostPayments = CostPayment::whereIn('cost_id', Cost::where('batch_id', 52)->pluck('id'))
            ->where('status', 'rejected')->count();
        echo "    - Rejected CostPayments: {$rejectedCostPayments}\n\n";

    } else {
        echo "❌ เกิดข้อผิดพลาด\n";
        echo "  - Error: {$result['message']}\n\n";
    }
}

echo str_repeat("=", 60) . "\n";
echo "✅ Test สำเร็จ\n";
