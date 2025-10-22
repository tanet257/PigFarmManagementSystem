<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== ตรวจการ track ของแต่ละ PigEntry ===\n\n";

// อย่างแรก: หมู 6000 เข้ามาสี่ครั้ง แต่ allocated คำนึถึง stack
// ที่จริง allocated_pigs ควรเก็บ "ต้นฉบับ" ไม่ใช่ "สะสม"

// ข้อเท็จจริง:
// - PigEntry 18 เข้า: addPigs() → allocated = 38 + allocated ที่อยู่ = 0 + 38 = 38 ✓
// - PigEntry 19 เข้า: addPigs() → allocated = 38 + 38 = 76 ✗ (เพิ่มทับ)
// - PigEntry 20 เข้า: addPigs() → allocated = 38 + 76 = 114 ✗
// - PigEntry 21 เข้า: addPigs() → allocated = 38 + 114 = 152 ✗

// สุดท้าย: allocated = 152 for each pen

$allocs = DB::table('batch_pen_allocations')
    ->where('batch_id', 18)
    ->get();

echo "Current State:\n";
echo "Total allocated_pigs: " . $allocs->sum('allocated_pigs') . " (should be 1500 แต่ stack ไปเป็นมากกว่า)\n";
echo "Total current_quantity: " . $allocs->sum('current_quantity') . " (should be 1300 หรือ 0 ลองดู)\n";
echo "Average per pen: " . round($allocs->avg('allocated_pigs')) . " (should be ~38)\n";

// ดู Batch record
$batch = \App\Models\Batch::find(18);
echo "\nBatch Record:\n";
echo "total_pig_amount: {$batch->total_pig_amount}\n";
echo "current_quantity: {$batch->current_quantity}\n";
echo "total_death: {$batch->total_death}\n";
