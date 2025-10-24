<?php
require 'vendor/autoload.php';
\Dotenv\Dotenv::createImmutable(__DIR__)->load();
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PigSale;
use Illuminate\Support\Facades\DB;

echo "=== SQL query ===\n";
$sql = "SELECT id, status, quantity FROM pig_sales WHERE status LIKE '%cancel%' OR status LIKE '%ยก%'";
echo $sql . "\n\n";

$results = DB::select($sql);
echo "Results: " . count($results) . " records\n";

foreach ($results as $row) {
    echo "  ID={$row->id}: status='{$row->status}' qty={$row->quantity}\n";
}

exit;
