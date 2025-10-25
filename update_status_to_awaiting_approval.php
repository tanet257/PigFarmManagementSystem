<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== แปลง 'ยกเลิกการขาย_รอสอบ' เป็น 'ยกเลิกการขาย_รอการอนุมัติ' ===\n\n";

// Update all 'ยกเลิกการขาย_รอสอบ' to 'ยกเลิกการขาย_รอการอนุมัติ'
$updated = DB::table('pig_sales')
    ->where('status', 'ยกเลิกการขาย_รอสอบ')
    ->update(['status' => 'ยกเลิกการขาย_รอการอนุมัติ']);

echo "✅ Updated: $updated records\n\n";

// Show summary
echo "=== สรุปสถานะปัจจุบัน ===\n";
$statuses = DB::table('pig_sales')->groupBy('status')->selectRaw('status, COUNT(*) as count')->get();
foreach ($statuses as $s) {
    echo "Status '{$s->status}': {$s->count} records\n";
}
