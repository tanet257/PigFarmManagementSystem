<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== ตรวจสอบ batch_pen_allocations สำหรับ Batch 18 ===\n\n";

// ดูสถานะทั้งหมด
$allocations = DB::table('batch_pen_allocations')
    ->where('batch_id', 18)
    ->orderBy('pen_id')
    ->get();

echo "Total Allocations: {$allocations->count()}\n\n";

// หาค่า max/min
$maxAllocated = $allocations->max('allocated_pigs');
$minAllocated = $allocations->min('allocated_pigs');
$maxCurrent = $allocations->max('current_quantity');
$minCurrent = $allocations->min('current_quantity');

echo "Allocated Pigs:\n";
echo "  - Max: {$maxAllocated}\n";
echo "  - Min: {$minAllocated}\n";
echo "Summing: " . $allocations->sum('allocated_pigs') . "\n\n";

echo "Current Quantity:\n";
echo "  - Max: {$maxCurrent}\n";
echo "  - Min: {$minCurrent}\n";
echo "Summing: " . $allocations->sum('current_quantity') . "\n\n";

// ตัวอย่าง pen ที่ share
echo "=== ตัวอย่าง Pen ID ===\n\n";
foreach ($allocations->take(5) as $alloc) {
    echo "Pen ID: {$alloc->pen_id}, Allocated: {$alloc->allocated_pigs}, Current: {$alloc->current_quantity}\n";
}
