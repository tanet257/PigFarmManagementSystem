<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Batch;
use App\Models\PigEntryDetail;
use Illuminate\Support\Facades\DB;

echo "=== แก้ไข total_pig_amount ให้ตรงกับ entry details ===\n\n";

$batch = Batch::find(18);

// หา total จำนวนหมูทั้งหมดจาก entry details
$totalFromDetails = PigEntryDetail::where('batch_id', 18)->sum('quantity');

echo "Current total_pig_amount: {$batch->total_pig_amount}\n";
echo "Total from PigEntryDetail: {$totalFromDetails}\n";

if ($batch->total_pig_amount != $totalFromDetails) {
    echo "\n⚠️ Mismatch! Fixing...\n";

    // อัปเดท total_pig_amount + current_quantity ให้เท่า
    $batch->update([
        'total_pig_amount' => $totalFromDetails,
        'current_quantity' => $totalFromDetails - 200,  // ลบ sales 200
    ]);

    echo "✓ Updated!\n";
    echo "  - total_pig_amount: {$batch->total_pig_amount}\n";
    echo "  - current_quantity: {$batch->current_quantity}\n";
} else {
    echo "✓ Already correct!\n";
}
