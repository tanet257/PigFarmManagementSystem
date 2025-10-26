<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Http\Kernel::class);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PigEntryRecord;
use App\Models\PigSale;
use App\Models\Profit;
use Carbon\Carbon;

echo "=== TEST FILTER QUERIES ===\n\n";

// Test 1: PigEntryRecord with farm_id filter
echo "1️⃣  PigEntryRecord with farm_id=1:\n";
$count = PigEntryRecord::where('farm_id', 1)->count();
echo "   Count: $count\n\n";

// Test 2: PigEntryRecord with date filter (today)
echo "2️⃣  PigEntryRecord created today:\n";
$today = Carbon::now()->toDateString();
$count = PigEntryRecord::whereDate('pig_entry_date', $today)->count();
echo "   Count: $count\n\n";

// Test 3: PigEntryRecord excluding cancelled
echo "3️⃣  PigEntryRecord (excluding cancelled):\n";
$count = PigEntryRecord::where('status', '!=', 'cancelled')->count();
echo "   Count: $count\n\n";

// Test 4: PigSale with farm_id filter
echo "4️⃣  PigSale with farm_id=1:\n";
$count = PigSale::where('farm_id', 1)->count();
echo "   Count: $count\n\n";

// Test 5: PigSale excluding cancelled/rejected
echo "5️⃣  PigSale (excluding cancelled/rejected):\n";
$count = PigSale::where('status', '!=', 'ยกเลิกการขาย')
               ->where('status', '!=', 'rejected')
               ->where('status', '!=', 'ยกเลิกการขาย_รอการอนุมัติ')
               ->count();
echo "   Count: $count\n\n";

// Test 6: Profit with status filter
echo "6️⃣  Profit with status='completed':\n";
$count = Profit::where('batch_status', 'เสร็จสิ้น')->count();
echo "   Count: $count\n\n";

echo "✅ All queries executed successfully!\n";

?>
