<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StoreHouse;
use App\Models\Farm;

class StoreHouseSeeder extends Seeder
{
    public function run(): void
    {
        // ดึง farms ทั้งหมด
        $farms = Farm::all();

        if ($farms->isEmpty()) {
            $this->command->info('ไม่มี Farm ให้สร้าง StoreHouse');
            return;
        }

        // สินค้าตัวอย่าง
        $items = [
            ['item_type' => 'feed', 'item_code' => 'F931L', 'item_name' => 'อาหารหมูเล็ก', 'unit' => 'กระสอบ', 'min_quantity' => 10],
            ['item_type' => 'feed', 'item_code' => 'F992', 'item_name' => 'อาหารหมูกลาง', 'unit' => 'กระสอบ', 'min_quantity' => 10],
            ['item_type' => 'feed', 'item_code' => 'F993', 'item_name' => 'อาหารหมูใหญ่', 'unit' => 'กระสอบ', 'min_quantity' => 10],
            ['item_type' => 'medicine', 'item_code' => 'MD001', 'item_name' => 'ยาป้องกันโรค', 'unit' => 'กล่อง', 'min_quantity' => 5],
            ['item_type' => 'vaccine', 'item_code' => 'VC001', 'item_name' => 'วัคซีนหมู', 'unit' => 'กล่อง', 'min_quantity' => 5],
        ];

        foreach ($farms as $farm) {
            foreach ($items as $item) {
                StoreHouse::create([
                    'farm_id'      => $farm->id,
                    'item_type'    => $item['item_type'],
                    'item_code'    => $item['item_code'] . '-' . $farm->id, // ผูกกับฟาร์ม
                    'item_name'    => $item['item_name'],
                    'stock'        => 0,
                    'min_quantity' => $item['min_quantity'],
                    'unit'         => $item['unit'],
                    'status'       => 'unavailable',
                    'note'         => null,
                    'date'         => now(),
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }

        $this->command->info('Seed StoreHouse เรียบร้อยแล้ว');
    }
}
