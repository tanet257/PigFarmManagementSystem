<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pen;
use App\Models\Barn;

class PenSeeder extends Seeder
{
    public function run(): void
    {
        // ดึง barns ทั้งหมดจากฐานข้อมูล
        $barns = Barn::all();

        foreach ($barns as $barn) {
            // ดึง farm_id และ barn_code ของแต่ละเล้า
            $farmId = $barn->farm_id;
            $barnCode = $barn->barn_code; // เช่น F1-B01

            // วนสร้าง pen 20 คอกในแต่ละเล้า
            for ($i = 1; $i <= 20; $i++) {
                Pen::create([
                    'barn_id'      => $barn->id,
                    'pen_code'     => "{$barnCode}-P" . str_pad($i, 2, '0', STR_PAD_LEFT), // เช่น F1-B01-P01
                    'pig_capacity' => 38, // ความจุตามจริง
                    'status'       => 'กำลังใช้งาน',
                    'note'         => null,
                ]);
            }
        }
    }
}
