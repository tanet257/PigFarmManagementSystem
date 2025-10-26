<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InventoryMovement;
use App\Models\Cost;
use App\Models\CostPayment;
use App\Models\Profit;

class CheckDataCommand extends Command
{
    protected $signature = 'data:check';
    protected $description = 'ตรวจสอบข้อมูล InventoryMovement, Cost, CostPayment, Profit';

    public function handle()
    {
        $this->info('=== InventoryMovement ล่าสุด ===');
        $inv = InventoryMovement::latest()->first();
        if ($inv) {
            $this->table(
                ['ID', 'Batch ID', 'Change Type', 'Quantity', 'Date'],
                [[$inv->id, $inv->batch_id, $inv->change_type, $inv->quantity, $inv->date]]
            );
        } else {
            $this->warn('ไม่มี InventoryMovement');
        }

        $this->info('=== Cost ล่าสุด ===');
        $cost = Cost::latest()->first();
        if ($cost) {
            $this->table(
                ['ID', 'Batch ID', 'Cost Type', 'Total Price', 'Date'],
                [[$cost->id, $cost->batch_id, $cost->cost_type, $cost->total_price, $cost->date]]
            );
        } else {
            $this->warn('ไม่มี Cost');
        }

        $this->info('=== CostPayment ล่าสุด ===');
        $costPayment = CostPayment::latest()->first();
        if ($costPayment) {
            $this->table(
                ['ID', 'Cost ID', 'Amount', 'Status', 'Approved Date'],
                [[$costPayment->id, $costPayment->cost_id, $costPayment->amount, $costPayment->status, $costPayment->approved_date]]
            );
        } else {
            $this->warn('ไม่มี CostPayment');
        }

        $this->info('=== Profit ล่าสุด ===');
        $profit = Profit::latest()->first();
        if ($profit) {
            $this->table(
                ['ID', 'Batch ID', 'Feed Cost', 'Medicine Cost', 'Total Cost', 'Total Revenue', 'Gross Profit'],
                [[$profit->id, $profit->batch_id, $profit->feed_cost, $profit->medicine_cost, $profit->total_cost, $profit->total_revenue, $profit->gross_profit]]
            );
        } else {
            $this->warn('ไม่มี Profit');
        }

        $this->info('=== สรุป ===');
        $this->line('InventoryMovement: ' . InventoryMovement::count());
        $this->line('Cost: ' . Cost::count());
        $this->line('CostPayment: ' . CostPayment::count());
        $this->line('Profit: ' . Profit::count());
    }
}
