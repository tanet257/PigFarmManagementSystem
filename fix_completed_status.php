<?php
/**
 * Fix: Convert 'completed' status to 'approved' and set approved_by, approved_at
 *
 * This script will:
 * 1. Convert status 'completed' → 'approved'
 * 2. Set approved_by to admin user (id = 1 or first admin)
 * 3. Set approved_at to updated_at timestamp
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PigSale;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Find admin user
$admin = User::where('email', 'admin@example.com')
    ->orWhereHas('roles', function ($query) {
        $query->where('name', 'admin');
    })
    ->first();

if (!$admin) {
    // Fallback to first user with id 1
    $admin = User::find(1);
}

if (!$admin) {
    echo "❌ ไม่พบ Admin user\n";
    exit(1);
}

echo "📝 Admin user: {$admin->name} (ID: {$admin->id})\n\n";

DB::beginTransaction();
try {
    // Fix completed status
    $completedSales = PigSale::where('status', 'completed')->get();

    echo "🔍 พบ {$completedSales->count()} records กับ status 'completed'\n";

    if ($completedSales->count() > 0) {
        foreach ($completedSales as $sale) {
            echo "  • ID {$sale->id}: ยกเลิก";

            $sale->update([
                'status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => $sale->updated_at,
            ]);

            echo " → approved ✅\n";
        }
    }

    // Check for records with payment_status but no approved_by/at (should be approved but missing approval info)
    $missingApproval = PigSale::whereIn('payment_status', ['ชำระแล้ว', 'ชำระบางส่วน', 'เกินกำหนด'])
        ->where('status', 'approved')
        ->whereNull('approved_by')
        ->get();

    echo "\n🔍 พบ {$missingApproval->count()} records ที่มี payment_status แต่ไม่มี approved_by\n";

    if ($missingApproval->count() > 0) {
        foreach ($missingApproval as $sale) {
            echo "  • ID {$sale->id}: อัปเดท approved_by";

            $sale->update([
                'approved_by' => $admin->id,
                'approved_at' => $sale->updated_at,
            ]);

            echo " ✅\n";
        }
    }

    DB::commit();

    echo "\n✅ แก้ไขสำเร็จ!\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📊 Summary:\n";
    echo "  • Converted 'completed' to 'approved': {$completedSales->count()}\n";
    echo "  • Fixed missing approved_by: {$missingApproval->count()}\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ เกิดข้อผิดพลาด: {$e->getMessage()}\n";
    exit(1);
}
