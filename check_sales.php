<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Batch, App\Models\PigSale;

$batch = Batch::where('status','closed')->orderBy('id','desc')->first();

echo "=== PIG SALES DETAILS ===\n";
$sales = PigSale::where('batch_id',$batch->id)->get();

foreach($sales as $i => $sale) {
    echo "\nSale " . ($i+1) . ":\n";
    echo "  Date: " . $sale->date->format('Y-m-d') . "\n";
    echo "  Quantity: {$sale->quantity} ตัว\n";
    echo "  Weight/pig: {$sale->avg_weight_per_pig} kg\n";
    echo "  Total weight: {$sale->total_weight} kg\n";
    echo "  Price/kg: {$sale->price_per_kg} baht\n";
    echo "  Price/pig: " . round($sale->avg_weight_per_pig * $sale->price_per_kg, 0) . " baht (" . ($sale->avg_weight_per_pig * $sale->price_per_kg) . ")\n";
    echo "  Total: ฿" . number_format($sale->total_price, 0) . "\n";
}

echo "\n=== SUMMARY ===\n";
echo "Total pigs sold: " . $sales->sum('quantity') . "\n";
echo "Total revenue: ฿" . number_format($sales->sum('total_price'), 0) . "\n";
echo "Avg price/pig: ฿" . number_format($sales->sum('total_price') / $sales->sum('quantity'), 0) . "\n";
?>
