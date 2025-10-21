# 📊 Revenue & Profit Recording System - Implementation Summary

## ✅ Completed Features

### 1. RevenueHelper Class (`app/Helpers/RevenueHelper.php`)
- **recordPigSaleRevenue()** - บันทึกรายได้จากการขายหมู
  - ตรวจสอบว่าเคยบันทึกรายได้นี้แล้วหรือไม่ (update หากเคยบันทึกแล้ว)
  - บันทึกข้อมูล: farm_id, batch_id, pig_sale_id, quantity, total_revenue, net_revenue, payment_status
  - บันทึกวันที่ชำระเงินหากสถานะเป็น "ชำระแล้ว"

- **calculateAndRecordProfit()** - คำนวณและบันทึกกำไรลงตาราง profits
  - ดึงรายได้ทั้งหมดของรุ่น: `Revenue::where('batch_id', $batchId)->sum('net_revenue')`
  - ดึงต้นทุนทั้งหมดและแยกตามหมวดหมู่:
    - 🌾 ค่าอาหาร (feed_cost)
    - 💊 ค่ายา/วัคซีน (medicine_cost)
    - 🚚 ค่าขนส่ง (transport_cost)
    - 👷 ค่าแรงงาน (labor_cost)
    - 💡 ค่ากระแสไฟ/น้ำ (utility_cost)
    - 📋 ค่าใช้สอยอื่นๆ (other_cost)
  
  - คำนวณ:
    - **gross_profit** = total_revenue - total_cost
    - **profit_margin_percent** = (gross_profit / total_revenue) × 100
    - **profit_per_pig** = gross_profit / total_pig_sold
    
  - บันทึก profit_details ด้วยรายละเอียดต้นทุนแต่ละรายการ

- **getBatchFinancialSummary()** - ดึงสรุปรายได้-กำไรของ batch

### 2. Models Created

#### Revenue Model (`app/Models/Revenue.php`)
```php
fields: farm_id, batch_id, pig_sale_id, revenue_type, quantity, unit_price, 
        total_revenue, discount, net_revenue, payment_status, revenue_date, 
        payment_received_date, note

relationships:
- farm() -> Farm
- batch() -> Batch
- pigSale() -> PigSale
```

#### Profit Model (`app/Models/Profit.php`)
```php
fields: farm_id, batch_id, total_revenue, total_cost, gross_profit, 
        profit_margin_percent, feed_cost, medicine_cost, transport_cost, 
        labor_cost, utility_cost, other_cost, total_pig_sold, total_pig_dead, 
        profit_per_pig, period_start, period_end, days_in_farm, status

relationships:
- farm() -> Farm
- batch() -> Batch
- profitDetails() -> ProfitDetail[]
```

#### ProfitDetail Model (`app/Models/ProfitDetail.php`)
```php
fields: profit_id, cost_id, cost_category, item_name, amount, note

relationships:
- profit() -> Profit
- cost() -> Cost
```

### 3. PigSaleController Integration
- เพิ่ม import: `use App\Helpers\RevenueHelper;`
- เมื่อสร้างการขายหมู จะ:
  1. บันทึกค่าขนส่งลง costs table (ถ้ามี)
  2. เรียก `RevenueHelper::recordPigSaleRevenue($pigSale)` เพื่อบันทึกรายได้
  3. เรียก `RevenueHelper::calculateAndRecordProfit($batchId)` เพื่อคำนวณกำไร

```php
// บันทึกรายได้จากการขายหมู
$revenueResult = RevenueHelper::recordPigSaleRevenue($pigSale);

// คำนวณกำไรและบันทึกลง profit table
$profitResult = RevenueHelper::calculateAndRecordProfit($validated['batch_id']);
```

### 4. ProfitController (`app/Http/Controllers/ProfitController.php`)
- **index()** - แสดงรายการกำไรทั้งหมดพร้อมตัวกรอง
  - Filter by: farm_id, batch_id, status
  - Sorting: โดยค่าเริ่มต้นเรียงตามวันสิ้นสุด (descending)
  - แสดง summary totals: total revenue, total cost, total profit, avg profit margin

- **show()** - แสดงรายละเอียดกำไรของ batch เดียว

- **recalculateBatchProfit()** - ตรวจสอบและอัปเดทกำไรของ batch

- **getFarmProfitSummary()** - API endpoint: ดึงข้อมูลสรุปกำไรตามฟาร์ม
  - Response: farm_name, total_revenue, total_cost, total_profit, avg_profit_margin, 
             completed_batches, incomplete_batches, cost_breakdown

- **getBatchProfitDetails()** - API endpoint: ดึงข้อมูลรายละเอียดกำไรตามรุ่น

### 5. Routes Added

**Web Routes** (`routes/web.php`)
```php
Route::prefix('profits')->middleware(['auth', 'prevent.cache'])->group(function () {
    Route::get('/', [ProfitController::class, 'index'])->name('profits.index');
    Route::get('/{id}', [ProfitController::class, 'show'])->name('profits.show');
    Route::post('/{batchId}/recalculate', [ProfitController::class, 'recalculateBatchProfit'])->name('profits.recalculate');
    Route::get('/export/pdf', [ProfitController::class, 'exportPdf'])->name('profits.export.pdf');
});
```

