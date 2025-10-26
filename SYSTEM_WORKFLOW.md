# ระบบจัดการฟาร์มหมู - Flow การทำงาน (Pig Farm Management System)

## 📊 ภาพรวมระบบ

```
┌─────────────────────────────────────────────────────────────────┐
│               ระบบจัดการฟาร์มหมู (Pig Farm System)             │
│  ใช้สำหรับบริหารจัดการเพาะเลี้ยงหมู คำนวณกำไร และติดตามข้อมูล │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🔄 Flow หลัก 1: การเลี้ยงหมูตั้งแต่เริ่มต้นถึงจำหน่าย

```
1️⃣ สร้างรุ่นหมูใหม่ (Create Batch)
   ├─ ใส่ข้อมูล: รหัสรุ่น, ฟาร์ม, วันเริ่มต้น, จำนวนหมู
   ├─ ระบบสร้าง Profit Record (เพื่อติดตามกำไร)
   └─ Status: incomplete
   
2️⃣ บันทึกหมูเข้าเล้า (Pig Entry)
   ├─ ระบุ: ฟาร์ม, รุ่น, เล้า, จำนวนหมู, น้ำหนัก
   ├─ ระบบสร้าง PigEntryRecord
   └─ ติดตามการเข้าของหมูแต่ละเล้า
   
3️⃣ บันทึกการเลี้ยงรายวัน (Dairy Record)
   ├─ บันทึกการให้อาหาร, ยา, การตายของหมู
   ├─ PigEntry → Dairy → InventoryMovement (อัตโนมัติ)
   └─ โครงสร้าง:
       ┌─ DairyStorehouseUse (ใช้อาหาร)
       ├─ BatchTreatment (ให้ยา)
       └─ PigDeath (บันทึกตาย)
   
4️⃣ จำหน่ายหมู (Pig Sale)
   ├─ บันทึกข้อมูล: ฟาร์ม, รุ่น, จำนวนขาย, ราคา, ผู้ซื้อ
   ├─ ระบบคำนวณ: รายได้ = จำนวน × ราคา
   ├─ อัปเดต PigEntryRecord: status = 'ขายแล้ว'
   └─ ส่วน Admin อนุมัติ → เปลี่ยน status → ระบบสร้าง Revenue
   
5️⃣ ปิดรุ่น (Close Batch)
   ├─ ระบบติดตามวันสิ้นสุดจริง
   ├─ คำนวณ KPI: ADG, FCR, FCG
   ├─ บวกรวม: รายได้ + ต้นทุน = กำไร
   └─ Status: completed/closed
```

---

## 💰 Flow 2: การติดตามต้นทุน (Cost Tracking)

```
ต้นทุนถูกแบ่งเป็นประเภทต่างๆ:

┌─ Feed Cost (ค่าอาหาร)
│  └─ บันทึกจาก: DairyStorehouseUse → Inventory
│     (ลด Stock, บันทึกต้นทุน)
│
├─ Medicine Cost (ค่ายา/วัคซีน)
│  └─ บันทึกจาก: BatchTreatment
│     (ยาที่ใช้กับรุ่นนั้น)
│
├─ Transport Cost (ค่าขนส่ง)
│  └─ บันทึกเมื่อขายหมู
│
├─ Labor Cost (ค่าแรงงาน)
│  └─ บันทึกด้วย Cost Model
│
├─ Utility Cost (ค่าไฟ/น้ำ)
│  └─ บันทึกด้วย Cost Model
│
└─ Other Cost (ค่าอื่นๆ)
   └─ บันทึกด้วย Cost Model

📊 ระบบรวบรวมทั้งหมด → Profit Record
   → Dashboard แสดงกำไรต่อรุ่น
