<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$batches = \App\Models\Batch::select('id', 'batch_code', 'farm_id')
    ->where('status', '!=', 'เสร็จสิ้น')
    ->where('status', '!=', 'cancelled')
    ->get();

echo "Batches Count: " . $batches->count() . "\n";
if ($batches->count() > 0) {
    echo "First batch: " . json_encode($batches->first()) . "\n";
} else {
    echo "No batches found\n";
}

// ตรวจสอบ $batch error
// โปรแกรมตรวจสอบว่า error มาจากไหน
echo "\n✅ Test complete\n";
