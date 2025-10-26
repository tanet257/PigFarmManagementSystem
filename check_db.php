<?php
/**
 * Direct Database Check
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "📊 Checking PigDeath table directly...\n";
echo "───────────────────────────────────────\n";

$deaths = DB::table('pig_deaths')
    ->where('batch_id', 52)
    ->where('pen_id', 41)
    ->get();

echo "Found " . count($deaths) . " records\n\n";

foreach ($deaths as $death) {
    echo "ID: {$death->id}\n";
    echo "  - quantity: {$death->quantity}\n";
    echo "  - quantity_sold_total: {$death->quantity_sold_total}\n";
    echo "  - price_per_pig: {$death->price_per_pig}\n";
    echo "  - status: {$death->status}\n";
    echo "\n";
}

echo "───────────────────────────────────────\n";
echo "Done!\n";
?>
