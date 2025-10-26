<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Batch;
use Carbon\Carbon;

// Check current batch 53
$batch = Batch::find(53);
echo "Before:\n";
echo "  entry_date: " . ($batch->entry_date ? $batch->entry_date->format('Y-m-d') : 'NULL') . "\n";
echo "  starting_avg_weight: " . $batch->starting_avg_weight . "\n\n";

// Update again with Carbon
$batch->entry_date = Carbon::now()->subDays(10);
$batch->starting_avg_weight = 15.5;
$batch->save();

$batch->refresh();
echo "After:\n";
echo "  entry_date: " . ($batch->entry_date ? $batch->entry_date->format('Y-m-d') : 'NULL') . "\n";
echo "  starting_avg_weight: " . $batch->starting_avg_weight . "\n";
?>
