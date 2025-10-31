<?php

namespace App\Helpers;

use App\Models\Cost;
use App\Models\CostPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PaymentApprovalHelper
{
    /**
     * สร้าง Cost + CostPayment record สำหรับลูกหมู (piglet)
     * เมื่อบันทึกเข้าหมูใหม่ สถานะรอการอนุมัติ
     *
     * @param int $farmId รหัสฟาร์ม
     * @param int $batchId รหัสรุ่น
     * @param int $pigEntryRecordId รหัสบันทึกเข้าหมู (optional)
     * @param int $quantity จำนวนหมู
     * @param float $totalPrice ราคารวม
     * @param float $pricePerUnit ราคาต่อหน่วย
     * @param string $batchCode รหัสรุ่น (สำหรับเอกสาร)
     * @return array ['success' => bool, 'message' => string, 'cost_id' => int, 'cost_payment_id' => int]
     */
    public static function createPigletCostPaymentPending(
        $farmId,
        $batchId,
        $quantity,
        $totalPrice,
        $pricePerUnit,
        $batchCode,
        $pigEntryRecordId = null
    ) {
        try {
            DB::beginTransaction();

            // 1. สร้าง Cost record
            $cost = Cost::create([
                'farm_id' => $farmId,
                'batch_id' => $batchId,
                'pig_entry_record_id' => $pigEntryRecordId,
                'cost_type' => 'piglet',
                'item_code' => 'PIGLET-' . $batchCode,
                'item_name' => 'ลูกหมู - ' . $batchCode,
                'quantity' => $quantity,
                'unit' => 'ตัว',
                'price_per_unit' => $pricePerUnit,
                'total_price' => $totalPrice,
                'date' => now()->toDateString(),
                'note' => 'ค่าลูกหมู - บันทึกเข้าใหม่',
            ]);

            // 2. สร้าง CostPayment record (pending approval)
            $costPayment = CostPayment::create([
                'cost_id' => $cost->id,
                'amount' => $totalPrice,  // ตั้งเป็นจำนวนเงิน
                'status' => 'pending',  // รอการอนุมัติ
            ]);

            DB::commit();

            Log::info('Piglet cost payment created (pending)', [
                'cost_id' => $cost->id,
                'cost_payment_id' => $costPayment->id,
                'batch_id' => $batchId,
                'quantity' => $quantity,
                'total_price' => $totalPrice,
            ]);

            return [
                'success' => true,
                'message' => "✅ บันทึกต้นทุนลูกหมู {$quantity} ตัว รอการอนุมัติ",
                'cost_id' => $cost->id,
                'cost_payment_id' => $costPayment->id,
            ];
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error creating piglet cost payment: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => '❌ เกิดข้อผิดพลาด: ' . $e->getMessage(),
                'cost_id' => null,
                'cost_payment_id' => null,
            ];
        }
    }

    /**
     * ตรวจสอบว่า batch มีต้นทุนลูกหมูที่ได้รับการอนุมัติหรือไม่
     *
     * @param int $batchId รหัสรุ่น
     * @return bool
     */
    public static function hasPigletCostApproved($batchId)
    {
        try {
            $approved = Cost::where('costs.batch_id', $batchId)
                ->where('costs.cost_type', 'piglet')
                ->join('cost_payments', 'costs.id', '=', 'cost_payments.cost_id')
                ->where('cost_payments.status', 'approved')
                ->exists();

            return $approved;
        } catch (Exception $e) {
            Log::error('Error checking piglet cost approval: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ดึงต้นทุนลูกหมูทั้งหมดของ batch (เฉพาะ approved)
     *
     * @param int $batchId รหัสรุ่น
     * @return float ราคารวม
     */
    public static function getTotalApprovedPigletCost($batchId)
    {
        try {
            $total = Cost::where('costs.batch_id', $batchId)
                ->where('costs.cost_type', 'piglet')
                ->join('cost_payments', 'costs.id', '=', 'cost_payments.cost_id')
                ->where('cost_payments.status', 'approved')
                ->sum('costs.total_price');

            return $total ?? 0;
        } catch (Exception $e) {
            Log::error('Error getting piglet cost: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * ดึง CostPayment ทั้งหมดที่รอการอนุมัติ
     *
     * @param int|null $batchId (optional) ระบุ batch เฉพาะ
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getPendingCostPayments($batchId = null)
    {
        try {
            $query = CostPayment::where('status', 'pending')
                ->with(['cost'])
                ->orderByDesc('created_at');

            if ($batchId) {
                $query->whereHas('cost', function ($q) use ($batchId) {
                    $q->where('batch_id', $batchId);
                });
            }

            return $query->get();
        } catch (Exception $e) {
            Log::error('Error getting pending cost payments: ' . $e->getMessage());
            return collect();
        }
    }
}