```

---

## 📈 Flow 3: Dashboard & Reporting

```
┌─ Dashboard (หน้าแรก)
│  ├─ 📊 Summary Cards
│  │  ├─ รายได้รวม (Total Revenue)
│  │  ├─ ต้นทุนรวม (Total Cost)
│  │  ├─ กำไรรวม (Total Profit)
│  │  └─ ร้อยละกำไร (Profit Margin %)
│  │
│  ├─ 📉 KPI Metrics
│  │  ├─ ADG: น้ำหนักเพิ่มต่อวัน (kg/head/day)
│  │  ├─ FCR: อัตราการแปลงอาหาร (kg feed/kg gain)
│  │  ├─ FCG: ต้นทุนอาหารต่อน้ำหนัก (baht/kg gain)
│  │  └─ อาหารรวม (Total Feed)
│  │
│  ├─ 📊 Charts
│  │  ├─ Cost Breakdown (โครงสร้างต้นทุน)
│  │  ├─ Revenue-Cost-Profit (รายได้-ต้นทุน-กำไร)
│  │  ├─ Monthly Trend (ต้นทุน-กำไรรายเดือน)
│  │  └─ FCG Performance (ประสิทธิภาพการเลี้ยง)
│  │
│  ├─ 🔍 Filter & Export
│  │  ├─ Filter by Farm, Batch, Status
│  │  ├─ Export CSV (ข้อมูลกำไร)
│  │  └─ Show Details (Modal)
│  │
│  └─ 📋 Profit Table
│     └─ รายละเอียดกำไรแต่ละรุ่น
│        (batch_code, farm, revenue, cost, profit, KPI)
```

---

## 🔐 Flow 4: ระบบอนุมัติการชำระเงิน (Payment Approval)

```
┌─ Payment Entry
│  └─ เมื่อ: บันทึกต้นทุน/ค่าใช้จ่าย
│
├─ CostPayment Record
│  └─ Status: pending (รอการอนุมัติ)
│
├─ Admin Approval
│  ├─ ดูรายการรออนุมัติ
│  ├─ ตรวจสอบ
│  └─ Action: อนุมัติ (approved) / ปฏิเสธ (rejected)
│
└─ Status Update
   ├─ approved → แสดง ✅ บนตาราง
   ├─ rejected → แสดง ❌ บนตาราง
   └─ Payment Report (สำหรับ accounting)
```

---

## 📱 Flow 5: ข้อมูลการขายหมู (Pig Sale Detail)

```
1️⃣ บันทึกการขาย
   ├─ Farm + Batch + Pen + Quantity
   ├─ Sale Type: ทั่วไป / ช่วงทดลอง
   ├─ Price (ต่อ kg หรือต่อตัว)
   └─ Buyer Info

2️⃣ ระบบคำนวณ
   ├─ ถ้า: Weight known → คำนวณ: Total = Weight × Price/kg
   ├─ ถ้า: Per head → คำนวณ: Total = Quantity × Price/head
   └─ บันทึก Total Price

3️⃣ Receipt Management
   ├─ Upload ใบเสร็จ (PDF/Image)
   ├─ Cloudinary: เก็บไฟล์รูป
   └─ Status: pending/confirmed/cancelled

4️⃣ Revenue Update
   ├─ Admin Approve → Revenue Record สร้าง
   ├─ ระบบ:
   │  ├─ เพิ่ม Total Revenue ของ Profit Record
   │  ├─ อัปเดต PigEntryRecord status
   │  └─ Recompute KPI (ADG, FCR, FCG)
   └─ Dashboard update อัตโนมัติ
```

---

## 🏪 Flow 6: คลังสินค้า (Inventory Management)

```
📦 StoreHouse (ข้อมูลสินค้า)
   ├─ Farm + Item (อาหาร, ยา)
   ├─ Category (ประเภท)
   ├─ Quantity (จำนวน)
   └─ Status (พร้อมใช้/หมดแล้ว)

📊 InventoryMovement (ย้ายสินค้า)
   ├─ From: StoreHouse
   ├─ To: Dairy Record
   ├─ Type: อาหาร, ยา, อื่น
   ├─ Quantity: ลดลง
   └─ Cost: บวกเข้า Profit Record

🔄 Flow
   1. DairyRecord บันทึกใช้อาหาร X กระสอบ
   2. Inventory Movement สร้างอัตโนมัติ
   3. StoreHouse: ลดจำนวน
   4. Cost: บวกเข้า Feed Cost ของ Profit
   5. Dashboard: Update ต้นทุน
