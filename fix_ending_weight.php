<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Batch;

// Fix ending weight
$batch = Batch::find(53);
if ($batch) {
    $batch->average_weight_per_pig = 95.5;  // Starting 82 + gain 13.5
    $batch->save();

    echo "✅ Batch 53 updated:\n";
    echo "  Starting Weight (from PigEntry): 82 kg\n";
    echo "  Ending Weight (updated): 95.5 kg\n";
    echo "  Weight Gain: 13.5 kg per head\n";
}
?>
