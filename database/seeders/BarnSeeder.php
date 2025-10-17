<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarnSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('barns')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('barns')->insert([
            [
                'farm_id' => 1,
                'barn_code' => 'B-01',
                'pig_capacity' => 750,
                'pen_capacity' => 20,
                'note' => 'เล้า 1 ของฟาร์ม ทะเลบกฟาร์ม',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'farm_id' => 2,
                'barn_code' => 'B-02',
                'pig_capacity' => 750,
                'pen_capacity' => 20,
                'note' => 'เล้า 2 ของฟาร์ม ทะเลบกฟาร์ม',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
