<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Farm;

class BarnSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('barns')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $farms = Farm::all();

        foreach ($farms as $farm) {
            for ($i = 1; $i <= 2; $i++) {
                DB::table('barns')->insert([
                    'farm_id' => $farm->id,
                    'barn_code' => 'F' . $farm->id . '-B' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'pig_capacity' => 760, // 20 pens × 38 capacity each
                    'pen_capacity' => 20,
                    'note' => "เล้า {$i} ของฟาร์ม {$farm->name}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
