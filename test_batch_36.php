<?php
require 'vendor/autoload.php';
\Dotenv\Dotenv::createImmutable(__DIR__)->load();
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PigSale;
use App\Models\BatchPenAllocation;
use App\Models\Batch;

echo "=== ตรวจสอบ Batch 36 (ที่มีการยกเลิก 200 ตัว 2 ครั้ง) ===\n\n";

$batch = Batch::find(36);
echo "Batch: ID=36, current_qty={$batch->current_quantity}, allocated_pigs={$batch->allocated_pigs}\n";

echo "\nBatchPenAllocation สำหรับ batch 36:\n";
$allocations = BatchPenAllocation::where('batch_id', 36)->get();
foreach ($allocations as $a) {
    echo "  - pen_id={$a->pen_id}, current_qty={$a->current_quantity}, allocated_pigs={$a->allocated_pigs}\n";
}

echo "\n=== ตรวจสอบการยกเลิก ID=33 ===\n";
$sale33 = PigSale::find(33);
echo "Sale: ID=33, status={$sale33->status}, qty={$sale33->quantity}\n";

$details33 = $sale33->details;
echo "Details (" . $details33->count() . "):\n";
$totalExpected = 0;
foreach ($details33 as $d) {
    echo "  - pen_id={$d->pen_id}, qty={$d->quantity}\n";
    $totalExpected += $d->quantity;
}
echo "Total expected to return: $totalExpected\n";

echo "\n=== คำถาม: ทำไม Batch.current_quantity ยังคง 0? ===\n";
echo "เหตุที่อาจเกิด:\n";
echo "1. Batch ถูกใช้ขายทั้งหมด (current_qty = 0) ตั้งแต่เริ่ม?\n";
echo "2. หรือ confirmCancel() ไม่ได้เรียก?\n";
echo "3. หรือ Batch update ล้มเหลว?\n";

// ตรวจสอบการขายทั้งหมดของ batch 36
echo "\n=== การขาย ALL สำหรับ Batch 36 ===\n";
$allSales36 = PigSale::where('batch_id', 36)->get();
echo "Total sales: " . $allSales36->count() . "\n";
foreach ($allSales36 as $s) {
    echo "  - ID={$s->id}, status={$s->status}, qty={$s->quantity}\n";
}

$totalSold = $allSales36->sum('quantity');
echo "Total sold: $totalSold (ควร = allocated_pigs)\n";

exit;
