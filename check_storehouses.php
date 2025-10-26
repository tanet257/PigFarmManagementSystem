<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\StoreHouse;

$storehouses = StoreHouse::where('farm_id', 2)->select('id', 'item_code', 'item_name')->get();

echo "=== Storehouses Farm 2 ===\n";
foreach ($storehouses as $s) {
    echo "ID: {$s->id}, Code: {$s->item_code}, Name: {$s->item_name}\n";
}

// ตรวจสอบ storehouse 258, 259, 260
echo "\n=== Check specific IDs ===\n";
foreach ([258, 259, 260] as $id) {
    $s = StoreHouse::find($id);
    if ($s) {
        echo "ID $id: {$s->item_name} (Farm: {$s->farm_id})\n";
    } else {
        echo "ID $id: NOT FOUND\n";
    }
}
?>
