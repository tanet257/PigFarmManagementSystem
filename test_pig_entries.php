<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PigEntryRecord;

echo "=== ดูสถานะ PigEntry ===\n\n";

for ($i = 18; $i <= 22; $i++) {
    $entry = PigEntryRecord::find($i);
    if ($entry) {
        echo "PigEntry ID: $i, Status: {$entry->status}, Batch: {$entry->batch_id}\n";
    }
}
