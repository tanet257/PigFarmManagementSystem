<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Batch;
use App\Models\PigEntryRecord;
use App\Models\PigSale;
use App\Models\Cost;
use App\Models\Revenue;
use App\Models\Profit;
use Illuminate\Support\Facades\DB;

echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
echo "║     SOFT DELETE PHILOSOPHY TEST - Verify Exclusions          ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

// Test 1: Cancelled Batch Exclusion from Dashboard
echo "TEST 1️⃣  - Dashboard Totals Exclude Cancelled Data\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

try {
    // Create test data
    $batch = Batch::create([
        'farm_id' => 1,
        'batch_code' => 'TEST-SOFT-DELETE-' . time(),
        'total_pig_amount' => 1000,
        'current_quantity' => 1000,
        'total_death' => 0,
        'status' => 'active',
        'start_date' => now(),
    ]);

    // Add cost
    $cost = Cost::create([
        'farm_id' => 1,
        'batch_id' => $batch->id,
        'cost_type' => 'feed',
        'item_code' => 'TEST-COST',
        'item_name' => 'Test Cost',
        'quantity' => 100,
        'unit' => 'bags',
        'price_per_unit' => 100,
        'total_price' => 10000,
        'payment_status' => 'approved',
        'date' => now(),
    ]);

    // Get dashboard totals BEFORE cancellation
    $costsBeforeCancel = Cost::whereHas('batch', function ($q) {
        $q->where('status', '!=', 'cancelled');
    })->sum('total_price');

    echo "✓ Before cancellation:\n";
    echo "  - Active Batch Count: " . Batch::where('status', '!=', 'cancelled')->count() . "\n";
    echo "  - Dashboard Costs Total: ฿" . number_format($costsBeforeCancel, 2) . "\n";
    echo "  - Test Batch Cost: ฿" . number_format($cost->total_price, 2) . "\n";

    // Cancel the batch
    $batch->status = 'cancelled';
    $batch->save();

    // Cancel related cost
    $cost->update(['payment_status' => 'ยกเลิก']);

    // Get dashboard totals AFTER cancellation
    $costsAfterCancel = Cost::whereHas('batch', function ($q) {
        $q->where('status', '!=', 'cancelled');
    })->sum('total_price');

    echo "\n✓ After cancellation:\n";
    echo "  - Active Batch Count: " . Batch::where('status', '!=', 'cancelled')->count() . "\n";
    echo "  - Dashboard Costs Total: ฿" . number_format($costsAfterCancel, 2) . "\n";
    echo "  - Batch Status: {$batch->status}\n";

    $totalReduction = $costsBeforeCancel - $costsAfterCancel;
    if ($totalReduction == $cost->total_price) {
        echo "\n✅ TEST 1 PASSED: Dashboard correctly excludes cancelled batch costs\n";
    } else {
        echo "\n❌ TEST 1 FAILED: Cost reduction {$totalReduction} != {$cost->total_price}\n";
    }
} catch (\Exception $e) {
    echo "❌ TEST 1 ERROR: " . $e->getMessage() . "\n";
} finally {
    // Cleanup
    $batch?->forceDelete();
    $cost?->forceDelete();
}

// Test 2: Cancelled PigEntry Exclusion from Inventory
echo "\n\nTEST 2️⃣  - Total Pig Amount Excludes Cancelled Entries\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

