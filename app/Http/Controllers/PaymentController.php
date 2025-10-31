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
        $result = \App\Services\PaymentService::recordSalePayment($request);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $result['message'],
                'data' => $result['data'] ?? null
            ], $result['success'] ? 200 : 422);
        }

        if (!$result['success']) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $result['message']);
        }

        // Show success toast notification
        return redirect()
            ->back()
            ->with('success', $result['message'])
            ->with('showToast', true);
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
