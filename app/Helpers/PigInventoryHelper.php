<?php

namespace App\Helpers;

use App\Models\Batch;
use App\Models\BatchPenAllocation;
use Illuminate\Support\Facades\DB;
use Exception;

class PigInventoryHelper
{
    /**
     * เพิ่มหมูเข้าเล้า-คอกและรุ่น (เมื่อมีการซื้อหมูเข้า)
     *
     * @param int $batchId รหัสรุ่น
     * @param int $barnId รหัสเล้า
     * @param int $penId รหัสคอก
     * @param int $quantity จำนวนหมูที่ต้องการเพิ่ม
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public static function addPigs($batchId, $barnId, $penId, $quantity)
    {
        try {
            DB::beginTransaction();

            // 1. ตรวจสอบว่ามีข้อมูลเล้า-คอกนี้แล้วหรือยัง
            $allocation = BatchPenAllocation::where('batch_id', $batchId)
                ->where('barn_id', $barnId)
                ->where('pen_id', $penId)
                ->lockForUpdate()
                ->first();

            if ($allocation) {
                // มีอยู่แล้ว - เพิ่มจำนวน
                $oldAllocatedPigs = $allocation->allocated_pigs;
                $oldCurrentQuantity = $allocation->current_quantity ?? $oldAllocatedPigs;

                $allocation->allocated_pigs = $oldAllocatedPigs + $quantity;
                $allocation->current_quantity = $oldCurrentQuantity + $quantity;
                $allocation->save();

                $message = "เพิ่มหมูในเล้า-คอกเดิม";
            } else {
                // ยังไม่มี - สร้างใหม่
                $allocation = BatchPenAllocation::create([
                    'batch_id'         => $batchId,
                    'barn_id'          => $barnId,
                    'pen_id'           => $penId,
                    'allocated_pigs'   => $quantity,
                    'current_quantity' => $quantity,
                ]);

                $message = "สร้างข้อมูลเล้า-คอกใหม่";
            }

            // 2. อัปเดตจำนวนหมูใน batches
            $batch = Batch::lockForUpdate()->find($batchId);

            if (!$batch) {
                throw new Exception('ไม่พบข้อมูลรุ่น');
            }

            $oldTotalAmount = $batch->total_pig_amount ?? 0;
            $oldCurrentQuantity = $batch->current_quantity ?? $oldTotalAmount;

            $batch->total_pig_amount = $oldTotalAmount + $quantity;
            $batch->current_quantity = $oldCurrentQuantity + $quantity;
            $batch->save();

            DB::commit();

            return [
                'success' => true,
                'message' => "✅ {$message} ({$quantity} ตัว)",
                'data' => [
                    'quantity_added' => $quantity,
                    'allocation_id' => $allocation->id,
                    'batch' => [
                        'total_pig_amount' => $batch->total_pig_amount,
                        'current_quantity' => $batch->current_quantity
                    ]
                ]
            ];
        } catch (Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => '❌ เกิดข้อผิดพลาด: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * ลดจำนวนหมูจากเล้า-คอกและรุ่น (เมื่อมีการขาย/ตาย/คัดทิ้ง)
     *
     * @param int $batchId รหัสรุ่น
     * @param int $penId รหัสเล้า-คอก
     * @param int $quantity จำนวนหมูที่ต้องการลด
     * @param string $reason เหตุผล (sale, death, culling)
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public static function reducePigInventory($batchId, $penId, $quantity, $reason = 'sale')
    {
        try {
            DB::beginTransaction();

            // 1. ตรวจสอบข้อมูล batch_pen_allocations
            $allocation = BatchPenAllocation::where('batch_id', $batchId)
                ->where('pen_id', $penId)
                ->lockForUpdate()
                ->first();

            if (!$allocation) {
                return [
                    'success' => false,
                    'message' => '❌ ไม่พบข้อมูลหมูในเล้า-คอกนี้',
                    'data' => null
                ];
            }

            // 2. ตรวจสอบจำนวนหมูคงเหลือ
            $currentQuantity = $allocation->current_quantity ?? $allocation->allocated_pigs;

            if ($currentQuantity < $quantity) {
                return [
                    'success' => false,
                    'message' => "❌ หมูในเล้า-คอกไม่เพียงพอ (มีอยู่ {$currentQuantity} ตัว ต้องการ {$quantity} ตัว)",
                    'data' => [
                        'available' => $currentQuantity,
                        'requested' => $quantity,
                        'shortage' => $quantity - $currentQuantity
                    ]
                ];
            }

            // 3. ลดจำนวนหมูใน batch_pen_allocations
            $newQuantity = $currentQuantity - $quantity;
            $allocation->current_quantity = $newQuantity;
            $allocation->save();

            // 4. ลดจำนวนหมูใน batches
            $batch = Batch::lockForUpdate()->find($batchId);

            if (!$batch) {
                throw new Exception('ไม่พบข้อมูลรุ่น');
            }

            $batchCurrentQuantity = $batch->current_quantity ?? $batch->total_pig_amount;
            $batch->current_quantity = $batchCurrentQuantity - $quantity;
            $batch->save();

            DB::commit();

            return [
                'success' => true,
                'message' => "✅ ลดจำนวนหมูเรียบร้อย ({$quantity} ตัว)",
                'data' => [
                    'reason' => $reason,
                    'quantity_reduced' => $quantity,
                    'pen_allocation' => [
                        'before' => $currentQuantity,
                        'after' => $newQuantity,
                        'remaining' => $newQuantity
                    ],
                    'batch' => [
                        'before' => $batchCurrentQuantity,
                        'after' => $batch->current_quantity,
                        'remaining' => $batch->current_quantity
                    ]
                ]
            ];
        } catch (Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => '❌ เกิดข้อผิดพลาด: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * เพิ่มจำนวนหมูกลับเข้าเล้า-คอกและรุ่น (เมื่อยกเลิกการขาย)
     *
     * @param int $batchId รหัสรุ่น
     * @param int $penId รหัสเล้า-คอก
     * @param int $quantity จำนวนหมูที่ต้องการเพิ่ม
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public static function increasePigInventory($batchId, $penId, $quantity)
    {
        try {
            DB::beginTransaction();

            // 1. ตรวจสอบข้อมูล batch_pen_allocations
            $allocation = BatchPenAllocation::where('batch_id', $batchId)
                ->where('pen_id', $penId)
                ->lockForUpdate()
                ->first();

            if (!$allocation) {
                return [
                    'success' => false,
                    'message' => '❌ ไม่พบข้อมูลเล้า-คอก',
                    'data' => null
                ];
            }

            // 2. เพิ่มจำนวนหมูใน batch_pen_allocations
            $currentQuantity = $allocation->current_quantity ?? $allocation->allocated_pigs;
            $newQuantity = $currentQuantity + $quantity;

            // ตรวจสอบไม่ให้เกินจำนวนเริ่มต้น
            $originalQuantity = $allocation->allocated_pigs;
            if ($newQuantity > $originalQuantity) {
                return [
                    'success' => false,
                    'message' => " จำนวนหมูจะเกินจำนวนเริ่มต้น ({$originalQuantity} ตัว)",
                    'data' => null
                ];
            }

            $allocation->current_quantity = $newQuantity;
            $allocation->save();

            // 3. เพิ่มจำนวนหมูใน batches
            $batch = Batch::lockForUpdate()->find($batchId);

            if (!$batch) {
                throw new Exception('ไม่พบข้อมูลรุ่น');
            }

            $batchCurrentQuantity = $batch->current_quantity ?? $batch->total_pig_amount;
            $batch->current_quantity = $batchCurrentQuantity + $quantity;
            $batch->save();

            DB::commit();

            return [
                'success' => true,
                'message' => "✅ เพิ่มจำนวนหมูกลับเรียบร้อย ({$quantity} ตัว)",
                'data' => [
                    'quantity_added' => $quantity,
                    'pen_allocation' => [
                        'before' => $currentQuantity,
                        'after' => $newQuantity,
                        'remaining' => $newQuantity
                    ],
                    'batch' => [
                        'before' => $batchCurrentQuantity,
                        'after' => $batch->current_quantity,
                        'remaining' => $batch->current_quantity
                    ]
                ]
            ];
        } catch (Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => '❌ เกิดข้อผิดพลาด: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * ดึงข้อมูลหมูที่มีในเล้า-คอกทั้งหมดของรุ่นนั้นๆ
     *
     * @param int $batchId รหัสรุ่น
     * @return array
     */
    public static function getPigsByBatch($batchId)
    {
        $allocations = BatchPenAllocation::where('batch_id', $batchId)
            ->with(['pen.barn'])
            ->get();

        $pigs = [];
        $totalAvailable = 0;

        foreach ($allocations as $allocation) {
            $currentQuantity = $allocation->current_quantity ?? $allocation->allocated_pigs;
            $totalAvailable += $currentQuantity;

            if ($currentQuantity > 0) {
                $pigs[] = [
                    'allocation_id' => $allocation->id,
                    'pen_id' => $allocation->pen_id,
                    'pen_name' => $allocation->pen->pen_name ?? 'ไม่ระบุ',
                    'barn_name' => $allocation->pen->barn->barn_name ?? 'ไม่ระบุ',
                    'original_quantity' => $allocation->allocated_pigs,
                    'current_quantity' => $currentQuantity,
                    'available' => $currentQuantity,
                    'display_name' => sprintf(
                        '%s - %s (%d ตัว)',
                        $allocation->pen->barn->barn_name ?? 'ไม่ระบุ',
                        $allocation->pen->pen_name ?? 'ไม่ระบุ',
                        $currentQuantity
                    )
                ];
            }
        }

        return [
            'pigs' => $pigs,
            'total_available' => $totalAvailable,
            'total_pens' => count($pigs)
        ];
    }

