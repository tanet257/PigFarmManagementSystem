<?php
/**
 * Fix: Update cancelled sales and rejected sales with rejected_by/rejected_at
 * 
 * This script will:
 * 1. Set rejected_by = admin ID for cancelled sales (status = 'ยกเลิกการขาย')
 * 2. Set rejected_by = admin ID for rejected sales (status = 'rejected') if missing
 * 3. Set rejected_at = updated_at timestamp
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PigSale;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Find admin user
$admin = User::whereHas('roles', function ($query) {
    $query->where('name', 'admin');
})->first() ?? User::find(1);

if (!$admin) {
    echo "❌ ไม่พบ Admin user\n";
    exit(1);
}

echo "📝 Admin user: {$admin->name} (ID: {$admin->id})\n\n";

DB::beginTransaction();
try {
    // Fix cancelled sales
    $cancelledSales = PigSale::where('status', 'ยกเลิกการขาย')
        ->whereNull('rejected_by')
        ->get();
    
    echo "🔍 พบ {$cancelledSales->count()} records ยกเลิกการขาย ที่ไม่มี rejected_by\n";
    
    if ($cancelledSales->count() > 0) {
        foreach ($cancelledSales as $sale) {
            echo "  • ID {$sale->id}: ยกเลิกการขาย";
            
            $sale->update([
                'rejected_by' => $admin->id,
                'rejected_at' => $sale->updated_at,
            ]);
            
            echo " → updated ✅\n";
        }
    }
    
    // Fix rejected sales
    $rejectedSales = PigSale::where('status', 'rejected')
        ->whereNull('rejected_by')
        ->get();
    
    echo "\n🔍 พบ {$rejectedSales->count()} records rejected ที่ไม่มี rejected_by\n";
    
    if ($rejectedSales->count() > 0) {
        foreach ($rejectedSales as $sale) {
            echo "  • ID {$sale->id}: rejected";
            
            $sale->update([
                'rejected_by' => $admin->id,
                'rejected_at' => $sale->updated_at,
            ]);
            
            echo " → updated ✅\n";
        }
    }
    
    DB::commit();
    
    echo "\n✅ แก้ไขสำเร็จ!\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📊 Summary:\n";
    echo "  • Fixed cancelled sales: {$cancelledSales->count()}\n";
    echo "  • Fixed rejected sales: {$rejectedSales->count()}\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ เกิดข้อผิดพลาด: {$e->getMessage()}\n";
    exit(1);
}
