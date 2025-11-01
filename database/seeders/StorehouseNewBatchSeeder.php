<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StoreHouse;
use App\Models\InventoryMovement;
use App\Models\Farm;
use App\Models\Batch;
use App\Models\Cost;
use App\Models\CostPayment;
use App\Models\Profit;
use App\Models\ProfitDetail;
use App\Helpers\RevenueHelper;
use Illuminate\Support\Facades\DB;

class StoreHouseNewBatchSeeder extends Seeder
{
    public function run(): void
    {

        // ปิด foreign key constraints ก่อน
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // เปิด foreign key constraints กลับ
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('');

        // ดึง farms ทั้งหมด
        $farms = Farm::all();

        if ($farms->isEmpty()) {
            $this->command->info('ไม่มี Farm ให้สร้าง StoreHouse');
            return;
        }

        // ✅ ดึง batches ที่มี status = 'raising' เท่านั้น (กำลังเลี้ยง)
        // ✅ ต้องมี farm_id ที่ตรงกัน
        $activeBatches = Batch::where('status', 'raising')
            ->where('deleted_at', null)  // ไม่นับ soft delete
            ->get();

        if ($activeBatches->isEmpty()) {
            $this->command->warn('ไม่มี Batch ที่มี status "raising" ให้สร้าง InventoryMovement');
            return;
        }

        $this->command->info("📊 พบ {$activeBatches->count()} batch ที่ active");
        $this->command->info('');

        // Feed Items - รวมประมาณ 700 กระสอบ (สมจริง)
        $feedItems = [
            ['item_code' => 'F001', 'item_name' => 'อาหารหมูเล็ก', 'unit' => 'กระสอบ (30kg)', 'min_quantity' => 100, 'stock' => 235, 'price' => 549, 'transport_cost' => 250],
            ['item_code' => 'F002', 'item_name' => 'อาหารหมูกลาง', 'unit' => 'กระสอบ (30kg)', 'min_quantity' => 100, 'stock' => 235, 'price' => 456, 'transport_cost' => 250],
            ['item_code' => 'F003', 'item_name' => 'อาหารหมูใหญ่', 'unit' => 'กระสอบ (30kg)', 'min_quantity' => 100, 'stock' => 235, 'price' => 456, 'transport_cost' => 250],
        ];
        // รวม: 280 + 280 + 140 = 700 กระสอบ ✓

        // Medicine Items - รวมประมาณ 500 (สมจริง)
        $medicineItems = [
            ['item_code' => 'MED001', 'item_name' => 'อะกริเพน (Agripene)', 'unit' => 'ขวด', 'min_quantity' => 10, 'stock' => 25, 'price' => 350, 'volume' => '100 ml', 'transport_cost' => 100],
            ['item_code' => 'MED002', 'item_name' => 'โนวาม็อกซีน 15%', 'unit' => 'ขวด', 'min_quantity' => 10, 'stock' => 25, 'price' => 220, 'volume' => '100 ml', 'transport_cost' => 100],
            ['item_code' => 'MED003', 'item_name' => 'ทิวแม็ก 20% (Tivamec)', 'unit' => 'ถุง', 'min_quantity' => 10, 'stock' => 25, 'price' => 480, 'volume' => '10 kg', 'transport_cost' => 100],
            ['item_code' => 'MED004', 'item_name' => 'เซอร์โคการ์ด (Surcocard)', 'unit' => 'ขวด', 'min_quantity' => 10, 'stock' => 25, 'price' => 1600, 'volume' => '50 ml', 'transport_cost' => 100],
            ['item_code' => 'MED005', 'item_name' => 'เพ็นไดเสต็บ LA', 'unit' => 'ขวด', 'min_quantity' => 10, 'stock' => 25, 'price' => 360, 'volume' => '100ml', 'transport_cost' => 100],
            ['item_code' => 'MED006', 'item_name' => 'คูเบอร์วิต (Cubervit)', 'unit' => 'ลัง', 'min_quantity' => 10, 'stock' => 25, 'price' => 3600, 'volume' => '25 kg', 'transport_cost' => 100],
            ['item_code' => 'MED007', 'item_name' => 'แอมโคซิลีน (Amoxicillin)', 'unit' => 'ขวด', 'min_quantity' => 10, 'stock' => 25, 'price' => 290, 'volume' => '100 ml', 'transport_cost' => 100],
            ['item_code' => 'MED008', 'item_name' => 'ยาฆ่าเชื้อ (Tornado)', 'unit' => 'ถัง', 'min_quantity' => 10, 'stock' => 25, 'price' => 2200, 'volume' => '20 l', 'transport_cost' => 100],
            ['item_code' => 'MED009', 'item_name' => 'แคลเซียมพลัส (Calcium Plus)', 'unit' => 'ถัง', 'min_quantity' => 10, 'stock' => 25, 'price' => 15000, 'volume' => '10kg', 'transport_cost' => 100],
            ['item_code' => 'MED010', 'item_name' => 'คลอเตตร้าไกรคลิน 20%', 'unit' => 'ถุง', 'min_quantity' => 10, 'stock' => 25, 'price' => 3300, 'volume' => '20kg', 'transport_cost' => 100],
        ];
        // รวม: 60+50+40+35+50+25+50+20+15+25 = 470 (ปัดเป็น ~500) ✓

        $totalCostCreated = 0;
        $totalCostAmount = 0;
        $totalFeedStock = 0;
        $totalMedicineStock = 0;

        foreach ($farms as $farm) {
            $this->command->line(" ประมวลผล Farm: {$farm->farm_name}");

            // ===== สร้าง Feed Items =====
            foreach ($feedItems as $item) {
                $itemCode = $item['item_code'] . '-F' . $farm->id;

                // ตรวจสอบว่ามี StoreHouse นี้แล้วหรือไม่
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
                        'note'          => '฿' . $item['price'] . ' / ' . $item['unit'] . ' + ค่าส่ง ฿' . $item['transport_cost'],
                        'date'          => now(),
                    ]
                );

                $totalFeedStock += $item['stock'];

                // สร้าง Inventory Movement สำหรับ Feed - เฉพาะ batch ที่ยังไม่มี
                foreach ($activeBatches->where('farm_id', $farm->id) as $batch) {
                    // ตรวจสอบว่ามี inventory movement ของ storehouse นี้สำหรับ batch นี้แล้วหรือไม่
                    $existingMovement = InventoryMovement::where('storehouse_id', $storehouse->id)
                        ->where('batch_id', $batch->id)
                        ->first();

                    if (!$existingMovement) {
                        $inventoryMovement = InventoryMovement::create([
                            'storehouse_id' => $storehouse->id,
                            'batch_id' => $batch->id,
                            'change_type' => 'in',  // change_type = in
                            'quantity' => $item['stock'],
                            'note' => 'สต็อกเริ่มต้น (seeder)',
                            'date' => now(),
                        ]);

                        // สร้าง Cost record
                        $pricePerUnit = $item['price'];
                        $transportCost = $item['transport_cost'];
                        $totalPrice = ($inventoryMovement->quantity * $pricePerUnit) + $transportCost;

                        $cost = Cost::create([
                            'farm_id' => $batch->farm_id,
                            'batch_id' => $batch->id,
                            'storehouse_id' => $storehouse->id,
                            'cost_type' => 'feed',
                            'item_code' => $storehouse->item_code,
                            'quantity' => $inventoryMovement->quantity,
                            'unit' => $storehouse->unit,
                            'price_per_unit' => $pricePerUnit,
                            'total_price' => $totalPrice,
                            'transport_cost' => $transportCost,
                            'note' => 'สินค้าเข้าคลัง: ' . $storehouse->item_name,
                            'date' => $inventoryMovement->date,
                        ]);

                        // CostObserver จะ auto-approve feed โดยอัตโนมัติ

                        $totalCostCreated++;
                        $totalCostAmount += $totalPrice;
                    }
                }
            }

