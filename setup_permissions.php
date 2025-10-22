<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Role;
use App\Models\Permission;
use App\Models\PigSale;

echo "=== ตั้งค่า Permissions ===\n\n";

// 1. สร้าง permission 'approve_sales' ถ้ายังไม่มี
$approveSalesPermission = Permission::firstOrCreate(
    ['name' => 'approve_sales'],
    ['description' => 'อนุมัติการขายหมู']
);
echo "✓ Permission 'approve_sales' created/found\n\n";

// 2. เพิ่ม permission ให้ admin role
$adminRole = Role::where('name', 'admin')->first();
if ($adminRole) {
    if (!$adminRole->permissions->contains('id', $approveSalesPermission->id)) {
        $adminRole->permissions()->attach($approveSalesPermission->id);
        echo "✓ Added approve_sales to admin role\n";
    } else {
        echo "✓ admin role already has approve_sales\n";
    }
}

// 3. เพิ่ม permission ให้ manager role
$managerRole = Role::where('name', 'manager')->first();
if ($managerRole) {
    if (!$managerRole->permissions->contains('id', $approveSalesPermission->id)) {
        $managerRole->permissions()->attach($approveSalesPermission->id);
        echo "✓ Added approve_sales to manager role\n";
    } else {
        echo "✓ manager role already has approve_sales\n";
    }
}

echo "\n=== อนุมัติ PigSale ===\n\n";

// 4. อนุมัติ PigSale
$pigSale = PigSale::first();
if ($pigSale) {
    $adminUser = \App\Models\User::where('usertype', 'admin')->first();
    if ($adminUser) {
        $pigSale->update([
            'status' => 'completed',
            'approved_by' => $adminUser->id,  // ต้องเป็น user ID
            'approved_at' => now(),
        ]);
        echo "✓ PigSale ID: {$pigSale->id} approved\n";
        echo "  - Status: {$pigSale->status}\n";
        echo "  - Approved By ID: {$pigSale->approved_by}\n";
        echo "  - Approved At: {$pigSale->approved_at}\n";
    }
}

echo "\nเสร็จ!\n";
