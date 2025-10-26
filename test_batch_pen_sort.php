<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\BatchPenAllocation;
use App\Models\Barn;

echo "=== TEST BATCH PEN ALLOCATION SORT ===\n\n";

// ดึง barns ทั้งหมด
$barns = Barn::with(['farm', 'pens.batchPenAllocations.batch'])->get();

echo "Total barns: " . $barns->count() . "\n\n";

// สร้าง summary
$summaries = $barns->map(function ($barn) {
    $pensInfo = $barn->pens->map(function ($pen) {
        $allocations = $pen->batchPenAllocations;
        return [
            'pen_code' => $pen->pen_code,
            'allocated' => $allocations->sum('allocated_pigs'),
        ];
    })->values();

    $totalAllocated = $pensInfo->sum('allocated');
    $batchCodes = $barn->pens->flatMap(function ($pen) {
        return $pen->batchPenAllocations->map(fn($a) => optional($a->batch)->batch_code);
    })->filter()->unique()->values()->all();

    return [
        'barn_code' => $barn->barn_code,
        'farm_name' => optional($barn->farm)->farm_name,
        'total_allocated' => $totalAllocated,
        'batch_code' => implode(', ', $batchCodes),
    ];
})->values();

echo "--- Original Order ---\n";
foreach ($summaries as $barn) {
    echo "Barn: {$barn['barn_code']} | Farm: {$barn['farm_name']} | Allocated: {$barn['total_allocated']}\n";
}

// Test sort: name_asc
echo "\n--- Sort by name_asc (A-Z) ---\n";
$sorted = $summaries->sortBy('barn_code')->values();
foreach ($sorted as $barn) {
    echo "Barn: {$barn['barn_code']} | Farm: {$barn['farm_name']} | Allocated: {$barn['total_allocated']}\n";
}

// Test sort: quantity_asc
echo "\n--- Sort by quantity_asc (Low to High) ---\n";
$sorted = $summaries->sortBy('total_allocated')->values();
foreach ($sorted as $barn) {
    echo "Barn: {$barn['barn_code']} | Farm: {$barn['farm_name']} | Allocated: {$barn['total_allocated']}\n";
}

// Test sort: quantity_desc
echo "\n--- Sort by quantity_desc (High to Low) ---\n";
$sorted = $summaries->sortBy('total_allocated')->reverse()->values();
foreach ($sorted as $barn) {
    echo "Barn: {$barn['barn_code']} | Farm: {$barn['farm_name']} | Allocated: {$barn['total_allocated']}\n";
}

?>
