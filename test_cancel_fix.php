<?php
require 'vendor/autoload.php';
\Dotenv\Dotenv::createImmutable(__DIR__)->load();
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PigSale;
use App\Models\BatchPenAllocation;
use App\Models\Batch;
use App\Http\Controllers\PigSaleController;

echo "=== ทดสอบ Fix confirmCancel() ===\n\n";

// ดึง PigSale ID=33 ที่มี status = ยกเลิกการขาย
$sale = PigSale::find(33);
if (!$sale) {
    echo "❌ ไม่พบ PigSale ID=33\n";
    exit;
}

echo "PigSale ID=33 ก่อน Fix:\n";
echo "  - status: {$sale->status}\n";
echo "  - qty: {$sale->quantity}\n";

$batch = Batch::find($sale->batch_id);
echo "\nBatch {$sale->batch_id} ก่อน Fix:\n";
echo "  - current_quantity: {$batch->current_quantity}\n";
echo "  - total_pig_amount: {$batch->total_pig_amount}\n";

echo "\nBatchPenAllocations ก่อน Fix:\n";
$allocBefore = BatchPenAllocation::where('batch_id', $sale->batch_id)
    ->whereIn('pen_id', [41, 42, 43, 44, 45, 46])
    ->get();
foreach ($allocBefore as $a) {
    echo "  - pen_id={$a->pen_id}: current_qty={$a->current_quantity}, allocated_pigs={$a->allocated_pigs}\n";
}

// ทำการ "reset" เพื่อทดสอบ (ยกเลิกการยกเลิก)
echo "\n" . str_repeat("=", 60) . "\n";
echo "🔄 เรียก confirmCancel() อีกครั้ง...\n";
echo str_repeat("=", 60) . "\n";

// Reset status กลับเป็น cancel_requested
$sale->status = 'cancel_requested';
$sale->payment_status = 'cancel_requested';
$sale->save();

// เซ็ตค่า allocation กลับเป็น 0 เพื่อ simulate ปัญหา
BatchPenAllocation::where('batch_id', $sale->batch_id)
    ->whereIn('pen_id', [41, 42, 43, 44, 45, 46])
    ->update(['current_quantity' => 0]);

$batch->current_quantity = 0;
$batch->save();

// เรียก confirmCancel()
$controller = new PigSaleController();
try {
    $controller->confirmCancel($sale->id);
} catch (\Exception $e) {
    echo "❌ Exception: {$e->getMessage()}\n";
}

echo "\n✅ หลังจากเรียก confirmCancel():\n";

// รีเฟช ข้อมูล
$sale->refresh();
$batch->refresh();

echo "\nPigSale ID=33 หลัง Fix:\n";
echo "  - status: {$sale->status}\n";
echo "  - qty: {$sale->quantity}\n";

echo "\nBatch {$sale->batch_id} หลัง Fix:\n";
echo "  - current_quantity: {$batch->current_quantity}\n";
echo "  - total_pig_amount: {$batch->total_pig_amount}\n";
echo "  - ✅ ควรจะเพิ่มจาก 0 เป็น 200\n";

echo "\nBatchPenAllocations หลัง Fix:\n";
$allocAfter = BatchPenAllocation::where('batch_id', $sale->batch_id)
    ->whereIn('pen_id', [41, 42, 43, 44, 45, 46])
    ->get();
foreach ($allocAfter as $a) {
    echo "  - pen_id={$a->pen_id}: current_qty={$a->current_quantity}, allocated_pigs={$a->allocated_pigs}\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
if ($batch->current_quantity == 200) {
    echo "✅ SUCCESS! Batch.current_quantity คืนค่าถูกต้อง (200 ตัว)\n";
} else {
    echo "❌ FAILED! Batch.current_quantity = {$batch->current_quantity} (ต้องเป็น 200)\n";
}
echo str_repeat("=", 60) . "\n";

exit;
