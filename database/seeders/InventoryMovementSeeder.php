<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventoryMovement;
use App\Models\StoreHouse;
use App\Models\Batch;

class InventoryMovementSeeder extends Seeder
{
    public function run(): void
    {
        // If there are no storehouses, nothing to do
        $storehouses = StoreHouse::all();
        if ($storehouses->isEmpty()) {
            $this->command->info('No StoreHouse records found, skipping InventoryMovement seeder.');
            return;
        }

        foreach ($storehouses as $sh) {
            // Try to pick a batch for the same farm if available
            $batch = Batch::where('farm_id', $sh->farm_id)->first();

            InventoryMovement::create([
                'storehouse_id' => $sh->id,
                'batch_id' => $batch?->id,
                // change_type field exists on the table (use 'in' for seeded incoming stock)
                'change_type' => 'in',
                'quantity' => $sh->stock ?? 0,
                'quantity_unit' => $sh->unit ?? 'หน่วย',
                'note' => 'seed initial balance',
                'date' => $sh->date ?? now(),
            ]);
        }

        $this->command->info('InventoryMovementSeeder completed.');
    }
}
