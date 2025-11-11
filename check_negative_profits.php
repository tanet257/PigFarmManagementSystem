<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap/app.php';

use App\Models\Profit;
use Illuminate\Support\Facades\DB;

$profits = Profit::all(['id', 'batch_id', 'total_revenue', 'total_cost', 'gross_profit', 'status']);

echo "=== Profit Analysis ===\n";
echo "Total Records: " . count($profits) . "\n\n";

$positiveCount = 0;
$negativeCount = 0;
$minProfit = PHP_INT_MAX;
$maxProfit = PHP_INT_MIN;

foreach ($profits as $profit) {
    if ($profit->gross_profit < 0) {
        $negativeCount++;
        echo "❌ LOSS: Batch {$profit->batch_id} = ฿" . number_format($profit->gross_profit, 2) . " (Revenue: ฿" . number_format($profit->total_revenue, 2) . ", Cost: ฿" . number_format($profit->total_cost, 2) . ")\n";
    } else {
        $positiveCount++;
    }

    $minProfit = min($minProfit, $profit->gross_profit);
    $maxProfit = max($maxProfit, $profit->gross_profit);
}

echo "\n=== Summary ===\n";
echo "Positive Profits: {$positiveCount}\n";
echo "Negative Profits (Losses): {$negativeCount}\n";
echo "Min Profit: ฿" . number_format($minProfit === PHP_INT_MAX ? 0 : $minProfit, 2) . "\n";
echo "Max Profit: ฿" . number_format($maxProfit === PHP_INT_MIN ? 0 : $maxProfit, 2) . "\n";
