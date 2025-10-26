<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Http\Kernel::class);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Profit;

echo "=== CHECK PROFIT STATUS VALUES ===\n\n";

// ดู distinct status values
$statusValues = Profit::distinct('status')->pluck('status')->toArray();
echo "Distinct status values in database:\n";
foreach ($statusValues as $status) {
    $count = Profit::where('status', $status)->count();
    echo "  - '$status': $count records\n";
}

// ทดสอบ filter
echo "\nFilter test:\n";
$completed = Profit::where('status', 'completed')->count();
echo "  - status='completed': $completed records\n";

$incomplete = Profit::where('status', 'incomplete')->count();
echo "  - status='incomplete': $incomplete records\n";

?>