```

---

## 🗄️ Database Schema ที่สำคัญ

```
┌─ farms (ฟาร์ม)
│  ├─ id, farm_name, barn_capacity
│  └─ Relationships: batches, storehouse, profits
│
├─ batches (รุ่นหมู)
│  ├─ id, batch_code, farm_id, status, period_start, period_end
│  └─ Relationships: pigEntryRecords, costs, profits
│
├─ pig_entry_records (บันทึกหมูเข้า)
│  ├─ id, batch_id, farm_id, barn_id, pen_id, quantity, avg_weight
│  ├─ status (new/in_farm/sold/died)
│  └─ Relationships: pigSales, pigDeaths, dairyRecords
│
├─ costs (ต้นทุน)
│  ├─ id, batch_id, farm_id, cost_type, item_name, quantity, total_price
│  └─ Relationships: costPayments, profitDetails
│
├─ pig_sales (การขายหมู)
│  ├─ id, batch_id, farm_id, quantity, weight, price, total_price
│  ├─ status (pending/confirmed/cancelled)
│  └─ Relationships: revenues
│
├─ dairy_records (บันทึกประจำวัน)
│  ├─ id, pig_entry_record_id, farm_id, date
│  └─ Relationships: dairyStorehouseUses, batchTreatments, pigDeaths
│
├─ dairy_storehouse_uses (ใช้อาหาร)
│  ├─ id, dairy_record_id, storehouse_id, quantity
│  └─ Triggers: InventoryMovement, Cost update
│
├─ batch_treatments (ให้ยา)
│  ├─ id, batch_id, diary_record_id, medicine_name, quantity
│  └─ Relationships: costs
│
├─ storehouse (คลังสินค้า)
│  ├─ id, farm_id, item_name, category, quantity
│  └─ Relationships: inventoryMovements
│
├─ inventory_movements (การเคลื่อนไหวสต็อก)
│  ├─ id, from_storehouse, to_dairy, quantity, movement_type
│  └─ Triggers: Cost calculation
│
├─ profits (กำไร)
│  ├─ id, batch_id, farm_id
│  ├─ total_revenue, total_cost, gross_profit, profit_margin_percent
│  ├─ feed_cost, medicine_cost, transport_cost, labor_cost, utility_cost
│  ├─ adg, fcr, fcg, starting_avg_weight, ending_avg_weight
│  ├─ total_pig_sold, total_pig_dead
│  ├─ period_start, period_end, days_in_farm, status
│  └─ Relationships: profitDetails, revenues
│
├─ revenues (รายได้)
│  ├─ id, profit_id, pig_sale_id, amount
│  └─ Source: PigSale (when approved)
│
├─ cost_payments (ชำระเงินต้นทุน)
│  ├─ id, cost_id, status (pending/approved/rejected)
│  └─ Relationships: costs
│
└─ pig_deaths (บันทึกตาย)
   ├─ id, dairy_record_id, pig_entry_record_id, quantity
   └─ Relationships: dairyRecords
```

---

## 🔄 Key Calculations & Updates

### ADG (Average Daily Gain)
```
ADG = (Ending Weight - Starting Weight) / Days in Farm
      (กก./ตัว/วัน)
```

### FCR (Feed Conversion Ratio)
```
FCR = Total Feed (kg) / Total Weight Gained (kg)
      ต่ำเท่าไหร่ดีเท่านั้น
```

### FCG (Feed Cost per kg Gain)
```
FCG = Feed Cost (บาท) / Total Weight Gained (kg)
      บาท/กก.
```

### Profit Margin %
```
Profit Margin % = (Gross Profit / Total Revenue) × 100
                  เปอร์เซ็นต์กำไร
```

---

## 🔐 Authentication & Authorization

```
3 Roles:
├─ Admin
│  └─ สำนักงาน: อนุมัติ, ดูรายงาน, ส่งออกข้อมูล
│
├─ Staff
│  └─ ฟาร์ม: บันทึกข้อมูลประจำวัน, ขายหมู
│
└─ Manager
   └─ ดูรายงาน, ตัดสินใจ
```

---

## 📤 Export Features

```
✅ CSV Export (Thai filename + UTF-8 BOM)
   ├─ Pig Entry Records
   ├─ Dairy Records
   ├─ Batch Pen Allocations
   ├─ Inventory Movements
   ├─ Pig Sales
   ├─ Batches
   ├─ Storehouses
   ├─ Payment Approvals
   ├─ Cost Payment Approvals
   └─ Dashboard Profits (ข้อมูลกำไร)

✅ PDF Export
   ├─ Pig Entry Records
   ├─ Batch Pen Allocations
   ├─ Dairy Records
   └─ Dashboard Report
```

---

## 📊 Real-time Calculations

```
เมื่อสิ้นสุดรุ่น (Close Batch):
1. Recompute all KPI:
   ├─ ADG = (avg_weight_end - avg_weight_start) / days
   ├─ FCR = total_feed_kg / total_weight_gained
   ├─ FCG = feed_cost / total_weight_gained
   └─ profit_per_pig = gross_profit / total_pig_sold

