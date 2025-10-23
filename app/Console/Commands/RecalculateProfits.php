<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Batch;
use App\Helpers\RevenueHelper;

class RecalculateProfits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'profit:recalculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate profits for all active batches';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $batches = Batch::where('status', 'กำลังเลี้ยง')->get();

        foreach ($batches as $batch) {
            $this->info("Recalculating profit for batch {$batch->id}...");
            RevenueHelper::calculateAndRecordProfit($batch->id);
        }

        $this->info('All profits recalculated successfully!');
        return Command::SUCCESS;
    }
}
