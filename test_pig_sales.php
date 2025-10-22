<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Batch;
use App\Models\PigSale;
use App\Models\Revenue;

echo "=== ตรวจสอบ PigSale + Revenue ===\n\n";

// ดึงทุก batch
$batches = Batch::all();

foreach ($batches as $batch) {
    echo "--- Batch: {$batch->batch_code} (ID: {$batch->id}) ---\n";

    // ตรวจสอบ PigSale
    $pigSales = PigSale::where('batch_id', $batch->id)->get();
    echo "  PigSale: " . $pigSales->count() . " records\n";
    foreach ($pigSales as $sale) {
        echo "    - ฿" . number_format($sale->net_total, 2) . " (qty: {$sale->quantity}) - status: {$sale->status} - payment: {$sale->payment_status}\n";
    }

    // ตรวจสอบ Revenue
    $revenues = Revenue::where('batch_id', $batch->id)->get();
    echo "  Revenue: " . $revenues->count() . " records\n";
    foreach ($revenues as $rev) {
        echo "    - ฿" . number_format($rev->net_revenue, 2) . " (pig_sale_id: {$rev->pig_sale_id}) - payment_status: {$rev->payment_status}\n";
    }

    echo "\n";
}
