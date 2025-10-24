<?php

namespace App\Helpers;

use App\Models\Revenue;
use App\Models\Profit;
use App\Models\ProfitDetail;
use App\Models\Cost;
use \App\Models\PigDeath;
use App\Models\StoreHouse;
use App\Models\InventoryMovement;
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
                    'payment_status' => 'อนุมัติแล้ว',  // เปลี่ยนเป็น 'อนุมัติแล้ว' เมื่ออนุมัติการขาย
                    'revenue_date' => $pigSale->date,
                    'payment_received_date' => null,  // ยังไม่มีการชำระเงิน
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
                'payment_status' => 'อนุมัติแล้ว',  // ✅ เปลี่ยนเป็น 'อนุมัติแล้ว' เมื่ออนุมัติการขาย (ไม่ต้องรอสถานะการชำระเงิน)
                'revenue_date' => $pigSale->date,
                'payment_received_date' => null,  // ยังไม่ได้รับการชำระเงิน
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

            // ดึงรายได้ทั้งหมดของรุ่นนี้ (เฉพาะที่ได้รับการอนุมัติแล้ว - payment_status = 'อนุมัติแล้ว' หรือ 'ชำระแล้ว')
            $totalRevenue = Revenue::where('batch_id', $batchId)
                ->whereIn('payment_status', ['อนุมัติแล้ว', 'ชำระแล้ว'])
                ->sum('net_revenue');

            // ดึงต้นทุนทั้งหมด (เฉพาะที่ได้อนุมัติแล้ว และ ไม่ถูกยกเลิก)
            // รองรับทั้ง Cost ที่มี CostPayment (payment_status = 'approved')
            // และ Cost ที่สร้างจาก inventory_movements (payment_status = 'approved' โดยตรง)
            $approvedCosts = Cost::where('batch_id', $batchId)
                ->where('payment_status', 'approved')
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

            // ✅ FIX: นับเฉพาะหมูที่มี Revenue อนุมัติแล้ว (payment approved) เท่านั้น
            // ไม่ใช่นับทุกการขายที่ไม่เป็น cancelled
            $totalPigSold = Revenue::where('batch_id', $batchId)
                ->whereIn('payment_status', ['อนุมัติแล้ว', 'ชำระแล้ว'])
                ->sum('quantity');

            // ใช้ sum('quantity') แทน count() เพื่อได้จำนวนหมูที่ตายจริง ๆ
            $totalPigDead = \App\Models\PigDeath::where('batch_id', $batchId)->sum('quantity');
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
                    'excess_weight_cost' => $excessWeightCost,
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
}