            // ===== สร้าง Medicine Items =====
            foreach ($medicineItems as $item) {
                $itemCode = $item['item_code'] . '-F' . $farm->id;

                // ตรวจสอบว่ามี StoreHouse นี้แล้วหรือไม่
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
                        'note'          => '฿' . $item['price'] . ' | ' . $item['volume'] . ' + ค่าส่ง ฿' . $item['transport_cost'],
                        'date'          => now(),
                    ]
                );

                $totalMedicineStock += $item['stock'];

                // สร้าง Inventory Movement สำหรับ Medicine - เฉพาะ batch ที่ยังไม่มี
                foreach ($activeBatches->where('farm_id', $farm->id) as $batch) {
                    // ตรวจสอบว่ามี inventory movement ของ storehouse นี้สำหรับ batch นี้แล้วหรือไม่
                    $existingMovement = InventoryMovement::where('storehouse_id', $storehouse->id)
                        ->where('batch_id', $batch->id)
                        ->first();

                    if (!$existingMovement) {
                        $inventoryMovement = InventoryMovement::create([
                            'storehouse_id' => $storehouse->id,
                            'batch_id' => $batch->id,
                            'change_type' => 'in',  // change_type = in
                            'quantity' => $item['stock'],
                            'note' => 'สต็อกเริ่มต้น (seeder)',
                            'date' => now(),
                        ]);

                        // สร้าง Cost record
                        $pricePerUnit = $item['price'];
                        $transportCost = $item['transport_cost'];
                        $totalPrice = ($inventoryMovement->quantity * $pricePerUnit) + $transportCost;

                        $cost = Cost::create([
                            'farm_id' => $batch->farm_id,
                            'batch_id' => $batch->id,
                            'storehouse_id' => $storehouse->id,
                            'cost_type' => 'medicine',
                            'item_code' => $storehouse->item_code,
                            'quantity' => $inventoryMovement->quantity,
                            'unit' => $storehouse->unit,
                            'price_per_unit' => $pricePerUnit,
                            'total_price' => $totalPrice,
                            'transport_cost' => $transportCost,
                            'note' => 'สินค้าเข้าคลัง: ' . $storehouse->item_name,
                            'date' => $inventoryMovement->date,
                        ]);

                        // CostObserver จะ auto-approve medicine โดยอัตโนมัติ

                        $totalCostCreated++;
                        $totalCostAmount += $totalPrice;
                    }
                }
            }
        }  // closing for farms foreach

        // ===== อัปเดท Profit สำหรับทุก batch =====
        foreach ($activeBatches as $batch) {
            RevenueHelper::calculateAndRecordProfit($batch->id);
            $profit = Profit::where('batch_id', $batch->id)->first();
            if ($profit) {
                $this->command->line(" Batch {$batch->batch_code}: Cost ฿" . number_format($profit->total_cost, 2));
            }
        }

        $this->command->info('');
        $this->command->info(' Seeder เสร็จสิ้น:');
        $this->command->info("   • Feed stock: {$totalFeedStock} กระสอบ");
        $this->command->info("   • Medicine stock: {$totalMedicineStock} หน่วย");
        $this->command->info("   • Cost records created: {$totalCostCreated}");
        $this->command->info("   • Total amount: ฿" . number_format($totalCostAmount, 2));
        $this->command->info('   • InventoryMovement change_type: in (ทั้งหมด)');
        $this->command->info('   • Auto-approved: YES (feed + medicine)');
        $this->command->info('   • Profit updated: YES (ทั้งหมด)');
    }
}
