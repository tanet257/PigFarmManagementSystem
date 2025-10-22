<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Batch;
use App\Models\Cost;

echo "=== ดู Cost ทั้งหมดของ Batch ===\n\n";

$batch = Batch::where('batch_code', 'ฺฺฺf1-b2501')->first();
$costs = Cost::where('batch_id', $batch->id)->with('payments')->get();

echo "Total Cost: {$costs->count()}\n\n";

foreach ($costs as $cost) {
    echo "Cost ID: {$cost->id}\n";
    echo "  - Type: {$cost->cost_type}\n";
    echo "  - Payment Status: {$cost->payment_status}\n";
    echo "  - Amount: ฿{$cost->total_price}\n";
    echo "  - PigEntry ID: {$cost->pig_entry_record_id}\n";
    echo "  - Payments: {$cost->payments->count()}\n";
    foreach ($cost->payments as $payment) {
        echo "    - Status: {$payment->status}\n";
    }
    echo "\n";
}
