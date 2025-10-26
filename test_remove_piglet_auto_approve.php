<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Cost;
use App\Models\CostPayment;

echo "=== ลบ Auto-Approve CostPayment สำหรับ Piglet ===\n\n";

// ค้นหา piglet cost ของ PigEntry 42
$pigletCost = Cost::where('pig_entry_record_id', 42)
    ->where('cost_type', 'piglet')
    ->first();

if (!$pigletCost) {
    echo "❌ ไม่พบ piglet cost สำหรับ PigEntry 42\n";
    exit;
}

echo "✓ พบ Piglet Cost:\n";
echo "  - Cost ID: {$pigletCost->id}\n";
echo "  - Total Price: ฿" . number_format($pigletCost->total_price, 2) . "\n\n";

// ค้นหา CostPayment ของ cost นี้
$costPayment = CostPayment::where('cost_id', $pigletCost->id)->first();

if (!$costPayment) {
    echo "❌ ไม่พบ CostPayment ของ Piglet Cost นี้\n";
    exit;
}

echo "ℹ️ พบ CostPayment (Auto-Approved):\n";
echo "  - CostPayment ID: {$costPayment->id}\n";
echo "  - Status: {$costPayment->status}\n";
echo "  - Amount: ฿" . number_format((float)$costPayment->amount, 2) . "\n\n";

// ลบ CostPayment นี้
echo "📝 ลบ CostPayment เพื่อให้ piglet กลับมาต้อง manual approval...\n";
$costPayment->delete();
echo "✓ ลบเสร็จแล้ว\n\n";

echo "✅ เสร็จสิ้น! Piglet cost 515 ตอนนี้ต้อง manual approval และปุ่ม payment จะแสดงอีกครั้ง\n";
