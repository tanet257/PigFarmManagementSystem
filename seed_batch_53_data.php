<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Batch;
use Carbon\Carbon;

// Update Batch 53
$batch = Batch::find(53);
if ($batch) {
    $batch->update([
        'entry_date' => Carbon::now()->subDays(10),  // 10 วันที่แล้ว
        'exit_date' => null,  // ยังไม่เสร็จ
        'starting_avg_weight' => 15.5,  // kg/head เมื่อเข้า
    ]);

    // คำนวณ ending weight (สมมติ gain ~0.7 kg/day สำหรับ 10 วัน)
    $daysInFarm = 10;
    $daysGain = $daysInFarm * 0.7;  // ~7 kg
    $endingWeight = 15.5 + $daysGain;

    $batch->average_weight_per_pig = round($endingWeight, 2);
    $batch->save();

    echo "✅ Batch 53 updated:\n";
    echo "  entry_date: " . $batch->entry_date . "\n";
    echo "  starting_avg_weight: " . $batch->starting_avg_weight . "\n";
    echo "  average_weight_per_pig: " . $batch->average_weight_per_pig . "\n";
} else {
    echo "❌ Batch 53 not found\n";
}
?>
