<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\PigSale;
use App\Models\Revenue;
use App\Helpers\RevenueHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class PaymentController extends Controller
{

    /**
     * บันทึกการชำระเงิน
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
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

            // ตรวจสอบจำนวนเงิน
            $totalPaid = Payment::where('pig_sale_id', $pigSale->id)
                ->where('status', 'approved')
                ->sum('amount');

            $remainingAmount = $pigSale->net_total - $totalPaid;

            if ($validated['amount'] > $remainingAmount) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "จำนวนเงินเกินกว่ายอดคงค้างที่เหลือ ($remainingAmount บาท)");
            }

            // อัปโหลดไฟล์ receipt ถ้ามี
            $receiptPath = null;
            if ($request->hasFile('receipt_file')) {
                $file = $request->file('receipt_file');
                if ($file->isValid()) {
                    try {
                        $uploadResponse = Cloudinary::upload($file->getRealPath(), [
                            'folder' => 'payment-receipts',
                        ]);
                        $receiptPath = $uploadResponse['secure_url'] ?? null;
                    } catch (\Exception $e) {
                        Log::error('Cloudinary upload error: ' . $e->getMessage());
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'ไม่สามารถอัปโหลดไฟล์ได้');
                    }
                }
            }

            // สร้าง Payment record
            $payment = Payment::create([
                'pig_sale_id' => $validated['pig_sale_id'],
                'payment_number' => Payment::generatePaymentNumber(),
                'payment_date' => $validated['payment_date'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'],
                'bank_name' => $validated['bank_name'],
                'receipt_file' => $receiptPath,
                'note' => $validated['note'],
                'status' => 'pending',
                'recorded_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'บันทึกการชำระเงินสำเร็จ');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PaymentController - store Error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * อนุมัติการชำระเงิน (Admin)
     */
    public function approve($id)
    {
        DB::beginTransaction();
        try {
            $payment = Payment::findOrFail($id);

            // ตรวจสอบสถานะ
            if ($payment->status !== 'pending') {
                return redirect()->back()
                    ->with('error', 'สามารถอนุมัติได้เฉพาะการชำระที่รอการอนุมัติเท่านั้น');
            }

            // อนุมัติ
            $payment->update([
                'status' => 'approved',
                'approved_by' => auth()->user()->name,
                'approved_at' => now(),
            ]);

            // อัปเดท Revenue และ PigSale status
            $pigSale = $payment->pigSale;
            if ($pigSale) {
                $totalPaid = Payment::where('pig_sale_id', $pigSale->id)
                    ->where('status', 'approved')
                    ->sum('amount');

                if ($totalPaid >= $pigSale->net_total) {
                    // ชำระเต็มแล้ว
                    $pigSale->update([
                        'payment_status' => 'ชำระแล้ว',
                        'paid_amount' => $totalPaid,
                        'balance' => 0,
                    ]);

                    Revenue::where('pig_sale_id', $pigSale->id)->update([
                        'payment_status' => 'ชำระแล้ว',
                        'payment_received_date' => now(),
                    ]);
                } else {
                    // ชำระบางส่วน
                    $pigSale->update([
                        'payment_status' => 'ชำระบางส่วน',
                        'paid_amount' => $totalPaid,
                        'balance' => $pigSale->net_total - $totalPaid,
                    ]);
                }

                // 🔥 Recalculate profit (สำคัญที่สุด)
                $profitResult = RevenueHelper::calculateAndRecordProfit($pigSale->batch_id);
                if (!$profitResult['success']) {
                    Log::warning('Payment Approve - Profit recalculation failed: ' . $profitResult['message']);
                }
            }

            DB::commit();

            return redirect()->back()
                ->with('success', 'อนุมัติการชำระเงินสำเร็จ (Profit ปรับปรุงแล้ว)');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PaymentController - approve Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * ปฏิเสธการชำระเงิน (Admin)
     */
    public function reject(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'reject_reason' => 'required|string|max:500',
            ]);

            $payment = Payment::findOrFail($id);

            // ตรวจสอบสถานะ
            if ($payment->status !== 'pending') {
                return redirect()->back()
                    ->with('error', 'สามารถปฏิเสธได้เฉพาะการชำระที่รอการอนุมัติเท่านั้น');
            }

            // ปฏิเสธ
            $payment->update([
                'status' => 'rejected',
                'rejected_by' => auth()->user()->name,
                'rejected_at' => now(),
                'reject_reason' => $validated['reject_reason'],
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'ปฏิเสธการชำระเงินสำเร็จ');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PaymentController - reject Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }
}