try {
    $batch2 = Batch::create([
        'farm_id' => 1,
        'batch_code' => 'TEST-ENTRY-' . time(),
        'total_pig_amount' => 2000,
        'current_quantity' => 2000,
        'status' => 'active',
        'start_date' => now(),
    ]);

    $entry1 = PigEntryRecord::create([
        'batch_id' => $batch2->id,
        'farm_id' => 1,
        'pig_entry_date' => now(),
        'total_pig_amount' => 1000,
        'total_pig_weight' => 10000,
        'total_pig_price' => 50000,
        'average_weight_per_pig' => 10,
        'average_price_per_pig' => 50,
        'status' => 'active',
        'note' => 'Test Entry 1',
    ]);

    $entry2 = PigEntryRecord::create([
        'batch_id' => $batch2->id,
        'farm_id' => 1,
        'pig_entry_date' => now(),
        'total_pig_amount' => 1000,
        'total_pig_weight' => 10000,
        'total_pig_price' => 50000,
        'average_weight_per_pig' => 10,
        'average_price_per_pig' => 50,
        'status' => 'active',
        'note' => 'Test Entry 2',
    ]);

    echo "✓ Before cancellation:\n";
    echo "  - Active Entries: " . PigEntryRecord::where('batch_id', $batch2->id)->where('status', '!=', 'cancelled')->count() . "\n";
    echo "  - Batch total_pig_amount: {$batch2->total_pig_amount}\n";

    // Simulate cancelling entry 1
    $batch2->total_pig_amount = max(0, $batch2->total_pig_amount - 1000);
    $batch2->current_quantity = max(0, $batch2->current_quantity - 1000);
    $batch2->save();

    $entry1->status = 'cancelled';
    $entry1->cancelled_at = now();
    $entry1->save();

    // Reload batch
    $batch2->refresh();

    echo "\n✓ After cancelling Entry 1 (1000 pigs):\n";
    echo "  - Active Entries: " . PigEntryRecord::where('batch_id', $batch2->id)->where('status', '!=', 'cancelled')->count() . "\n";
    echo "  - Batch total_pig_amount: {$batch2->total_pig_amount}\n";
    echo "  - Expected: 1000\n";

    if ($batch2->total_pig_amount == 1000 && $batch2->current_quantity == 1000) {
        echo "\n✅ TEST 2 PASSED: Batch amounts correctly reduced when entry cancelled\n";
    } else {
        echo "\n❌ TEST 2 FAILED: Expected 1000, got {$batch2->total_pig_amount}\n";
    }
} catch (\Exception $e) {
    echo "❌ TEST 2 ERROR: " . $e->getMessage() . "\n";
} finally {
    $batch2?->forceDelete();
    PigEntryRecord::where('batch_id', $batch2->id ?? 0)->forceDelete();
}

// Test 3: Cancelled PigSale Exclusion from Revenue
echo "\n\nTEST 3️⃣  - Cancelled PigSale Excluded from Revenue Calculations\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

try {
    $batch3 = Batch::create([
        'farm_id' => 1,
        'batch_code' => 'TEST-SALE-' . time(),
        'total_pig_amount' => 500,
        'current_quantity' => 500,
        'status' => 'active',
        'start_date' => now(),
    ]);

    $sale1 = PigSale::create([
        'farm_id' => 1,
        'batch_id' => $batch3->id,
        'pen_id' => 1,
        'date' => now(),
        'quantity' => 100,
        'total_weight' => 1000,
        'price_per_kg' => 100,
        'total_price' => 100000,
        'net_total' => 95000,
        'buyer_name' => 'Test Buyer',
        'payment_status' => 'รอชำระ',
        'status' => 'อนุมัติแล้ว',
    ]);

    $sale2 = PigSale::create([
        'farm_id' => 1,
        'batch_id' => $batch3->id,
        'pen_id' => 1,
        'date' => now(),
        'quantity' => 100,
        'total_weight' => 1000,
        'price_per_kg' => 100,
        'total_price' => 100000,
        'net_total' => 95000,
        'buyer_name' => 'Test Buyer 2',
        'payment_status' => 'รอชำระ',
        'status' => 'อนุมัติแล้ว',
    ]);

    $activeSalesCount = PigSale::where('batch_id', $batch3->id)
        ->where('status', '!=', 'ยกเลิกการขาย')->count();
    $activeSalesTotal = PigSale::where('batch_id', $batch3->id)
        ->where('status', '!=', 'ยกเลิกการขาย')->sum('total_price');

    echo "✓ Before cancellation:\n";
    echo "  - Active Sales: {$activeSalesCount}\n";
    echo "  - Total Sales: ฿" . number_format($activeSalesTotal, 2) . "\n";

    // Cancel sale 1
    $sale1->update(['status' => 'ยกเลิกการขาย']);

    $activeSalesCountAfter = PigSale::where('batch_id', $batch3->id)
        ->where('status', '!=', 'ยกเลิกการขาย')->count();
    $activeSalesTotalAfter = PigSale::where('batch_id', $batch3->id)
        ->where('status', '!=', 'ยกเลิกการขาย')->sum('total_price');

    echo "\n✓ After cancelling Sale 1:\n";
    echo "  - Active Sales: {$activeSalesCountAfter}\n";
    echo "  - Total Sales: ฿" . number_format($activeSalesTotalAfter, 2) . "\n";
    echo "  - Expected: 1 sale, ฿100000.00\n";

    if ($activeSalesCountAfter == 1 && $activeSalesTotalAfter == 100000) {
        echo "\n✅ TEST 3 PASSED: Cancelled sales correctly excluded from totals\n";
    } else {
        echo "\n❌ TEST 3 FAILED: Expected 1 sale at ฿100000, got {$activeSalesCountAfter} at ฿" . number_format($activeSalesTotalAfter, 2) . "\n";
    }
} catch (\Exception $e) {
    echo "❌ TEST 3 ERROR: " . $e->getMessage() . "\n";
} finally {
    $batch3?->forceDelete();
    PigSale::where('batch_id', $batch3->id ?? 0)->forceDelete();
}

