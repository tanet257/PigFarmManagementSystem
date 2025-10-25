<?php

namespace App\Http\Controllers;

use App\Models\CostPayment;
use App\Models\Cost;
use App\Helpers\RevenueHelper;
use App\Helpers\StoreHouseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CostPaymentApprovalController extends Controller
{
    /**
     * ดึงรายการต้นทุนที่รออนุมัติการชำระเงิน
     */
    public function index()
    {
        $pendingPayments = CostPayment::where('status', 'pending')
            ->with(['cost.batch', 'cost.pigEntryRecord'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $approvedPayments = CostPayment::where('status', 'approved')
            ->with(['cost.batch', 'cost.pigEntryRecord'])
            ->orderBy('approved_date', 'desc')
            ->limit(10)
            ->get();

        $rejectedPayments = CostPayment::where('status', 'rejected')
            ->with(['cost.batch', 'cost.pigEntryRecord'])
            ->orderBy('approved_date', 'desc')
            ->limit(10)
            ->get();

        return view('admin.cost_payment_approvals.index', compact('pendingPayments', 'approvedPayments', 'rejectedPayments'));
    }

    /**
     * ดูรายละเอียดการอนุมัติ
     */
    public function show($id)
    {
        $payment = CostPayment::findOrFail($id)
            ->load(['cost.batch', 'cost.pigEntryRecord', 'approver']);

        return view('admin.cost_payment_approvals.show', compact('payment'));
    }

    /**
     * อนุมัติการชำระเงิน
     */
    public function approve(Request $request, $id)
    {
        $validated = $request->validate([
            'note' => 'nullable|string|max:500',
        ]);

        try {
            $payment = CostPayment::findOrFail($id);

            if ($payment->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่สามารถอนุมัติการชำระเงินที่ได้อนุมัติแล้ว',
                ], 400);
            }

            // อัปเดท payment
            $payment->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_date' => now(),
                'reason' => $validated['note'] ?? null,
            ]);

            // ✅ Get cost for further processing (but don't update its payment_status)
            $cost = $payment->cost;

            // Auto-create StoreHouse record (inventory update)
            if (in_array($cost->cost_type, ['feed', 'medicine'])) {
                $storeResult = StoreHouseHelper::createFromCost($cost);
                Log::info('StoreHouse creation result', $storeResult);
            }

            // Recalculate profit
            if ($cost->batch_id) {
                $profitResult = RevenueHelper::calculateAndRecordProfit($cost->batch_id);
                Log::info('Profit calculation result', $profitResult);
            }

            // บันทึก notification
            \App\Models\Notification::create([
                'user_id' => Auth::id(),
                'type' => 'payment_approved',
                'related_type' => Cost::class,
                'related_id' => $cost->id,
                'title' => 'อนุมัติการชำระเงินค่า' . $cost->cost_type,
                'message' => "อนุมัติการชำระเงินจำนวน ฿" . number_format((float)$payment->amount, 2) . " สำหรับ " . $cost->cost_type,
                'is_read' => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'อนุมัติการชำระเงินสำเร็จ',
            ]);
        } catch (\Exception $e) {
            Log::error('CostPaymentApprovalController approve error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ปฏิเสธการชำระเงิน
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $payment = CostPayment::findOrFail($id);

            if ($payment->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่สามารถปฏิเสธการชำระเงินที่ได้อนุมัติแล้ว',
                ], 400);
            }

            // อัปเดท payment
            $payment->update([
                'status' => 'rejected',
                'rejected_by' => Auth::id(),
                'rejected_at' => now(),
                'reason' => $validated['reason'],
            ]);

            // บันทึก notification
            \App\Models\Notification::create([
                'user_id' => Auth::id(),
                'type' => 'payment_rejected',
                'related_type' => Cost::class,
                'related_id' => $payment->cost_id,
                'title' => 'ปฏิเสธการชำระเงินค่า' . $payment->cost->cost_type,
                'message' => "เหตุผล: " . $validated['reason'],
                'is_read' => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'ปฏิเสธการชำระเงินสำเร็จ',
            ]);
        } catch (\Exception $e) {
            Log::error('CostPaymentApprovalController reject error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
            ], 500);
        }
    }
}

