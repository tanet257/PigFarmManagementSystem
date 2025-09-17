<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Storehouse;
use App\Models\Farm;
use App\Models\Batch;

class StorehouseSeeder extends Seeder
{
    public function run(): void
    {
        // ดึง farms และ batches ทั้งหมด
        $farms = Farm::all();
        $batches = Batch::all();

        // ถ้าไม่มี farms หรือ batches ให้หยุด
        if ($farms->isEmpty() || $batches->isEmpty()) {
            $this->command->info('ไม่มี Farm หรือ Batch ให้สร้าง Storehouse');
            return;
        }

        // สร้างตัวอย่าง storehouses
        foreach ($farms as $farm) {
            foreach ($batches->where('farm_id', $farm->id) as $batch) {

                // สุ่มจำนวนสินค้าหลายประเภทต่อ batch
                $items = [
                    ['item_type' => 'feed', 'item_code' => 'F931', 'item_name' => 'อาหารหมูเล็ก', 'unit' => 'กระสอบ'],
                    ['item_type' => 'feed', 'item_code' => 'F932', 'item_name' => 'อาหารหมูกลาง', 'unit' => 'กระสอบ'],
                    ['item_type' => 'feed', 'item_code' => 'F933', 'item_name' => 'อาหารหมูใหญ่', 'unit' => 'กระสอบ'],
                    ['item_type' => 'medicine', 'item_code' => 'MD001', 'item_name' => 'ยาป้องกันโรค', 'unit' => 'ขวด'],
                    ['item_type' => 'vaccine', 'item_code' => 'VC001', 'item_name' => 'วัคซีนหมู', 'unit' => 'ขวด'],
                ];

                foreach ($items as $item) {
                    Storehouse::create([
                        'status' => 'available', // หรือ 'unavailable' ถ้าต้องการ
                        'farm_id' => $farm->id,
                        'batch_id' => $batch->id,
                        'item_type' => $item['item_type'],
                        'item_code' => $item['item_code'] . '-' . $batch->id,
                        'item_name' => $item['item_name'],
                        'unit' => $item['unit'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info('Seed Storehouse เรียบร้อยแล้ว');
    }
}
