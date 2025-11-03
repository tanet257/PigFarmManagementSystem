<?php

namespace App\Services;

use App\Models\Cost;
use App\Models\CostPayment;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\PigSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * บันทึกการชำระเงินสำหรับ Cost (Batch/Piglet)
     */
    public static function recordCostPayment(Request $request, $batchId)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'cost_type' => 'required|string|in:batch',
                'batch_id' => 'required|exists:batches,id',
                'amount' => 'required|numeric|min:0.01',
                'action_type' => 'required|string|in:เงินสด,โอนเงิน,เช็ค',
                'receipt_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'reason' => 'nullable|string|max:1000',
            ]);

            $batch = \App\Models\Batch::findOrFail($batchId);

            // Upload receipt file
            $uploadResult = UploadService::uploadToCloudinary($request->file('receipt_file'));
            if (!$uploadResult['success']) {
                return [
                    'success' => false,
                    'message' => 'ไม่สามารถอัปโหลดไฟล์สลิปได้: ' . ($uploadResult['error'] ?? 'กรุณาลองใหม่')
                ];
            }

            // Get latest pig entry record
            $pigEntryRecord = $batch->pig_entry_records()->latest()->first();

            DB::beginTransaction();

            // ✅ หา Cost ที่มีอยู่เดิมสำหรับ batch นี้ - ต้องไม่ duplicate!
            $cost = Cost::where('batch_id', $batch->id)
                ->where('cost_type', 'piglet')
                ->first();

            if (!$cost) {
                // สร้าง Cost record ใหม่ถ้ายังไม่มี
                // CostObserver จะสร้าง CostPayment pending อัตโนมัติ
                $cost = Cost::create([
                    'farm_id' => $batch->farm_id,
                    'batch_id' => $batch->id,
                    'pig_entry_record_id' => $pigEntryRecord->id,
                    'cost_type' => 'piglet',
                    'item_code' => 'PIGLET-' . $batch->batch_code,
                    'quantity' => $pigEntryRecord->total_pig_amount,
                    'unit' => 'ตัว',
                    'price_per_unit' => $pigEntryRecord->average_price_per_pig,
                    'amount' => $validated['amount'],
                    'total_price' => $pigEntryRecord->total_pig_price,
                    'receipt_file' => $uploadResult['url'],
                    'note' => $validated['reason'] ?? null,
                    'date' => now(),
                ]);
            } else {
                // Update Cost ที่มีอยู่เดิม
                $cost->update([
                    'amount' => $validated['amount'],
                    'receipt_file' => $uploadResult['url'],
                    'note' => $validated['reason'] ?? null,
                    'date' => now(),
                ]);
            }

            // ✅ หา CostPayment ที่มีอยู่เดิม
            $payment = $cost->payments()->first();

            if (!$payment) {
                // ถ้าไม่มี ให้สร้างใหม่
                $payment = CostPayment::create([
                    'cost_id' => $cost->id,
                    'amount' => $validated['amount'],
                    'payment_method' => $validated['action_type'],
                    'status' => 'pending',
                    'payment_date' => now(),
                    'recorded_by' => auth()->id(),
                ]);
            } else {
                // Update payment method และ recorded_by (แต่ไม่เปลี่ยน amount เพราะอยู่ใน cost แล้ว)
                $payment->update([
                    'amount' => $validated['amount'],
                    'payment_method' => $validated['action_type'],
                    'recorded_by' => auth()->id(),
                    'payment_date' => now(),
                ]);
            }

            // Create notification using NotificationHelper
            \App\Helpers\NotificationHelper::notifyAdminsPaymentChannelRecorded($payment, auth()->user());

            DB::commit();

            return [
                'success' => true,
                'message' => 'บันทึกการชำระเงินสำเร็จ รอการอนุมัติ',
                'data' => [
                    'cost' => $cost,
                    'payment' => $payment
                ]
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in recordCostPayment: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ];
        }
    }

    /**
     * บันทึกการชำระเงินสำหรับ PigSale
     */
    public static function recordSalePayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'pig_sale_id' => 'required|exists:pig_sales,id',
                'amount' => 'required|numeric|min:0.01',
                'payment_method' => 'required|in:เงินสด,โอนเงิน,เช็ค',
                'payment_date' => 'required|date',
                'reference_number' => 'nullable|string|max:100',
                'bank_name' => 'nullable|string|max:100',
                'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'note' => 'nullable|string',
            ]);

            $pigSale = PigSale::findOrFail($validated['pig_sale_id']);

            // Check remaining amount
            $totalPaid = Payment::where('pig_sale_id', $pigSale->id)
                ->where('status', 'approved')
                ->sum('amount');

            $remainingAmount = $pigSale->net_total - $totalPaid;

            if ($validated['amount'] > $remainingAmount) {
                return [
                    'success' => false,
                    'message' => "จำนวนเงินเกินกว่ายอดคงค้างที่เหลือ ($remainingAmount บาท)"
                ];
            }

            // Upload receipt file
            $uploadResult = UploadService::uploadToCloudinary($request->file('receipt_file'));
            if (!$uploadResult['success']) {
                return [
                    'success' => false,
                    'message' => 'ไม่สามารถอัปโหลดไฟล์สลิปได้: ' . ($uploadResult['error'] ?? 'กรุณาลองใหม่')
                ];
            }

            DB::beginTransaction();

            // Create Payment record
            $payment = Payment::create([
                'pig_sale_id' => $validated['pig_sale_id'],
                'payment_number' => Payment::generatePaymentNumber(),
                'payment_date' => $validated['payment_date'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'],
                'bank_name' => $validated['bank_name'],
                'receipt_file' => $uploadResult['url'],
                'note' => $validated['note'],
                'status' => 'pending',
                'recorded_by' => auth()->id(),
            ]);

            // Create notification using NotificationHelper
            \App\Helpers\NotificationHelper::notifyAdminsPaymentChannelRecorded($payment, auth()->user());

            DB::commit();

            return [
                'success' => true,
                'message' => 'บันทึกการชำระเงินสำเร็จ',
                'data' => $payment
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in recordSalePayment: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ];
        }
    }
}