    /**
     * ดึงจำนวนหมูที่มีในเล้า-คอกเฉพาะ
     *
     * @param int $batchId รหัสรุ่น
     * @param int $penId รหัสเล้า-คอก
     * @return int
     */
    public static function getAvailablePigs($batchId, $penId)
    {
        $allocation = BatchPenAllocation::where('batch_id', $batchId)
            ->where('pen_id', $penId)
            ->first();

        if (!$allocation) {
            return 0;
        }

        return $allocation->current_quantity ?? $allocation->allocated_pigs;
    }

    /**
     * ตรวจสอบว่ามีหมูเพียงพอสำหรับขายหรือไม่
     *
     * @param int $batchId รหัสรุ่น
     * @param int $penId รหัสเล้า-คอก
     * @param int $quantity จำนวนที่ต้องการ
     * @return array ['available' => bool, 'current' => int, 'requested' => int]
     */
    public static function checkPigAvailability($batchId, $penId, $quantity)
    {
        $currentQuantity = self::getAvailablePigs($batchId, $penId);

        return [
            'available' => $currentQuantity >= $quantity,
            'current' => $currentQuantity,
            'requested' => $quantity,
            'shortage' => max(0, $quantity - $currentQuantity)
        ];
    }

    /**
     * ดึงสรุปข้อมูลหมูทั้งหมดในรุ่น
     *
     * @param int $batchId รหัสรุ่น
     * @return array
     */
    public static function getBatchInventorySummary($batchId)
    {
        $batch = Batch::find($batchId);

        if (!$batch) {
            return null;
        }

        $allocations = BatchPenAllocation::where('batch_id', $batchId)
            ->with(['pen.barn'])
            ->get();

        $totalOriginal = 0;
        $totalCurrent = 0;
        $totalSold = 0;
        $penDetails = [];

        foreach ($allocations as $allocation) {
            $original = $allocation->allocated_pigs;
            $current = $allocation->current_quantity ?? $allocation->allocated_pigs;
            $sold = $original - $current;

            $totalOriginal += $original;
            $totalCurrent += $current;
            $totalSold += $sold;

            $penDetails[] = [
                'barn_name' => $allocation->pen->barn->barn_name ?? 'ไม่ระบุ',
                'pen_name' => $allocation->pen->pen_name ?? 'ไม่ระบุ',
                'original' => $original,
                'current' => $current,
                'sold' => $sold,
                'percentage_remaining' => $original > 0 ? round(($current / $original) * 100, 2) : 0
            ];
        }

        return [
            'batch_code' => $batch->batch_code,
            'batch_original' => $batch->total_pig_amount,
            'batch_current' => $batch->current_quantity ?? $batch->total_pig_amount,
            'total_original' => $totalOriginal,
            'total_current' => $totalCurrent,
            'total_sold' => $totalSold,
            'percentage_remaining' => $totalOriginal > 0 ? round(($totalCurrent / $totalOriginal) * 100, 2) : 0,
            'pen_details' => $penDetails
        ];
    }

