<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\BatchPenAllocation;
use App\Models\Pen;
use App\Models\Barn;
use Illuminate\Support\Facades\DB;

/**
 * ========================================================================
 * BarnPenSelectionService
 * ========================================================================
 *
 * Service นี้ใช้สำหรับดึงข้อมูลเล้า-คอกเพื่อใช้ในการสร้าง checkbox tables
 * สำหรับการเลือกเล้า-คอกในหลาย ๆ หน้า เช่น:
 * - Treatments: เลือกเล้า-คอกสำหรับการรักษา
 * - Dairy Records: เลือกเล้า-คอกสำหรับบันทึกนม
 * - Pig Sales: เลือกเล้า-คอกสำหรับการขายหมู
 *
 * ข้อดี: Centralized logic, consistent format, reusable across all pages
 *
 * ========================================================================
 */
class BarnPenSelectionService
{
    /**
     * ดึงรายการเล้า-คอกสำหรับรุ่นที่ระบุ พร้อมข้อมูลจำนวนหมู
     *
     * @param int $farmId รหัสฟาร์ม
     * @param int $batchId รหัสรุ่น
     * @param bool $includeEmpty รวมคอกที่ว่าง (default: false)
     * @return array ['success' => bool, 'data' => array, 'message' => string]
     *
     * FORMAT RESPONSE:
     * [
     *   'success' => true,
     *   'data' => [
     *     [
     *       'id' => 123,
     *       'barn_id' => 1,
     *       'barn_code' => 'เล้า A',
     *       'pen_id' => 5,
     *       'pen_number' => '01',
     *       'current_pig_count' => 50,
     *       'display_name' => 'เล้า A - คอก 01 (50 ตัว)'
     *     ],
     *     ...
     *   ],
     *   'message' => 'พบ 5 คอก'
     * ]
     */
    public static function getPensByFarmAndBatch($farmId, $batchId, $includeEmpty = false)
    {
        try {
            console_log('📋 [BarnPenSelectionService] Getting pens for farm: ' . $farmId . ', batch: ' . $batchId);

            // Validate inputs
            if (!$farmId || !$batchId) {
                console_log('⚠️ [BarnPenSelectionService] Missing farm_id or batch_id');
                return [
                    'success' => false,
                    'data' => [],
                    'message' => 'ต้องระบุ farm_id และ batch_id'
                ];
            }

            // ✅ STEP 1: Verify farm and batch exist
            $batch = Batch::find($batchId);
            if (!$batch || $batch->farm_id != $farmId) {
                console_log('⚠️ [BarnPenSelectionService] Batch not found or farm mismatch');
                return [
                    'success' => false,
                    'data' => [],
                    'message' => 'ไม่พบรุ่นนี้ในฟาร์มที่เลือก'
                ];
            }

            // ✅ STEP 2: Get barn-pen allocations with pig counts
            $allocations = BatchPenAllocation::where('batch_id', $batchId)
                ->with(['pen.barn'])
                ->get();

            console_log('✅ [BarnPenSelectionService] Found ' . $allocations->count() . ' allocations');

            // ✅ STEP 3: Format data for frontend table
            $pens = [];
            foreach ($allocations as $allocation) {
                $currentCount = $allocation->current_quantity ?? $allocation->allocated_pigs ?? 0;

                // Skip empty pens if includeEmpty is false
                if ($currentCount == 0 && !$includeEmpty) {
                    console_log('⏭️ [BarnPenSelectionService] Skipping empty pen: ' . $allocation->pen->pen_code);
                    continue;
                }

                $pens[] = [
                    'id' => $allocation->pen->id,  // ✅ FIXED: Use pen->id not allocation->id
                    'barn_id' => $allocation->pen->barn_id,
                    'barn_code' => $allocation->pen->barn->barn_code ?? 'N/A',
                    'pen_id' => $allocation->pen->id,
                    'pen_number' => $allocation->pen->pen_code ?? 'N/A',
                    'current_pig_count' => $currentCount,
                    'display_name' => sprintf(
                        '%s - %s (%d ตัว)',
                        $allocation->pen->barn->barn_code ?? 'N/A',
                        $allocation->pen->pen_code ?? 'N/A',
                        $currentCount
                    )
                ];
            }

            console_log('✅ [BarnPenSelectionService] Formatted ' . count($pens) . ' pens for display');

            if (empty($pens)) {
                console_log('⚠️ [BarnPenSelectionService] No pens found');
                return [
                    'success' => false,
                    'data' => [],
                    'message' => 'ไม่พบเล้า-คอกสำหรับรุ่นนี้'
                ];
            }

            return [
                'success' => true,
                'data' => $pens,
                'message' => 'พบ ' . count($pens) . ' คอก'
            ];

        } catch (\Exception $e) {
            console_log('❌ [BarnPenSelectionService] Error: ' . $e->getMessage());
            return [
                'success' => false,
                'data' => [],
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ดึงรายการเล้าของฟาร์มที่ระบุ (ไม่จำเป็นต้องเลือกรุ่น)
     *
     * @param int $farmId รหัสฟาร์ม
     * @return array ['success' => bool, 'data' => array, 'message' => string]
     */
    public static function getBarnsByFarm($farmId)
    {
        try {
            console_log(' [BarnPenSelectionService] Getting barns for farm: ' . $farmId);

            $barns = Barn::where('farm_id', $farmId)
                ->with('pens')
                ->get();

            console_log('✅ [BarnPenSelectionService] Found ' . $barns->count() . ' barns');

            $formatted = $barns->map(function ($barn) {
                return [
                    'id' => $barn->id,
                    'barn_code' => $barn->barn_code,
                    'barn_name' => $barn->barn_name,
                    'pen_count' => $barn->pens->count(),
                ];
            });

            return [
                'success' => true,
                'data' => $formatted,
                'message' => 'พบ ' . $formatted->count() . ' เล้า'
            ];

        } catch (\Exception $e) {
            console_log('❌ [BarnPenSelectionService] Error: ' . $e->getMessage());
            return [
                'success' => false,
                'data' => [],
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ];
        }
    }
}

/**
 * ========================================================================
 * Helper function: console_log
 * ========================================================================
 * ใช้สำหรับ debug ในโค้ด PHP โดยจะแสดง log ไปยัง Laravel logs
 * สามารถดูได้ใน: storage/logs/laravel.log
 *
 * @param string $message ข้อความที่ต้องการ log
 * @param string $level log level (debug, info, warning, error)
 */
if (!function_exists('console_log')) {
    function console_log($message, $level = 'debug')
    {
        \Illuminate\Support\Facades\Log::$level($message);
    }
}
