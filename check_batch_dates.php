<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Batch;
use App\Models\Profit;

$batch = Batch::find(53);
echo "Batch 53:\n";
echo json_encode($batch->only(['id', 'entry_date', 'exit_date', 'starting_avg_weight', 'average_weight_per_pig', 'total_pig_weight']), JSON_PRETTY_PRINT) . "\n";

$profit = Profit::where('batch_id', 53)->first();
echo "\nProfit 53:\n";
echo json_encode($profit->only(['id', 'starting_avg_weight', 'ending_avg_weight', 'days_in_farm']), JSON_PRETTY_PRINT) . "\n";
?>
