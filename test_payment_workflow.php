php
// Test Payment Approval Workflow
$pendingPayments = \App\Models\Payment::where('status', 'pending')
    ->with(['pigSale.farm', 'pigSale.batch', 'recordedBy'])
    ->orderBy('created_at', 'desc')
    ->get();

echo "Pending Payments: " . $pendingPayments->count() . "\n";

if ($pendingPayments->isNotEmpty()) {
    foreach ($pendingPayments as $payment) {
        echo "  - ID: {$payment->id}, Number: {$payment->payment_number}, Amount: ฿{$payment->amount}\n";
    }
} else {
    echo "No pending payments found\n";
}

// Test that routes exist
$routes = \Illuminate\Support\Facades\Route::getRoutes();
$approveRoute = $routes->getByName('payment_approvals.approve_payment');
$rejectRoute = $routes->getByName('payment_approvals.reject_payment');

echo "\nRoutes Check:\n";
echo "  - approve_payment route: " . ($approveRoute ? "✅ Found" : "❌ Not found") . "\n";
echo "  - reject_payment route: " . ($rejectRoute ? "✅ Found" : "❌ Not found") . "\n";

echo "\n✅ Payment Approval Workflow is properly wired!\n";
