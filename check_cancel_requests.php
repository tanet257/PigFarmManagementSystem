<?php
require 'vendor/autoload.php';
\Dotenv\Dotenv::createImmutable(__DIR__)->load();
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PigSale;

echo "=== ตรวจสอบ Cancel Requests ===\n\n";

$cancelRequests = PigSale::where('status', 'ยกเลิกการขาย')->get();
echo "PigSale with status 'ยกเลิกการขาย': " . $cancelRequests->count() . "\n";

foreach ($cancelRequests as $sale) {
    echo "\nID={$sale->id}:\n";
    echo "  status: {$sale->status}\n";
    echo "  batch_id: {$sale->batch_id}\n";
    echo "  quantity: {$sale->quantity}\n";
    echo "  created_at: " . $sale->created_at->format('d/m/Y H:i:s') . "\n";
    echo "  farm: " . ($sale->farm?->farm_name ?? 'N/A') . "\n";
}

echo "\n=== ทั้งหมด PigSale Status ===\n";
$statusCounts = PigSale::selectRaw('status, COUNT(*) as count')
    ->groupBy('status')
    ->get();

foreach ($statusCounts as $row) {
    echo "  {$row->status}: {$row->count}\n";
}

exit;
