<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cost;
use App\Models\CostPayment;

class CheckCostPaymentCommand extends Command
{
    protected $signature = 'data:check-cost-payment';
    protected $description = 'ตรวจสอบ Cost ที่ไม่มี CostPayment';

    public function handle()
    {
        $costsWithoutPayment = Cost::whereNotIn('id', function($query) {
            $query->select('cost_id')->from('cost_payments');
        })->get();

        $this->info('=== Cost ที่ไม่มี CostPayment ===');
        $this->line('จำนวน: ' . $costsWithoutPayment->count());

        if ($costsWithoutPayment->count() > 0) {
            $this->table(
                ['ID', 'Batch ID', 'Cost Type', 'Total Price', 'Status'],
                $costsWithoutPayment->map(fn($c) => [$c->id, $c->batch_id, $c->cost_type, $c->total_price, $c->payment_status])->toArray()
            );

            $this->warn('⚠️ ต้องสร้าง CostPayment ให้กับ Cost เหล่านี้!');

            // สร้างอัตโนมัติ
            foreach ($costsWithoutPayment as $cost) {
                CostPayment::firstOrCreate(
                    ['cost_id' => $cost->id],
                    [
                        'amount' => $cost->total_price,
                        'status' => 'approved',
                        'approved_by' => 1,
                        'approved_date' => now(),
                        'reason' => 'Auto-created from seeder (missing CostPayment)',
                    ]
                );
            }

            $this->info('✅ สร้าง CostPayment เรียบร้อยแล้ว');
        } else {
            $this->info('✅ ทั้งหมด Cost มี CostPayment แล้ว');
        }
    }
}
