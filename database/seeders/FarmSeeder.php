<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Farm;

class FarmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Use updateOrCreate so running the seeder multiple times is idempotent
        Farm::updateOrCreate([
            'farm_name' => 'ศรีเจริญการกิจฟาร์ม1',
        ], [
            'barn_capacity' => 2,
        ]);

        Farm::updateOrCreate([
            'farm_name' => 'ศรีเจริญการกิจฟาร์ม2',
        ], [
            'barn_capacity' => 2,
        ]);

    }
}
