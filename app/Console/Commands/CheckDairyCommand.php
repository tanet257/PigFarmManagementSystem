<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DairyRecord;
use App\Models\DairyStorehouseUse;

class CheckDairyCommand extends Command
{
    protected $signature = 'dairy:check {batch_id?}';
    protected $description = 'ตรวจสอบ Dairy records';

    public function handle()
    {
        $batchId = $this->argument('batch_id');

        if (!$batchId) {
            $this->info('=== ทั้งหมด Dairy Records ===');
            $count = DairyRecord::count();
            $this->line('Count: ' . $count);
            return;
        }

        $this->info('=== Batch ID: ' . $batchId . ' ===');

        $dairyRecords = DairyRecord::where('batch_id', $batchId)->get();
        $this->info('DairyRecord: ' . $dairyRecords->count());

        if ($dairyRecords->count() > 0) {
            $this->table(
                ['ID', 'Date', 'Pig Count', 'Avg Weight'],
                $dairyRecords->map(fn($d) => [$d->id, $d->date, $d->total_pigs, $d->average_weight_per_pig])->toArray()
            );
        }

        $dairyUse = DairyStorehouseUse::whereIn('dairy_record_id', $dairyRecords->pluck('id'))->get();
        $this->info('DairyStorehouseUse: ' . $dairyUse->count());

        if ($dairyUse->count() > 0) {
            $this->table(
                ['ID', 'Storehouse', 'Quantity', 'Unit'],
                $dairyUse->map(fn($d) => [$d->id, $d->storehouse->item_code ?? 'N/A', $d->quantity, $d->unit])->toArray()
            );
        }
    }
}