2. Sum all revenues from PigSale (status=confirmed)

3. Sum all costs:
   ├─ Feed: from DairyStorehouseUse
   ├─ Medicine: from BatchTreatment
   ├─ Transport: from costs
   └─ Labor, Utility, Other: from Cost model

4. Calculate:
   ├─ gross_profit = total_revenue - total_cost
   ├─ profit_margin_percent = (gross_profit/total_revenue)*100
   └─ Update Profit status → completed
```

---

## 🎯 User Journey Examples

### ตัวอย่างที่ 1: ฟาร์มเลี้ยงหมูจำหน่ายครั้งแรก

```
Day 1:
  1. Admin สร้าง Batch: "BATCH-001", ฟาร์ม "Nakhon Sawan", เริ่ม 2025-10-01

Day 5:
  2. Staff บันทึก: หมู 100 ตัว ขนาด 10 kg/ตัว เข้าเล้า A5

Day 7-60:
  3. Staff ทำ Dairy Record ทุกวัน:
     - ให้อาหาร 5 กระสอบ
     - ให้ยา (วัคซีน)
     - บันทึกหมูตาย (ถ้ามี)
  
  4. ระบบอัตโนมัติ:
     - Inventory: ลดอาหาร 5 กระสอบ
     - Cost: +5×300 = +1,500 บาท
     - Profit: update ต้นทุนรวม

Day 61:
  5. Staff บันทึก: ขายหมู 95 ตัว, เฉลี่ย 100 kg/ตัว, 45 บาท/kg
     - Total: 95 × 100 × 45 = 427,500 บาท

Day 62:
  6. Admin อนุมัติการขาย
     - ระบบสร้าง Revenue: 427,500 บาท
     - Update Profit:
       * total_revenue = 427,500
       * total_cost = 7,500 (estimated)
       * gross_profit = 420,000
       * profit_margin = 98%
       * ADG = (100-10)/61 = 1.48 kg/ตัว/วัน
       * FCR = 305 kg / 8550 kg = 0.036
       * FCG = 7,500 / 8,550 = 0.877 บาท/kg

Day 63:
  7. Admin ดู Dashboard:
     - ข้อมูลกำไรอัปเดตแล้ว
     - Export CSV เพื่อรายงาน
```

### ตัวอย่างที่ 2: ปิดรุ่นหมูเมื่อขายหมดแล้ว

```
Status Flow:
incomplete (กำลังเลี้ยง)
    ↓
completed (ขายหมดแล้ว, โปรแกรมคำนวณกำไรเสร็จสิ้น)
    ↓
closed (ปิดบัญชี, ไม่อนุญาตแก้ไข)
```

---

## 🚨 Exception Handling

```
❌ ถ้าหมูตาย:
   1. Staff บันทึก PigDeath
   2. total_pig_dead + 1
   3. ลบออกจาก "ขายได้" → recalculate

❌ ถ้าอนุมัติการขายแล้วต้องแก้:
   1. Admin ยกเลิกการขาย (Cancel)
   2. ระบบ: ลบ Revenue
   3. ลบ InventoryMovement ที่เกี่ยวข้อง
   4. Profit: recalculate

❌ ถ้ากลับมาขายอีก:
   1. Status: sold → in_farm (ให้ admin แก้)
   2. DairyRecord: ยังบันทึกต่อ
```

---

## 📋 Summary

| ขั้นตอน | การดำเนินการ | ผู้รับผิดชอบ | Output |
|--------|-------------|-----------|--------|
| 1. Create Batch | สร้างรุ่นใหม่ | Admin | Profit Record |
| 2. Pig Entry | บันทึกหมูเข้า | Staff | PigEntryRecord |
| 3. Daily Record | บันทึกประจำวัน | Staff | DairyRecord + InventoryMovement |
| 4. Pig Sale | บันทึกการขาย | Staff | PigSale (pending) |
| 5. Approve Sale | อนุมัติการขาย | Admin | Revenue + Update Profit |
| 6. Close Batch | ปิดรุ่น | Admin | Finalized Profit + KPI |
| 7. Report | ดูรายงาน | Manager | Dashboard + Export CSV |

---

**ระบบนี้ช่วยให้ฟาร์มหมูมีการบริหารจัดการที่มีประสิทธิภาพ ติดตามต้นทุนอย่างชัดเจน และคำนวณกำไรได้อย่างแม่นยำ** 🐖📊✨
