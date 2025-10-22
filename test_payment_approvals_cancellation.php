<?php
/**
 * TEST: Payment Approvals Cancellation on Batch Delete (Phase 7H)
 *
 * This test verifies that when a batch is cancelled:
 * 1. Payment records (for PigSale) are marked as rejected
 * 2. CostPayment records (for Cost) are marked as rejected
 * 3. Payment notifications are marked with [ยกเลิกแล้ว]
 * 4. CostPayment notifications are marked with [ยกเลิกแล้ว]
 *
 * Tests pass when all payment approvals are properly cancelled with batch
 */

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap/app.php';

use Illuminate\Support\Facades\DB;
use App\Models\Batch;
use App\Models\Farm;
use App\Models\PigSale;
use App\Models\Payment;
use App\Models\Cost;
use App\Models\CostPayment;
use App\Models\Notification;
use App\Helpers\PigInventoryHelper;

// Set up test database connection
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "========================================\n";
echo "TEST: Payment Approvals Cancellation\n";
echo "========================================\n\n";

$testsPassed = 0;
$testsFailed = 0;

// TEST 1: Setup test data
echo "TEST 1: Setup test data with payments\n";
try {
    // Create farm
    $farm = Farm::create([
        'farm_name' => 'Test Farm - ' . time(),
        'location' => 'Test Location',
        'barn_capacity' => 5,
    ]);

    // Create batch
    $batch = Batch::create([
        'farm_id' => $farm->id,
        'batch_code' => 'TEST-PAYMENT-' . time(),
        'start_date' => now(),
        'total_pig_amount' => 100,
        'current_quantity' => 100,
        'average_weight_per_pig' => 20,
        'status' => 'active',
    ]);

    // Create pig sale
    $pigSale = PigSale::create([
        'batch_id' => $batch->id,
        'customer_name' => 'Test Customer',
        'sale_date' => now(),
        'total_head' => 10,
        'total_weight' => 200,
        'price_per_unit' => 1000,
        'total_price' => 10000,
        'status' => 'อนุมัติแล้ว',
    ]);

    // Create payment for pig sale
    $payment = Payment::create([
        'pig_sale_id' => $pigSale->id,
        'payment_number' => 'PAY-' . time(),
        'payment_date' => now(),
        'amount' => 10000,
        'payment_method' => 'ธนาคาร',
        'status' => 'pending', // 🔑 Waiting for approval
        'recorded_by' => 1, // User ID
    ]);

    // Create cost for batch
    $cost = Cost::create([
        'farm_id' => $farm->id,
        'batch_id' => $batch->id,
        'cost_type' => 'อาหาร',
        'amount' => 5000,
        'date' => now(),
        'payment_status' => 'approved',
    ]);

    // Create cost payment approval
    $costPayment = CostPayment::create([
        'cost_id' => $cost->id,
        'amount' => 5000,
        'status' => 'approved',
        'approved_by' => 1, // User ID
        'approved_date' => now(),
    ]);

    // Create notifications
    $paymentNotification = Notification::create([
        'user_id' => 1,
        'related_model' => 'Payment',
        'related_model_id' => $payment->id,
        'title' => 'อนุมัติการชำระเงิน - ' . $payment->payment_number,
        'message' => 'awaiting approval',
        'is_read' => false,
    ]);

    $costPaymentNotification = Notification::create([
        'user_id' => 1,
        'related_model' => 'CostPayment',
        'related_model_id' => $costPayment->id,
        'title' => 'อนุมัติการชำระเงินค่าใช้จ่าย - ' . $cost->cost_type,
        'message' => 'awaiting approval',
        'is_read' => false,
    ]);

    echo "  ✓ Test data created\n";
    echo "    - Batch ID: {$batch->id} ({$batch->batch_code})\n";
    echo "    - PigSale ID: {$pigSale->id}\n";
    echo "    - Payment ID: {$payment->id} (Status: {$payment->status})\n";
    echo "    - Cost ID: {$cost->id}\n";
    echo "    - CostPayment ID: {$costPayment->id} (Status: {$costPayment->status})\n";
    $testsPassed++;
} catch (\Exception $e) {
    echo "  ✗ Failed: {$e->getMessage()}\n";
    $testsFailed++;
}

// TEST 2: Verify initial state
echo "\nTEST 2: Verify initial state before cancellation\n";
try {
    $payment = Payment::find($payment->id);
    $costPayment = CostPayment::find($costPayment->id);

    if ($payment->status === 'pending' && $costPayment->status === 'approved') {
        echo "  ✓ Initial state correct\n";
        echo "    - Payment status: {$payment->status}\n";
        echo "    - CostPayment status: {$costPayment->status}\n";
        $testsPassed++;
    } else {
        echo "  ✗ Initial state incorrect\n";
        echo "    - Payment status: {$payment->status} (expected: pending)\n";
        echo "    - CostPayment status: {$costPayment->status} (expected: approved)\n";
        $testsFailed++;
    }
} catch (\Exception $e) {
    echo "  ✗ Failed: {$e->getMessage()}\n";
    $testsFailed++;
}

