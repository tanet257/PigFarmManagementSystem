<?php

namespace App\Helpers;

use App\Models\Revenue;
use App\Models\Profit;
use App\Models\ProfitDetail;
use App\Models\Cost;
use \App\Models\PigDeath;
use App\Models\StoreHouse;
use App\Models\InventoryMovement;
use App\Models\DairyStorehouseUse;
use App\Models\DairyRecord;
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
                    'revenue_date' => $pigSale->date,
                    'note' => 'ขายหมู ' . $pigSale->quantity . ' ตัว ให้ ' . $pigSale->buyer_name,
                ]);

                DB::commit();
                return [
                    'success' => true,
                    'message' => 'อัปเดทรายได้สำเร็จ',
                    'revenue' => $existingRevenue,
                ];
            }

            // สร้างรายได้ใหม่ - เมื่อ PigSale ได้รับการอนุมัติแล้ว
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
                'revenue_date' => $pigSale->date,
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

            // ✅ FIXED: ดึงจาก Payment ที่ approved (ไม่ใช่ PigSale.status)
            $approvedPaymentIds = \App\Models\Payment::where('status', 'approved')
                ->pluck('pig_sale_id')
                ->toArray();

            $totalRevenue = Revenue::where('batch_id', $batchId)
                ->whereIn('pig_sale_id', $approvedPaymentIds)
                ->sum('net_revenue');

            // ✅ ดึงต้นทุนทั้งหมด (เฉพาะที่ได้อนุมัติแล้ว)
            // Cost ที่มี CostPayment.status = 'approved'
            $approvedCostIds = \App\Models\CostPayment::where('status', 'approved')
                ->pluck('cost_id')
                ->toArray();

            $approvedCosts = Cost::where('batch_id', $batchId)
                ->whereIn('id', $approvedCostIds)
                ->get();

            // คำนวณต้นทุนตามหมวดหมู่ (เฉพาะ approved payments)
            $feedCost = $approvedCosts->where('cost_type', 'feed')->sum('total_price');
            $medicineCost = $approvedCosts->where('cost_type', 'medicine')->sum('total_price');
            $transportCost = $approvedCosts->where('cost_type', 'shipping')->sum('transport_cost')
                + $approvedCosts->where('cost_type', 'piglet')->sum('transport_cost')
                + $approvedCosts->where('cost_type', 'feed')->sum('transport_cost')
                + $approvedCosts->where('cost_type', 'medicine')->sum('transport_cost');
            $excessWeightCost = $approvedCosts->where('cost_type', 'piglet')->sum('excess_weight_cost');
            $laborCost = $approvedCosts->where('cost_type', 'wage')->sum('total_price');
            $utilityCost = $approvedCosts->whereIn('cost_type', ['electric_bill', 'water_bill'])->sum('total_price');
            $otherCost = $approvedCosts->where('cost_type', 'other')->sum('total_price');
            $pigletCost = $approvedCosts->where('cost_type', 'piglet')->sum('total_price');

            // รวมต้นทุนทั้งหมด
            $totalCost = $feedCost + $medicineCost + $transportCost + $excessWeightCost + $laborCost + $utilityCost + $otherCost + $pigletCost;

            // คำนวณกำไร
            $grossProfit = $totalRevenue - $totalCost;
            $profitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue * 100) : 0;

            // ✅ นับเฉพาะหมูที่ขายกับ approved Payment เท่านั้น
            $totalPigSold = Revenue::where('batch_id', $batchId)
                ->whereIn('pig_sale_id', $approvedPaymentIds)
                ->sum('quantity');

            // ✅ NEW: หมูตายที่ขายไปแล้ว = sum(quantity_sold_total)
            // (ไม่ใช่ sum(quantity) เพราะ quantity ยังคงเดิม ไม่ลด)
            $totalPigDeadSold = \App\Models\PigDeath::where('batch_id', $batchId)
                ->where('status', 'sold')  // ✅ เฉพาะที่ขายไปแล้ว
                ->sum('quantity_sold_total');

            // 🔴 BUG FIX: ลบการคำนวณ deadPigRevenue เพราะบันทึกไว้ใน Revenue table แล้ว
            // ไม่ควรคำนวณเพิ่มเติมอีก ด้านบนแล้ว ($totalRevenue จาก Revenue table)
            // $deadPigRevenue = ...
            // $totalRevenue += $deadPigRevenue;  ← ลบออก (คำนวณเบิ้ล)

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
                    'excess_weight_cost' => $excessWeightCost,
                    'labor_cost' => $laborCost,
                    'utility_cost' => $utilityCost,
                    'other_cost' => $otherCost,
                    'total_pig_sold' => $totalPigSold,
                    'total_pig_dead' => $totalPigDeadSold,
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
                    'excess_weight_cost' => $excessWeightCost,
                    'labor_cost' => $laborCost,
                    'utility_cost' => $utilityCost,
                    'other_cost' => $otherCost,
                    'total_pig_sold' => $totalPigSold,
                    'total_pig_dead' => $totalPigDeadSold,
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

    /**
     * แยกราคาต่อหน่วยจาก storehouse note
     * ตัวอย่าง: "ราคา: ฿549 ต่อ กระสอบ" → 549
     *
     * @param string $note
     * @return float|int
     */
    public static function extractPriceFromNote($note)
    {
        if (!$note) return 0;

        // ค้นหา pattern "ราคา: ฿XXXX" หรือ "฿XX.XX"
        if (preg_match('/ราคา:\s*฿([\d.]+)/', $note, $matches)) {
            return (float) $matches[1];
        }

        return 0;
    }

    /**
     * แยกค่าส่งจาก storehouse note
     * ตัวอย่าง: "ค่าส่ง: ฿100" → 100
     *
     * @param string $note
     * @return float|int
     */
    public static function extractTransportCostFromNote($note)
    {
        if (!$note) return 0;

        // ค้นหา pattern "ค่าส่ง: ฿XXXX"
        if (preg_match('/ค่าส่ง:\s*฿([\d.]+)/', $note, $matches)) {
            return (float) $matches[1];
        }

        return 0;
    }

    /**
     * บันทึกต้นทุนจากการใช้สินค้า storehouse
     * เรียกเมื่อ inventory_movement ถูกสร้าง (change_type = 'in')
     *
     * @param $inventoryMovement - InventoryMovement object
     * @return array ['success' => bool, 'message' => string, 'cost' => Cost object or null]
     */
    public static function recordStorehouseCost($inventoryMovement)
    {
        DB::beginTransaction();
        try {
            // ตรวจสอบว่าเป็น 'in' movement เท่านั้น (เข้าคลัง)
            if ($inventoryMovement->change_type !== 'in') {
                DB::commit();
                return [
                    'success' => true,
                    'message' => 'ไม่บันทึก - เป็น out movement',
                    'cost' => null,
                ];
            }

            // ตรวจสอบว่ามี batch แล้ว
            if (!$inventoryMovement->batch_id) {
                DB::commit();
                return [
                    'success' => true,
                    'message' => 'ไม่บันทึก - ไม่มี batch',
                    'cost' => null,
                ];
            }

            // ดึง storehouse ข้อมูล
            $storehouse = StoreHouse::findOrFail($inventoryMovement->storehouse_id);

            // แยกราคาต่อหน่วยจาก note
            $pricePerUnit = self::extractPriceFromNote($storehouse->note);
            $transportCost = self::extractTransportCostFromNote($storehouse->note);

            if ($pricePerUnit <= 0) {
                Log::warning('RevenueHelper - No price found in storehouse note: ' . $storehouse->note);
                DB::commit();
                return [
                    'success' => true,
                    'message' => 'ไม่บันทึก - ไม่พบราคาในข้อมูล',
                    'cost' => null,
                ];
            }

            // คำนวณต้นทุนรวม (ราคาต่อหน่วย x จำนวน + ค่าส่ง)
            $totalPrice = ($inventoryMovement->quantity * $pricePerUnit) + $transportCost;

            // บันทึก Cost record
            $cost = Cost::create([
                'farm_id' => $inventoryMovement->batch->farm_id,
                'batch_id' => $inventoryMovement->batch_id,
                'storehouse_id' => $inventoryMovement->storehouse_id,
                'cost_type' => $storehouse->item_type, // 'feed' หรือ 'medicine'
                'item_code' => $storehouse->item_code,
                'quantity' => $inventoryMovement->quantity,
                'unit' => $storehouse->unit,
                'price_per_unit' => $pricePerUnit,
                'transport_cost' => $transportCost,
                'total_price' => $totalPrice,
                'payment_status' => 'approved',
                'note' => 'ต้นทุน ' . $storehouse->item_type . ' จาก ' . $storehouse->item_name . ' - ' . $inventoryMovement->note,
                'date' => $inventoryMovement->date,
            ]);

            // ✅ AUTO-APPROVE: สร้าง CostPayment และ auto-approve เลย
            \App\Models\CostPayment::create([
                'cost_id' => $cost->id,
                'amount' => $totalPrice,
                'status' => 'approved', // ✅ auto-approve
                'approved_by' => 1, // System user (admin)
                'approved_date' => now(),
                'reason' => 'Auto-approved from InventoryMovement (Stock In)',
            ]);

            // ✅ บันทึก Profit ทันที
            if ($inventoryMovement->batch_id) {
                self::calculateAndRecordProfit($inventoryMovement->batch_id);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'บันทึกต้นทุนสำเร็จ',
                'cost' => $cost,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('RevenueHelper - recordStorehouseCost Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'ไม่สามารถบันทึกต้นทุนได้: ' . $e->getMessage(),
                'cost' => null,
            ];
        }
    }

    /**
     * ✅ NEW: คำนวณ KPI metrics (ADG, FCR, FCG) - ใช้ข้อมูลจริงจาก Dairy/Inventory
     */
    public static function calculateKPIMetrics($batch)
    {
        try {
            $profit = Profit::where('batch_id', $batch->id)->first();

            if (!$profit) {
                return [];
            }

            // ✅ ดึงข้อมูลจริง: อาหารที่ใช้จากการบันทึก Dairy (DairyStorehouseUse)
            $totalFeedKg = 0;
            $totalFeedBags = 0;

            // ดึงจาก DairyStorehouseUse ที่สัมพันธ์กับ batch นี้
            $dairyRecords = \App\Models\DairyRecord::where('batch_id', $batch->id)->get();

            foreach ($dairyRecords as $dairy) {
                // ดึงการใช้สินค้าอาหาร
                $feedUses = DairyStorehouseUse::where('dairy_record_id', $dairy->id)->get();

                foreach ($feedUses as $feedUse) {
                    // ✅ FIX: quantity เป็น kg แล้ว ไม่ต้องคูณ 50
                    $totalFeedKg += $feedUse->quantity;
                    $totalFeedBags += ceil($feedUse->quantity / 50); // convert to bags (1 bag = 50 kg)
                }
            }

            // ✅ Alternative: ดึงจาก InventoryMovement (out movement)
            // ถ้าไม่มี DairyStorehouseUse ให้ใช้ inventory
            if ($totalFeedKg == 0) {
                $inventoryOut = \App\Models\InventoryMovement::where('batch_id', $batch->id)
                    ->where('change_type', 'out')
                    ->sum('quantity');
                $totalFeedKg = $inventoryOut; // ปริมาณเป็น kg โดยตรง
                $totalFeedBags = ceil($totalFeedKg / 50); // convert to bags
            }

            // Weight calculations
            // ✅ ดึง starting weight จาก PigEntryRecord ให้แม่นยำ
            $pigEntry = \App\Models\PigEntryRecord::where('batch_id', $batch->id)->first();
            $startingWeight = $pigEntry ? $pigEntry->average_weight_per_pig : ($profit->starting_avg_weight ?? 0);
            $endingWeight = $batch->average_weight_per_pig ?? $profit->ending_avg_weight ?? 0;
            $weightGainPerPig = max($endingWeight - $startingWeight, 0);

            // ✅ FIX: total_pig_sold อาจ 0 ให้ใช้ current_quantity แทน
            $pigsForCalculation = max($profit->total_pig_sold, $batch->current_quantity, 1);
            $totalWeightGained = $weightGainPerPig * $pigsForCalculation;

            // ✅ FIX: Days in farm ต้องคำนวณจาก PigEntryRecord.pig_entry_date
            $daysInFarm = 1;
            if ($pigEntry && $pigEntry->pig_entry_date) {
                $daysInFarm = max(\Carbon\Carbon::parse($pigEntry->pig_entry_date)->diffInDays(\Carbon\Carbon::now()), 1);
            } elseif ($batch->entry_date) {
                // Fallback: ใช้ batch.entry_date ถ้ามี
                $daysInFarm = max(\Carbon\Carbon::parse($batch->entry_date)->diffInDays(\Carbon\Carbon::now()), 1);
            }
            // Note: ไม่ใช้ $profit->days_in_farm เพราะมันเก่า ให้คำนวณจริง

            // ADG = Average Daily Gain (kg/head/day)
            $adg = $daysInFarm > 0 ? ($weightGainPerPig / $daysInFarm) : 0;

            // FCR = Feed Conversion Ratio (kg feed / kg gain)
            $fcr = $totalWeightGained > 0 ? ($totalFeedKg / $totalWeightGained) : 0;

            // FCG = Feed Cost per kg Gain (baht/kg gain)
            $fcg = $totalWeightGained > 0 ? ($profit->feed_cost / $totalWeightGained) : 0;

            // Update profit record
            $profit->update([
                'adg' => round($adg, 3),
                'fcr' => round($fcr, 3),
                'fcg' => round($fcg, 2),
                'starting_avg_weight' => $startingWeight,
                'ending_avg_weight' => $endingWeight,
                'total_feed_bags' => $totalFeedBags,
                'total_feed_kg' => round($totalFeedKg, 2),
                'total_weight_gained' => round($totalWeightGained, 2),
                'days_in_farm' => $daysInFarm,
            ]);

            Log::info('KPI Calculated for batch', [
                'batch_id' => $batch->id,
                'adg' => $adg,
                'fcr' => $fcr,
                'fcg' => $fcg,
                'total_feed_kg' => $totalFeedKg,
                'daysInFarm' => $daysInFarm,
            ]);

            return [
                'adg' => $adg,
                'fcr' => $fcr,
                'fcg' => $fcg,
                'total_feed_kg' => $totalFeedKg,
                'total_weight_gained' => $totalWeightGained,
                'days_in_farm' => $daysInFarm,
            ];
        } catch (\Exception $e) {
            Log::error('RevenueHelper - calculateKPIMetrics Error: ' . $e->getMessage());
            return [];
        }
    }
}
