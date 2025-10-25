<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PigSale;

$sales = PigSale::all();
foreach ($sales as $sale) {
    echo "ID {$sale->id}: Status={$sale->status}, Rejected_by={$sale->rejected_by}, Rejected_at={$sale->rejected_at}\n";
}
