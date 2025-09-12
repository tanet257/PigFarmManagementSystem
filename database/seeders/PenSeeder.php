<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pen;
use App\Models\Barn;

class PenSeeder extends Seeder
{
    public function run(): void
    {
        // ดึง barns ทั้งหมด
        $barns = Barn::all();

        // ตัวอักษรเริ่มจาก A, B, C ...
        $alphabet = range('A', 'B');

        foreach ($barns as $index => $barn) {
            if ($index >= 4) break; // หยุดเมื่อเกิน 4 barns

            $prefix = chr(65 + $index); // 65 คือ 'A' → A, B, C, D

            for ($i = 1; $i <= 20; $i++) {
                Pen::create([
                    'barn_id'      => $barn->id,
                    'pen_code'     => sprintf('%s%02d', $prefix, $i), // เช่น A01, A02 … A20
                    'pig_capacity' => 38, // กำหนดค่าตามจริง
                    'status'       => 'กำลังใช้งาน',
                    'note'         => null,
                ]);
            }
        }
    }
}
