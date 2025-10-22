<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== เปรียบเทียบ Batch vs batch_pen_allocations ===\n\n";

$batch = \App\Models\Batch::find(18);
$allocTotal = DB::table('batch_pen_allocations')
    ->where('batch_id', 18)
    ->sum('current_quantity');

echo "Batch.current_quantity: {$batch->current_quantity}\n";
echo "Sum of batch_pen_allocations.current_quantity: {$allocTotal}\n";
echo "Difference: " . ($batch->current_quantity - $allocTotal) . "\n";

// ที่จริง batch.current_quantity ควรเท่ากับ sum of batch_pen_allocations
// ถ้าไม่เท่า ต้องแก้ batch_pen_allocations

if ($batch->current_quantity != $allocTotal) {
    echo "\n❌ NOT IN SYNC! Updating batch_pen_allocations...\n";

    // ดึงจำนวนเดิม allocated ของ allocations
    $allocations = DB::table('batch_pen_allocations')
        ->where('batch_id', 18)
        ->get();

    $totalAllocated = $allocations->sum('allocated_pigs');

    // คำนวณ ratio
    if ($totalAllocated > 0) {
        $ratio = $batch->current_quantity / $totalAllocated;

        echo "Total allocated: {$totalAllocated}\n";
        echo "Expected current_quantity per allocation: {$ratio} ratio\n";

        // ถ้าต้อง scale up
        foreach ($allocations as $alloc) {
            $newCurrent = round($alloc->allocated_pigs * $ratio);
            DB::table('batch_pen_allocations')
                ->where('id', $alloc->id)
                ->update(['current_quantity' => $newCurrent]);
        }

        echo "✓ Updated batch_pen_allocations\n";
    }
}
