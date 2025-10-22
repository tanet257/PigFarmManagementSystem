<?php

namespace App\Helpers;

use App\Models\Revenue;
use App\Models\Profit;
use App\Models\ProfitDetail;
use App\Models\Cost;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RevenueHelper
{
    /**
     * บันทึกรายได้จากการขายหมู
     *
     * @param $pigSale - PigSale object
     * @return array ['success' => bool, 'message' => string, 'revenue' => Revenue object]
     */
    public static function recordPigSaleRevenue($pigSale)
    {
        DB::beginTransaction();
        try {
            // ตรวจสอบว่าเคยบันทึกรายได้นี้แล้วหรือไม่
            $existingRevenue = Revenue::where('pig_sale_id', $pigSale->id)->first();

            if ($existingRevenue) {
                // หากเคยบันทึกแล้ว ให้อัปเดท
                $existingRevenue->update([
                    'total_revenue' => $pigSale->total_price,
                    'net_revenue' => $pigSale->net_total,
                    'payment_status' => $pigSale->payment_status,
                    'revenue_date' => $pigSale->date,
                    'payment_received_date' => $pigSale->payment_status === 'ชำระแล้ว' ? now() : null,
                    'note' => 'ขายหมู ' . $pigSale->quantity . ' ตัว ให้ ' . $pigSale->buyer_name,
                ]);

                DB::commit();
                return [
                    'success' => true,
                    'message' => 'อัปเดทรายได้สำเร็จ',
                    'revenue' => $existingRevenue,
                ];
            }

            // สร้างรายได้ใหม่
            $revenue = Revenue::create([
                'farm_id' => $pigSale->farm_id,
                'batch_id' => $pigSale->batch_id,
                'pig_sale_id' => $pigSale->id,
                'revenue_type' => 'pig_sale',
                'quantity' => $pigSale->quantity,
                'unit_price' => $pigSale->price_per_pig,
                'total_revenue' => $pigSale->total_price,
                'discount' => 0,
                'net_revenue' => $pigSale->net_total,
                'payment_status' => $pigSale->payment_status,
                'revenue_date' => $pigSale->date,
                'payment_received_date' => $pigSale->payment_status === 'ชำระแล้ว' ? now() : null,
                'note' => 'ขายหมู ' . $pigSale->quantity . ' ตัว ให้ ' . $pigSale->buyer_name,
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'บันทึกรายได้สำเร็จ',
                'revenue' => $revenue,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('RevenueHelper - recordPigSaleRevenue Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'ไม่สามารถบันทึกรายได้ได้: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * คำนวณกำไรและบันทึกลง Profit table
     *
     * @param $batchId - Batch ID
     * @return array ['success' => bool, 'message' => string, 'profit' => Profit object]
     */
    public static function calculateAndRecordProfit($batchId)
    {
        DB::beginTransaction();
        try {
            $batch = \App\Models\Batch::findOrFail($batchId);

            // ดึงรายได้ทั้งหมดของรุ่นนี้ (เฉพาะที่ชำระแล้ว)
            $totalRevenue = Revenue::where('batch_id', $batchId)
                ->where('payment_status', 'ชำระแล้ว')
                ->sum('net_revenue');

            // ดึงต้นทุนทั้งหมด (เฉพาะที่ได้อนุมัติการชำระเงินแล้ว)
            $approvedCosts = Cost::where('batch_id', $batchId)
                ->whereHas('payments', function ($query) {
                    $query->where('status', 'approved');
                })
                ->get();

            // คำนวณต้นทุนตามหมวดหมู่ (เฉพาะ approved payments)
            $feedCost = $approvedCosts->where('cost_type', 'feed')->sum('total_price');
            $medicineCost = $approvedCosts->where('cost_type', 'medicine')->sum('total_price');
            $transportCost = $approvedCosts->where('cost_type', 'shipping')->sum('transport_cost');
            $laborCost = $approvedCosts->where('cost_type', 'wage')->sum('total_price');
            $utilityCost = $approvedCosts->whereIn('cost_type', ['electric_bill', 'water_bill'])->sum('total_price');
            $otherCost = $approvedCosts->where('cost_type', 'other')->sum('total_price');
            $pigletCost = $approvedCosts->where('cost_type', 'piglet')->sum('total_price');

            $totalCost = $feedCost + $medicineCost + $transportCost + $laborCost + $utilityCost + $otherCost + $pigletCost;

            // คำนวณกำไร
            $grossProfit = $totalRevenue - $totalCost;
            $profitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue * 100) : 0;

            // ดึงจำนวนหมู (ไม่รวม cancelled)
            $totalPigSold = \App\Models\PigSale::where('batch_id', $batchId)
                ->where('status', '!=', 'ยกเลิกการขาย')
                ->sum('quantity');

            $totalPigDead = \App\Models\PigDeath::where('batch_id', $batchId)->count();
            $profitPerPig = $totalPigSold > 0 ? ($grossProfit / $totalPigSold) : 0;

            // ตรวจสอบว่ามี Profit record แล้วหรือไม่
            $profit = Profit::where('batch_id', $batchId)->first();

            if ($profit) {
                // อัปเดท
                $profit->update([
                    'total_revenue' => $totalRevenue,
                    'total_cost' => $totalCost,
                    'gross_profit' => $grossProfit,
                    'profit_margin_percent' => $profitMargin,
                    'feed_cost' => $feedCost,
                    'medicine_cost' => $medicineCost,
                    'transport_cost' => $transportCost,
                    'labor_cost' => $laborCost,
                    'utility_cost' => $utilityCost,
                    'other_cost' => $otherCost,
                    'total_pig_sold' => $totalPigSold,
                    'total_pig_dead' => $totalPigDead,
                    'profit_per_pig' => $profitPerPig,
                    'period_start' => $batch->created_at,
                    'period_end' => now(),
                ]);
            } else {
                // สร้างใหม่
                $profit = Profit::create([
                    'farm_id' => $batch->farm_id,
                    'batch_id' => $batchId,
                    'total_revenue' => $totalRevenue,
                    'total_cost' => $totalCost,
                    'gross_profit' => $grossProfit,
                    'profit_margin_percent' => $profitMargin,
                    'feed_cost' => $feedCost,
                    'medicine_cost' => $medicineCost,
                    'transport_cost' => $transportCost,
                    'labor_cost' => $laborCost,
                    'utility_cost' => $utilityCost,
                    'other_cost' => $otherCost,
                    'total_pig_sold' => $totalPigSold,
                    'total_pig_dead' => $totalPigDead,
                    'profit_per_pig' => $profitPerPig,
                    'period_start' => $batch->created_at,
                    'period_end' => now(),
                    'days_in_farm' => now()->diffInDays($batch->created_at),
                    'status' => 'incomplete',
                ]);
            }

            // บันทึก profit details
            self::recordProfitDetails($profit, $approvedCosts);

            DB::commit();

            return [
                'success' => true,
                'message' => 'คำนวณกำไรสำเร็จ',
                'profit' => $profit,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('RevenueHelper - calculateAndRecordProfit Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'ไม่สามารถคำนวณกำไรได้: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * บันทึก profit details จากต้นทุน
     */
    private static function recordProfitDetails($profit, $allCosts)
    {
        try {
            // ลบ profit details เดิมก่อน
            ProfitDetail::where('profit_id', $profit->id)->delete();

            // บันทึก profit details ใหม่
            foreach ($allCosts as $cost) {
                ProfitDetail::create([
                    'profit_id' => $profit->id,
                    'cost_id' => $cost->id,
                    'cost_category' => $cost->cost_type,
                    'item_name' => $cost->item_code ?? 'ต้นทุน - ' . $cost->cost_type,
                    'amount' => $cost->total_price,
                    'note' => $cost->note,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('RevenueHelper - recordProfitDetails Error: ' . $e->getMessage());
        }
    }

    /**
     * ดึงสรุป Revenue และ Profit ของ Batch
     */
    public static function getBatchFinancialSummary($batchId)
    {
        try {
            $batch = \App\Models\Batch::findOrFail($batchId);

            $revenue = Revenue::where('batch_id', $batchId)->first();
            $profit = Profit::where('batch_id', $batchId)->first();

            return [
                'batch' => $batch,
                'revenue' => $revenue,
                'profit' => $profit,
                'has_revenue' => $revenue !== null,
                'has_profit' => $profit !== null,
            ];
        } catch (\Exception $e) {
            Log::error('RevenueHelper - getBatchFinancialSummary Error: ' . $e->getMessage());
            return null;
        }
    }
}