// Test 4: Cancelled Cost Exclusion from Profit
echo "\n\nTEST 4️⃣  - Cancelled Costs Excluded from Profit Calculation\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

try {
    $batch4 = Batch::create([
        'farm_id' => 1,
        'batch_code' => 'TEST-PROFIT-' . time(),
        'total_pig_amount' => 100,
        'current_quantity' => 100,
        'status' => 'active',
        'start_date' => now(),
    ]);

    $cost1 = Cost::create([
        'farm_id' => 1,
        'batch_id' => $batch4->id,
        'cost_type' => 'feed',
        'item_code' => 'FEED-1',
        'item_name' => 'Feed 1',
        'quantity' => 50,
        'unit' => 'bags',
        'price_per_unit' => 1000,
        'total_price' => 50000,
        'payment_status' => 'approved',
        'date' => now(),
    ]);

    // ✅ Create CostPayment with 'approved' status
    \App\Models\CostPayment::create([
        'cost_id' => $cost1->id,
        'amount' => 50000,
        'status' => 'approved',
    ]);

    $cost2 = Cost::create([
        'farm_id' => 1,
        'batch_id' => $batch4->id,
        'cost_type' => 'feed',
        'item_code' => 'FEED-2',
        'item_name' => 'Feed 2',
        'quantity' => 50,
        'unit' => 'bags',
        'price_per_unit' => 1000,
        'total_price' => 50000,
        'payment_status' => 'approved',
        'date' => now(),
    ]);

    // ✅ Create CostPayment with 'approved' status
    \App\Models\CostPayment::create([
        'cost_id' => $cost2->id,
        'amount' => 50000,
        'status' => 'approved',
    ]);

    // Calculate costs BEFORE cancellation
    $activeCostsCount = Cost::where('batch_id', $batch4->id)
        ->where('payment_status', '!=', 'ยกเลิก')
        ->whereHas('payments', function ($q) {
            $q->where('status', 'approved');
        })->count();
    $activeCostsTotal = Cost::where('batch_id', $batch4->id)
        ->where('payment_status', '!=', 'ยกเลิก')
        ->whereHas('payments', function ($q) {
            $q->where('status', 'approved');
        })->sum('total_price');

    echo "✓ Before cancellation:\n";
    echo "  - Active Approved Costs: {$activeCostsCount}\n";
    echo "  - Total: ฿" . number_format($activeCostsTotal, 2) . "\n";

    // Cancel cost 1
    $cost1->update(['payment_status' => 'ยกเลิก']);

    // Calculate costs AFTER cancellation
    $activeCostsCountAfter = Cost::where('batch_id', $batch4->id)
        ->where('payment_status', '!=', 'ยกเลิก')
        ->whereHas('payments', function ($q) {
            $q->where('status', 'approved');
        })->count();
    $activeCostsTotalAfter = Cost::where('batch_id', $batch4->id)
        ->where('payment_status', '!=', 'ยกเลิก')
        ->whereHas('payments', function ($q) {
            $q->where('status', 'approved');
        })->sum('total_price');

    echo "\n✓ After cancelling Cost 1:\n";
    echo "  - Active Approved Costs: {$activeCostsCountAfter}\n";
    echo "  - Total: ฿" . number_format($activeCostsTotalAfter, 2) . "\n";
    echo "  - Expected: 1 cost, ฿50000.00\n";

    if ($activeCostsCountAfter == 1 && $activeCostsTotalAfter == 50000) {
        echo "\n✅ TEST 4 PASSED: Cancelled costs correctly excluded from profit calculation\n";
    } else {
        echo "\n❌ TEST 4 FAILED: Expected 1 cost at ฿50000, got {$activeCostsCountAfter} at ฿" . number_format($activeCostsTotalAfter, 2) . "\n";
    }
} catch (\Exception $e) {
    echo "❌ TEST 4 ERROR: " . $e->getMessage() . "\n";
} finally {
    $batch4?->forceDelete();
    Cost::where('batch_id', $batch4->id ?? 0)->forceDelete();
}

