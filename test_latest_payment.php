<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Cost;

echo "=== TEST latestPayment RELATIONSHIP ===\n\n";

// ดึง Cost ล่าสุดของ Batch 53
$cost = Cost::where('batch_id', 53)->latest()->first();

if (!$cost) {
    echo "❌ ไม่พบ Cost สำหรับ Batch 53\n";
    exit;
}

echo "✅ Cost ID: " . $cost->id . "\n";
echo "   Type: " . $cost->cost_type . "\n";

// ทดสอบ relationship
$payment = $cost->latestPayment;

if ($payment) {
    echo "✅ Payment FOUND\n";
    echo "   ID: " . $payment->id . "\n";
    echo "   Status: " . $payment->status . "\n";
    echo "   Amount: " . $payment->amount . "\n";
    echo "   Approved By: " . $payment->approved_by . "\n";
    echo "   Approved Date: " . ($payment->approved_date ? $payment->approved_date->format('d/m/Y H:i') : 'NULL') . "\n";
} else {
    echo "❌ Payment NOT FOUND via relationship\n";

    // ค้นหา direct
    $directPayments = $cost->payments()->get();
    echo "   Direct payments count: " . $directPayments->count() . "\n";

    if ($directPayments->count() > 0) {
        $p = $directPayments->first();
        echo "   First payment status: " . $p->status . "\n";
    }
}

?>
