<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// ดูโครงสร้าง pig_sales table
$columns = DB::select("DESCRIBE pig_sales");
echo "=== pig_sales table structure ===\n\n";
foreach ($columns as $column) {
    if ($column->Field === 'approved_by' || $column->Field === 'created_by') {
        echo "{$column->Field}: {$column->Type} - Null: {$column->Null} - Default: {$column->Default}\n";
    }
}
