<?php

use App\Models\Batch;
use App\Models\Cost;

if (env('APP_ENV') === 'testing') {
    return;
}

// DISABLED FOR NOW - Being auto-executed and interfering with seeders
/*
$batch = Batch::withTrashed()->where('batch_code', 'DELETE-TEST-20251029082118')->first();

if ($batch) {
    echo "Batch ID: {$batch->id}\n";
    echo "Batch deleted_at: " . ($batch->deleted_at ?? 'null') . "\n";
    echo "Batch trashed: " . ($batch->trashed() ? 'YES' : 'NO') . "\n\n";

    $costs = Cost::withTrashed()->where('batch_id', $batch->id)->get();
    echo "Total Costs found: " . $costs->count() . "\n";
    foreach ($costs as $cost) {
        echo "  - Cost ID {$cost->id}: deleted_at = " . ($cost->deleted_at ?? 'null') . " (trashed: " . ($cost->trashed() ? 'YES' : 'NO') . ")\n";
        $payments = $cost->payments()->withTrashed()->get();
        echo "    - Payments: " . $payments->count() . "\n";
        foreach ($payments as $p) {
            echo "      - Payment {$p->id}: deleted_at = " . ($p->deleted_at ?? 'null') . " (trashed: " . ($p->trashed() ? 'YES' : 'NO') . ")\n";
        }
    }
} else {
    echo "Batch not found\n";
}
*/
