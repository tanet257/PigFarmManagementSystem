<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PigEntryRecord;
use App\Models\Cost;
use App\Models\Batch;
use App\Helpers\RevenueHelper;

echo "=== เพิ่มค่าหมูเข้า (Piglet Cost) สำหรับ PigEntry 42 ===\n\n";

$entry = PigEntryRecord::find(42);
if (!$entry) {
    echo "❌ ไม่พบ PigEntry ID 42\n";
    exit;
}

echo "✓ PigEntry ID: {$entry->id}\n";
echo "  - Batch ID: {$entry->batch_id}\n";
echo "  - Farm ID: {$entry->farm_id}\n\n";

$batch = Batch::find($entry->batch_id);
if ($batch) {
    echo "ℹ️ Batch Info:\n";
    echo "  - Batch Code: {$batch->batch_code}\n";
    echo "  - Batch ID: {$batch->id}\n\n";
}

// ตรวจสอบว่ามี piglet cost แล้วหรือไม่
$pigletCost = Cost::where('pig_entry_record_id', 42)
    ->where('cost_type', 'piglet')
    ->first();

if ($pigletCost) {
    echo "ℹ️ พบ Piglet Cost อยู่แล้ว:\n";
    echo "  - Cost ID: {$pigletCost->id}\n";
    echo "  - Total Price: {$pigletCost->total_price}\n\n";
    echo "📝 อัปเดท piglet cost เป็น 5,000,000 บาท...\n";

    $oldPrice = $pigletCost->total_price;
    $pigletCost->total_price = 5000000;
    $pigletCost->save();

    echo "✓ อัปเดท:\n";
    echo "  - Old: {$oldPrice}\n";
    echo "  - New: 5,000,000\n\n";
} else {
    echo "📝 สร้าง Piglet Cost ใหม่ ...\n";
    $pigletCost = Cost::create([
        'pig_entry_record_id' => 42,
        'batch_id' => $entry->batch_id,
        'farm_id' => $entry->farm_id,
        'cost_type' => 'piglet',
        'quantity' => 1,
        'unit' => 'batch',
        'total_price' => 5000000,
        'note' => 'Piglet cost for pigentry 42 (recovered from wipeout)',
        'date' => now(),
    ]);
    echo "✓ สร้าง Cost ID: {$pigletCost->id}\n";
    echo "  - Cost Type: piglet\n";
    echo "  - Total Price: 5,000,000 บาท\n\n";
}

// Auto-approve CostPayment
$costPayment = \App\Models\CostPayment::where('cost_id', $pigletCost->id)->first();
if (!$costPayment) {
    echo "📝 สร้าง CostPayment ให้ auto-approve ...\n";
    $costPayment = \App\Models\CostPayment::create([
        'cost_id' => $pigletCost->id,
        'cost_type' => 'piglet',
        'status' => 'approved',
        'amount' => $pigletCost->total_price,
        'approved_by' => 1,
        'approved_date' => now(),
    ]);
    echo "✓ สร้าง CostPayment ID: {$costPayment->id}\n";
    echo "  - Status: approved\n";
    echo "  - Amount: 5,000,000 บาท\n\n";
} else {
    echo "ℹ️ CostPayment มีอยู่แล้ว อัปเดท amount...\n";
    $oldAmount = $costPayment->amount;
    $costPayment->amount = $pigletCost->total_price;
    $costPayment->status = 'approved';
    $costPayment->save();
    echo "✓ อัปเดท:\n";
    echo "  - Old: {$oldAmount}\n";
    echo "  - New: 5,000,000 บาท\n\n";
}

// รีคำนวณ Profit
if ($entry->batch_id) {
    echo "🔄 รีคำนวณ Profit สำหรับ Batch {$entry->batch_id} ...\n";
    RevenueHelper::calculateAndRecordProfit($entry->batch_id);
    echo "✓ Profit recalculated\n\n";
}

// แสดงรายการ Cost ทั้งหมดของ PigEntry 42
$allCosts = Cost::where('pig_entry_record_id', 42)->get();
echo "📋 Cost Summary สำหรับ PigEntry 42:\n";
$totalCost = 0;
foreach ($allCosts as $cost) {
    echo "  - {$cost->cost_type}: ฿" . number_format($cost->total_price, 2) . "\n";
    $totalCost += $cost->total_price;
}
echo "  ━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  รวม: ฿" . number_format($totalCost, 2) . "\n\n";

echo "✅ เสร็จสิ้น! ค่าหมูเข้า 5,000,000 บาท + ค่าขนส่ง 76,000 บาท = 5,076,000 บาท เข้า Dashboard แล้ว\n";
