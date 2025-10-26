<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Profit;

$batchId = 53;

echo "=== Clearing Batch 53 ===\n";

// Clear data in order
DB::table('pig_deaths')->where('batch_id', $batchId)->delete();
DB::table('batch_treatments')->where('batch_id', $batchId)->delete();

$dairyIds = DB::table('dairy_records')->where('batch_id', $batchId)->pluck('id');
DB::table('dairy_storehouse_uses')->whereIn('dairy_record_id', $dairyIds)->delete();
DB::table('dairy_records')->where('batch_id', $batchId)->delete();

DB::table('inventory_movements')->where('batch_id', $batchId)->delete();

// Reset Profit
$profit = Profit::where('batch_id', $batchId)->first();
if ($profit) {
    $profit->update([
        'adg' => 0,
        'fcr' => 0,
        'fcg' => 0,
        'total_feed_kg' => 0,
        'total_feed_bags' => 0,
        'total_weight_gained' => 0,
    ]);
}

echo "✅ Batch 53 cleared\n";
?>
