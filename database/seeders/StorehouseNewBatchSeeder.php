<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StoreHouse;
use App\Models\InventoryMovement;
use App\Models\Farm;
use App\Models\Batch;
use App\Models\Cost;
use App\Models\CostPayment;

class StoreHouseNewBatchSeeder extends Seeder
{
    public function run(): void
    {
        // ดึง farms ทั้งหมด
        $farms = Farm::all();

        if ($farms->isEmpty()) {
            $this->command->info('ไม่มี Farm ให้สร้าง StoreHouse');
            return;
        }

        // ดึง batches ที่มี status = 'กำลังเลี้ยง' เท่านั้น (ห้าม cancelled)
        // ✅ FIX: ต้องเช็ค != 'cancelled' อย่างถูกต้อง (logic error เดิม)
        $activeBatches = Batch::where('status', 'กำลังเลี้ยง')
            ->get();

        if ($activeBatches->isEmpty()) {
            $this->command->warn('ไม่มี Batch ที่มี status "กำลังเลี้ยง" ให้สร้าง InventoryMovement');
            return;
        }

        // ✅ เช็ค InventoryMovement ที่มีอยู่แล้ว เพื่อหลีกเลี่ยง duplicate
        $existingMovements = InventoryMovement::pluck('storehouse_id', 'batch_id')->toArray();

        // Feed Items (เหมือนเดิม)
        $feedItems = [
            ['item_code' => 'F931L', 'item_name' => 'อาหารหมูเล็ก', 'unit' => 'กระสอบ', 'min_quantity' => 10, 'stock' => 50, 'price' => 549, 'transport_cost' => 200],
            ['item_code' => 'F992', 'item_name' => 'อาหารหมูกลาง', 'unit' => 'กระสอบ', 'min_quantity' => 10, 'stock' => 40, 'price' => 456, 'transport_cost' => 200],
            ['item_code' => 'F993', 'item_name' => 'อาหารหมูใหญ่', 'unit' => 'กระสอบ', 'min_quantity' => 10, 'stock' => 30, 'price' => 456, 'transport_cost' => 200],
        ];

        // Medicine Items (เพิ่มใหม่)
        $medicineItems = [
            ['item_code' => 'MED001', 'item_name' => 'อะกริเพน', 'unit' => 'ขวด', 'min_quantity' => 5, 'stock' => 10, 'price' => 300, 'volume' => '100 ml', 'transport_cost' => 50],
            ['item_code' => 'MED002', 'item_name' => 'โนวาม็อกซีน15%', 'unit' => 'ขวด', 'min_quantity' => 5, 'stock' => 10, 'price' => 180, 'volume' => '100 ml', 'transport_cost' => 50],
            ['item_code' => 'MED003', 'item_name' => 'ทิวแม็ก 20%', 'unit' => 'ถุง', 'min_quantity' => 3, 'stock' => 8, 'price' => 410, 'volume' => '10 kg', 'transport_cost' => 80],
            ['item_code' => 'MED004', 'item_name' => 'เซอร์โคการ์ด', 'unit' => 'ขวด', 'min_quantity' => 5, 'stock' => 5, 'price' => 1350, 'volume' => '50 ml', 'transport_cost' => 100],
            ['item_code' => 'MED005', 'item_name' => 'เพ็นไดเสต็บ LA', 'unit' => 'ขวด', 'min_quantity' => 5, 'stock' => 10, 'price' => 300, 'volume' => '100ml', 'transport_cost' => 60],
            ['item_code' => 'MED006', 'item_name' => 'คูเบอร์วิต', 'unit' => 'ลัง', 'min_quantity' => 2, 'stock' => 5, 'price' => 3100, 'volume' => '25 kg', 'transport_cost' => 150],
            ['item_code' => 'MED007', 'item_name' => 'แอมโคซิลีน', 'unit' => 'ขวด', 'min_quantity' => 5, 'stock' => 10, 'price' => 250, 'volume' => '100 ml', 'transport_cost' => 50],
            ['item_code' => 'MED008', 'item_name' => 'ยาฆ่าเชื้อ (ทอร์นาโด)', 'unit' => 'ถัง', 'min_quantity' => 2, 'stock' => 4, 'price' => 1900, 'volume' => '20 l', 'transport_cost' => 120],
            ['item_code' => 'MED009', 'item_name' => 'แคลเซียมพลัส', 'unit' => 'ถัง', 'min_quantity' => 1, 'stock' => 3, 'price' => 13000, 'volume' => '10kg', 'transport_cost' => 200],
            ['item_code' => 'MED010', 'item_name' => 'คลอเตตร้าไกรคลิน 20%', 'unit' => 'ถุง', 'min_quantity' => 2, 'stock' => 5, 'price' => 2850, 'volume' => '20kg', 'transport_cost' => 150],
        ];

        foreach ($farms as $farm) {
            // สร้าง Feed Items
            foreach ($feedItems as $item) {
                $itemCode = $item['item_code'] . '-' . $farm->id;

                // ✅ ตรวจสอบว่ามี StoreHouse นี้แล้วหรือไม่
                $storehouse = StoreHouse::firstOrCreate(
                    ['item_code' => $itemCode],
                    [
                        'farm_id'       => $farm->id,
                        'item_type'     => 'feed',
                        'item_name'     => $item['item_name'],
                        'stock'         => $item['stock'],
                        'min_quantity'  => $item['min_quantity'],
                        'unit'          => $item['unit'],
                        'status'        => 'available',
                        'note'          => 'ราคา: ฿' . $item['price'] . ' ต่อ ' . $item['unit'] . ' | ค่าส่ง: ฿' . $item['transport_cost'],
                        'date'          => now(),
                    ]
                );

                // สร้าง Inventory Movement สำหรับ Feed - เฉพาะ batch ที่ยังไม่มี InventoryMovement
                foreach ($activeBatches as $batch) {
                    // ✅ FIX: ใช้ firstOrCreate เพื่อป้องกัน duplicate
                    $inventoryMovement = InventoryMovement::firstOrCreate(
                        [
                            'storehouse_id' => $storehouse->id,
                            'batch_id' => $batch->id,
                            'change_type' => 'in',
                        ],
                        [
                            'quantity' => $item['stock'],
                            'note' => 'สต็อกเริ่มต้น',
                            'date' => now(),
                        ]
                    );

                    // ✅ NEW: สร้าง Cost และ CostPayment ตรงในที่นี้
                    if ($inventoryMovement->wasRecentlyCreated) {
                        $pricePerUnit = $item['price'];
                        $transportCost = $item['transport_cost'];
                        $totalPrice = ($inventoryMovement->quantity * $pricePerUnit) + $transportCost;

                        // สร้าง Cost record
                        $cost = Cost::create([
                            'farm_id' => $batch->farm_id,
                            'batch_id' => $batch->id,
                            'storehouse_id' => $storehouse->id,
                            'cost_type' => $storehouse->item_type, // 'feed' หรือ 'medicine'
                            'item_code' => $storehouse->item_code,
                            'quantity' => $inventoryMovement->quantity,
                            'unit' => $storehouse->unit,
                            'price_per_unit' => $pricePerUnit,
                            'total_price' => $totalPrice,
                            'transport_cost' => $transportCost,
                            'note' => 'ต้นทุน ' . $storehouse->item_type . ' จาก ' . $storehouse->item_name . ' - ' . $inventoryMovement->note,
                            'date' => $inventoryMovement->date,
                        ]);

                        // สร้าง CostPayment auto-approved
                        CostPayment::create([
                            'cost_id' => $cost->id,
                            'amount' => $totalPrice,
                            'status' => 'approved',
                            'approved_by' => 1,
                            'approved_date' => now(),
                            'reason' => 'Auto-approved from seeder',
                        ]);
                    }
                }
            }

            // สร้าง Medicine Items
            foreach ($medicineItems as $item) {
                $itemCode = $item['item_code'] . '-' . $farm->id;

                // ✅ ตรวจสอบว่ามี StoreHouse นี้แล้วหรือไม่
                $storehouse = StoreHouse::firstOrCreate(
                    ['item_code' => $itemCode],
                    [
                        'farm_id'       => $farm->id,
                        'item_type'     => 'medicine',
                        'item_name'     => $item['item_name'],
                        'stock'         => $item['stock'],
                        'min_quantity'  => $item['min_quantity'],
                        'unit'          => $item['unit'],
                        'status'        => 'available',
                        'note'          => 'ราคา: ฿' . $item['price'] . ' | ปริมาณ: ' . $item['volume'] . ' | ค่าส่ง: ฿' . $item['transport_cost'],
                        'date'          => now(),
                    ]
                );

                // สร้าง Inventory Movement สำหรับ Medicine - เฉพาะ batch ที่ยังไม่มี InventoryMovement
                foreach ($activeBatches as $batch) {
                    // ✅ FIX: ใช้ firstOrCreate เพื่อป้องกัน duplicate
                    $inventoryMovement = InventoryMovement::firstOrCreate(
                        [
                            'storehouse_id' => $storehouse->id,
                            'batch_id' => $batch->id,
                            'change_type' => 'in',
                        ],
                        [
                            'quantity' => $item['stock'],
                            'note' => 'สต็อกเริ่มต้น - ราคา: ฿' . $item['price'] . ' ต่อ ' . $item['volume'],
                            'date' => now(),
                        ]
                    );

                    // ✅ NEW: สร้าง Cost และ CostPayment ตรงในที่นี้
                    if ($inventoryMovement->wasRecentlyCreated) {
                        $pricePerUnit = $item['price'];
                        $transportCost = $item['transport_cost'];
                        $totalPrice = ($inventoryMovement->quantity * $pricePerUnit) + $transportCost;

                        // สร้าง Cost record
                        $cost = Cost::create([
                            'farm_id' => $batch->farm_id,
                            'batch_id' => $batch->id,
                            'storehouse_id' => $storehouse->id,
                            'cost_type' => $storehouse->item_type, // 'feed' หรือ 'medicine'
                            'item_code' => $storehouse->item_code,
                            'quantity' => $inventoryMovement->quantity,
                            'unit' => $storehouse->unit,
                            'price_per_unit' => $pricePerUnit,
                            'total_price' => $totalPrice,
                            'transport_cost' => $transportCost,
                            'note' => 'ต้นทุน ' . $storehouse->item_type . ' จาก ' . $storehouse->item_name . ' - ' . $inventoryMovement->note,
                            'date' => $inventoryMovement->date,
                        ]);

                        // สร้าง CostPayment auto-approved
                        CostPayment::create([
                            'cost_id' => $cost->id,
                            'amount' => $totalPrice,
                            'status' => 'approved',
                            'approved_by' => 1,
                            'approved_date' => now(),
                            'reason' => 'Auto-approved from seeder',
                        ]);
                    }
                }
            }
        }

        $this->command->info('Seed StoreHouse (Feed + Medicines) และ InventoryMovements เรียบร้อยแล้ว');
    }
}
