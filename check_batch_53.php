<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Batch;

// ดู batch ทั้งหมด
$batches = Batch::select('id', 'batch_code', 'status', 'current_quantity')->get();

echo "=== Batches ===\n";
foreach ($batches as $batch) {
    echo "ID: {$batch->id}, Code: {$batch->batch_code}, Status: {$batch->status}, Qty: {$batch->current_quantity}\n";
}
?>
