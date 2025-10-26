<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Batch;
use App\Helpers\RevenueHelper;

class RecalculateAllProfitsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'profit:recalculate-all {--exclude-cancelled : Exclude cancelled batches}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Recalculate profit for all batches';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $query = Batch::query();

            if ($this->option('exclude-cancelled')) {
                $query->where('status', '!=', 'cancelled');
            }

            $batches = $query->get();

            if ($batches->isEmpty()) {
                $this->warn('ไม่มี Batch ให้ recalculate');
                return;
            }

            $this->info("🔄 Recalculating profit for " . $batches->count() . " batches...\n");

            $bar = $this->output->createProgressBar($batches->count());
            $bar->start();

            $successCount = 0;
            $failCount = 0;

            foreach ($batches as $batch) {
                try {
                    $result = RevenueHelper::calculateAndRecordProfit($batch->id);

                    if ($result['success']) {
                        $successCount++;
                    } else {
                        $failCount++;
                    }
                } catch (\Exception $e) {
                    $failCount++;
                }

                $bar->advance();
            }

            $bar->finish();

            $this->newLine(2);
            $this->info("✅ สำเร็จ: {$successCount} Batch");
            $this->error("❌ ล้มเหลว: {$failCount} Batch");
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
        }
    }
}
