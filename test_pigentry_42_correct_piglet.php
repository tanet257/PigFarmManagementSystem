<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PigEntryRecord;
use App\Models\Cost;
use App\Models\CostPayment;
use App\Helpers\RevenueHelper;

echo "=== ดึงข้อมูล PigEntry 42 ===\n\n";

$entry = PigEntryRecord::find(42);
if (!$entry) {
    echo "❌ ไม่พบ PigEntry ID 42\n";
    exit;
}

echo "✓ PigEntry ID: {$entry->id}\n";
echo "  - Batch ID: {$entry->batch_id}\n";
echo "  - Farm ID: {$entry->farm_id}\n";
echo "  - Status: {$entry->status}\n";

// ดึงข้อมูลทั้งหมดของ entry เพื่อเห็นราคา
echo "\n📊 PigEntry Data:\n";
foreach ($entry->getAttributes() as $key => $value) {
    if (is_numeric($value) && $value > 0) {
        echo "  - {$key}: {$value}\n";
    }
}

// ตรวจสอบ piglet cost ที่มีอยู่
$pigletCost = Cost::where('pig_entry_record_id', 42)
    ->where('cost_type', 'piglet')
    ->first();

if ($pigletCost) {
    echo "\nℹ️ พบ Piglet Cost ที่มีอยู่แล้ว:\n";
    echo "  - Cost ID: {$pigletCost->id}\n";
    echo "  - Total Price (Old): ฿" . number_format($pigletCost->total_price, 2) . "\n";

    echo "\n📝 อัปเดท piglet cost เป็น 5,797,120.00 บาท...\n";
    $pigletCost->total_price = 5797120.00;
    $pigletCost->save();
    echo "✓ อัปเดท:\n";
    echo "  - Total Price (New): ฿" . number_format($pigletCost->total_price, 2) . "\n\n";
} else {
    echo "\n❌ ไม่พบ Piglet Cost ที่สร้างไว้\n\n";
    exit;
}

// อัปเดท CostPayment ให้ตรงกับ Cost
$costPayment = CostPayment::where('cost_id', $pigletCost->id)->first();
if ($costPayment) {
    echo "📝 อัปเดท CostPayment...\n";
    $costPayment->amount = 5797120.00;
    $costPayment->save();
    echo "✓ CostPayment อัปเดท:\n";
    echo "  - Amount: ฿" . number_format((float)$costPayment->amount, 2) . "\n\n";
}

// รีคำนวณ Profit
if ($entry->batch_id) {
    echo "🔄 รีคำนวณ Profit สำหรับ Batch {$entry->batch_id} ...\n";
    RevenueHelper::calculateAndRecordProfit($entry->batch_id);
    echo "✓ Profit recalculated\n\n";
}

// แสดงรายการ Cost ทั้งหมด
$allCosts = Cost::where('pig_entry_record_id', 42)->get();
echo "📋 Cost Summary สำหรับ PigEntry 42:\n";
$totalCost = 0;
foreach ($allCosts as $cost) {
    echo "  - {$cost->cost_type}: ฿" . number_format($cost->total_price, 2) . "\n";
    $totalCost += $cost->total_price;
}
echo "  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  รวม: ฿" . number_format($totalCost, 2) . "\n\n";

echo "✅ เสร็จสิ้น! Piglet Cost อัปเดทเป็น 5,797,120.00 บาท เข้า Dashboard แล้ว\n";
