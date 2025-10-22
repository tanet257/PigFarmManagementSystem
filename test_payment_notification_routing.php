<?php
/**
 * TEST: Payment Approval Notification Routing (Phase 7I)
 *
 * This test verifies that:
 * 1. PigEntry payment notifications go to Cost Payment Approvals page
 * 2. PigSale payment notifications go to Payment Approvals page
 * 3. Both use correct models in notifications
 * 4. Notification links point to correct routes
 */

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap/app.php';

use Illuminate\Support\Facades\DB;
use App\Models\Farm;
use App\Models\Batch;
use App\Models\PigEntryRecord;
use App\Models\PigSale;
use App\Models\Cost;
use App\Models\CostPayment;
use App\Models\Payment;
use App\Models\Notification;
use App\Models\User;
use App\Helpers\NotificationHelper;

// Set up test database connection
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "================================================\n";
echo "TEST: Payment Approval Notification Routing\n";
echo "================================================\n\n";

$testsPassed = 0;
$testsFailed = 0;

// Create test user
$testUser = User::first() ?? User::create([
    'name' => 'Test User',
    'email' => 'test@test.com',
    'password' => bcrypt('password'),
]);

// TEST 1: Setup PigEntry data
echo "TEST 1: Setup PigEntry data for notification\n";
try {
    $farm = Farm::create([
        'farm_name' => 'Test Farm - ' . time(),
        'location' => 'Test Location',
        'barn_capacity' => 5,
    ]);

    $batch = Batch::create([
        'farm_id' => $farm->id,
        'batch_code' => 'TEST-PIGENTRY-' . time(),
        'start_date' => now(),
        'total_pig_amount' => 100,
        'current_quantity' => 100,
        'average_weight_per_pig' => 20,
        'status' => 'active',
    ]);

    $pigEntry = PigEntryRecord::create([
        'batch_id' => $batch->id,
        'farm_id' => $farm->id,
        'total_pig_amount' => 10,
        'average_price_per_pig' => 1000,
        'total_pig_price' => 10000,
        'pig_entry_date' => now(),
        'recorded_by' => $testUser->id,
    ]);

    echo "  ✓ PigEntry data created\n";
    echo "    - Farm ID: {$farm->id}\n";
    echo "    - Batch ID: {$batch->id}\n";
    echo "    - PigEntry ID: {$pigEntry->id}\n";
    $testsPassed++;
} catch (\Exception $e) {
    echo "  ✗ Failed: {$e->getMessage()}\n";
    $testsFailed++;
}

// TEST 2: Create Cost and CostPayment for PigEntry
echo "\nTEST 2: Create Cost and CostPayment for PigEntry\n";
try {
    $cost = Cost::create([
        'farm_id' => $farm->id,
        'batch_id' => $batch->id,
        'pig_entry_record_id' => $pigEntry->id,
        'cost_type' => 'piglet',
        'item_name' => 'ลูกหมู',
        'quantity' => 10,
        'unit' => 'ตัว',
        'price_per_unit' => 1000,
        'total_price' => 10000,
        'payment_status' => 'pending',
        'paid_date' => now(),
        'date' => now(),
    ]);

    $costPayment = CostPayment::create([
        'cost_id' => $cost->id,
        'amount' => 10000,
        'status' => 'pending',
    ]);

    echo "  ✓ Cost and CostPayment created\n";
    echo "    - Cost ID: {$cost->id}\n";
    echo "    - CostPayment ID: {$costPayment->id}\n";
    $testsPassed++;
} catch (\Exception $e) {
    echo "  ✗ Failed: {$e->getMessage()}\n";
    $testsFailed++;
}

// TEST 3: Send PigEntry payment notification
echo "\nTEST 3: Send PigEntry payment notification\n";
try {
    $notificationsBefore = Notification::count();

    NotificationHelper::notifyAdminsPigEntryPaymentRecorded($costPayment, $testUser);

    $notificationsAfter = Notification::count();

    if ($notificationsAfter > $notificationsBefore) {
        echo "  ✓ Notification created\n";
        echo "    - Total notifications: {$notificationsAfter}\n";
        $testsPassed++;
    } else {
        echo "  ✗ Notification not created\n";
        $testsFailed++;
    }
} catch (\Exception $e) {
    echo "  ✗ Failed: {$e->getMessage()}\n";
    $testsFailed++;
}

