<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Batch;
use App\Models\Cost;
use App\Models\CostPayment;

echo "=== DEBUG BATCH 53 COST PAYMENT ===\n\n";

// ค้นหา Batch 53
$batch = Batch::where('id', 53)->first();
if (!$batch) {
    echo "❌ ไม่พบ Batch 53\n";
    exit;
}

echo "✅ Batch: " . $batch->batch_code . " (ID: " . $batch->id . ")\n\n";

// ค้นหา Cost ของ Batch 53
$costs = Cost::where('batch_id', $batch->id)->get();
echo "Total Costs: " . $costs->count() . "\n\n";

// ดึงเฉพาะ 3 รายการแรก
foreach ($costs->take(3) as $cost) {
    echo "Cost ID: " . $cost->id . " | Type: " . $cost->cost_type . "\n";

    // ลอง relationship
    $payment = $cost->costPayment;

    if ($payment) {
        echo "  ✅ CostPayment ID: " . $payment->id . "\n";
        echo "     Status: " . $payment->status . "\n";
        echo "     Amount: " . $payment->amount . "\n";
        echo "     Approved By: " . $payment->approved_by . "\n";
        echo "     Approved Date: " . ($payment->approved_date ? $payment->approved_date->format('d/m/Y H:i') : 'NULL') . "\n";
    } else {
        echo "  ❌ NO CostPayment relationship\n";

        // ค้นหา direct ด้วย WHERE
        $directPayment = CostPayment::where('cost_id', $cost->id)->latest()->first();
        if ($directPayment) {
            echo "  ℹ️ But found via direct query:\n";
            echo "     ID: " . $directPayment->id . " | Status: " . $directPayment->status . "\n";
        }
    }
    echo "\n";
}

// Check Cost model relationship definition
echo "\n=== CHECK COST MODEL ===\n";
$testCost = $costs->first();
if ($testCost) {
    echo "Methods on Cost:\n";
    echo "  - costPayments (plural): " . (method_exists($testCost, 'costPayments') ? 'EXISTS' : 'NOT FOUND') . "\n";
    echo "  - costPayment (singular): " . (method_exists($testCost, 'costPayment') ? 'EXISTS' : 'NOT FOUND') . "\n";
}

?>
