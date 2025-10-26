<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PigEntryRecord;
use App\Models\Cost;
use App\Models\Batch;
use App\Helpers\RevenueHelper;

echo "=== ดึงข้อมูล PigEntry ID 42 ===\n\n";

$entry = PigEntryRecord::find(42);
if (!$entry) {
    echo "❌ ไม่พบ PigEntry ID 42\n";
    exit;
}

echo "✓ PigEntry ID: {$entry->id}\n";
echo "  - Farm ID: {$entry->farm_id}\n";
echo "  - Batch ID: {$entry->batch_id}\n";
echo "  - Entry Code: {$entry->entry_code}\n";
echo "  - Status: {$entry->status}\n\n";

// ดูว่า Cost มีหรือไม่
$cost = Cost::where('pig_entry_record_id', 42)->first();
if ($cost) {
    echo "✓ พบ Cost ที่เชื่อมต่อ:\n";
    echo "  - Cost ID: {$cost->id}\n";
    echo "  - Cost Type: {$cost->cost_type}\n";
    echo "  - Transport Cost: {$cost->transport_cost}\n";
    echo "  - Total Price: {$cost->total_price}\n\n";
} else {
    echo "❌ ไม่พบ Cost ที่เชื่อมต่อ\n\n";
}

// ตรวจสอบ Batch
$batch = Batch::find($entry->batch_id);
if ($batch) {
    echo "ℹ️ Batch Info:\n";
    echo "  - Batch ID: {$batch->id}\n";
    echo "  - Batch Code: {$batch->batch_code}\n";
    echo "  - Farm ID: {$batch->farm_id}\n\n";
}

// สร้าง Cost ใหม่ถ้าไม่มี
if (!$cost) {
    echo "📝 สร้าง Cost ใหม่ ...\n";
    $basePrice = 0; // อาจมีราคาอื่นด้วย
    $cost = Cost::create([
        'pig_entry_record_id' => 42,
        'batch_id' => $entry->batch_id,
        'farm_id' => $entry->farm_id,
        'cost_type' => 'shipping',
        'transport_cost' => 76000,
        'total_price' => $basePrice + 76000,
        'note' => 'Transport cost for pigentry 42 (recovered from wipeout)',
        'date' => now(),
    ]);
    echo "✓ สร้าง Cost ID: {$cost->id}\n";
    echo "  - Cost Type: {$cost->cost_type}\n";
    echo "  - Transport Cost: {$cost->transport_cost}\n";
    echo "  - Total Price: {$cost->total_price}\n\n";
} else {
    echo "ℹ️ Cost มีอยู่แล้ว อัปเดท transport_cost...\n";
    $oldPrice = $cost->total_price;
    $cost->transport_cost = 76000;
    $cost->total_price = ($cost->total_price - ($cost->transport_cost ?? 0)) + 76000;
    $cost->save();
    echo "✓ อัปเดท transport_cost เป็น 76000\n";
    echo "  - Old Total: {$oldPrice}\n";
    echo "  - New Total: {$cost->total_price}\n\n";
}

// สร้าง CostPayment ให้ auto-approve
$costPayment = \App\Models\CostPayment::where('cost_id', $cost->id)->first();
if (!$costPayment) {
    echo "📝 สร้าง CostPayment ให้ auto-approve ...\n";
    $costPayment = \App\Models\CostPayment::create([
        'cost_id' => $cost->id,
        'cost_type' => $cost->cost_type,
        'status' => 'approved',
        'amount' => $cost->total_price,
        'approved_by' => 1,
        'approved_date' => now(),
    ]);
    echo "✓ สร้าง CostPayment ID: {$costPayment->id}\n";
    echo "  - Status: {$costPayment->status}\n";
    echo "  - Amount: {$costPayment->amount}\n\n";
} else {
    echo "ℹ️ CostPayment มีอยู่แล้ว อัปเดท amount...\n";
    $costPayment->amount = $cost->total_price;
    $costPayment->status = 'approved';
    $costPayment->save();
    echo "✓ อัปเดท amount เป็น {$costPayment->amount}\n\n";
}

// รีคำนวณ Profit
if ($entry->batch_id) {
    echo "🔄 รีคำนวณ Profit สำหรับ Batch {$entry->batch_id} ...\n";
    RevenueHelper::calculateAndRecordProfit($entry->batch_id);
    echo "✓ Profit recalculated\n\n";
}

echo "✅ เสร็จสิ้น! ข้อมูล pigentry 42 ประกอบกับ transport cost 76000 เข้า dashboard แล้ว\n";