// TEST 4: Verify PigEntry notification points to Cost Payment Approvals
echo "\nTEST 4: Verify PigEntry notification routing\n";
try {
    $notification = Notification::where('type', 'payment_recorded_pig_entry')
        ->latest()
        ->first();

    if ($notification && str_contains($notification->url, 'cost_payment_approvals')) {
        echo "  ✓ PigEntry notification points to Cost Payment Approvals\n";
        echo "    - URL: {$notification->url}\n";
        echo "    - Related Model: {$notification->related_model}\n";
        echo "    - Related Model ID: {$notification->related_model_id}\n";
        $testsPassed++;
    } else {
        echo "  ✗ PigEntry notification URL incorrect\n";
        if ($notification) {
            echo "    - Expected: cost_payment_approvals\n";
            echo "    - Got: {$notification->url}\n";
        }
        $testsFailed++;
    }
} catch (\Exception $e) {
    echo "  ✗ Failed: {$e->getMessage()}\n";
    $testsFailed++;
}

// TEST 5: Verify PigEntry notification uses CostPayment model
echo "\nTEST 5: Verify PigEntry notification uses CostPayment model\n";
try {
    $notification = Notification::where('type', 'payment_recorded_pig_entry')
        ->latest()
        ->first();

    if ($notification && $notification->related_model === 'CostPayment' && $notification->related_model_id === $costPayment->id) {
        echo "  ✓ PigEntry notification uses correct model\n";
        echo "    - Model: {$notification->related_model}\n";
        echo "    - Model ID: {$notification->related_model_id}\n";
        echo "    - CostPayment ID: {$costPayment->id}\n";
        $testsPassed++;
    } else {
        echo "  ✗ PigEntry notification model incorrect\n";
        if ($notification) {
            echo "    - Expected Model: CostPayment\n";
            echo "    - Got: {$notification->related_model}\n";
            echo "    - Expected ID: {$costPayment->id}\n";
            echo "    - Got: {$notification->related_model_id}\n";
        }
        $testsFailed++;
    }
} catch (\Exception $e) {
    echo "  ✗ Failed: {$e->getMessage()}\n";
    $testsFailed++;
}

// TEST 6: Setup PigSale data
echo "\nTEST 6: Setup PigSale data for notification\n";
try {
    $pigSale = PigSale::create([
        'farm_id' => $farm->id,  // ✅ เพิ่ม farm_id
        'batch_id' => $batch->id,
        'customer_name' => 'Test Customer',
        'buyer_name' => 'Test Buyer',
        'sale_date' => now(),
        'date' => now(),
        'total_head' => 5,
        'total_weight' => 100,
        'price_per_unit' => 1000,
        'total_price' => 5000,
        'status' => 'อนุมัติแล้ว',
    ]);

    echo "  ✓ PigSale data created\n";
    echo "    - PigSale ID: {$pigSale->id}\n";
    $testsPassed++;
} catch (\Exception $e) {
    echo "  ✗ Failed: {$e->getMessage()}\n";
    $testsFailed++;
}

// TEST 7: Create Payment for PigSale
echo "\nTEST 7: Create Payment for PigSale\n";
try {
    $payment = Payment::create([
        'pig_sale_id' => $pigSale->id,
        'payment_number' => 'PAY-' . time(),
        'payment_date' => now(),
        'amount' => 5000,
        'payment_method' => 'ธนาคาร',
        'status' => 'pending',
        'recorded_by' => $testUser->id,
    ]);

    echo "  ✓ Payment created\n";
    echo "    - Payment ID: {$payment->id}\n";
    $testsPassed++;
} catch (\Exception $e) {
    echo "  ✗ Failed: {$e->getMessage()}\n";
    $testsFailed++;
}

