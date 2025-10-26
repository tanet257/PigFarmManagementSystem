<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CostPayment;
use App\Models\Cost;
use App\Models\Profit;
use Illuminate\Support\Facades\DB;

class ResetCostPaymentCommand extends Command
{
    protected $signature = 'cost-payment:reset';
    protected $description = 'ลบ CostPayment ทั้งหมด และสร้างใหม่จาก Cost ทั้งหมด';

    public function handle()
    {
        $this->warn('⚠️ คำเตือน: ลบ CostPayment ทั้งหมด!');

        if (!$this->confirm('ยืนยันหรือไม่?')) {
            $this->info('ยกเลิก');
            return;
        }

        // ลบ ProfitDetail ก่อน
        DB::statement('DELETE FROM profit_details');
        $this->info('✅ ลบ ProfitDetail แล้ว');

        // ลบ Profit
        DB::statement('DELETE FROM profits');
        $this->info('✅ ลบ Profit แล้ว');

        // ลบ CostPayment
        CostPayment::truncate();
        $this->info('✅ ลบ CostPayment แล้ว');

        // สร้าง CostPayment ใหม่
        $costs = Cost::all();
        foreach ($costs as $cost) {
            CostPayment::create([
                'cost_id' => $cost->id,
                'amount' => $cost->total_price,
                'status' => 'approved',
                'approved_by' => 1,
                'approved_date' => now(),
                'reason' => 'Auto-created from reset',
            ]);
        }

        $this->info('✅ สร้าง CostPayment ใหม่: ' . $costs->count() . ' records');
        $this->info('✅ เรียบร้อย');
    }
}
