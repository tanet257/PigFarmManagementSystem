<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Cost;
use App\Models\CostPayment;

echo "=== ดูการสัมพันธ์ Cost + CostPayment ===\n\n";

$costs = Cost::with('payments')->where('payment_status', 'ยกเลิก')->get();
echo "Cost ที่ยกเลิก: {$costs->count()}\n\n";

foreach ($costs as $cost) {
    echo "Cost ID: {$cost->id}, Type: {$cost->cost_type}, Payment Status: {$cost->payment_status}\n";
    echo "  - Payments ({$cost->payments->count()}):\n";
    foreach ($cost->payments as $payment) {
        echo "    - ID: {$payment->id}, Status: {$payment->status}\n";
    }
}

echo "\nเสร็จ!\n";
