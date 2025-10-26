<?php
// Test Chart API Data

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Profit;
use Carbon\Carbon;

echo "=== TEST CHART DATA ===\n\n";

// Test 1: Monthly Cost-Profit Data
echo "1️⃣ MONTHLY COST-PROFIT DATA (ปี " . now()->year . "):\n";
echo "================================================\n";

$currentYear = now()->year;
$monthlyData = [];

for ($month = 1; $month <= 12; $month++) {
    $monthlyData[$month] = ['cost' => 0, 'profit' => 0];
}

$profits = Profit::whereYear('period_end', $currentYear)
    ->whereHas('batch', function ($q) {
        $q->where('status', '!=', 'cancelled');
    })->get();

echo "Profit records found: " . $profits->count() . "\n\n";

foreach ($profits as $profit) {
    $month = $profit->period_end ? $profit->period_end->month : now()->month;
    $monthlyData[$month]['cost'] += $profit->total_cost;
    $monthlyData[$month]['profit'] += $profit->gross_profit;
}

$months = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
          'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];

for ($i = 1; $i <= 12; $i++) {
    $cost = $monthlyData[$i]['cost'];
    $profit = $monthlyData[$i]['profit'];
    echo sprintf("%s: ต้นทุน: ฿%.2f | กำไร: ฿%.2f\n", $months[$i-1], $cost, $profit);
}

// Test 2: FCG Performance Data
echo "\n\n2️⃣ FCG PERFORMANCE DATA (12 batch ล่าสุด):\n";
echo "================================================\n";

$fcgProfits = Profit::with('batch')
    ->whereHas('batch', function ($q) {
        $q->where('status', '!=', 'cancelled');
    })
    ->orderBy('period_end', 'desc')
    ->limit(12)
    ->get();

echo "Profit records found: " . $fcgProfits->count() . "\n\n";

$totalFcg = 0;
$fcgCount = 0;

foreach ($fcgProfits as $profit) {
    $fcg = ($profit->total_weight_gained ?? 0) > 0
        ? ($profit->feed_cost ?? 0) / $profit->total_weight_gained
        : 0;

    $batchCode = $profit->batch?->batch_code ?? 'Unknown';
    echo sprintf("Batch %s: FCG = ฿%.2f/kg | Cost: ฿%.2f | Weight Gained: %.2f kg\n",
        $batchCode, $fcg, $profit->feed_cost, $profit->total_weight_gained);

    if ($fcg > 0) {
        $totalFcg += $fcg;
        $fcgCount++;
    }
}

if ($fcgCount > 0) {
    echo sprintf("\nAverage FCG: ฿%.2f/kg\n", $totalFcg / $fcgCount);
}

echo "\n✅ ทุก API ควร return ข้อมูลนี้ได้ถูกต้อง\n";
?>
