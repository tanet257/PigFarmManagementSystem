<?php

namespace App\Helpers;

use App\Models\Batch;
use App\Models\Cost;
use App\Models\CostPayment;
use App\Models\PigEntryRecord;
use App\Models\BatchPenAllocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BatchRestoreHelper
{
    /**
     * ลบ Batch แบบ soft delete
     * สามารถ restore ได้ภายหลัง
     *
     * @param int $batchId
     * @param string $reason เหตุผลการลบ
     * @return array ['success' => bool, 'message' => string]
     */
    public static function softDeleteBatch($batchId, $reason = 'User deletion')
    {
        try {
            DB::beginTransaction();

            $batch = Batch::findOrFail($batchId);

            // ตรวจสอบ status - ไม่ให้ลบ 'raising' หรือ 'selling' batches
            // อนุญาตให้ลบ 'closed' และ 'cancelled' batches เท่านั้น
            if (in_array($batch->status, ['raising', 'selling'])) {
                return [
                    'success' => false,
                    'message' => 'ไม่สามารถลบรุ่นที่อยู่ระหว่างเลี้ยงหรือขายได้ โปรดเปลี่ยนสถานะเป็น "ยกเลิก" ก่อน'
                ];
            }

            // ลบ costs ของ batch นี้ด้วย
            $costs = Cost::where('batch_id', $batchId)->get();
            foreach ($costs as $cost) {
                // ลบ cost payments ก่อน
                $cost->payments()->delete();
                // จากนั้นลบ cost
                $cost->delete();
            }

            // ทำการ soft delete batch
            $batch->delete();

            // ✅ Update notification status ที่เกี่ยวข้องกับรุ่นนี้
            // ค้นหา notification ที่เกี่ยวข้องและอัปเดตให้เป็น "batch_deleted"
            \App\Models\Notification::where('related_model', 'Batch')
                ->where('related_model_id', $batchId)
                ->update([
                    'type' => 'batch_deleted',
                    'title' => "รุ่น {$batch->batch_code} ถูกลบแล้ว",
                    'message' => "รุ่น {$batch->batch_code} ได้ถูกลบออกจากระบบ (สามารถกู้คืนได้)",
                    'is_read' => false,
                ]);

            Log::info("Batch soft deleted", [
                'batch_id' => $batchId,
                'batch_code' => $batch->batch_code,
                'status' => $batch->status,
                'costs_deleted' => $costs->count(),
                'reason' => $reason,
                'deleted_at' => now(),
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => "ลบรุ่น {$batch->batch_code} สำเร็จ (สามารถกู้คืนได้)"
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error deleting batch: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ];
        }
    }

    /**
     * กู้คืน Batch ที่ถูก soft delete
     * คืนค่า status และข้อมูลที่เกี่ยวข้องทั้งหมด
     *
     * @param int $batchId
     * @return array ['success' => bool, 'message' => string, 'batch' => Batch|null]
     */
    public static function restoreBatch($batchId)
    {
        try {
            DB::beginTransaction();

            $batch = Batch::withTrashed()->findOrFail($batchId);

            // ตรวจสอบว่า batch ถูก delete จริงหรือไม่
            if (!$batch->trashed()) {
                return [
                    'success' => false,
                    'message' => 'รุ่นนี้ไม่ได้ถูกลบ ไม่สามารถกู้คืนได้'
                ];
            }

            // ดึง costs ที่ soft deleted
            $costs = Cost::withTrashed()
                ->where('batch_id', $batchId)
                ->get();

            // ดึง cost payments ที่ soft deleted
            $costPayments = CostPayment::withTrashed()
                ->whereIn('cost_id', $costs->pluck('id'))
                ->get();

            // Restore batch
            $batch->restore();

            // Restore costs
            foreach ($costs as $cost) {
                if ($cost->trashed()) {
                    $cost->restore();
                }
            }

            // Restore cost payments
            foreach ($costPayments as $payment) {
                if ($payment->trashed()) {
                    $payment->restore();
                }
            }

            // ✅ Update notification status ที่เกี่ยวข้องกับรุ่นนี้
            // เปลี่ยน notification type จาก "batch_deleted" กลับเป็น "pig_entry_recorded"
            \App\Models\Notification::where('related_model', 'Batch')
                ->where('related_model_id', $batchId)
                ->where('type', 'batch_deleted')
                ->update([
                    'type' => 'pig_entry_recorded',
                    'title' => "รุ่น {$batch->batch_code} ได้ถูกกู้คืนแล้ว",
                    'message' => "รุ่น {$batch->batch_code} ได้ถูกกู้คืนจากการลบและกลับเข้ามาในระบบ",
                    'is_read' => false,
                ]);

            Log::info("Batch restored successfully", [
                'batch_id' => $batchId,
                'batch_code' => $batch->batch_code,
                'costs_restored' => $costs->count(),
                'payments_restored' => $costPayments->count(),
                'restored_at' => now(),
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => "กู้คืนรุ่น {$batch->batch_code} และข้อมูลที่เกี่ยวข้องทั้งหมดสำเร็จ",
                'batch' => $batch->fresh()
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error restoring batch: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
                'batch' => null
            ];
        }
    }

    /**
     * ดึง Batch ที่ถูก soft delete (archived batches)
     *
     * @param int|null $farmId - filter by farm (optional)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getDeletedBatches($farmId = null)
    {
        try {
            $query = Batch::onlyTrashed()
                ->with(['farm', 'batch_metric'])
                ->orderByDesc('deleted_at');

            if ($farmId) {
                $query->where('farm_id', $farmId);
            }

            return $query->get();

        } catch (\Exception $e) {
            Log::error("Error fetching deleted batches: " . $e->getMessage());
            return collect();
        }
    }

    /**
     * ตรวจสอบว่า batch ถูก cancelled (soft delete) หรือไม่
     *
     * @param int $batchId
     * @return bool
     */
    public static function isBatchDeleted($batchId)
    {
        $batch = Batch::withTrashed()->find($batchId);
        return $batch && $batch->trashed();
    }

    /**
     * ดึงข้อมูล Batch (รวม deleted) โดยไม่ filter soft delete
     * ใช้สำหรับ dashboard ที่ต้องการแสดงรุ่นทั้งหมด
     *
     * @param int $batchId
     * @return Batch|null
     */
    public static function getBatchIncludingDeleted($batchId)
    {
        return Batch::withTrashed()->find($batchId);
    }

    /**
     * ดึงสถิติ Batch รวมทั้ง deleted
     *
     * @param int $farmId
     * @return array
     */
    public static function getBatchStatistics($farmId)
    {
        try {
            $total = Batch::where('farm_id', $farmId)->count();
            $active = Batch::where('farm_id', $farmId)->whereNull('deleted_at')->count();
            $raising = Batch::where('farm_id', $farmId)->where('status', 'raising')->count();
            $selling = Batch::where('farm_id', $farmId)->where('status', 'selling')->count();
            $closed = Batch::where('farm_id', $farmId)->where('status', 'closed')->count();
            $deleted = Batch::where('farm_id', $farmId)->onlyTrashed()->count();

            return [
                'total' => $total,
                'active' => $active,
                'raising' => $raising,
                'selling' => $selling,
                'closed' => $closed,
                'deleted' => $deleted,
            ];
        } catch (\Exception $e) {
            Log::error("Error getting batch statistics: " . $e->getMessage());
            return [
                'total' => 0,
                'active' => 0,
                'raising' => 0,
                'selling' => 0,
                'closed' => 0,
                'deleted' => 0,
            ];
        }
    }
}
