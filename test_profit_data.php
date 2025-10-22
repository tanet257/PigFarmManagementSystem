<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Batch;
use App\Models\Cost;
use App\Models\CostPayment;
use App\Models\Revenue;
use App\Models\Profit;
use App\Models\ProfitDetail;

echo "=== ทดสอบข้อมูล Profit/Revenue ===\n\n";

// ดึงทุก batch
$batches = Batch::all();
echo "จำนวน Batch ทั้งหมด: " . $batches->count() . "\n\n";

foreach ($batches as $batch) {
    echo "--- Batch: {$batch->batch_code} (ID: {$batch->id}) ---\n";

    // ตรวจสอบ Revenue
    $revenues = Revenue::where('batch_id', $batch->id)->get();
    echo "  Revenue: " . $revenues->count() . " records\n";
    foreach ($revenues as $rev) {
        echo "    - ฿" . number_format($rev->net_revenue, 2) . " (status: {$rev->payment_status})\n";
    }

    // ตรวจสอบ Cost
    $costs = Cost::where('batch_id', $batch->id)->get();
    echo "  Cost: " . $costs->count() . " records\n";
    foreach ($costs as $cost) {
        $payments = $cost->payments;
        echo "    - ฿" . number_format($cost->total_price, 2) . " ({$cost->cost_type}) - Payments: " . $payments->count();
        if ($payments->count() > 0) {
            echo " [" . $payments->first()->status . "]";
        }
        echo "\n";
    }

    // ตรวจสอบ Profit
    $profit = Profit::where('batch_id', $batch->id)->first();
    if ($profit) {
        echo "  Profit: ✓ (gross_profit: ฿" . number_format($profit->gross_profit, 2) . ")\n";
        echo "    - Revenue: ฿" . number_format($profit->total_revenue, 2) . "\n";
        echo "    - Cost: ฿" . number_format($profit->total_cost, 2) . "\n";
        echo "      - Feed: ฿" . number_format($profit->feed_cost, 2) . "\n";
        echo "      - Medicine: ฿" . number_format($profit->medicine_cost, 2) . "\n";
        echo "      - Transport: ฿" . number_format($profit->transport_cost, 2) . "\n";
        echo "      - Excess Weight: ฿" . number_format($profit->excess_weight_cost ?? 0, 2) . "\n";
        echo "      - Labor: ฿" . number_format($profit->labor_cost, 2) . "\n";
        echo "      - Piglet: ฿" . number_format($profit->feed_cost, 2) . "\n";

        // ตรวจสอบ ProfitDetail
        $details = ProfitDetail::where('profit_id', $profit->id)->get();
        echo "  ProfitDetails: " . $details->count() . " records\n";
        foreach ($details as $detail) {
            echo "    - {$detail->cost_category}: ฿" . number_format($detail->amount, 2) . "\n";
        }
    } else {
        echo "  Profit: ✗ (ยังไม่มี)\n";
    }

    echo "\n";
}
