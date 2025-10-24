<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PigSale;

// ดูสถานะทั้งหมดของ PigSale
$pigSales = PigSale::select('id', 'status', 'payment_status', 'approved_at', 'quantity')
    ->orderBy('id', 'DESC')
    ->limit(20)
    ->get();

echo "=== ตรวจสอบ PigSale Status ===\n\n";

foreach ($pigSales as $sale) {
    echo "ID: {$sale->id} | Status: {$sale->status} | Payment: {$sale->payment_status} | Approved: " . ($sale->approved_at ? 'Yes' : 'No') . " | Qty: {$sale->quantity}\n";
}

echo "\n=== สรุป ===\n";
$statuses = PigSale::groupBy('status')->selectRaw('status, COUNT(*) as count')->get();
foreach ($statuses as $s) {
    echo "Status '{$s->status}': {$s->count} records\n";
}
