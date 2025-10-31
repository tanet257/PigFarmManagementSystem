<?php

namespace Database\Seeders;

use App\Models\DairyRecord;
use App\Models\DairyRecordItem;
use App\Models\BatchTreatment;
use App\Models\PigDeath;
use App\Models\InventoryMovement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MigrateDairyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder migrates existing data from:
     * - dairy_storehouse_uses → dairy_record_items (feed)
     * - batch_treatments → dairy_record_items (medicine)
     * - pig_deaths → dairy_record_items (death)
     * - dairy_records.note (Row X: Y kg) → dairy_record_items (feed)
     */
    public function run(): void
    {
        // ✅ 1. Migrate from dairy_storehouse_uses (feed)
        $this->migrateFeeds();

        // ✅ 2. Migrate from batch_treatments (medicine)
        $this->migrateMedicines();

        // ✅ 3. Migrate from pig_deaths (death)
        $this->migrateDeaths();

        // ✅ 4. Migrate from dairy_records.note (legacy Row X: Y kg)
        $this->migrateLegacyFeedFromNote();

        echo "\n✅ Migration completed successfully!\n";
    }

    /**
     * Migrate feed data from dairy_storehouse_uses
     */
    private function migrateFeeds(): void
    {
        $feedUses = DB::table('dairy_storehouse_uses')
            ->whereNotNull('dairy_record_id')
            ->get();

        foreach ($feedUses as $use) {
            $storehouse = DB::table('storehouses')->find($use->storehouse_id);

            $item = DairyRecordItem::create([
                'dairy_record_id' => $use->dairy_record_id,
                'item_type' => 'feed',
                'storehouse_id' => $use->storehouse_id,
                'barn_id' => $use->barn_id,
                'quantity' => $use->quantity,
                'unit' => $storehouse->unit ?? 'kg',
                'note' => $use->note,
                'created_at' => $use->created_at,
                'updated_at' => $use->updated_at,
            ]);

            // Create InventoryMovement record
            InventoryMovement::create([
                'storehouse_id' => $use->storehouse_id,
                'dairy_record_item_id' => $item->id,
                'batch_id' => $use->batch_id ?? null,
                'barn_id' => $use->barn_id,
                'change_type' => 'out', // Feed used = out
                'quantity' => $use->quantity,
                'note' => 'From dairy record: ' . $use->note,
                'date' => $use->date ?? now(),
                'created_at' => $use->created_at,
                'updated_at' => $use->updated_at,
            ]);
        }

        echo "✅ Migrated " . $feedUses->count() . " feed items with inventory movements\n";
    }

    /**
     * Migrate medicine data from batch_treatments
     * Link to dairy_record by matching batch_id and approximate date
     */
    private function migrateMedicines(): void
    {
        $treatments = DB::table('batch_treatments')
            ->whereNotNull('batch_id')
            ->get();

        $count = 0;
        foreach ($treatments as $treatment) {
            // Find matching dairy_record by batch_id (on same date)
            $dairyRecord = DairyRecord::where('batch_id', $treatment->batch_id)
                ->whereDate('date', '>=', Carbon::parse($treatment->created_at)->toDateString())
                ->first();

            if ($dairyRecord) {
                $recordItem = DairyRecordItem::create([
                    'dairy_record_id' => $dairyRecord->id,
                    'item_type' => 'medicine',
                    'medicine_code' => $treatment->medicine_code,
                    'batch_id' => $treatment->batch_id,
                    'pen_id' => $treatment->pen_id,
                    'quantity' => $treatment->quantity,
                    'unit' => $treatment->unit,
                    'treatment_status' => $treatment->status,
                    'treatment_date' => $treatment->treatment_date ?? Carbon::parse($treatment->created_at),
                    'note' => $treatment->note,
                    'created_at' => $treatment->created_at,
                    'updated_at' => $treatment->updated_at,
                ]);

                // Create InventoryMovement record for medicine (if from storehouse)
                // Note: If medicine is tracked in storehouse, link it
                if ($treatment->storehouse_id ?? null) {
                    InventoryMovement::create([
                        'storehouse_id' => $treatment->storehouse_id,
                        'dairy_record_item_id' => $recordItem->id,
                        'batch_id' => $treatment->batch_id,
                        'barn_id' => $dairyRecord->barn_id,
                        'change_type' => 'out', // Medicine used = out
                        'quantity' => $treatment->quantity,
                        'note' => 'Medicine: ' . ($treatment->medicine_code ?? 'Unknown'),
                        'date' => $treatment->treatment_date ?? now(),
                        'created_at' => $treatment->created_at,
                        'updated_at' => $treatment->updated_at,
                    ]);
                }

                $count++;
            }
        }

        echo "✅ Migrated $count medicine items with inventory movements\n";
    }

    /**
     * Migrate death data from pig_deaths
     * Link to dairy_record by matching batch_id and date
     */
    private function migrateDeaths(): void
    {
        $deaths = DB::table('pig_deaths')
            ->whereNotNull('batch_id')
            ->get();

        $count = 0;
        foreach ($deaths as $death) {
            // Find matching dairy_record by batch_id
            $dairyRecord = DairyRecord::where('batch_id', $death->batch_id)
                ->whereDate('date', '>=', Carbon::parse($death->created_at)->toDateString())
                ->first();

            if ($dairyRecord) {
                // Create dairy record item for death
                $recordItem = DairyRecordItem::create([
                    'dairy_record_id' => $dairyRecord->id,
                    'item_type' => 'death',
                    'pen_id' => $death->pen_id,
                    'batch_id' => $death->batch_id,
                    'quantity' => $death->quantity,
                    'death_date' => $death->death_date ?? Carbon::parse($death->created_at),
                    'note' => $death->note,
                    'created_at' => $death->created_at,
                    'updated_at' => $death->updated_at,
                ]);

                // Create InventoryMovement record (pig death = inventory out)
                InventoryMovement::create([
                    'dairy_record_item_id' => $recordItem->id,
                    'batch_id' => $death->batch_id,
                    'change_type' => 'out', // Death = loss
                    'quantity' => $death->quantity,
                    'note' => 'Pig death in pen: ' . (optional($death->pen)->pen_code ?? 'Unknown'),
                    'date' => $death->death_date ?? now(),
                    'created_at' => $death->created_at,
                    'updated_at' => $death->updated_at,
                ]);

                $count++;
            }
        }

        echo "✅ Migrated $count death items with inventory movements\n";
    }

    /**
     * Migrate legacy data from dairy_records.note (Row X: Y kg pattern)
     * These are records that don't have corresponding dairy_storehouse_uses
     */
    private function migrateLegacyFeedFromNote(): void
    {
        $records = DairyRecord::whereNotNull('note')
            ->whereRaw("note REGEXP 'Row\\s+[0-9]+:\\s*[0-9]+\\s*kg'")
            ->get();

        $count = 0;
        foreach ($records as $record) {
            // Check if already migrated
            if ($record->feedItems()->exists()) {
                continue;
            }

            // Extract quantity from note
            preg_match('/(\d+)\s*kg/i', $record->note, $matches);
            $quantity = $matches[1] ?? 0;

            DairyRecordItem::create([
                'dairy_record_id' => $record->id,
                'item_type' => 'feed',
                'barn_id' => $record->barn_id,
                'quantity' => $quantity,
                'unit' => 'kg',
                'note' => $record->note,
                'created_at' => $record->created_at,
                'updated_at' => $record->updated_at,
            ]);
            $count++;
        }

        echo "✅ Migrated $count legacy feed notes (Row X: Y kg pattern)\n";
    }
}
