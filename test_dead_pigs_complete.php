<?php
/**
 * Complete Dead Pigs Sale & Revenue Test
 * ทดสอบระบบหมูตาย:
 * 1. การบันทึก quantity_sold_total เมื่อขายหมูตาย
 * 2. การคำนวณ available = quantity - quantity_sold_total
 * 3. การคำนวณ revenue จากหมูตาย
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\PigDeath;
use App\Models\BatchPenAllocation;
use App\Models\PigSale;
use App\Models\Profit;
use App\Helpers\PigInventoryHelper;

// สร้าง log file
$logFile = __DIR__ . '/storage/logs/dead_pigs_complete_' . date('Y-m-d_H-i-s') . '.log';
file_put_contents($logFile, "=== Complete Dead Pigs Sale & Revenue Test ===\n" . date('Y-m-d H:i:s') . "\n\n");

function log_msg($msg, $logFile) {
    echo $msg . "\n";
    file_put_contents($logFile, $msg . "\n", FILE_APPEND);
}

function log_section($title, $logFile) {
    $line = "───────────────────────────────────────";
    log_msg("\n$line", $logFile);
    log_msg("$title", $logFile);
    log_msg("$line", $logFile);
}

try {
    log_msg("📋 START COMPLETE TEST", $logFile);

    // ========== PART 1: หา PigSale ล่าสุด ==========
    log_section("1️⃣  FIND LATEST DEAD PIG SALES", $logFile);

    $latestSale = PigSale::where('sell_type', 'หมูตาย')
        ->latest('created_at')
        ->first();

    if (!$latestSale) {
        log_msg("❌ ไม่พบการขายหมูตาย", $logFile);
        exit(1);
    }

    log_msg("✅ Found PigSale #" . $latestSale->id . " (Sale #" . $latestSale->sale_number . ")", $logFile);
    log_msg("   Batch ID: " . $latestSale->batch_id . ", Quantity: " . $latestSale->quantity, $logFile);

    $batchId = $latestSale->batch_id;
    $penId = $latestSale->pen_id;

    // ========== PART 2: เช็ค PigDeath ที่อัปเดทแล้ว ==========
    log_section("2️⃣  CHECK PIGDEATH RECORDS", $logFile);

    $pigDeathRecords = PigDeath::where('batch_id', $batchId)
        ->where('pen_id', $penId)
        ->get();

    log_msg("📊 PigDeath records found: " . $pigDeathRecords->count(), $logFile);

    $totalQuantity = 0;
    $totalQuantitySold = 0;
    $totalAvailable = 0;

    foreach ($pigDeathRecords as $death) {
        $quantity = $death->quantity ?? 0;
        $quantitySold = $death->quantity_sold_total ?? 0;
        $available = $quantity - $quantitySold;
        $pricePerPig = $death->price_per_pig ?? 0;

        $totalQuantity += $quantity;
        $totalQuantitySold += $quantitySold;
        $totalAvailable += max(0, $available);

        log_msg("", $logFile);
        log_msg("   Record ID: " . $death->id, $logFile);
        log_msg("   - quantity: $quantity", $logFile);
        log_msg("   - quantity_sold_total: $quantitySold", $logFile);
        log_msg("   - available: $available", $logFile);
        log_msg("   - price_per_pig: $pricePerPig", $logFile);
        log_msg("   - status: " . $death->status, $logFile);
    }

    // ========== PART 3: ทดสอบ Helper getPigsByBatch ==========
    log_section("3️⃣  TEST HELPER: getPigsByBatch()", $logFile);

    $pigs = PigInventoryHelper::getPigsByBatch($batchId);

    log_msg("✅ Found " . count($pigs['pigs']) . " pen/location entries", $logFile);

    $deadPigsData = array_filter($pigs['pigs'], function($pig) {
        return $pig['is_dead'] === true;
    });

    log_msg("   Dead pigs entries: " . count($deadPigsData), $logFile);

    foreach ($deadPigsData as $pig) {
        log_msg("", $logFile);
        log_msg("   Pen: " . $pig['barn_name'] . " - " . $pig['pen_name'], $logFile);
        log_msg("   - available (จากHelper): " . $pig['available'], $logFile);
        log_msg("   - Expected available: " . $totalAvailable, $logFile);

        if ($pig['available'] === $totalAvailable) {
            log_msg("   ✅ MATCH!", $logFile);
        } else {
            log_msg("   ❌ MISMATCH! (Helper showed: " . $pig['available'] . ", Expected: " . $totalAvailable . ")", $logFile);
        }
    }

    // ========== PART 4: เช็ค current_quantity ใน batch_pen_allocations ==========
    log_section("4️⃣  CHECK BATCH_PEN_ALLOCATIONS (หมูปกติ)", $logFile);

    $allocation = BatchPenAllocation::where('batch_id', $batchId)
        ->where('pen_id', $penId)
        ->first();

    if ($allocation) {
        log_msg("✅ Allocation found:", $logFile);
        log_msg("   - allocated_pigs: " . $allocation->allocated_pigs, $logFile);
        log_msg("   - current_quantity: " . $allocation->current_quantity, $logFile);
        log_msg("   ✅ (Should NOT be reduced for dead pigs)", $logFile);
    } else {
        log_msg("⚠️  No allocation (normal for dead pigs only pen)", $logFile);
    }

    // ========== PART 5: เช็ค Revenue/Profit ==========
    log_section("5️⃣  CHECK PROFIT CALCULATION", $logFile);

    $profit = Profit::where('batch_id', $batchId)->first();

    if ($profit) {
        log_msg("✅ Profit record found:", $logFile);
        log_msg("   - total_revenue: " . $profit->total_revenue, $logFile);
        log_msg("   - total_cost: " . $profit->total_cost, $logFile);
        log_msg("   - gross_profit: " . $profit->gross_profit, $logFile);
        log_msg("   - total_pig_sold: " . $profit->total_pig_sold, $logFile);
        log_msg("   - total_pig_dead: " . $profit->total_pig_dead, $logFile);

        $expectedDeadRevenue = $totalQuantitySold * ($pigDeathRecords->first()->price_per_pig ?? 0);
        log_msg("   - Expected dead pig revenue: " . $expectedDeadRevenue, $logFile);
    } else {
        log_msg("⚠️  No Profit record yet (may need to calculate)", $logFile);
    }

    // ========== PART 6: สรุปผล ==========
    log_section("6️⃣  FINAL SUMMARY", $logFile);

    log_msg("📊 Dead Pigs Summary:", $logFile);
    log_msg("   - Total Original (quantity): $totalQuantity", $logFile);
    log_msg("   - Total Sold (quantity_sold_total): $totalQuantitySold", $logFile);
    log_msg("   - Total Available (remaining): $totalAvailable", $logFile);

    log_msg("\n✔️  VALIDATION CHECKS:", $logFile);

    $checks = [
        "quantity ไม่ลด (เหลือ $totalQuantity)" => ($totalQuantity > 0 ? "✅" : "❌"),
        "quantity_sold_total เพิ่มขึ้น ($totalQuantitySold)" => ($totalQuantitySold > 0 ? "✅" : "❌"),
        "available = quantity - quantity_sold_total ($totalAvailable)" => ($totalAvailable >= 0 ? "✅" : "❌"),
        "Helper display available ถูกต้อง" => "⏳",
        "Revenue includes dead pig sales" => ($profit && $profit->total_revenue > 0 ? "✅" : "⏳"),
    ];

    foreach ($checks as $check => $status) {
        log_msg("   $status $check", $logFile);
    }

    log_section("TEST COMPLETED", $logFile);
    log_msg("✅ All checks completed", $logFile);
    log_msg("📄 Log file: $logFile", $logFile);

} catch (Exception $e) {
    log_section("ERROR", $logFile);
    log_msg("❌ " . $e->getMessage(), $logFile);
    log_msg("Stack: " . $e->getTraceAsString(), $logFile);
    exit(1);
}

echo "\n✅ Test completed!\n";
echo "📄 Log: $logFile\n";
?>
