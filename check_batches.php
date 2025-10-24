<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';

use App\Models\Batch;
use App\Models\PigDeath;

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Active Batches ===\n";
$batches = Batch::where('status', '!=', 'completed')->get(['id', 'batch_code']);
foreach ($batches as $batch) {
    echo "ID: {$batch->id}, Code: {$batch->batch_code}\n";
}

echo "\n=== PigDeath Records ===\n";
$deaths = PigDeath::all(['batch_id', 'pen_id', 'quantity', 'status']);
foreach ($deaths as $death) {
    echo "Batch: {$death->batch_id}, Pen: {$death->pen_id}, Qty: {$death->quantity}, Status: {$death->status}\n";
}
