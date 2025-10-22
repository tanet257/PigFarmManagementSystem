<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Batch;
use App\Models\PigSale;
use App\Models\User;

echo "=== ตรวจสอบ PigSale Status + User Permissions ===\n\n";

// ตรวจสอบ PigSale
$pigSales = PigSale::all();
echo "PigSale ทั้งหมด: " . $pigSales->count() . "\n\n";

foreach ($pigSales as $sale) {
    $batchCode = $sale->batch ? $sale->batch->batch_code : 'N/A';
    $approvedBy = $sale->approved_by ?? 'None';
    $approvedAt = $sale->approved_at ?? 'None';
    $netTotal = $sale->net_total ?? 0;

    echo "PigSale ID: {$sale->id}\n";
    echo "  - Batch: $batchCode\n";
    echo "  - Buyer: {$sale->buyer_name}\n";
    echo "  - Status: {$sale->status}\n";
    echo "  - Payment Status: {$sale->payment_status}\n";
    echo "  - Net Total: ฿" . number_format($netTotal, 2) . "\n";
    echo "  - Approved By: $approvedBy\n";
    echo "  - Approved At: $approvedAt\n";
    echo "\n";
}

// ตรวจสอบ Users + Permissions
echo "=== Users + Permissions ===\n\n";
$users = User::all();
foreach ($users as $user) {
    echo "User: {$user->name} ({$user->email})\n";
    echo "  - Roles: " . ($user->roles ? $user->roles->pluck('name')->implode(', ') : 'None') . "\n";

    // ใช้ hasPermission() ที่มี custom
    echo "  - Has approve_sales: " . ($user->hasPermission('approve_sales') ? 'YES' : 'NO') . "\n";
    echo "\n";
}
