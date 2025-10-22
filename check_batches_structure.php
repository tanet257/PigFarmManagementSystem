<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// ดูโครงสร้าง batches table
$columns = DB::select("DESCRIBE batches");
echo "=== batches table columns ===\n\n";
foreach ($columns as $column) {
    if (strpos(strtolower($column->Field), 'pig') !== false ||
        strpos(strtolower($column->Field), 'initial') !== false ||
        strpos(strtolower($column->Field), 'current') !== false) {
        echo "{$column->Field}: {$column->Type}\n";
    }
}

echo "\n=== All columns ===\n";
foreach ($columns as $column) {
    echo "{$column->Field}\n";
}
