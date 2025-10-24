<?php
require 'vendor/autoload.php';

// Load environment
\Dotenv\Dotenv::createImmutable(__DIR__)->load();

// Create Laravel app
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Use Eloquent
$db = $app->make('db');

use App\Models\PigSale;
use App\Models\PigSaleDetail;
use App\Models\Batch;
use App\Models\BatchPenAllocation;

echo "=== ทดสอบข้อมูล PigSaleDetail ===\n\n";

// 1. ตรวจสอบจำนวน PigSaleDetail
$totalDetails = PigSaleDetail::count();
echo "1. จำนวน PigSaleDetail ทั้งหมด: $totalDetails\n";

// 2. ดึง PigSale ที่ยังรอยกเลิก
echo "\n2. PigSale ที่สถานะ 'cancel_requested':\n";
$cancelRequests = PigSale::where('status', 'cancel_requested')->latest()->take(3)->get();
foreach ($cancelRequests as $sale) {
    echo "   - ID={$sale->id}, batch_id={$sale->batch_id}, qty={$sale->quantity}, pen_id={$sale->pen_id}\n";

    $details = PigSaleDetail::where('pig_sale_id', $sale->id)->get();
    echo "     Details: " . $details->count() . " records\n";
    foreach ($details as $d) {
        echo "       * pen_id={$d->pen_id}, quantity={$d->quantity}\n";
    }
}

// 3. ดึง PigSale ที่ยกเลิกแล้ว
echo "\n3. PigSale ที่สถานะ 'ยกเลิกการขาย' (ล่าสุด 3):\n";
$cancelled = PigSale::where('status', 'ยกเลิกการขาย')->latest()->take(3)->get();
foreach ($cancelled as $sale) {
    echo "   - ID={$sale->id}, batch_id={$sale->batch_id}, qty={$sale->quantity}\n";

    // ตรวจสอบ batch.current_quantity
    $batch = Batch::find($sale->batch_id);
    if ($batch) {
        echo "     Batch current_quantity: {$batch->current_quantity}\n";
    }

    $details = PigSaleDetail::where('pig_sale_id', $sale->id)->get();
    echo "     Details: " . $details->count() . " records\n";
    foreach ($details as $d) {
        echo "       * pen_id={$d->pen_id}, quantity={$d->quantity}\n";
    }
}

// 4. ดึง allocation ตัวอย่าง
echo "\n4. BatchPenAllocation samples:\n";
$allocations = BatchPenAllocation::latest()->take(5)->get();
foreach ($allocations as $a) {
    echo "   - batch_id={$a->batch_id}, pen_id={$a->pen_id}, current_qty={$a->current_quantity}\n";
}

echo "\n✅ Test complete\n";
