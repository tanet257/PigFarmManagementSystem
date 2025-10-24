<?php
require 'vendor/autoload.php';
\Dotenv\Dotenv::createImmutable(__DIR__)->load();
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PigSale;

echo "=== ตรวจสอบเวลาสร้างคำขอยกเลิก ===\n\n";

// ดึง PigSale ทั้งหมดที่มี status = cancel_requested หรือ ยกเลิกการขาย
$cancelSales = PigSale::whereIn('status', ['cancel_requested', 'ยกเลิกการขาย'])
    ->orderBy('created_at', 'desc')
    ->get();

echo "พบคำขอยกเลิก: " . $cancelSales->count() . " รายการ\n\n";

foreach ($cancelSales as $sale) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "ID: {$sale->id}\n";
    echo "  ประเภท: {$sale->sell_type}\n";
    echo "  สถานะ: {$sale->status}\n";
    echo "  จำนวน: {$sale->quantity} ตัว\n";
    echo "  ราคา: " . number_format($sale->net_total, 2) . " ฿\n";
    echo "  บันทึกเมื่อ: " . $sale->created_at->format('d/m/Y H:i:s') . "\n";
    echo "  ปรับปรุงเมื่อ: " . $sale->updated_at->format('d/m/Y H:i:s') . "\n";

    if ($sale->created_by) {
        $creator = \App\Models\User::find($sale->created_by);
        echo "  ผู้บันทึก: " . ($creator ? $creator->name : 'ไม่ทราบ') . "\n";
    }

    echo "\n";
}

exit;
