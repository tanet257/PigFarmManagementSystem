<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Batch;
use App\Models\PigEntryRecord;
use App\Models\Cost;
use App\Models\CostPayment;

echo "=== ค้นหา Batches ทั้งหมด ===\n\n";

$batches = Batch::all();
echo "Total Batches: " . $batches->count() . "\n\n";

foreach ($batches as $batch) {
    echo "Batch: " . $batch->batch_code . " (ID: " . $batch->id . ")\n";

    // ค้นหา PigEntryRecord
    $entries = PigEntryRecord::where('batch_id', $batch->id)->get();
    echo "  PigEntries: " . $entries->count() . "\n";

    // ค้นหา Cost
    $costs = Cost::where('batch_id', $batch->id)->get();
    echo "  Costs: " . $costs->count() . "\n";

    if ($costs->count() > 0) {
        foreach ($costs as $cost) {
            $payment = CostPayment::where('cost_id', $cost->id)->latest()->first();
            if ($payment) {
                echo "    ├─ Cost ID: " . $cost->id . " | Status: " . $payment->status . "\n";
            }
        }
    }
    echo "\n";
}

?>
