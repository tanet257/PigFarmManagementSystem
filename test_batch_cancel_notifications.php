<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Batch;
use App\Models\PigEntryRecord;
use App\Models\PigSale;
use App\Models\Cost;
use App\Models\CostPayment;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
echo "║     BATCH CANCEL NOTIFICATIONS TEST                          ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

try {
    // สร้าง Batch
    $batch = Batch::create([
        'farm_id' => 1,
        'batch_code' => 'TEST-NOTIF-' . time(),
        'total_pig_amount' => 1000,
        'current_quantity' => 1000,
        'status' => 'active',
        'start_date' => now(),
    ]);

    echo "✓ สร้าง Batch: {$batch->batch_code}\n\n";

    // สร้าง PigEntry
    $entry = PigEntryRecord::create([
        'batch_id' => $batch->id,
        'farm_id' => 1,
        'pig_entry_date' => now(),
        'total_pig_amount' => 1000,
        'total_pig_weight' => 10000,
        'total_pig_price' => 50000,
        'average_weight_per_pig' => 10,
        'average_price_per_pig' => 50,
        'status' => 'active',
    ]);

    echo "✓ สร้าง PigEntry: ID {$entry->id}\n";

    // สร้าง Cost
    $cost = Cost::create([
        'farm_id' => 1,
        'batch_id' => $batch->id,
        'pig_entry_record_id' => $entry->id,
        'cost_type' => 'piglet',
        'item_code' => 'PIGLET-TEST',
        'item_name' => 'Piglet Test',
        'quantity' => 1000,
        'unit' => 'ตัว',
        'price_per_unit' => 50,
        'total_price' => 50000,
        'payment_status' => 'pending',
        'date' => now(),
    ]);

    echo "✓ สร้าง Cost (Piglet): ID {$cost->id}\n";

    // สร้าง CostPayment
    $costPayment = CostPayment::create([
        'cost_id' => $cost->id,
        'amount' => 50000,
        'status' => 'pending',
    ]);

    echo "✓ สร้าง CostPayment: ID {$costPayment->id}\n";

    // สร้าง PigSale
    $sale = PigSale::create([
        'farm_id' => 1,
        'batch_id' => $batch->id,
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

    echo "✓ สร้าง PigSale: ID {$sale->id}\n\n";

    // สร้าง Notifications
    $notif1 = Notification::create([
        'user_id' => 1,
        'type' => 'cost_payment_approval',
        'title' => 'อนุมัติการชำระเงินค่า piglet',
        'message' => 'มีการชำระเงินค่า piglet จำนวน 50,000 บาท ที่รอการอนุมัติ',
        'related_model' => 'CostPayment',
        'related_model_id' => $costPayment->id,
        'read_at' => null,
    ]);

    $notif2 = Notification::create([
        'user_id' => 1,
        'type' => 'pig_sale_recorded',
        'title' => 'บันทึกการขายหมู',
        'message' => 'มีการขายหมู 100 ตัว ราคารวม 100,000 บาท',
        'related_model' => 'PigSale',
        'related_model_id' => $sale->id,
        'read_at' => now(),
    ]);

    $notif3 = Notification::create([
        'user_id' => 1,
        'type' => 'cost_payment_approval',
        'title' => 'อนุมัติการชำระเงินค่า feed',
        'message' => 'มีการชำระเงินค่า feed จำนวน 20,000 บาท ที่รอการอนุมัติ',
        'related_model' => 'Cost',
        'related_model_id' => $cost->id,
        'read_at' => null,
    ]);

    echo "✓ สร้าง Notifications:\n";
    echo "  - CostPayment Notification: {$notif1->title}\n";
    echo "  - PigSale Notification: {$notif2->title}\n";
    echo "  - Cost Notification: {$notif3->title}\n\n";

    // แสดงสถานะก่อนยกเลิก
    echo "📋 BEFORE CANCEL:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $notifsBefore = Notification::whereIn('id', [$notif1->id, $notif2->id, $notif3->id])->get();
    foreach ($notifsBefore as $notif) {
        echo "  - [{$notif->related_model}] {$notif->title}\n";
    }

    // ยกเลิก Batch
    echo "\n⏳ Cancelling Batch...\n";
    $result = \App\Helpers\PigInventoryHelper::deleteBatchWithAllocations($batch->id);

    if ($result['success']) {
        echo "✅ " . $result['message'] . "\n\n";
    } else {
        echo "❌ " . $result['message'] . "\n\n";
        exit;
    }

    // แสดงสถานะหลังยกเลิก
    echo "📋 AFTER CANCEL:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $notifsAfter = Notification::whereIn('id', [$notif1->id, $notif2->id, $notif3->id])->get();
    foreach ($notifsAfter as $notif) {
        $hasTag = str_contains($notif->title, '[ยกเลิกแล้ว]');
        $status = $hasTag ? "✅ UPDATED" : "❌ NOT UPDATED";
        echo "  $status - {$notif->title}\n";
    }

    // ตรวจสอบผลลัพธ์
    echo "\n" . str_repeat("═", 60) . "\n";
    $allUpdated = $notifsAfter->every(function ($notif) {
        return str_contains($notif->title, '[ยกเลิกแล้ว]');
    });

    if ($allUpdated) {
        echo "✅ TEST PASSED: All notifications properly marked as cancelled!\n";
    } else {
        echo "❌ TEST FAILED: Some notifications were not updated\n";
    }

} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
} finally {
    // Cleanup
    if (isset($batch)) {
        $batch->forceDelete();
    }
}

echo "\n";