**API Routes** (`routes/api.php`)
```php
Route::middleware('auth:sanctum')->prefix('profits')->group(function () {
    Route::get('/farm/{farmId}/summary', [ProfitController::class, 'getFarmProfitSummary']);
    Route::get('/batch/{batchId}/details', [ProfitController::class, 'getBatchProfitDetails']);
});
```

### 6. Profit Dashboard (`resources/views/profits/index.blade.php`)
- Summary Cards แสดง: รายได้รวม, ต้นทุนรวม, กำไรรวม, อัตราส่วนกำไร
- Filters: ฟาร์ม, รุ่น, สถานะ
- ตารางแสดงรายละเอียดกำไรแต่ละรุ่น:
  - แสดง: farm, batch_code, revenue, cost, profit, margin%, profit/pig, pigs sold, pigs dead, status
  - Action buttons: ดูรายละเอียด (modal popup)

- Modal แสดงรายละเอียด:
  - Summary: revenue, cost, profit, margin, profit/pig
  - Cost Breakdown: feed, medicine, transport, labor, utility, other
  - Sales Data: total pigs sold, pigs dead
  - Detailed Cost Items: รายละเอียดต้นทุนแต่ละรายการ

## 🔄 Data Flow

```
PigSale Created
    ↓
Step 1: บันทึกค่าขนส่ง → costs table
Step 2: RevenueHelper::recordPigSaleRevenue()
        ↓
        ตรวจสอบ pig_sale_id ใน revenues
        → ถ้ามีเคยบันทึก: UPDATE
        → ถ้ายังไม่มี: CREATE
Step 3: RevenueHelper::calculateAndRecordProfit()
        ↓
        ดึงรายได้ทั้งหมดของ batch
        ดึงต้นทุนทั้งหมดและแยกตามหมวดหมู่
        คำนวณ: gross_profit, profit_margin, profit_per_pig
        → ถ้ามี profit record: UPDATE
        → ถ้ายังไม่มี: CREATE
        
        บันทึก profit_details ด้วยรายละเอียดต้นทุน
```

## 📊 Cost Categories Mapping

| cost_type | Mapped To |
|-----------|-----------|
| feed | feed_cost |
| medicine | medicine_cost |
| shipping | transport_cost |
| wage | labor_cost |
| electric_bill, water_bill | utility_cost |
| other | other_cost |
| payment | (skip - ไม่นับในต้นทุน) |

## 🎯 Key Metrics Calculated

1. **Total Revenue** - รวมรายได้ net_revenue จากการขาย
2. **Total Cost** - รวมต้นทุนทั้งหมดจากตารางต้นทุน
3. **Gross Profit** = Total Revenue - Total Cost
4. **Profit Margin %** = (Gross Profit / Total Revenue) × 100
5. **Profit per Pig** = Gross Profit / Total Pigs Sold
6. **Cost per Pig** = Total Cost / Total Pigs Sold
7. **Average Revenue per Pig** = Total Revenue / Total Pigs Sold

## 🛠️ Database Tables Used

- **revenues** - บันทึกรายได้จากการขายหมู
- **profits** - บันทึกกำไรสรุปของแต่ละรุ่น
- **profit_details** - รายละเอียดต้นทุนแต่ละรายการ (link กับ costs)
- **costs** - ตารางต้นทุนที่มีอยู่แล้ว
- **pig_sales** - ตารางการขายหมูที่มีอยู่แล้ว
- **batches** - ตารางรุ่นหมูที่มีอยู่แล้ว

## ✨ Features Highlights

✅ **Automatic Revenue Recording** - บันทึกรายได้อัตโนมัติเมื่อขายหมู
✅ **Real-time Profit Calculation** - คำนวณกำไรทันที
✅ **Cost Breakdown** - แยกต้นทุนตามหมวดหมู่
✅ **Profit Per Pig** - แสดงกำไรต่อตัวหมู
✅ **Mobile Responsive** - Dashboard ตอบสนองดี
✅ **Filter & Sort** - ค้นหาและเรียงลำดับ
✅ **API Endpoints** - สำหรับการอ้างอิงจากอื่นๆ
✅ **Update Support** - สามารถอัปเดทรายได้-กำไรได้

## 🔐 Security & Validation

- Protected by auth middleware
- Transaction rollback ถ้าเกิดข้อผิดพลาด
- Error logging ด้วย Log::error()
- Input validation ผ่าน Request::validate()

## 📝 Future Enhancements

1. ✅ Add export to PDF report
2. ✅ Add graphical charts for profit trends
3. ✅ Add profit comparison between batches/farms
4. ✅ Add monthly/yearly profit summary
5. ✅ Add alerts for low profit margin
6. ✅ Add profit forecasting

## 🚀 How to Use

### 1. View Profits Dashboard
```
Navigate to: /profits
Filter by farm, batch, or status
Click "ดู" button to see detailed breakdown
```

### 2. Manually Recalculate Profit
```php
POST /profits/{batchId}/recalculate
// Re-calculate profit for a specific batch
```

### 3. API: Get Farm Profit Summary
```php
GET /api/profits/farm/{farmId}/summary
// Returns: total_revenue, total_cost, gross_profit, etc.
```

### 4. API: Get Batch Profit Details
```php
GET /api/profits/batch/{batchId}/details
// Returns: detailed profit breakdown with cost items
```

---

**Status**: ✅ Ready for Production
**Last Updated**: 2025-10-21
