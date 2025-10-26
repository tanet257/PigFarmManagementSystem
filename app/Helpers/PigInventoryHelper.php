<?php

namespace App\Helpers;

use App\Models\Batch;
use App\Models\BatchPenAllocation;
use App\Models\PigEntryRecord;
use App\Models\PigSale;
use App\Models\Cost;
use App\Models\CostPayment;
use App\Models\Profit;
use App\Models\ProfitDetail;
use App\Models\Revenue;
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
     * ลดจำนวนหมู current_quantity เท่านั้น (ไม่ลด allocated_pigs)
     * ใช้เมื่อบันทึกการขายหมู - ลดจำนวนหมูคงเหลือแต่ไม่ลดจำนวนที่จัดสรรเริ่มต้น
     *
     * @param int $batchId รหัสรุ่น
     * @param int $penId รหัสเล้า-คอก
     * @param int $quantity จำนวนหมูที่ต้องการลด
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public static function reduceCurrentQuantityOnly($batchId, $penId, $quantity)
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

            // 3. ลดจำนวนหมู current_quantity เท่านั้น (ไม่ลด allocated_pigs)
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
                    'quantity_reduced' => $quantity,
                    'pen_allocation' => [
                        'before' => $currentQuantity,
                        'after' => $newQuantity,
                        'remaining' => $newQuantity,
                        'allocated_pigs' => $allocation->allocated_pigs  // ไม่เปลี่ยนแปลง
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
     * ลดจำนวนหมูจากเล้า-คอกและรุ่น (เมื่อมีการขาย/ตาย/คัดทิ้ง)
     *
     * @param int $batchId รหัสรุ่น
     * @param int $penId รหัสเล้า-คอก
     * @param int $quantity จำนวนหมูที่ต้องการลด
     * @param string $reason เหตุผล (sale, death, culling)
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public static function reducePigInventory($batchId, $penId, $quantity, $reason = 'sale', $shouldReduceAllocatedPigs = false)
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

            // ✅ เฉพาะกรณียกเลิก PigEntry ต้องลด allocated_pigs ด้วย (ต้องคืนค่าจำนวนหมูที่เข้าครั้งแรก)
            if ($shouldReduceAllocatedPigs) {
                $allocation->allocated_pigs = max($allocation->allocated_pigs - $quantity, 0);
            }
            // ❌ กรณีอื่น (dairy/sale): ไม่ลด allocated_pigs เพราะมันคือจำนวนหมูที่เข้าคอกครั้งแรก

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
                    'pen_name' => $allocation->pen->pen_code ?? 'ไม่ระบุ',
                    'barn_name' => $allocation->pen->barn->barn_code ?? 'ไม่ระบุ',
                    'original_quantity' => $allocation->allocated_pigs,
                    'current_quantity' => $currentQuantity,
                    'available' => $currentQuantity,
                    'is_dead' => false,  // ✅ NEW: หมูปกติ
                    'display_name' => sprintf(
                        '%s - %s (%d ตัว)',
                        $allocation->pen->barn->barn_code ?? 'ไม่ระบุ',
                        $allocation->pen->pen_code ?? 'ไม่ระบุ',
                        $currentQuantity
                    )
                ];
            }
        }

        // ✅ NEW: ดึงหมูที่ตายแล้ว และคำนวณจำนวนที่ยังไม่ได้ขาย (status = 'recorded')
        $pigDeaths = \App\Models\PigDeath::where('batch_id', $batchId)
            ->get()
            ->groupBy('pen_id');

        foreach ($pigDeaths as $penId => $deaths) {
            // ✅ NEW: คำนวณจำนวนหมูตายที่เหลือ = quantity - quantity_sold_total
            // quantity = จำนวนเดิม (ไม่เปลี่ยน)
            // quantity_sold_total = จำนวนที่ขายไปแล้ว (สะสม)
            // remaining = quantity - quantity_sold_total

            $deathQuantity = 0;
            foreach ($deaths as $death) {
                $remaining = ($death->quantity ?? 0) - ($death->quantity_sold_total ?? 0);
                $deathQuantity += max(0, $remaining);  // แสดงเฉพาะจำนวนบวก
            }

            $totalAvailable += $deathQuantity;

            // หาชื่อ barn/pen จาก pen_id
            $pen = \App\Models\Pen::with('barn')->find($penId);
            if ($pen && $deathQuantity > 0) {  // ✅ แสดงแค่ที่ยังมี available > 0
                $pigs[] = [
                    'allocation_id' => null, // ไม่ใช่ allocation เนื่องจากตายไปแล้ว
                    'pen_id' => $penId,
                    'pen_name' => $pen->pen_code ?? 'ไม่ระบุ',
                    'barn_name' => $pen->barn->barn_code ?? 'ไม่ระบุ',
                    'original_quantity' => 0,
                    'current_quantity' => 0,
                    'available' => $deathQuantity,  // ✅ จำนวนที่เหลือ = quantity - quantity_sold_total
                    'is_dead' => true, // ✅ FLAG: นี่คือหมูตาย
                    'display_name' => sprintf(
                        '%s - %s (หมูตาย %d ตัว)',
                        $pen->barn->barn_code ?? 'ไม่ระบุ',
                        $pen->pen_code ?? 'ไม่ระบุ',
                        $deathQuantity
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
     * ลบรุ่นโดยอัปเดทสถานะเป็น 'cancelled' (Soft Delete)
     * ทำตามแนวเดียวกับการอัปเดทสถานะเป็น 'เสร็จสิ้น'
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

            // เก็บข้อมูลเดิมก่อนอัปเดท
            $oldStatus = $batch->status;
            $oldAllocations = BatchPenAllocation::where('batch_id', $batchId)
                ->lockForUpdate()
                ->count();

            // 🔥 Soft Delete: อัปเดทสถานะเป็น 'cancelled' แทนการลบจริง ๆ
            $batch->status = 'cancelled';

            // ✅ Reset ค่าทั้งหมดของ batch เมื่อยกเลิก
            $batch->total_pig_amount = 0;
            $batch->current_quantity = 0;
            $batch->total_death = 0;

            $batch->save();

            // ✅ Delete batch pen allocations entirely (ลบ allocation rows ของ batch นี้)
            BatchPenAllocation::where('batch_id', $batchId)
                ->lockForUpdate()
                ->delete();

            // ✅ Cancel ทั้งหมดที่เกี่ยวกับ batch
            // 1. Cancel PigEntry ที่ยังไม่ cancelled
            \App\Models\PigEntryRecord::where('batch_id', $batchId)
                ->where('status', '!=', 'cancelled')
                ->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancelled_by' => 'System - Batch Cancelled',
                    'cancellation_reason' => 'Batch cancelled automatically',
                ]);

            // 2. Cancel PigSale ที่ยังไม่ cancelled
            \App\Models\PigSale::where('batch_id', $batchId)
                ->where('status', '!=', 'ยกเลิกการขาย')
                ->update([
                    'status' => 'ยกเลิกการขาย',
                ]);

            // ✅ 2.1 Cancel Payment approvals ที่เกี่ยวข้องกับ PigSale ของรุ่นนี้
            $pigSaleIds = \App\Models\PigSale::where('batch_id', $batchId)
                ->pluck('id')
                ->toArray();

            if (!empty($pigSaleIds)) {
                \App\Models\Payment::whereIn('pig_sale_id', $pigSaleIds)
                    ->where('status', '!=', 'rejected')  // ไม่ update ถ้าถูก reject แล้ว
                    ->update([
                        'status' => 'rejected',
                        'rejected_by' => 'System - Batch Cancelled',
                        'rejected_at' => now(),
                        'reject_reason' => 'Batch cancelled - Payment automatically rejected',
                    ]);
            }

            // 3. Cancel Cost ที่ยังไม่ cancelled - ลบ Cost records (หรือสามารถทำ soft delete ได้)
            // ข้อมูล Cost ถูกต้องแล้ว ไม่ต้องทำอะไรพิเศษ (เพราะ Profit/Revenue จะถูกลบแล้ว)

            // ✅ 3.1 Cancel CostPayment approvals (Payment Approvals สำหรับค่าใช้จ่าย)
            $costIds = \App\Models\Cost::where('batch_id', $batchId)
                ->pluck('id')
                ->toArray();

            if (!empty($costIds)) {
                // ✅ Get system user or use null (ถ้า system user ไม่มี)
                $systemUserId = \App\Models\User::where('name', 'System')->value('id');

                CostPayment::whereIn('cost_id', $costIds)
                    ->where('status', '!=', 'rejected')  // ไม่ update ถ้าถูก reject แล้ว
                    ->update([
                        'status' => 'rejected',
                        'rejected_at' => now(),
                        'rejected_by' => $systemUserId,  // ✅ ใช้ user ID แทน string
                    ]);
            }

            // 4. Delete/Clear Profit records (includes related ProfitDetail via cascade)
            $profitIds = \App\Models\Profit::where('batch_id', $batchId)->pluck('id')->toArray();
            \App\Models\ProfitDetail::whereIn('profit_id', $profitIds)->delete();
            \App\Models\Profit::where('batch_id', $batchId)->delete();

            // 5. Delete Revenue records
            \App\Models\Revenue::where('batch_id', $batchId)->delete();

            // อัปเดตการแจ้งเตือนที่เกี่ยวข้องกับรุ่นนี้ (ไม่ลบ แต่เพิ่ม prefix)
            self::markBatchAndRelatedNotificationsAsCancelled($batchId);

            DB::commit();

            return [
                'success' => true,
                'message' => "✅ ยกเลิกรุ่นเรียบร้อย (Status: cancelled)",
                'data' => [
                    'batch_id' => $batchId,
                    'batch_code' => $batch->batch_code,
                    'old_status' => $oldStatus,
                    'new_status' => 'cancelled',
                    'allocations_reset' => $oldAllocations
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
     * อัปเดตการแจ้งเตือนที่เกี่ยวข้องกับรุ่นนี้ให้เป็น "[ยกเลิกแล้ว]"
     * (ไม่ลบ แต่เพิ่ม prefix ให้สอดคล้องกับ PigEntry/PigSale)
     */
    private static function markBatchAndRelatedNotificationsAsCancelled($batchId)
    {
        try {
            // อัปเดต Batch notifications
            \App\Helpers\NotificationHelper::markBatchNotificationsAsCancelled($batchId);

            // อัปเดต PigEntry notifications ของรุ่นนี้
            $pigEntryIds = \App\Models\PigEntryRecord::where('batch_id', $batchId)
                ->pluck('id')
                ->toArray();

            if (!empty($pigEntryIds)) {
                $pigEntryNotifications = \App\Models\Notification::where('related_model', 'PigEntryRecord')
                    ->whereIn('related_model_id', $pigEntryIds)
                    ->get();

                foreach ($pigEntryNotifications as $notification) {
                    if (!str_contains($notification->title, '[ลบแล้ว]')) {
                        $notification->update([
                            'title' => '[ลบแล้ว] ' . $notification->title,
                        ]);
                    }
                }
            }

            // อัปเดต PigSale notifications ของรุ่นนี้
            $pigSaleIds = \App\Models\PigSale::where('batch_id', $batchId)
                ->pluck('id')
                ->toArray();

            if (!empty($pigSaleIds)) {
                $pigSaleNotifications = \App\Models\Notification::where('related_model', 'PigSale')
                    ->whereIn('related_model_id', $pigSaleIds)
                    ->get();

                foreach ($pigSaleNotifications as $notification) {
                    if (!str_contains($notification->title, '[ยกเลิกแล้ว]')) {
                        $notification->update([
                            'title' => '[ยกเลิกแล้ว] ' . $notification->title,
                        ]);
                    }
                }

                // ✅ อัปเดต Payment Approval notifications สำหรับการขาย
                $paymentIds = \App\Models\Payment::whereIn('pig_sale_id', $pigSaleIds)
                    ->pluck('id')
                    ->toArray();

                if (!empty($paymentIds)) {
                    $paymentNotifications = \App\Models\Notification::where('related_model', 'Payment')
                        ->whereIn('related_model_id', $paymentIds)
                        ->get();

                    foreach ($paymentNotifications as $notification) {
                        if (!str_contains($notification->title, '[ยกเลิกแล้ว]')) {
                            $notification->update([
                                'title' => '[ยกเลิกแล้ว] ' . $notification->title,
                            ]);
                        }
                    }
                }
            }

            // ✅ อัปเดต Cost/CostPayment Approval notifications ของรุ่นนี้
            $costIds = \App\Models\Cost::where('batch_id', $batchId)
                ->pluck('id')
                ->toArray();

            if (!empty($costIds)) {
                // อัปเดต Cost notifications
                $costNotifications = \App\Models\Notification::where('related_model', 'Cost')
                    ->whereIn('related_model_id', $costIds)
                    ->get();

                foreach ($costNotifications as $notification) {
                    if (!str_contains($notification->title, '[ยกเลิกแล้ว]')) {
                        $notification->update([
                            'title' => '[ยกเลิกแล้ว] ' . $notification->title,
                        ]);
                    }
                }

                // อัปเดต CostPayment Approval notifications
                $costPaymentIds = CostPayment::whereIn('cost_id', $costIds)
                    ->pluck('id')
                    ->toArray();

                if (!empty($costPaymentIds)) {
                    $costPaymentNotifications = \App\Models\Notification::where('related_model', 'CostPayment')
                        ->whereIn('related_model_id', $costPaymentIds)
                        ->get();

                    foreach ($costPaymentNotifications as $notification) {
                        if (!str_contains($notification->title, '[ยกเลิกแล้ว]')) {
                            $notification->update([
                                'title' => '[ยกเลิกแล้ว] ' . $notification->title,
                            ]);
                        }
                    }
                }
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Error marking related notifications as cancelled: ' . $e->getMessage());
            // ไม่ throw error เพื่อให้ batch deletion ยังคงดำเนินต่อ
        }
    }

    /**
     * ลบการแจ้งเตือนที่เกี่ยวข้องกับรุ่นนี้
     * (DEPRECATED - ใช้ markBatchAndRelatedNotificationsAsCancelled แทน)
     */
    private static function deleteRelatedNotifications($batchId)
    {
        try {
            $notificationModel = \App\Models\Notification::class;

            // ลบ notification ของ pig entry ของรุ่นนี้
            $pigEntryIds = \App\Models\PigEntryRecord::where('batch_id', $batchId)
                ->pluck('id')
                ->toArray();

            if (!empty($pigEntryIds)) {
                \App\Models\Notification::where('related_model', 'PigEntryRecord')
                    ->whereIn('related_model_id', $pigEntryIds)
                    ->delete();
            }

            // ลบ notification ของ pig sale ของรุ่นนี้
            $pigSaleIds = \App\Models\PigSale::where('batch_id', $batchId)
                ->pluck('id')
                ->toArray();

            if (!empty($pigSaleIds)) {
                \App\Models\Notification::where('related_model', 'PigSale')
                    ->whereIn('related_model_id', $pigSaleIds)
                    ->delete();
            }

            // ลบ notification ที่มี batch_id ในข้อมูล data (approval notifications)
            \App\Models\Notification::where('type', 'like', '%approval%')
                ->where('related_model', 'Batch')
                ->where('related_model_id', $batchId)
                ->delete();

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Error deleting related notifications: ' . $e->getMessage());
            // ไม่ throw error เพื่อให้ batch deletion ยังคงดำเนินต่อ
        }
    }
}
