<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$columns = DB::select("DESCRIBE cost_payments");
echo "=== cost_payments table ===\n";
foreach ($columns as $col) {
    if ($col->Field === 'status') {
        echo "{$col->Field}: {$col->Type}\n";
    }
}
