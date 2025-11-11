<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Cost;
use App\Models\CostPayment;
use App\Models\DairyRecord;
use App\Models\DairyRecordItem;
use App\Models\Farm;
use App\Models\InventoryMovement;
use App\Models\Payment;
use App\Models\PigDeath;
use App\Models\PigEntryRecord;
use App\Models\PigSale;
use App\Models\PigSaleDetail;
use App\Models\Profit;
use App\Models\Revenue;
use App\Models\StoreHouse;
use App\Helpers\RevenueHelper;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class CompletedBatchSeeder extends Seeder
{
    /**
     * Create a COMPLETED batch with realistic data
     * - 3 months raising + 1 week selling (7 days ago)
     * - 1,500 pigs, 25-30 kg starting weight
     * - Daily dairy records (90 days) with 20 bags/day feed usage
     * - 7 pig sales (200 pigs each = 1,500 total)
     * - Complete inventory movements and profit calculation
     */
    public function run()
    {
        try {
            Log::info('Starting CompletedBatchSeeder...');

        // Get Farm ID 1
        $farm = Farm::find(1);
        if (!$farm) {
            $farm = Farm::create([
                'farm_name' => 'ฟาร์มหมูของเรา',
                'barn_capacity' => 5000,
            ]);
        }
        Log::info("Using Farm: {$farm->farm_name} (ID: {$farm->id})");

        $adminId = 1;

        // Calculate dates (completed 7 days ago, raised for 90 days)
        $endDate = now()->subDays(7); // Selling ended 7 days ago
        $startDate = $endDate->copy()->subDays(90); // Raising period: 90 days

        Log::info("Batch dates: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')} (90 days)");

        // ==================== 1. CREATE BATCH ====================
        $totalPigs = 1500;
        $startingWeight = 27; // kg per pig
        $endingWeight = 115; // kg per pig

        $batch = Batch::create([
            'farm_id' => $farm->id,
            'batch_code' => 'F1-B003-COMPLETED-' . now()->format('YmdHis'),
            'total_pig_amount' => $totalPigs,
            'current_quantity' => $totalPigs, // No deaths for simplicity
            'status' => 'closed', // ✅ Completed
            'start_date' => $startDate,
            'end_date' => $endDate,
            'note' => 'Complete batch with 90 days data for testing'
        ]);

        Log::info("✓ Created Batch: {$batch->batch_code} (ID: {$batch->id})");

        // ==================== 2. CREATE PIG ENTRY RECORD ====================
        $pigEntry = PigEntryRecord::create([
            'batch_id' => $batch->id,
            'farm_id' => $farm->id,
            'pig_entry_date' => $startDate,
            'total_pig_amount' => $totalPigs,
            'total_pig_weight' => $totalPigs * $startingWeight,
            'average_weight_per_pig' => $startingWeight,
            'status' => 'completed',
            'note' => "Entry: {$totalPigs} pigs @ {$startingWeight}kg"
        ]);

        Log::info("✓ Created PigEntryRecord: {$totalPigs} pigs @ {$startingWeight}kg");

        // ==================== 3. CREATE DAILY DAIRY RECORDS (90 days) ====================
        $raisingDays = 90;
        $feedPerDay = 20; // bags/day
        $feedKgPerBag = 50; // kg per bag
        $feedKgPerDay = $feedPerDay * $feedKgPerBag; // 1,000 kg/day

        // Get feed storehouse (item_type = 'feed')
        $feedStorehouse = StoreHouse::where('item_type', 'feed')->first();
        if (!$feedStorehouse) {
            Log::warning("No feed storehouse found! Create one first.");
            return;
        }

        Log::info("Using feed storehouse: {$feedStorehouse->item_name} (ID: {$feedStorehouse->id})");

        $totalFeedUsed = 0;
        $dairyRecords = [];

        for ($day = 1; $day <= $raisingDays; $day++) {
            $recordDate = $startDate->copy()->addDays($day - 1);

            // Create daily dairy record
            $dairyRecord = DairyRecord::create([
                'batch_id' => $batch->id,
                'barn_id' => 1, // Default barn
                'date' => $recordDate,
                'note' => "Daily record day {$day}/{$raisingDays}"
            ]);

            // Create dairy record item for feed (20 bags/day)
            DairyRecordItem::create([
                'dairy_record_id' => $dairyRecord->id,
                'item_type' => 'feed',
                'storehouse_id' => $feedStorehouse->id,
                'batch_id' => $batch->id,
                'quantity' => $feedPerDay, // 20 bags
                'unit' => 'bags',
                'note' => "{$feedPerDay} bags ({$feedKgPerDay} kg)"
            ]);

            $dairyRecords[] = $dairyRecord;
            $totalFeedUsed += $feedKgPerDay;
        }

        Log::info("✓ Created {$raisingDays} dairy records with {$feedPerDay} bags/day = {$totalFeedUsed}kg total");

        // ==================== 4. CREATE INVENTORY MOVEMENTS (feed 'out') ====================
        // Simulate feeding: 20 bags/day for 90 days
        // Each day create inventory movement 'out'

        foreach ($dairyRecords as $index => $dairy) {
            $movementDate = $dairy->date;

            $movement = InventoryMovement::create([
                'storehouse_id' => $feedStorehouse->id,
                'batch_id' => $batch->id,
                'barn_id' => 1,
                'change_type' => 'out', // ✅ Usage (OUT)
                'quantity' => $feedPerDay, // 20 bags = 1,000 kg
                'quantity_unit' => 'bags',
                'date' => $movementDate,
                'note' => "Daily feed usage day " . ($index + 1)
            ]);
        }

        Log::info("✓ Created {$raisingDays} inventory movements (feed usage)");

        // ==================== 5. UPDATE BATCH FINAL WEIGHT ====================
        $batch->update([
            'average_weight_per_pig' => $endingWeight,
            'total_pig_weight' => $totalPigs * $endingWeight,
        ]);

        Log::info("✓ Updated batch final weight: {$endingWeight}kg per pig");

        // ==================== 6. CREATE COSTS FOR BATCH ====================
        // Feed costs from inventory movements
        $feedCost = $this->createFeedCosts($batch, $startDate, $totalFeedUsed, $adminId);

        // Medicine costs
        $medicineCost = $this->createMedicineCosts($batch, $startDate, $raisingDays, $adminId);

        // Labor costs (weekly)
        $laborCost = $this->createLaborCosts($batch, $startDate, $raisingDays, $adminId);

        // Utility costs (monthly)
        $utilityCost = $this->createUtilityCosts($batch, $startDate, $raisingDays, $adminId);

        // Other costs
        $otherCost = $this->createOtherCosts($batch, $startDate, $raisingDays, $adminId);

        $totalCost = $feedCost + $medicineCost + $laborCost + $utilityCost + $otherCost;
        Log::info("✓ Created all costs: Feed={$feedCost}, Medicine={$medicineCost}, Labor={$laborCost}, Utility={$utilityCost}, Other={$otherCost}");

        // ==================== 7. CREATE PIG SALES (8 times, 200 pigs each mostly) ====================
        $pigsPerSale = 200;
        $totalSales = 8; // 8 sales to cover 1,500 pigs
        $pricePerKg = 58; // 58 baht/kg (realistic market price)
        $weightPerPigAtSale = 120; // 120 kg per pig

        $saleDates = [];
        for ($i = 0; $i < $totalSales; $i++) {
            $saleDate = $endDate->copy()->subDays($totalSales - $i - 1);
            $saleDates[] = $saleDate;
        }

        $totalRevenue = 0;
        foreach ($saleDates as $saleIndex => $saleDate) {
            $saleQuantity = $pigsPerSale;

            // Adjust quantity for last sale to use remaining pigs (1,500 total)
            if ($saleIndex === count($saleDates) - 1) {
                $saleQuantity = $totalPigs - ($saleIndex * $pigsPerSale);
            }

            // Slightly vary weight per pig (118-122 kg) for realism
            $pigWeight = $weightPerPigAtSale + rand(-2, 2);
            $totalWeight = $saleQuantity * $pigWeight;
            $totalSalePrice = $totalWeight * $pricePerKg;

            // Create pig sale
            $saleNum = $saleIndex + 1;
            $pigSale = PigSale::create([
                'farm_id' => $farm->id,
                'batch_id' => $batch->id,
                'date' => $saleDate,
                'quantity' => $saleQuantity,
                'total_weight' => $totalWeight,
                'avg_weight_per_pig' => $pigWeight,
                'price_per_kg' => $pricePerKg,
                'total_price' => $totalSalePrice,
                'net_total' => $totalSalePrice,
                'status' => 'approved',
                'note' => "Sale {$saleNum}/{$totalSales}: {$saleQuantity} pigs @ {$pigWeight}kg × {$pricePerKg} baht/kg"
            ]);

            // Create pig sale detail
            PigSaleDetail::create([
                'pig_sale_id' => $pigSale->id,
                'pen_id' => 1, // Default pen
                'quantity' => $saleQuantity,
            ]);

            // Create and approve payment
            $paymentNum = $saleIndex + 1;
            $payment = Payment::create([
                'pig_sale_id' => $pigSale->id,
                'payment_date' => $saleDate,
                'amount' => $totalSalePrice,
                'payment_method' => 'transfer',
                'status' => 'approved',
                'approved_by' => 'admin',
                'approved_at' => $saleDate,
                'note' => "Payment for sale {$paymentNum}"
            ]);

            // Record revenue
            $revenue = RevenueHelper::recordPigSaleRevenue($pigSale);

            $totalRevenue += $totalSalePrice;

            Log::info("✓ Sale " . ($saleIndex + 1) . ": {$saleQuantity} pigs @ {$weightPerPigAtSale}kg = ฿{$totalSalePrice}");
        }

        Log::info("✓ Created {$totalSales} pig sales with total revenue: ฿{$totalRevenue}");

        // ==================== 8. CALCULATE PROFIT ====================
        Log::info("Calculating profit for batch ID: {$batch->id}");
        $profitResult = RevenueHelper::calculateAndRecordProfit($batch->id);

        if ($profitResult['success']) {
            $profit = $profitResult['profit'];
            Log::info("✓ Profit calculated: Revenue={$profit->total_revenue}, Cost={$profit->total_cost}, Profit={$profit->gross_profit}");
        } else {
            Log::warning("Profit calculation result: " . $profitResult['message']);
        }

        // ==================== 9. CALCULATE KPI METRICS ====================
        $kpiResult = RevenueHelper::calculateKPIMetrics($batch);
        Log::info("✓ KPI calculated: ADG={$kpiResult['adg']}, FCR={$kpiResult['fcr']}, FCG={$kpiResult['fcg']}");

        Log::info('✅ CompletedBatchSeeder completed successfully!');
        Log::info("Summary: Batch {$batch->batch_code}");
        Log::info("  - Pigs: {$totalPigs}");
        Log::info("  - Weight: {$startingWeight}kg → {$endingWeight}kg");
        Log::info("  - Days: {$raisingDays}");
        Log::info("  - Total Revenue: ฿{$totalRevenue}");
        Log::info("  - Total Cost: ฿{$totalCost}");
        if ($profitResult['success']) {
            Log::info("  - Profit: ฿{$profit->gross_profit}");
        }
        } catch (\Exception $e) {
            Log::error('CompletedBatchSeeder failed: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            throw $e;
        }
    }

    private function createFeedCosts($batch, $startDate, $totalFeedUsed, $adminId)
    {
        // Feed cost: ~5000 baht per bag (wholesale)
        $pricePerBag = 5000;
        $feedBags = ceil($totalFeedUsed / 50);
        $totalFeedCost = $feedBags * $pricePerBag;

        $cost = Cost::create([
            'batch_id' => $batch->id,
            'farm_id' => $batch->farm_id,
            'cost_type' => 'feed',
            'quantity' => $feedBags,
            'unit' => 'bags',
            'amount' => $feedBags,
            'price_per_unit' => $pricePerBag,
            'total_price' => $totalFeedCost,
            'date' => $startDate,
            'note' => "Feed: {$feedBags} bags ({$totalFeedUsed}kg)"
        ]);

        // Auto-approve by CostObserver
        return $totalFeedCost;
    }

    private function createMedicineCosts($batch, $startDate, $raisingDays, $adminId)
    {
        // Medicine costs: periodic purchases
        $medicines = [
            ['name' => 'ยาฆ่าเชื้อ', 'unit' => 'ลิตร', 'quantity' => 5, 'price' => 500],
            ['name' => 'วิตามิน', 'unit' => 'กิโลกรัม', 'quantity' => 10, 'price' => 1000],
            ['name' => 'แอนติไบโอติก', 'unit' => 'กรัม', 'quantity' => 500, 'price' => 50],
        ];

        $totalMedicineCost = 0;

        foreach ($medicines as $medicine) {
            $cost = Cost::create([
                'batch_id' => $batch->id,
                'farm_id' => $batch->farm_id,
                'cost_type' => 'medicine',
                'quantity' => $medicine['quantity'],
                'unit' => $medicine['unit'],
                'amount' => $medicine['quantity'],
                'price_per_unit' => $medicine['price'],
                'total_price' => $medicine['quantity'] * $medicine['price'],
                'date' => $startDate->copy()->addDays(rand(0, $raisingDays - 1)),
                'note' => $medicine['name']
            ]);

            $totalMedicineCost += $medicine['quantity'] * $medicine['price'];
        }

        return $totalMedicineCost;
    }

    private function createLaborCosts($batch, $startDate, $raisingDays, $adminId)
    {
        // Labor: 3 workers, 6000 baht/week each
        $weeksInBatch = ceil($raisingDays / 7);
        $pricePerWeek = 6000 * 3; // 3 workers
        $totalLaborCost = 0;

        for ($week = 0; $week < $weeksInBatch; $week++) {
            $weekDate = $startDate->copy()->addWeeks($week);

            $cost = Cost::create([
                'batch_id' => $batch->id,
                'farm_id' => $batch->farm_id,
                'cost_type' => 'wage',
                'quantity' => 1,
                'unit' => 'week',
                'amount' => 1,
                'price_per_unit' => $pricePerWeek,
                'total_price' => $pricePerWeek,
                'date' => $weekDate,
                'note' => "Labor - Week " . ($week + 1)
            ]);

            $totalLaborCost += $pricePerWeek;
        }

        return $totalLaborCost;
    }

    private function createUtilityCosts($batch, $startDate, $raisingDays, $adminId)
    {
        // Utilities: Electric + Water
        $monthsInBatch = ceil($raisingDays / 30);
        $electricPerMonth = 8000;
        $waterPerMonth = 3000;
        $totalUtilityCost = 0;

        for ($month = 0; $month < $monthsInBatch; $month++) {
            $monthDate = $startDate->copy()->addMonths($month);

            // Electric
            Cost::create([
                'batch_id' => $batch->id,
                'farm_id' => $batch->farm_id,
                'cost_type' => 'electric_bill',
                'quantity' => 1,
                'unit' => 'bill',
                'amount' => 1,
                'price_per_unit' => $electricPerMonth,
                'total_price' => $electricPerMonth,
                'date' => $monthDate,
                'note' => "Electric - Month " . ($month + 1)
            ]);

            // Water
            Cost::create([
                'batch_id' => $batch->id,
                'farm_id' => $batch->farm_id,
                'cost_type' => 'water_bill',
                'quantity' => 1,
                'unit' => 'bill',
                'amount' => 1,
                'price_per_unit' => $waterPerMonth,
                'total_price' => $waterPerMonth,
                'date' => $monthDate,
                'note' => "Water - Month " . ($month + 1)
            ]);

            $totalUtilityCost += $electricPerMonth + $waterPerMonth;
        }

        return $totalUtilityCost;
    }

    private function createOtherCosts($batch, $startDate, $raisingDays, $adminId)
    {
        // Miscellaneous: cleaning supplies, repairs, etc.
        $otherCost = Cost::create([
            'batch_id' => $batch->id,
            'farm_id' => $batch->farm_id,
            'cost_type' => 'other',
            'quantity' => 1,
            'unit' => 'item',
            'amount' => 1,
            'price_per_unit' => 15000,
            'total_price' => 15000,
            'date' => $startDate->copy()->addDays(rand(10, 80)),
            'note' => 'Miscellaneous supplies and maintenance'
        ]);

        return 15000;
    }
}
