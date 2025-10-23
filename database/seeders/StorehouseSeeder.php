<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StoreHouse;
use App\Models\InventoryMovement;
use App\Models\Farm;
use App\Models\Batch;

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

        // ดึง batch ตัวอย่าง
        $batch = Batch::first();
        if (!$batch) {
            $this->command->info('ไม่มี Batch ให้สร้าง InventoryMovement');
            return;
        }

        // Feed Items (เหมือนเดิม)
        $feedItems = [
            ['item_code' => 'F931L', 'item_name' => 'อาหารหมูเล็ก', 'unit' => 'กระสอบ', 'min_quantity' => 10, 'stock' => 50],
            ['item_code' => 'F992', 'item_name' => 'อาหารหมูกลาง', 'unit' => 'กระสอบ', 'min_quantity' => 10, 'stock' => 40],
            ['item_code' => 'F993', 'item_name' => 'อาหารหมูใหญ่', 'unit' => 'กระสอบ', 'min_quantity' => 10, 'stock' => 30],
        ];

        // Medicine Items (เพิ่มใหม่)
        $medicineItems = [
            ['item_code' => 'MED001', 'item_name' => 'อะกริเพน', 'unit' => 'ขวด', 'min_quantity' => 5, 'stock' => 10, 'price' => 300, 'volume' => '100 ml'],
            ['item_code' => 'MED002', 'item_name' => 'โนวาม็อกซีน15%', 'unit' => 'ขวด', 'min_quantity' => 5, 'stock' => 10, 'price' => 180, 'volume' => '100 ml'],
            ['item_code' => 'MED003', 'item_name' => 'ทิวแม็ก 20%', 'unit' => 'ถุง', 'min_quantity' => 3, 'stock' => 8, 'price' => 410, 'volume' => '10 kg'],
            ['item_code' => 'MED004', 'item_name' => 'เซอร์โคการ์ด', 'unit' => 'ขวด', 'min_quantity' => 5, 'stock' => 5, 'price' => 1350, 'volume' => '50 ml'],
            ['item_code' => 'MED005', 'item_name' => 'เพ็นไดเสต็บ LA', 'unit' => 'ขวด', 'min_quantity' => 5, 'stock' => 10, 'price' => 300, 'volume' => '100ml'],
            ['item_code' => 'MED006', 'item_name' => 'คูเบอร์วิต', 'unit' => 'ลัง', 'min_quantity' => 2, 'stock' => 5, 'price' => 3100, 'volume' => '25 kg'],
            ['item_code' => 'MED007', 'item_name' => 'แอมโคซิลีน', 'unit' => 'ขวด', 'min_quantity' => 5, 'stock' => 10, 'price' => 250, 'volume' => '100 ml'],
            ['item_code' => 'MED008', 'item_name' => 'ยาฆ่าเชื้อ (ทอร์นาโด)', 'unit' => 'ถัง', 'min_quantity' => 2, 'stock' => 4, 'price' => 1900, 'volume' => '20 l'],
            ['item_code' => 'MED009', 'item_name' => 'แคลเซียมพลัส', 'unit' => 'ถัง', 'min_quantity' => 1, 'stock' => 3, 'price' => 13000, 'volume' => '10kg'],
            ['item_code' => 'MED010', 'item_name' => 'คลอเตตร้าไกรคลิน 20%', 'unit' => 'ถุง', 'min_quantity' => 2, 'stock' => 5, 'price' => 2850, 'volume' => '20kg'],
        ];

        foreach ($farms as $farm) {
            // สร้าง Feed Items
            foreach ($feedItems as $item) {
                $storehouse = StoreHouse::create([
                    'farm_id'       => $farm->id,
                    'item_type'     => 'feed',
                    'item_code'     => $item['item_code'] . '-' . $farm->id,
                    'item_name'     => $item['item_name'],
                    'stock'         => $item['stock'],
                    'min_quantity'  => $item['min_quantity'],
                    'unit'          => $item['unit'],
                    'status'        => 'available',
                    'note'          => 'สินค้าตัวอย่าง Feed',
                    'date'          => now(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                // สร้าง Inventory Movement สำหรับ Feed
                InventoryMovement::create([
                    'storehouse_id' => $storehouse->id,
                    'batch_id'      => $batch->id,
                    'change_type'   => 'in',
                    'quantity'      => $item['stock'],
                    'note'          => 'สต็อกเริ่มต้น',
                    'date'          => now(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            // สร้าง Medicine Items
            foreach ($medicineItems as $item) {
                $storehouse = StoreHouse::create([
                    'farm_id'       => $farm->id,
                    'item_type'     => 'medicine',
                    'item_code'     => $item['item_code'] . '-' . $farm->id,
                    'item_name'     => $item['item_name'],
                    'stock'         => $item['stock'],
                    'min_quantity'  => $item['min_quantity'],
                    'unit'          => $item['unit'],
                    'status'        => 'available',
                    'note'          => 'ราคา: ฿' . $item['price'] . ' | ปริมาณ: ' . $item['volume'],
                    'date'          => now(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                // สร้าง Inventory Movement สำหรับ Medicine
                InventoryMovement::create([
                    'storehouse_id' => $storehouse->id,
                    'batch_id'      => $batch->id,
                    'change_type'   => 'in',
                    'quantity'      => $item['stock'],
                    'note'          => 'สต็อกเริ่มต้น - ราคา: ฿' . $item['price'] . ' ต่อ ' . $item['volume'],
                    'date'          => now(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }

        $this->command->info('Seed StoreHouse (Feed + Medicines) และ InventoryMovements เรียบร้อยแล้ว');
    }
}
