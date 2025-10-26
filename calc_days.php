<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PigEntryRecord;
use Carbon\Carbon;

$pigEntry = PigEntryRecord::where('batch_id', 53)->first();

if ($pigEntry) {
    echo "Entry Date: " . $pigEntry->pig_entry_date . "\n";
    echo "Today: " . Carbon::now() . "\n";

    $daysInFarm = max(Carbon::parse($pigEntry->pig_entry_date)->diffInDays(Carbon::now()), 1);
    echo "Days in farm: " . $daysInFarm . "\n";

    echo "Starting Weight: " . $pigEntry->average_weight_per_pig . "\n";
    echo "Ending Weight: 22.5\n";
    echo "Weight Gain: " . (22.5 - $pigEntry->average_weight_per_pig) . "\n";
    echo "ADG = " . ((22.5 - $pigEntry->average_weight_per_pig) / $daysInFarm) . "\n";
}
?>