// TEST 8: Send PigSale payment notification
echo "\nTEST 8: Send PigSale payment notification\n";
try {
    $pigSale->load('batch', 'farm', 'customer');  // ✅ Load relationships

    $notificationsBefore = Notification::count();

    NotificationHelper::notifyAdminsPigSalePaymentRecorded($pigSale, $testUser);

    $notificationsAfter = Notification::count();

    if ($notificationsAfter > $notificationsBefore) {
        echo "  ✓ Notification created\n";
        echo "    - Total notifications: {$notificationsAfter}\n";
        $testsPassed++;
    } else {
        echo "  ✗ Notification not created\n";
        $testsFailed++;
    }
} catch (\Exception $e) {
    echo "  ✗ Failed: {$e->getMessage()}\n";
    $testsFailed++;
}

// TEST 9: Verify PigSale notification points to Payment Approvals
echo "\nTEST 9: Verify PigSale notification routing\n";
try {
    $notification = Notification::where('type', 'payment_recorded_pig_sale')
        ->latest()
        ->first();

    if ($notification && str_contains($notification->url, 'payment_approvals')) {
        echo "  ✓ PigSale notification points to Payment Approvals\n";
        echo "    - URL: {$notification->url}\n";
        echo "    - Related Model: {$notification->related_model}\n";
        echo "    - Related Model ID: {$notification->related_model_id}\n";
        $testsPassed++;
    } else {
        echo "  ✗ PigSale notification URL incorrect\n";
        if ($notification) {
            echo "    - Expected: payment_approvals\n";
            echo "    - Got: {$notification->url}\n";
        }
        $testsFailed++;
    }
} catch (\Exception $e) {
    echo "  ✗ Failed: {$e->getMessage()}\n";
    $testsFailed++;
}

// TEST 10: Verify PigSale notification uses PigSale model
echo "\nTEST 10: Verify PigSale notification uses PigSale model\n";
try {
    $notification = Notification::where('type', 'payment_recorded_pig_sale')
        ->latest()
        ->first();

    if ($notification && $notification->related_model === 'PigSale' && $notification->related_model_id === $pigSale->id) {
        echo "  ✓ PigSale notification uses correct model\n";
        echo "    - Model: {$notification->related_model}\n";
        echo "    - Model ID: {$notification->related_model_id}\n";
        echo "    - PigSale ID: {$pigSale->id}\n";
        $testsPassed++;
    } else {
        echo "  ✗ PigSale notification model incorrect\n";
        if ($notification) {
            echo "    - Expected Model: PigSale\n";
            echo "    - Got: {$notification->related_model}\n";
            echo "    - Expected ID: {$pigSale->id}\n";
            echo "    - Got: {$notification->related_model_id}\n";
        }
        $testsFailed++;
    }
} catch (\Exception $e) {
    echo "  ✗ Failed: {$e->getMessage()}\n";
    $testsFailed++;
}

// Cleanup
echo "\n\nCleaning up test data...\n";
try {
    PigEntryRecord::where('id', $pigEntry->id ?? null)->delete();
    PigSale::where('id', $pigSale->id ?? null)->delete();
    Payment::where('id', $payment->id ?? null)->delete();
    Cost::where('id', $cost->id ?? null)->delete();
    CostPayment::where('id', $costPayment->id ?? null)->delete();
    Batch::where('id', $batch->id ?? null)->delete();
    Farm::where('id', $farm->id ?? null)->delete();
    Notification::where('type', 'payment_recorded_pig_entry')->delete();
    Notification::where('type', 'payment_recorded_pig_sale')->delete();
    echo "✓ Test data cleaned up\n";
} catch (\Exception $e) {
    echo "✗ Cleanup error: {$e->getMessage()}\n";
}

// Final summary
echo "\n================================================\n";
echo "TEST RESULTS - Phase 7I\n";
echo "================================================\n";
echo "Tests Passed: $testsPassed\n";
echo "Tests Failed: $testsFailed\n";
echo "Total Tests:  " . ($testsPassed + $testsFailed) . "\n";

if ($testsFailed === 0) {
    echo "\n✅ ALL TESTS PASSED!\n";
    echo "Payment approval notifications route correctly:\n";
    echo "- PigEntry payments → Cost Payment Approvals\n";
    echo "- PigSale payments → Payment Approvals\n";
} else {
    echo "\n❌ SOME TESTS FAILED\n";
}

echo "================================================\n";
?>
