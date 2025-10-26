<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cost;

class CheckCostColumnsCommand extends Command
{
    protected $signature = 'cost:columns';
    protected $description = 'ดู columns ของ Cost record';

    public function handle()
    {
        $cost = Cost::where('cost_type', 'feed')->first();

        if (!$cost) {
            $this->warn('ไม่มี cost type feed');
            return;
        }

        $this->info('=== Cost Feed Record ===');
        $this->line('ID: ' . $cost->id);
        $this->line('cost_type: ' . $cost->cost_type);
        $this->line('quantity: ' . $cost->quantity);
        $this->line('price_per_unit: ' . $cost->price_per_unit);
        $this->line('transport_cost: ' . $cost->transport_cost);
        $this->line('total_price: ' . ($cost->total_price ?? 'NULL'));
        $this->line('amount: ' . ($cost->amount ?? 'NULL'));

        $this->info('=== Attributes ===');
        $this->table(
            ['Key', 'Value'],
            collect($cost->getAttributes())->map(fn($v, $k) => [$k, $v])->toArray()
        );
    }
}
