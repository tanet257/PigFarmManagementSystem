<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventoryMovementSeeder extends Seeder
{
    public function run()
    {
        // ดึง costs ที่มี item_code และ batch_id
        $costs = DB::table('costs')
            ->select('batch_id', 'item_code', 'quantity', 'unit', 'note', 'date')
            ->get();

        foreach ($costs as $cost) {
            // หา storehouse ที่ item_code ตรงกัน
            $storehouse = DB::table('storehouses')
                ->where('item_code', $cost->item_code)
                ->first();

            if ($storehouse) {
                DB::table('inventory_movements')->insert([
                    'storehouse_id' => $storehouse->id,
                    'batch_id'      => $cost->batch_id,   // ให้ตรงกับ batch_id ใน costs
                    'change_type'   => 'in',              // สมมติว่าการเพิ่ม stock
                    'quantity'      => $cost->quantity,
                    'note'          => 'สร้างจาก cost_id: ' . $cost->batch_id,
                    'date'          => $cost->date ?? now(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }
    }
}