// TEST 3: Cancel batch (should cancel payment approvals)
echo "\nTEST 3: Cancel batch and verify payment approvals are rejected\n";
try {
    $result = PigInventoryHelper::deleteBatchWithAllocations($batch->id);

    if ($result['success']) {
        echo "  ✓ Batch cancelled successfully\n";
        echo "    - Result: {$result['message']}\n";
        $testsPassed++;
    } else {
        echo "  ✗ Batch cancellation failed\n";
        echo "    - Error: {$result['message']}\n";
        $testsFailed++;
    }
} catch (\Exception $e) {
    echo "  ✗ Failed: {$e->getMessage()}\n";
    $testsFailed++;
}

// TEST 4: Verify Payment status is rejected
echo "\nTEST 4: Verify Payment status changed to rejected\n";
try {
    $payment = Payment::find($payment->id);

    if ($payment->status === 'rejected') {
        echo "  ✓ Payment status correctly changed to 'rejected'\n";
        echo "    - Status: {$payment->status}\n";
        echo "    - Rejected By: {$payment->rejected_by}\n";
        echo "    - Reject Reason: {$payment->reject_reason}\n";
        $testsPassed++;
    } else {
        echo "  ✗ Payment status not updated\n";
        echo "    - Current status: {$payment->status} (expected: rejected)\n";
        $testsFailed++;
    }
} catch (\Exception $e) {
    echo "  ✗ Failed: {$e->getMessage()}\n";
    $testsFailed++;
}

// TEST 5: Verify CostPayment status is rejected
echo "\nTEST 5: Verify CostPayment status changed to rejected\n";
try {
    $costPayment = CostPayment::find($costPayment->id);

    if ($costPayment->status === 'rejected') {
        echo "  ✓ CostPayment status correctly changed to 'rejected'\n";
        echo "    - Status: {$costPayment->status}\n";
        echo "    - Cancelled By: {$costPayment->cancelled_by}\n";
        $testsPassed++;
    } else {
        echo "  ✗ CostPayment status not updated\n";
        echo "    - Current status: {$costPayment->status} (expected: rejected)\n";
        $testsFailed++;
    }
} catch (\Exception $e) {
    echo "  ✗ Failed: {$e->getMessage()}\n";
    $testsFailed++;
}

// TEST 6: Verify Payment notification marked
echo "\nTEST 6: Verify Payment notification marked with [ยกเลิกแล้ว]\n";
try {
    $notification = Notification::find($paymentNotification->id);

    if (str_contains($notification->title, '[ยกเลิกแล้ว]')) {
        echo "  ✓ Payment notification marked correctly\n";
        echo "    - Title: {$notification->title}\n";
        $testsPassed++;
    } else {
        echo "  ✗ Payment notification not marked\n";
        echo "    - Current title: {$notification->title}\n";
        $testsFailed++;
    }
} catch (\Exception $e) {
    echo "  ✗ Failed: {$e->getMessage()}\n";
    $testsFailed++;
}

// TEST 7: Verify CostPayment notification marked
echo "\nTEST 7: Verify CostPayment notification marked with [ยกเลิกแล้ว]\n";
try {
    $notification = Notification::find($costPaymentNotification->id);

    if (str_contains($notification->title, '[ยกเลิกแล้ว]')) {
        echo "  ✓ CostPayment notification marked correctly\n";
        echo "    - Title: {$notification->title}\n";
        $testsPassed++;
    } else {
        echo "  ✗ CostPayment notification not marked\n";
        echo "    - Current title: {$notification->title}\n";
        $testsFailed++;
    }
} catch (\Exception $e) {
    echo "  ✗ Failed: {$e->getMessage()}\n";
    $testsFailed++;
}

// Cleanup
echo "\n\nCleaning up test data...\n";
try {
    Payment::where('pig_sale_id', $pigSale->id ?? null)->delete();
    PigSale::where('batch_id', $batch->id ?? null)->delete();
    Cost::where('batch_id', $batch->id ?? null)->delete();
    CostPayment::where('cost_id', $cost->id ?? null)->delete();
    Batch::where('id', $batch->id ?? null)->delete();
    Farm::where('id', $farm->id ?? null)->delete();
    Notification::where('id', $paymentNotification->id ?? null)->delete();
    Notification::where('id', $costPaymentNotification->id ?? null)->delete();
    echo "✓ Test data cleaned up\n";
} catch (\Exception $e) {
    echo "✗ Cleanup error: {$e->getMessage()}\n";
}

// Final summary
echo "\n========================================\n";
echo "TEST RESULTS - Phase 7H\n";
echo "========================================\n";
echo "Tests Passed: $testsPassed\n";
echo "Tests Failed: $testsFailed\n";
echo "Total Tests:  " . ($testsPassed + $testsFailed) . "\n";

if ($testsFailed === 0) {
    echo "\n✅ ALL TESTS PASSED!\n";
    echo "Payment approvals are correctly cancelled with batch.\n";
} else {
    echo "\n❌ SOME TESTS FAILED\n";
}

echo "========================================\n";
?>
