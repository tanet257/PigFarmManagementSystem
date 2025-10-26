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
     * แสดง Dashboard (สรุปผลกำไร)
     */
    public function index(Request $request)
    {
        try {
            $query = Profit::with(['farm', 'batch', 'profitDetails']);

            // ✅ Exclude cancelled batches (soft delete)
            $query->whereHas('batch', function ($q) {
                $q->where('status', '!=', 'cancelled');
            });

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

            // Get all batches for filter dropdown (exclude cancelled)
            $batches = Batch::where('status', '!=', 'cancelled')->get();

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

            // ✅ NEW: Calculate cost breakdown (for pie chart)
            $feedCost = $allProfits->sum('feed_cost');
            $medicineCost = $allProfits->sum('medicine_cost');
            $transportCost = $allProfits->sum('transport_cost');
            $laborCost = $allProfits->sum('labor_cost');
            $utilityCost = $allProfits->sum('utility_cost');
            $otherCost = $allProfits->sum('other_cost');

            // Paginate
            $profits = $query->paginate(15);

            return view('admin.dashboard.index', [
                'profits' => $profits,
                'farms' => $farms,
                'batches' => $batches,
                'totalRevenue' => $totalRevenue,
                'totalCost' => $totalCost,
                'totalProfit' => $totalProfit,
                'avgProfitMargin' => $avgProfitMargin,
                // ✅ NEW: Cost breakdown data
                'feedCost' => $feedCost,
                'medicineCost' => $medicineCost,
                'transportCost' => $transportCost,
                'laborCost' => $laborCost,
                'utilityCost' => $utilityCost,
                'otherCost' => $otherCost,
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

            return view('admin.dashboard.show', [
                'profit' => $profit,
            ]);
        } catch (\Exception $e) {
            Log::error('ProfitController - show Error: ' . $e->getMessage());
            return redirect()->route('dashboard.index')->with('error', 'ไม่พบข้อมูลกำไร');
        }
    }

    /**
     * ส่งออกรายงานกำไรเป็น PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            $query = Profit::with(['farm', 'batch', 'profitDetails']);

            // ✅ Exclude cancelled batches (soft delete)
            $query->whereHas('batch', function ($q) {
                $q->where('status', '!=', 'cancelled');
            });

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

            return view('admin.dashboard.pdf', [
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

            // ✅ Exclude cancelled batches (soft delete)
            $profits = Profit::where('farm_id', $farmId)
                ->whereHas('batch', function ($q) {
                    $q->where('status', '!=', 'cancelled');
                })
                ->get();

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

    /**
     * ✅ NEW API: ดึง chart data สำหรับ AJAX refresh
     */
    public function getChartData(Request $request)
    {
        try {
            $query = Profit::query();

            // Exclude cancelled batches
            $query->whereHas('batch', function ($q) {
                $q->where('status', '!=', 'cancelled');
            });

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

            $allProfits = $query->get();

            // Calculate totals
            $totalRevenue = $allProfits->sum('total_revenue');
            $totalCost = $allProfits->sum('total_cost');
            $totalProfit = $allProfits->sum('gross_profit');
            $avgProfitMargin = $totalRevenue > 0 ? (($totalProfit / $totalRevenue) * 100) : 0;

            // Calculate cost breakdown
            $feedCost = $allProfits->sum('feed_cost');
            $medicineCost = $allProfits->sum('medicine_cost');
            $transportCost = $allProfits->sum('transport_cost');
            $laborCost = $allProfits->sum('labor_cost');
            $utilityCost = $allProfits->sum('utility_cost');
            $otherCost = $allProfits->sum('other_cost');

            return response()->json([
                'success' => true,
                'totalRevenue' => $totalRevenue,
                'totalCost' => $totalCost,
                'totalProfit' => $totalProfit,
                'avgProfitMargin' => $avgProfitMargin,
                'feedCost' => $feedCost,
                'medicineCost' => $medicineCost,
                'transportCost' => $transportCost,
                'laborCost' => $laborCost,
                'utilityCost' => $utilityCost,
                'otherCost' => $otherCost,
            ]);
        } catch (\Exception $e) {
            Log::error('ProfitController - getChartData Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ✅ NEW: ดึงข้อมูล Cost-Profit ต่อเดือนในปีนี้
     */
    public function getMonthlyCostProfitData(Request $request)
    {
        try {
            $currentYear = now()->year;
            $monthlyData = [];

            // Initialize all 12 months
            for ($month = 1; $month <= 12; $month++) {
                $monthlyData[$month] = [
                    'cost' => 0,
                    'profit' => 0,
                ];
            }

            // Query profits for current year
            $query = Profit::whereYear('period_end', $currentYear)
                ->whereHas('batch', function ($q) {
                    $q->where('status', '!=', 'cancelled');
                });

            // Apply filters
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

            // Group by month
            foreach ($profits as $profit) {
                $month = $profit->period_end ? $profit->period_end->month : now()->month;
                $monthlyData[$month]['cost'] += $profit->total_cost;
                $monthlyData[$month]['profit'] += $profit->gross_profit;
            }

            // Format for chart
            $months = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
                      'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];

            $costData = [];
            $profitData = [];

            for ($i = 1; $i <= 12; $i++) {
                $costData[] = $monthlyData[$i]['cost'];
                $profitData[] = $monthlyData[$i]['profit'];
            }

            return response()->json([
                'success' => true,
                'months' => $months,
                'cost' => $costData,
                'profit' => $profitData,
            ]);
        } catch (\Exception $e) {
            Log::error('ProfitController - getMonthlyCostProfitData Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ✅ NEW: ดึงข้อมูล FCG Performance ของทุก batch
     */
    public function getFcgPerformanceData(Request $request)
    {
        try {
            $query = Profit::with('batch')
                ->whereHas('batch', function ($q) {
                    $q->where('status', '!=', 'cancelled');
                });

            // Apply filters
            if ($request->has('farm_id') && $request->farm_id) {
                $query->where('farm_id', $request->farm_id);
            }

            if ($request->has('batch_id') && $request->batch_id) {
                $query->where('batch_id', $request->batch_id);
            }

            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            $profits = $query->orderBy('period_end', 'desc')->limit(12)->get();

            // Calculate FCG for each batch
            $batchCodes = [];
            $fcgValues = [];

            foreach ($profits as $profit) {
                $fcg = ($profit->total_weight_gained ?? 0) > 0
                    ? ($profit->feed_cost ?? 0) / $profit->total_weight_gained
                    : 0;

                $batchCodes[] = $profit->batch?->batch_code ?? 'Unknown';
                $fcgValues[] = round($fcg, 2);
            }

            // Reverse to show oldest first
            $batchCodes = array_reverse($batchCodes);
            $fcgValues = array_reverse($fcgValues);

            return response()->json([
                'success' => true,
                'batches' => $batchCodes,
                'fcg' => $fcgValues,
            ]);
        } catch (\Exception $e) {
            Log::error('ProfitController - getFcgPerformanceData Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
