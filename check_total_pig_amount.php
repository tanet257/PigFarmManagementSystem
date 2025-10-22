<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Batch;
use App\Models\PigEntryRecord;

echo "=== ตรวจสอบ total_pig_amount vs PigEntry ===\n\n";

$batch = Batch::find(18);
echo "Batch ID: 18\n";
echo "Current total_pig_amount: {$batch->total_pig_amount}\n\n";

// ดูจำนวนจาก PigEntry
$entries = PigEntryRecord::where('batch_id', 18)->get();
$totalFromEntries = $entries->sum('total_pig_amount');

echo "From PigEntry records:\n";
echo "  - Count: {$entries->count()}\n";
echo "  - Total: {$totalFromEntries}\n\n";

// ดู current_quantity
echo "Current quantities:\n";
echo "  - Batch current_quantity: {$batch->current_quantity}\n";

$allocSum = \Illuminate\Support\Facades\DB::table('batch_pen_allocations')
    ->where('batch_id', 18)
    ->sum('current_quantity');
echo "  - Sum from allocations: {$allocSum}\n\n";

// ดู PigSale
$sales = \App\Models\PigSale::where('batch_id', 18)->where('status', '!=', 'ยกเลิกการขาย')->sum('quantity');
echo "PigSale: {$sales}\n\n";

// คำนวณเชค
echo "=== การคำนวณเชค ===\n";
echo "Expected logic:\n";
echo "  total_pig_amount (initial): " . $totalFromEntries . "\n";
echo "  - PigSale: " . $sales . "\n";
echo "  = Expected current_quantity: " . ($totalFromEntries - $sales) . "\n";
echo "  Actual current_quantity: " . $batch->current_quantity . "\n";

if ($batch->current_quantity == $totalFromEntries - $sales) {
    echo "  ✓ MATCH\n";
} else {
    echo "  ✗ MISMATCH\n";
}
