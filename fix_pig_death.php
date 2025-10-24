<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PigDeath;
use App\Models\Batch;
use Illuminate\Support\Facades\DB;
use App\Helpers\PigInventoryHelper;

$death = PigDeath::find(4);
if ($death) {
    $oldQuantity = $death->quantity; // 0
    $newQuantity = 4;
    $diffQuantity = $newQuantity - $oldQuantity; // 4

    echo "Updating PigDeath id=4:\n";
    echo "  Old Quantity: $oldQuantity\n";
    echo "  New Quantity: $newQuantity\n";
    echo "  Diff: $diffQuantity\n\n";

    // Update PigDeath
    $death->update(['quantity' => $newQuantity]);
    echo "✅ PigDeath updated\n";

    // Update Batch
    $batch = Batch::find($death->batch_id);
    if ($batch) {
        $batch->total_death += $diffQuantity;
        $batch->current_quantity = max(($batch->current_quantity ?? 0) - $diffQuantity, 0);
        $batch->save();
        echo "✅ Batch updated (total_death, current_quantity)\n";
        echo "   Batch total_death: " . $batch->total_death . "\n";
        echo "   Batch current_quantity: " . $batch->current_quantity . "\n";
    }

    // Update batch_pen_allocations if pen_id exists
    if ($death->pen_id) {
        $allocation = DB::table('batch_pen_allocations')
            ->where('batch_id', $death->batch_id)
            ->where('pen_id', $death->pen_id)
            ->first();

        if ($allocation) {
            $availableInAllocation = $allocation->current_quantity ?? $allocation->allocated_pigs;
            $reduce = min($diffQuantity, $availableInAllocation);

            $result = PigInventoryHelper::reducePigInventory(
                $death->batch_id,
                $death->pen_id,
                $reduce,
                'death'
            );

            if ($result['success']) {
                echo "✅ PigInventoryHelper reduced inventory\n";
            } else {
                // Fallback
                if (property_exists($allocation, 'current_quantity')) {
                    $newCurrent = max(($allocation->current_quantity ?? $allocation->allocated_pigs) - $reduce, 0);
                    DB::table('batch_pen_allocations')
                        ->where('id', $allocation->id)
                        ->update([
                            'current_quantity' => $newCurrent,
                            'updated_at' => now(),
                        ]);
                    echo "✅ batch_pen_allocations updated (fallback)\n";
                    echo "   New current_quantity: $newCurrent\n";
                }
            }
        }
    }

    // Trigger observer to recalculate profit
    echo "\n✅ Observer will trigger calculateAndRecordProfit\n";
    echo "✅ All done!\n";
} else {
    echo "❌ PigDeath id=4 not found!\n";
}