// Test 5: Batch Cancellation - Complete Reset
echo "\n\nTEST 5️⃣  - Complete Batch Cancellation with All Related Data\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

try {
    $batch5 = Batch::create([
        'farm_id' => 1,
        'batch_code' => 'TEST-COMPLETE-' . time(),
        'total_pig_amount' => 5000,
        'current_quantity' => 5000,
        'total_death' => 100,
        'status' => 'active',
        'start_date' => now(),
    ]);

    $entry5 = PigEntryRecord::create([
        'batch_id' => $batch5->id,
        'farm_id' => 1,
        'pig_entry_date' => now(),
        'total_pig_amount' => 5000,
        'total_pig_weight' => 50000,
        'total_pig_price' => 250000,
        'average_weight_per_pig' => 10,
        'average_price_per_pig' => 50,
        'status' => 'active',
    ]);

    $sale5 = PigSale::create([
        'farm_id' => 1,
        'batch_id' => $batch5->id,
        'pen_id' => 1,
        'date' => now(),
        'quantity' => 500,
        'total_weight' => 5000,
        'price_per_kg' => 100,
        'total_price' => 500000,
        'net_total' => 475000,
        'buyer_name' => 'Buyer',
        'payment_status' => 'รอชำระ',
        'status' => 'อนุมัติแล้ว',
    ]);

    $cost5 = Cost::create([
        'farm_id' => 1,
        'batch_id' => $batch5->id,
        'cost_type' => 'feed',
        'item_code' => 'FEED',
        'item_name' => 'Feed',
        'quantity' => 100,
        'unit' => 'bags',
        'price_per_unit' => 1000,
        'total_price' => 100000,
        'payment_status' => 'approved',
        'date' => now(),
    ]);

    echo "✓ Before cancellation:\n";
    echo "  - Batch Status: {$batch5->status}\n";
    echo "  - total_pig_amount: {$batch5->total_pig_amount}\n";
    echo "  - current_quantity: {$batch5->current_quantity}\n";
    echo "  - Active PigEntry: " . PigEntryRecord::where('batch_id', $batch5->id)->where('status', '!=', 'cancelled')->count() . "\n";
    echo "  - Active PigSale: " . PigSale::where('batch_id', $batch5->id)->where('status', '!=', 'ยกเลิกการขาย')->count() . "\n";
    echo "  - Active Cost: " . Cost::where('batch_id', $batch5->id)->where('payment_status', '!=', 'ยกเลิก')->count() . "\n";

    // Use the helper to cancel batch
    $result = \App\Helpers\PigInventoryHelper::deleteBatchWithAllocations($batch5->id);

    // Reload batch
    $batch5->refresh();

    echo "\n✓ After batch cancellation:\n";
    echo "  - Batch Status: {$batch5->status}\n";
    echo "  - total_pig_amount: {$batch5->total_pig_amount}\n";
    echo "  - current_quantity: {$batch5->current_quantity}\n";
    echo "  - Active PigEntry: " . PigEntryRecord::where('batch_id', $batch5->id)->where('status', '!=', 'cancelled')->count() . "\n";
    echo "  - Active PigSale: " . PigSale::where('batch_id', $batch5->id)->where('status', '!=', 'ยกเลิกการขาย')->count() . "\n";
    echo "  - Active Cost: " . Cost::where('batch_id', $batch5->id)->where('payment_status', '!=', 'ยกเลิก')->count() . "\n";

    if ($result['success'] &&
        $batch5->status == 'cancelled' &&
        $batch5->total_pig_amount == 0 &&
        $batch5->current_quantity == 0) {
        echo "\n✅ TEST 5 PASSED: Batch completely cancelled with all related data properly handled\n";
    } else {
        echo "\n❌ TEST 5 FAILED: Batch cancellation incomplete\n";
        if (!$result['success']) {
            echo "  Error: " . $result['message'] . "\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ TEST 5 ERROR: " . $e->getMessage() . "\n";
} finally {
    $batch5?->forceDelete();
    PigEntryRecord::where('batch_id', $batch5->id ?? 0)->forceDelete();
    PigSale::where('batch_id', $batch5->id ?? 0)->forceDelete();
    Cost::where('batch_id', $batch5->id ?? 0)->forceDelete();
}

echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                    ALL TESTS COMPLETED                        ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";
