<?php
// Quick test to check batch uniqueness

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$batches = \App\Models\Batch::all(['id', 'farm_id', 'batch_code', 'status']);

echo "=== Batch Records ===\n";
foreach($batches as $b) {
    echo "ID: {$b->id}, Farm: {$b->farm_id}, Code: {$b->batch_code}, Status: {$b->status}\n";
}

echo "\n=== Check for duplicate batch_codes ===\n";
$batchCodes = $batches->groupBy('batch_code');
foreach($batchCodes as $code => $records) {
    if(count($records) > 1) {
        echo "Duplicate batch_code: $code\n";
        foreach($records as $r) {
            echo "  - ID: {$r->id}, Farm: {$r->farm_id}\n";
        }
    }
}
?>