    /**
     * ลบรุ่นและคืนค่า allocations ทั้งหมด (เมื่อลบรุ่น)
     *
     * @param int $batchId รหัสรุ่น
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public static function deleteBatchWithAllocations($batchId)
    {
        try {
            DB::beginTransaction();

            $batch = Batch::lockForUpdate()->find($batchId);

            if (!$batch) {
                return [
                    'success' => false,
                    'message' => '❌ ไม่พบรุ่นที่ต้องการลบ',
                    'data' => null
                ];
            }

            // ดึงข้อมูล allocations ทั้งหมดของรุ่นนี้
            $allocations = BatchPenAllocation::where('batch_id', $batchId)
                ->lockForUpdate()
                ->get();

            $deletedCount = 0;
            $totalAllocations = 0;

            foreach ($allocations as $allocation) {
                $totalAllocations++;
                // ลบ BatchPenAllocation records
                $allocation->delete();
                $deletedCount++;
            }

            // ลบ batch record
            $batch->delete();

            DB::commit();

            return [
                'success' => true,
                'message' => "✅ ลบรุ่นและคืนค่า allocations เรียบร้อย",
                'data' => [
                    'batch_id' => $batchId,
                    'batch_code' => $batch->batch_code,
                    'deleted_allocations' => $deletedCount,
                    'total_allocations' => $totalAllocations
                ]
            ];
        } catch (Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => '❌ เกิดข้อผิดพลาด: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
}
