<?php
/**
 * Check: Verify PigSale approval status
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PigSale;
use Illuminate\Support\Facades\DB;

echo "📊 PigSale Status Report\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Get all sales
$allSales = PigSale::all();

echo "📈 Total records: {$allSales->count()}\n\n";

// Group by status
$byStatus = $allSales->groupBy('status');
echo "Status Distribution:\n";
foreach ($byStatus as $status => $records) {
    echo "  • {$status}: {$records->count()}\n";
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "⚠️  Records with payment_status but missing approved_by:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$missingApproval = PigSale::whereIn('payment_status', ['ชำระแล้ว', 'ชำระบางส่วน', 'เกินกำหนด', 'เกินกำหนด'])
    ->whereNull('approved_by')
    ->get();

if ($missingApproval->count() > 0) {
    foreach ($missingApproval as $sale) {
        echo "ID {$sale->id}:\n";
        echo "  • Status: {$sale->status}\n";
        echo "  • Payment Status: {$sale->payment_status}\n";
        echo "  • Approved By: " . ($sale->approved_by ? $sale->approved_by : 'NULL') . "\n";
        echo "  • Approved At: " . ($sale->approved_at ? $sale->approved_at : 'NULL') . "\n";
        echo "  • Rejected By: " . ($sale->rejected_by ? $sale->rejected_by : 'NULL') . "\n";
        echo "  • Updated At: {$sale->updated_at}\n\n";
    }
} else {
    echo "✅ ไม่มี records ที่ต้องแก้ไข\n\n";
}

// Show all records with their approval info
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📋 All Sales with Approval Info:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$sales = PigSale::orderBy('id')->get();
foreach ($sales as $sale) {
    echo "ID {$sale->id}: Status={$sale->status}, Payment={$sale->payment_status}\n";
    echo "  └─ Approved By: " . ($sale->approved_by ? $sale->approvedBy->name . " ({$sale->approved_by})" : 'NULL') . "\n";
    echo "  └─ Approved At: " . ($sale->approved_at ? $sale->approved_at : 'NULL') . "\n";
    echo "  └─ Rejected By: " . ($sale->rejected_by ? $sale->rejectedBy->name . " ({$sale->rejected_by})" : 'NULL') . "\n";
    echo "\n";
}
