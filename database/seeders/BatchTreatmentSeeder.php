<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Storehouse;
use App\Models\Farm;
use App\Models\Batch;
use App\Models\Barn;
use App\Models\BatchTreatment;
use App\Models\Pen;

class BatchTreatmentSeeder extends Seeder
{
    public function run(): void
    {
        $farms = Farm::all();
        $batches = Batch::all();
        $barns = Barn::all();

        if ($farms->isEmpty() || $batches->isEmpty() || $barns->isEmpty()) {
            $this->command->info('ไม่มี Farm / Batch / Barn ให้สร้าง BatchTreatment');
            return;
        }

        $storehouses = Storehouse::where('item_type', 'medicine')->get();

        if ($storehouses->isEmpty()) {
            $this->command->info('ไม่มี storehouses ที่เป็นยา');
            return;
        }

        foreach ($farms as $farm) {
            foreach ($batches->where('farm_id', $farm->id) as $batch) {
                foreach ($barns->where('farm_id', $farm->id) as $barn) {

                    // ดึง pens ของ barn นี้ แล้วเลือก 2 คอกสุดท้าย
                    $lastPens = Pen::where('barn_id', $barn->id)
                        ->orderBy('id', 'desc') // เรียงจากหลังมาหน้า
                        ->take(2)               // เอา 2 คอกสุดท้าย
                        ->get();

                    foreach ($lastPens as $pen) {
                        $medicine = $storehouses->random();

                        BatchTreatment::create([
                            'status'        => 'วางแผนว่าจะให้ยา',
                            'farm_id'       => $farm->id,
                            'batch_id'      => $batch->id,
                            'barn_id'       => $barn->id,
                            'pen_id'        => $pen->id,
                            'medicine_code' => $medicine->item_code,
                            'medicine_name' => $medicine->item_name,
                            'quantity' => $medicine->stock,
                            'unit'          => $medicine->unit,
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]);
                    }
                }
            }
        }

        $this->command->info('Seed BatchTreatment (ใช้แค่ 2 คอกสุดท้ายของแต่ละเล้า) เรียบร้อยแล้ว');
    }
}
