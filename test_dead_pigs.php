<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== Checking Dead Pigs in Batch 50 ===\n";

$deaths = \App\Models\PigDeath::where('batch_id', 50)->where('status', 'recorded')->get();
echo "Total deaths: " . $deaths->count() . "\n\n";

foreach ($deaths as $d) {
    echo "Death ID: {$d->id}, Pen ID: {$d->pen_id}, Qty: {$d->quantity}\n";
    
    $pen = \App\Models\Pen::find($d->pen_id);
    if ($pen) {
        echo "  ✓ Pen found: {$pen->pen_code} (ID: {$pen->id})\n";
        $barn = $pen->barn;
        if ($barn) {
            echo "    Barn: {$barn->barn_code}\n";
        } else {
            echo "    ✗ Barn NOT found!\n";
        }
    } else {
        echo "  ✗ Pen NOT found (pen_id: {$d->pen_id})\n";
    }
}

echo "\n=== Testing getPigsByBatch ===\n";
$pigs = \App\Helpers\PigInventoryHelper::getPigsByBatch(50);
echo "Total pigs: " . count($pigs['pigs']) . "\n";
$dead_count = 0;
foreach ($pigs['pigs'] as $pig) {
    if ($pig['is_dead'] ?? false) {
        $dead_count++;
        echo "Dead pig: Pen {$pig['pen_id']}, Qty: {$pig['current_quantity']}\n";
    }
}
echo "Dead pigs in result: $dead_count\n";
