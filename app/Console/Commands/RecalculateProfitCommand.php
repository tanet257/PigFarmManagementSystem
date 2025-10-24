<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Batch;
use App\Helpers\RevenueHelper;

class RecalculateProfitCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'profit:recalculate {batchId : The Batch ID to recalculate profit for}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Recalculate profit for a specific batch';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $batchId = $this->argument('batchId');

        try {
            $batch = Batch::findOrFail($batchId);

            $this->info("Recalculating profit for Batch {$batch->batch_code}...");

            $result = RevenueHelper::calculateAndRecordProfit($batchId);

            if ($result['success']) {
                $this->info("✅ {$result['message']}");
                $profit = $result['profit'];
                $this->line("Revenue: ฿" . number_format($profit->total_revenue, 2));
                $this->line("Cost: ฿" . number_format($profit->total_cost, 2));
                $this->line("Profit: ฿" . number_format($profit->gross_profit, 2));
                $this->line("Pigs Dead: {$profit->total_pig_dead}");
            } else {
                $this->error("❌ {$result['message']}");
            }
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
        }
    }
}
