<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PigSale;
use Illuminate\Support\Facades\DB;

echo "=== แปลง 'completed' เป็น 'approved' ===\n\n";

// Update all 'completed' to 'approved'
$updated = DB::table('pig_sales')->where('status', 'completed')->update(['status' => 'approved']);
echo "✅ Updated: $updated records\n\n";

// Show summary
echo "=== สรุปสถานะปัจจุบัน ===\n";
$statuses = DB::table('pig_sales')->groupBy('status')->selectRaw('status, COUNT(*) as count')->get();
foreach ($statuses as $s) {
    echo "Status '{$s->status}': {$s->count} records\n";
}
