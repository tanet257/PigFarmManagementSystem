<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// ดูโครงสร้าง costs table
$columns = DB::select("DESCRIBE costs");
echo "=== costs table columns ===\n\n";
foreach ($columns as $column) {
    if (strpos($column->Field, 'status') !== false ||
        strpos($column->Field, 'cancelled') !== false) {
        echo "{$column->Field}: {$column->Type}\n";
    }
}

echo "\n=== All columns ===\n";
foreach ($columns as $column) {
    echo "{$column->Field}\n";
}
