<?php

namespace App\Helpers;

use App\Models\BatchTreatment;
use App\Models\DairyStorehouseUse;

class BatchTreatmentHelper
{
    /**
     * Sync BatchTreatment ตาม DairyStorehouseUse
     *
     * @param int $dairyRecordId
     * @param string $itemCode
     * @param int $batchId
     * @param string|null $status
     * @param string|null $note
     */
    public static function syncBatchTreatment(int $dairyRecordId, string $itemCode, int $batchId, ?string $status = null, ?string $note = null)
    {
        // 1️⃣ ลบ BatchTreatment เดิมสำหรับ medicine นี้
        BatchTreatment::where('dairy_record_id', $dairyRecordId)
            ->where('medicine_code', $itemCode)
            ->delete();

        // 2️⃣ คำนวณผลรวม quantity ของทุก use ที่ใช้ item_code เดียวกัน
        $totalQty = DairyStorehouseUse::where('dairy_record_id', $dairyRecordId)
            ->whereHas('storehouse', function ($q) use ($itemCode) {
                $q->where('item_code', $itemCode);
            })
            ->sum('quantity');

        // 3️⃣ ถ้ามี use เลยสร้าง BatchTreatment ใหม่
        if ($totalQty > 0) {
            $use = DairyStorehouseUse::where('dairy_record_id', $dairyRecordId)
                ->whereHas('storehouse', function ($q) use ($itemCode) {
                    $q->where('item_code', $itemCode);
                })
                ->first();

            if ($use) {
                BatchTreatment::create([
                    'dairy_record_id' => $dairyRecordId,
                    'batch_id'        => $batchId,
                    'medicine_code'   => $itemCode,
                    'medicine_name'   => $use->storehouse->item_name ?? null,
                    'quantity'        => $totalQty,
                    'unit'            => $use->storehouse->unit ?? null,
                    'status'          => $status,
                    'note'            => $note,
                    'date'            => now(),
                ]);
            }
        }
    }
}
