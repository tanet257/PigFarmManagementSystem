<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profit;
use App\Models\Farm;
use App\Models\Batch;
use Illuminate\Support\Facades\Log;

class ProfitController extends Controller
{
    /**
     * แสดงรายการกำไรทั้งหมด
     */
    public function index(Request $request)
    {
        try {
            $query = Profit::with(['farm', 'batch', 'profitDetails']);

            // Filter by farm
            if ($request->has('farm_id') && $request->farm_id) {
                $query->where('farm_id', $request->farm_id);
            }

            // Filter by batch
            if ($request->has('batch_id') && $request->batch_id) {
                $query->where('batch_id', $request->batch_id);
            }

            // Filter by status
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Get all farms for filter dropdown
            $farms = Farm::all();

            // Get all batches for filter dropdown
            $batches = Batch::all();

            // Sort
            $sortBy = $request->get('sort_by', 'period_end');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Calculate totals
            $allProfits = $query->get();
            $totalRevenue = $allProfits->sum('total_revenue');
            $totalCost = $allProfits->sum('total_cost');
            $totalProfit = $allProfits->sum('gross_profit');
            $avgProfitMargin = $totalRevenue > 0 ? (($totalProfit / $totalRevenue) * 100) : 0;

            // Paginate
            $profits = $query->paginate(15);

            return view('profits.index', [
                'profits' => $profits,
                'farms' => $farms,
                'batches' => $batches,
                'totalRevenue' => $totalRevenue,
                'totalCost' => $totalCost,
                'totalProfit' => $totalProfit,
                'avgProfitMargin' => $avgProfitMargin,
            ]);
        } catch (\Exception $e) {
            Log::error('ProfitController - index Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * แสดงรายละเอียดกำไรของ batch เดียว
     */
    public function show($id)
    {
        try {
            $profit = Profit::with(['farm', 'batch', 'profitDetails.cost'])->findOrFail($id);

            return view('profits.show', [
                'profit' => $profit,
            ]);
        } catch (\Exception $e) {
            Log::error('ProfitController - show Error: ' . $e->getMessage());
            return redirect()->route('profits.index')->with('error', 'ไม่พบข้อมูลกำไร');
        }
    }

    /**
     * ส่งออกรายงานกำไรเป็น PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            $query = Profit::with(['farm', 'batch', 'profitDetails']);

            // Apply same filters as index
            if ($request->has('farm_id') && $request->farm_id) {
                $query->where('farm_id', $request->farm_id);
            }

            if ($request->has('batch_id') && $request->batch_id) {
                $query->where('batch_id', $request->batch_id);
            }

            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            $profits = $query->get();

            $totalRevenue = $profits->sum('total_revenue');
            $totalCost = $profits->sum('total_cost');
            $totalProfit = $profits->sum('gross_profit');

            return view('profits.pdf', [
                'profits' => $profits,
                'totalRevenue' => $totalRevenue,
                'totalCost' => $totalCost,
                'totalProfit' => $totalProfit,
            ]);
        } catch (\Exception $e) {
            Log::error('ProfitController - exportPdf Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'ไม่สามารถส่งออกรายงาน: ' . $e->getMessage());
        }
    }

    /**
     * ตรวจสอบและอัปเดทกำไรของ batch
     */
    public function recalculateBatchProfit($batchId)
    {
        try {
            $batch = Batch::findOrFail($batchId);

            // ใช้ RevenueHelper ในการคำนวณกำไรใหม่
            $result = \App\Helpers\RevenueHelper::calculateAndRecordProfit($batchId);

            if (!$result['success']) {
                return redirect()->back()->with('error', $result['message']);
            }

            return redirect()->back()->with('success', 'อัปเดทกำไรสำเร็จ');
        } catch (\Exception $e) {
            Log::error('ProfitController - recalculateBatchProfit Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * API: ดึงข้อมูลสรุปกำไรตามฟาร์ม
     */
    public function getFarmProfitSummary($farmId)
    {
        try {
            $farm = Farm::findOrFail($farmId);

            $profits = Profit::where('farm_id', $farmId)->get();

            $summary = [
                'farm_name' => $farm->name,
                'total_revenue' => $profits->sum('total_revenue'),
                'total_cost' => $profits->sum('total_cost'),
                'total_profit' => $profits->sum('gross_profit'),
                'avg_profit_margin' => $profits->isNotEmpty() ? ($profits->sum('gross_profit') / max($profits->sum('total_revenue'), 1)) * 100 : 0,
                'completed_batches' => $profits->where('status', 'completed')->count(),
                'incomplete_batches' => $profits->where('status', 'incomplete')->count(),
                'cost_breakdown' => [
                    'feed_cost' => $profits->sum('feed_cost'),
                    'medicine_cost' => $profits->sum('medicine_cost'),
                    'transport_cost' => $profits->sum('transport_cost'),
                    'labor_cost' => $profits->sum('labor_cost'),
                    'utility_cost' => $profits->sum('utility_cost'),
                    'other_cost' => $profits->sum('other_cost'),
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $summary,
            ]);
        } catch (\Exception $e) {
            Log::error('ProfitController - getFarmProfitSummary Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: ดึงข้อมูลสรุปกำไรตามรุ่น (batch)
     */
    public function getBatchProfitDetails($batchId)
    {
        try {
            $profit = Profit::with(['profitDetails'])->where('batch_id', $batchId)->first();

            if (!$profit) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบข้อมูลกำไรของรุ่นนี้',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $profit,
            ]);
        } catch (\Exception $e) {
            Log::error('ProfitController - getBatchProfitDetails Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
