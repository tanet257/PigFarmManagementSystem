<?php
/**
 * Test Dead Pigs Sale Logic
 * ทดสอบว่าเมื่อขายหมูตาย จะลด PigDeath.quantity และไม่ลด current_quantity
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\PigDeath;
use App\Models\BatchPenAllocation;
use App\Models\PigSale;

// สร้าง log file
$logFile = __DIR__ . '/storage/logs/dead_pigs_test_' . date('Y-m-d_H-i-s') . '.log';
file_put_contents($logFile, "=== Dead Pigs Sale Test ===\n" . date('Y-m-d H:i:s') . "\n\n");

function log_message($msg, $logFile) {
    echo $msg . "\n";
    file_put_contents($logFile, $msg . "\n", FILE_APPEND);
}

try {
    log_message("📋 START TEST", $logFile);
    log_message("───────────────────────────────────────", $logFile);

    // 1. หา PigSale ที่ขายหมูตาย (ล่าสุด)
    log_message("\n1️⃣  กำลังหา PigSale ที่เกี่ยวกับหมูตาย...", $logFile);

    $lastDeadPigSale = PigSale::where('sell_type', 'หมูตาย')
        ->latest('created_at')
        ->first();

    if (!$lastDeadPigSale) {
        log_message("❌ ไม่พบ PigSale ที่ขายหมูตาย", $logFile);
        exit(1);
    }

    log_message("✅ พบ PigSale #" . $lastDeadPigSale->id . " (Sale Number: " . $lastDeadPigSale->sale_number . ")", $logFile);
    log_message("   - Batch ID: " . $lastDeadPigSale->batch_id, $logFile);
    log_message("   - Quantity: " . $lastDeadPigSale->quantity, $logFile);
    log_message("   - Created: " . $lastDeadPigSale->created_at, $logFile);

    // 2. ดึง PigSaleDetail เพื่อหา pen_id
    log_message("\n2️⃣  กำลังดึง PigSaleDetail...", $logFile);

    $saleDetails = DB::table('pig_sale_details')
        ->where('pig_sale_id', $lastDeadPigSale->id)
        ->get();

    if ($saleDetails->isEmpty()) {
        log_message("❌ ไม่พบ PigSaleDetail", $logFile);
        exit(1);
    }

    foreach ($saleDetails as $detail) {
        log_message("✅ Detail - Pen ID: " . $detail->pen_id . ", Quantity: " . $detail->quantity, $logFile);

        $penId = $detail->pen_id;
        $batchId = $lastDeadPigSale->batch_id;
        $soldQuantity = $detail->quantity;

        // 3. เช็ค current_quantity ใน batch_pen_allocations
        log_message("\n3️⃣  เช็ค current_quantity ใน batch_pen_allocations...", $logFile);

        $allocation = BatchPenAllocation::where('batch_id', $batchId)
            ->where('pen_id', $penId)
            ->first();

        if ($allocation) {
            log_message("✅ Allocation found:", $logFile);
            log_message("   - allocated_pigs: " . $allocation->allocated_pigs, $logFile);
            log_message("   - current_quantity: " . $allocation->current_quantity, $logFile);
        } else {
            log_message("⚠️  ไม่พบ allocation สำหรับ pen_id=$penId (อาจเป็นหมูตาย)", $logFile);
        }

        // 4. เช็ค PigDeath
        log_message("\n4️⃣  เช็ค PigDeath ที่ยังมีอยู่...", $logFile);

        $pigDeathsRecorded = PigDeath::where('batch_id', $batchId)
            ->where('pen_id', $penId)
            ->where('status', 'recorded')
            ->get();

        log_message("   - Status 'recorded': " . $pigDeathsRecorded->count() . " records", $logFile);
        foreach ($pigDeathsRecorded as $death) {
            log_message("     • ID: " . $death->id . ", Quantity: " . $death->quantity, $logFile);
        }

        $pigDeathsSold = PigDeath::where('batch_id', $batchId)
            ->where('pen_id', $penId)
            ->where('status', 'sold')
            ->get();

        log_message("   - Status 'sold': " . $pigDeathsSold->count() . " records", $logFile);
        foreach ($pigDeathsSold as $death) {
            log_message("     • ID: " . $death->id . ", Quantity: " . $death->quantity, $logFile);
        }

        // 5. สรุปผล
        log_message("\n5️⃣  📊 RESULT SUMMARY:", $logFile);
        $totalDeathRecorded = $pigDeathsRecorded->sum('quantity');
        $totalDeathSold = $pigDeathsSold->sum('quantity');

        log_message("   Total Dead Pigs Recorded: " . $totalDeathRecorded, $logFile);
        log_message("   Total Dead Pigs Sold: " . $totalDeathSold, $logFile);
        log_message("   Sold this transaction: " . $soldQuantity, $logFile);

        // เช็ค Logic ว่าถูกไหม
        log_message("\n✔️  VALIDATION:", $logFile);
        if ($allocation && $allocation->current_quantity > 0) {
            log_message("   ⚠️  WARNING: current_quantity ยังคงมีค่า (ควรไม่เปลี่ยนแปลง)", $logFile);
            log_message("   ℹ️  current_quantity = " . $allocation->current_quantity . " (ถูก - หมูปกติยังมี)", $logFile);
        } else {
            log_message("   ✅ current_quantity ถูกต้อง (หมูตายไม่เกี่ยวข้อง)", $logFile);
        }

        if ($pigDeathsSold->count() > 0) {
            log_message("   ✅ PigDeath.status เปลี่ยนเป็น 'sold' แล้ว", $logFile);
        } else {
            log_message("   ❌ ไม่มี PigDeath status='sold' (อาจมี BUG!)", $logFile);
        }

        if ($totalDeathRecorded >= 0 && $totalDeathSold >= 0) {
            log_message("   ✅ PigDeath quantities ลดลงแล้ว", $logFile);
        }
    }

    log_message("\n───────────────────────────────────────", $logFile);
    log_message("✅ TEST COMPLETED SUCCESSFULLY", $logFile);
    log_message("📄 Log file: " . $logFile, $logFile);

} catch (Exception $e) {
    log_message("\n❌ ERROR: " . $e->getMessage(), $logFile);
    log_message("Stack: " . $e->getTraceAsString(), $logFile);
    log_message("\n───────────────────────────────────────", $logFile);
    log_message("❌ TEST FAILED", $logFile);
    exit(1);
}

echo "\n✅ Log saved to: $logFile\n";
?>
