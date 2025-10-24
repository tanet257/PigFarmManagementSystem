<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== Full getPigsByBatch output ===\n";
$pigs = \App\Helpers\PigInventoryHelper::getPigsByBatch(50);

echo "Dead pigs:\n";
foreach ($pigs['pigs'] as $pig) {
    if ($pig['is_dead'] ?? false) {
        echo "  Pen: {$pig['pen_id']}\n";
        echo "    current_quantity: {$pig['current_quantity']}\n";
        echo "    available: {$pig['available']}\n";
        echo "    display_name: {$pig['display_name']}\n";
        echo "\n";
    }
}
