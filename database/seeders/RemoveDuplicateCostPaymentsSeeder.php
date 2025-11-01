<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CostPayment;
use Illuminate\Support\Facades\DB;

class RemoveDuplicateCostPaymentsSeeder extends Seeder
{
    /**
     * ลบ CostPayment ซ้ำ - เก็บ ID ตัวแรก ลบที่เหลือ
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // หา cost_id ที่มี CostPayment หลายตัว
            $costIdsWithDuplicates = CostPayment::select('cost_id')
                ->groupBy('cost_id')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('cost_id')
                ->toArray();

            if (empty($costIdsWithDuplicates)) {
                $this->command->info('✅ ไม่มี CostPayment ซ้ำ');
                return;
            }

            $this->command->info('🔍 พบ ' . count($costIdsWithDuplicates) . ' Cost ที่มี CostPayment ซ้ำ');

            $totalDeleted = 0;
            foreach ($costIdsWithDuplicates as $costId) {
                $payments = CostPayment::where('cost_id', $costId)
                    ->orderBy('id', 'asc')
                    ->get();

                $this->command->info("  Cost ID: {$costId} มี " . $payments->count() . " CostPayment");

                // เก็บตัวแรก ลบที่เหลือ
                foreach ($payments->skip(1) as $payment) {
                    $this->command->line("    ❌ ลบ CostPayment ID: {$payment->id}");
                    $payment->delete();
                    $totalDeleted++;
                }
            }

            DB::commit();
            $this->command->info("✅ ลบเสร็จ - ลบทั้งหมด {$totalDeleted} CostPayment");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }
}
