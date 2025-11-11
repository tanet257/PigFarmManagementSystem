<?php
// List and update medicine stock

use App\Models\StoreHouse;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap/app.php';

$app = app();

$medicines = StoreHouse::where('item_type', 'medicine')->get();

echo "Found " . count($medicines) . " medicines\n";
echo str_repeat("=", 80) . "\n";

foreach ($medicines as $medicine) {
    echo "ID: {$medicine->id}\n";
    echo "Name: {$medicine->item_name}\n";
    echo "Current Stock: {$medicine->stock}\n";
    echo "Current Min: {$medicine->min_quantity}\n";

    // Update to stock=120, min_quantity=60
    $medicine->update([
        'stock' => 120,
        'min_quantity' => 60,
    ]);

    echo "✓ Updated to Stock: 120, Min: 60\n";
    echo str_repeat("-", 80) . "\n";
}

echo "\nAll medicines updated!\n";
