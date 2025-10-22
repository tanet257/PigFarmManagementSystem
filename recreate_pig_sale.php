<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PigSale;
use App\Models\Revenue;
use App\Models\Batch;

echo "=== ลบข้อมูลเก่าและสร้างใหม่ ===\n\n";

// 1. ลบ Revenue เก่า
Revenue::truncate();
echo "✓ ลบ Revenue เก่า\n";

// 2. สร้าง PigSale ใหม่ (ทำเหมือน approve ผ่าน controller)
$batch = Batch::where('batch_code', 'ฺฺฺf1-b2501')->first();
if (!$batch) {
    echo "✗ ไม่พบ Batch\n";
    exit;
}

// ลบ PigSale เก่า
$oldPigSale = PigSale::where('batch_id', $batch->id)->first();
if ($oldPigSale) {
    Revenue::where('pig_sale_id', $oldPigSale->id)->delete();
    $oldPigSale->delete();
    echo "✓ ลบ PigSale เก่า\n";
}

// สร้าง PigSale ใหม่
$adminUser = \App\Models\User::where('usertype', 'admin')->first();

$pigSale = PigSale::create([
    'farm_id' => $batch->farm_id,
    'batch_id' => $batch->id,
    'date' => now()->format('Y-m-d'),
    'sell_type' => 'general',
    'quantity' => 200,
    'total_weight' => 2800,
    'estimated_weight' => 2800,
    'actual_weight' => 2800,
    'avg_weight_per_pig' => 14,
    'price_per_kg' => 500,
    'price_per_pig' => 7000,
    'cpf_reference_price' => 500,
    'cpf_reference_date' => now()->format('Y-m-d'),
    'total_price' => 1400000,
    'discount' => 3200,
    'shipping_cost' => 0,
    'net_total' => 1396800,
    'payment_method' => 'transfer',
    'payment_term' => 30,
    'payment_status' => 'รอชำระ',
    'paid_amount' => 0,
    'balance' => 1396800,
    'due_date' => now()->addDays(30)->format('Y-m-d'),
    'buyer_name' => 'คนซื้อ1',
    'status' => 'completed',
    'created_by' => $adminUser->id,  // user id ไม่ใช่ string
]);
echo "✓ สร้าง PigSale ใหม่ (ID: {$pigSale->id})\n";

// 3. simulate approve
$pigSale->update([
    'approved_by' => $adminUser->id,
    'approved_at' => now(),
]);
echo "✓ อนุมัติ PigSale\n";

// 4. เรียก RevenueHelper::recordPigSaleRevenue
$result = \App\Helpers\RevenueHelper::recordPigSaleRevenue($pigSale);
echo "✓ Record Revenue: " . ($result['success'] ? 'SUCCESS' : 'FAILED - ' . $result['message']) . "\n";

// 5. Calculate Profit
$profitResult = \App\Helpers\RevenueHelper::calculateAndRecordProfit($batch->id);
echo "✓ Calculate Profit: " . ($profitResult['success'] ? 'SUCCESS' : 'FAILED - ' . $profitResult['message']) . "\n";

echo "\nเสร็จ!\n";
