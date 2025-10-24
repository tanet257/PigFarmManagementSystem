<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PigDeath;

$death = PigDeath::find(4);
if ($death) {
    echo "Before: Batch " . $death->batch_id . ", Quantity: " . $death->quantity . "\n";
    $death->update(['quantity' => 4]);
    echo "After: Quantity: " . $death->quantity . "\n";
    echo "Observer should have triggered calculateAndRecordProfit!\n";
} else {
    echo "PigDeath not found!\n";
}
