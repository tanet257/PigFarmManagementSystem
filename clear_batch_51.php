<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$batchId = 51;

// Clear DairyRecord และสิ่งที่เกี่ยวข้อง
DB::table('pig_deaths')->where('batch_id', $batchId)->delete();
DB::table('batch_treatments')->where('batch_id', $batchId)->delete();
DB::table('dairy_storehouse_uses')
    ->whereIn('dairy_record_id',
        DB::table('dairy_records')->where('batch_id', $batchId)->pluck('id'))
    ->delete();
DB::table('dairy_records')->where('batch_id', $batchId)->delete();

// Clear InventoryMovement
DB::table('inventory_movements')->where('batch_id', $batchId)->delete();

// Reset Profit
DB::table('profits')->where('batch_id', $batchId)->update([
    'adg' => 0,
    'fcr' => 0,
    'fcg' => 0,
    'total_feed_kg' => 0,
]);

echo "✅ Batch 51 cleared for re-upload\n";
?>
