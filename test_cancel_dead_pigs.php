<?php
/**
 * Test Cancel Dead Pigs Sale
 * ทดสอบการยกเลิกการขายหมูตายและการคืน quantity_sold_total
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\PigDeath;
use App\Models\PigSale;
use App\Models\PigSaleDetail;

$logFile = __DIR__ . '/storage/logs/cancel_dead_pigs_' . date('Y-m-d_H-i-s') . '.log';
file_put_contents($logFile, "=== Cancel Dead Pigs Sale Test ===\n" . date('Y-m-d H:i:s') . "\n\n");

function log_msg($msg, $logFile) {
    echo $msg . "\n";
    file_put_contents($logFile, $msg . "\n", FILE_APPEND);
}

try {
    log_msg("📋 START CANCEL TEST", $logFile);
    log_msg("───────────────────────────────────────", $logFile);

    // หา PigSale ล่าสุด (หมูตาย)
    log_msg("\n1️⃣  Finding latest dead pig sale...", $logFile);

    $latestSale = PigSale::where('sell_type', 'หมูตาย')
        ->where('status', 'pending')  // ยังไม่อนุมัติ ให้ยกเลิกได้
        ->latest('created_at')
        ->first();

    if (!$latestSale) {
        log_msg("❌ ไม่พบ PigSale ที่เป็น pending", $logFile);
        exit(1);
    }

    log_msg("✅ Found: PigSale #" . $latestSale->id . " - " . $latestSale->sale_number, $logFile);
    log_msg("   Quantity: " . $latestSale->quantity, $logFile);

    // บันทึกค่าก่อน
    log_msg("\n2️⃣  Before Cancel - PigDeath Status:", $logFile);

    $details = PigSaleDetail::where('pig_sale_id', $latestSale->id)->get();

    $beforeData = [];
    foreach ($details as $detail) {
        $deaths = PigDeath::where('batch_id', $latestSale->batch_id)
            ->where('pen_id', $detail->pen_id)
            ->get();

        foreach ($deaths as $death) {
            $beforeData[$death->id] = [
                'quantity' => $death->quantity,
                'quantity_sold_total' => $death->quantity_sold_total,
                'status' => $death->status,
            ];

            log_msg("   Death ID {$death->id}:", $logFile);
            log_msg("      - quantity: {$death->quantity}", $logFile);
            log_msg("      - quantity_sold_total: {$death->quantity_sold_total}", $logFile);
            log_msg("      - status: {$death->status}", $logFile);
        }
    }

    // ยกเลิก (Soft delete)
    log_msg("\n3️⃣  Cancelling PigSale...", $logFile);

    DB::beginTransaction();

    // Simulate confirmCancel logic
    foreach ($details as $detail) {
        $pigDeaths = PigDeath::where('batch_id', $latestSale->batch_id)
            ->where('pen_id', $detail->pen_id)
            ->where('status', 'sold')
            ->orderBy('created_at', 'desc')
            ->get();

        $remainingToRestore = $detail->quantity;
        foreach ($pigDeaths as $death) {
            if ($remainingToRestore <= 0) break;

            $restoreAmount = min($remainingToRestore, $death->quantity_sold_total ?? 0);
            $death->quantity_sold_total = ($death->quantity_sold_total ?? 0) - $restoreAmount;

            if ($death->quantity_sold_total <= 0) {
                $death->quantity_sold_total = 0;
                $death->status = 'recorded';
                $death->price_per_pig = null;
            }

            $death->save();
            $remainingToRestore -= $restoreAmount;
        }
    }

    $latestSale->update([
        'status' => 'ยกเลิกการขาย',
        'payment_status' => 'ยกเลิกการขาย',
    ]);

    DB::commit();

    log_msg("✅ Cancel successful", $logFile);

    // ตรวจสอบหลังยกเลิก
    log_msg("\n4️⃣  After Cancel - PigDeath Status:", $logFile);

    $afterOk = true;
    foreach ($details as $detail) {
        $deaths = PigDeath::where('batch_id', $latestSale->batch_id)
            ->where('pen_id', $detail->pen_id)
            ->get();

        foreach ($deaths as $death) {
            log_msg("   Death ID {$death->id}:", $logFile);
            log_msg("      - quantity: {$death->quantity}", $logFile);
            log_msg("      - quantity_sold_total: {$death->quantity_sold_total}", $logFile);
            log_msg("      - status: {$death->status}", $logFile);

            // ตรวจสูจน์
            if ($beforeData[$death->id]['quantity_sold_total'] > 0) {
                if ($death->quantity_sold_total == 0 && $death->status == 'recorded') {
                    log_msg("      ✅ Correctly restored", $logFile);
                } else {
                    log_msg("      ❌ ERROR: Not restored properly", $logFile);
                    $afterOk = false;
                }
            }
        }
    }

    log_msg("\n───────────────────────────────────────", $logFile);
    if ($afterOk) {
        log_msg("✅ TEST PASSED - Cancel worked correctly!", $logFile);
    } else {
        log_msg("❌ TEST FAILED - Some records not restored", $logFile);
    }

} catch (Exception $e) {
    log_msg("\n❌ ERROR: " . $e->getMessage(), $logFile);
    log_msg("Stack: " . $e->getTraceAsString(), $logFile);
    exit(1);
}

echo "\n✅ Done! Check log: $logFile\n";
?>
