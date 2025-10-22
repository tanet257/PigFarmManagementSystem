<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PigEntryDetail;

echo "=== PigEntryDetail ทั้งหมด ===\n\n";

for ($i = 18; $i <= 21; $i++) {
    $details = PigEntryDetail::where('pig_entry_id', $i)->get();
    echo "PigEntry ID: $i - {$details->count()} details\n";

    $totalQty = 0;
    foreach ($details as $detail) {
        echo "  - Pen ID: {$detail->pen_id}, Qty: {$detail->quantity}\n";
        $totalQty += $detail->quantity;
    }
    echo "  Total: {$totalQty} pigs\n\n";
}
