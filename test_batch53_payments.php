<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Cost;
use App\Models\CostPayment;

echo "=== FIND COSTS WITH PAYMENTS FOR BATCH 53 ===\n\n";

// ดึงทั้งหมด Cost ของ Batch 53
$costs = Cost::where('batch_id', 53)->get();
echo "Total Costs for Batch 53: " . $costs->count() . "\n\n";

// ค้นหา Cost ที่มี CostPayment
$costsWithPayments = $costs->filter(function($cost) {
    return $cost->payments()->count() > 0;
});

echo "Costs WITH Payments: " . $costsWithPayments->count() . "\n";

if ($costsWithPayments->count() > 0) {
    echo "\n--- Details ---\n";
    foreach ($costsWithPayments->take(5) as $cost) {
        $payment = $cost->latestPayment;
        echo "✅ Cost ID: " . $cost->id . " | Type: " . $cost->cost_type . "\n";
        if ($payment) {
            echo "   └─ Payment ID: " . $payment->id . " | Status: " . $payment->status . " | Amount: " . $payment->amount . "\n";
        }
    }
} else {
    echo "\n❌ No costs with payments found!\n";

    // ตรวจสอบ CostPayment ทั้งหมด
    echo "\nChecking all CostPayments for Batch 53...\n";
    $allPayments = CostPayment::whereHas('cost', function($q) {
        $q->where('batch_id', 53);
    })->take(5)->get();

    echo "Total CostPayments found: " . $allPayments->count() . "\n";
    foreach ($allPayments as $p) {
        echo "  - Payment ID: " . $p->id . " | Cost ID: " . $p->cost_id . " | Status: " . $p->status . "\n";
    }
}

?>
