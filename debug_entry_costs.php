<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PigEntryRecord;
use App\Models\Cost;
use App\Models\CostPayment;

echo "=== CHECK PigEntryRecord Costs ===\n\n";

// ค้นหา PigEntryRecord ทั้งหมด
$records = PigEntryRecord::with('costs')->get();

echo "Total PigEntryRecords: " . $records->count() . "\n\n";

foreach ($records as $record) {
    echo "Record ID: " . $record->id . " | Batch: " . $record->batch?->batch_code . "\n";

    // ลอง 2 วิธี
    echo "  Method 1: \$record->costs() = " . $record->costs()->count() . " costs\n";

    $costs = Cost::where('pig_entry_record_id', $record->id)->get();
    echo "  Method 2: Cost WHERE pig_entry_record_id = " . $costs->count() . " costs\n";

    // ค้นหา Cost ที่ latest
    $latestCost = $record->costs()->latest()->first();
    if ($latestCost) {
        echo "    ├─ Latest Cost ID: " . $latestCost->id . "\n";
        $payment = $latestCost->costPayment;
        if ($payment) {
            echo "    └─ CostPayment Status: " . $payment->status . "\n";
        } else {
            echo "    └─ ไม่มี CostPayment\n";
        }
    }

    echo "\n";
}

?>
