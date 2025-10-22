# Pig Inventory Tracking System

## Overview
ระบบ tracking หมูเกษตร ที่จัดการ:
- PigEntry: บันทึกเมื่อหมูเข้ามา
- PigSale: บันทึกเมื่อขายหมู
- PigDeath: บันทึกเมื่อหมูตาย
- Inventory: ติดตามจำนวนหมูปัจจุบัน

## Key Fields

### Batch (รุ่น)
- **total_pig_amount**: จำนวนหมูรวมทั้งหมดที่เข้ามา (initial entry)
- **current_quantity**: จำนวนหมูปัจจุบัน (ใช้ tracking จริง)
  - Formula: `total_pig_amount - deaths - sales`

### batch_pen_allocations (การจัดสรร)
- **allocated_pigs**: จำนวนที่จัดสรรเบื้องต้น (per entry)
- **current_quantity**: จำนวนปัจจุบันในเล้า-คอก

### บัญชี (Accounting)
- **Revenue** (รายได้): บันทึกเมื่อ PigSale approved
- **Cost** (ต้นทุน): บันทึกเมื่อ PigEntry + อนุมัติการชำระเงิน
- **Profit** (กำไร): คำนวณจาก Revenue - Cost

## Operation Flows

### 1. เพิ่มหมูเข้า (PigEntry Upload)
```
User uploads PigEntry (e.g., 1500 pigs)
  ↓
PigEntryController::upload_pig_entry_record()
  ↓
สร้าง PigEntryRecord + PigEntryDetail (per pen)
  ↓
เรียก PigInventoryHelper::addPigs() สำหรับแต่ละ pen
  ↓
Update batch_pen_allocations (allocated_pigs += qty, current_quantity += qty)
Update batches (total_pig_amount += qty, current_quantity += qty)
  ↓
สร้าง Cost record (piglet cost)
```

### 2. ยกเลิก PigEntry
```
User clicks cancel on PigEntry
  ↓
PigEntryController::deletePigEntryRecord()
  ↓
หา PigEntryDetail ทั้งหมด
  ↓
เรียก PigInventoryHelper::reducePigInventory() สำหรับแต่ละ detail
  ↓
Update batch_pen_allocations (current_quantity -= qty)
Update batches (current_quantity -= qty)
  ↓
ยกเลิก Cost records (payment_status = 'ยกเลิก')
ยกเลิก CostPayment (status = 'rejected')
  ↓
Recalculate Profit (ลบ cost ที่ cancelled)
```

### 3. ขายหมู (PigSale)
```
User creates PigSale (e.g., 200 pigs)
  ↓
PigSaleController::store()
  ↓
สร้าง PigSale record (payment_status = 'รอชำระ')
ลด inventory: reducePigInventory() → current_quantity -= qty
  ↓
Admin approves sale
  ↓
PigSaleController::approve()
  ↓
ยอมรับการขาย + อนุมัติ
สร้าง Revenue record (payment_status = 'อนุมัติแล้ว')
Recalculate Profit
```

## Important Notes

### Max per Pen
- **1 Pen**: 38 หมู (Pig Capacity = 38)
- **1 Barn**: 20 Pens = 760 หมู
- **Farm**: 2 Barns = 1,520 หมู

### Multiple Entries Same Batch
เมื่อเพิ่มหมูหลายครั้ง (เข้าคนละครั้ง) ในเล้า-คอกเดียวกัน:
- ✓ ระบบจะ "stack" allocation ถูกต้อง
- ⚠️ `allocated_pigs` จะเพิ่มขึ้นเรื่อยๆ (tracking จำนวนสะสม)
- ✓ `current_quantity` track จำนวนปัจจุบันอย่างถูกต้อง
- ✓ PigEntryDetail เก็บจำนวนที่ entry นี้เพิ่มเท่านั้น

### Cancel Logic
เมื่อ cancel PigEntry จะ:
1. ✓ ลด inventory ตาม PigEntryDetail quantity
2. ✓ ยกเลิก Cost + CostPayment
3. ✓ Recalculate Profit (exclude cancelled costs)

## Calculation Examples

### Scenario: 4 PigEntry (1500 each) + 1 Sale
```
Initial:
  - total_pig_amount: 6000 (4 x 1500)
  - current_quantity: 6000

After Sale 200:
  - current_quantity: 5800

If Cancel Entry 1:
  - current_quantity: 4300
  - Cost removed from Profit calculation
  - Profit re-calculated
```

## Testing & Verification

Use scripts:
- `check_batch_pigs.php` - ดู PigEntry + Inventory balance
- `test_pig_entry_cancel.php` - ทดสอบ cancel flow
- `check_allocations.php` - ดู batch_pen_allocations state
- `check_total_pig_amount.php` - verify total_pig_amount sync
