# 🔧 แก้ไขชื่อคอลัมน์ในระบบ

## สรุปการแก้ไข
เนื่องจากตารางจริงในฐานข้อมูลใช้ชื่อคอลัมน์ `allocated_pigs` ไม่ใช่ `pig_amount`
จึงต้องแก้โค้ดทั้งหมดให้ตรงกับโครงสร้างจริง

---

## 📊 ตารางที่เกี่ยวข้อง

### **1. batch_pen_allocations**
```
id                    bigint      PK
batch_id              bigint      FK → batches
barn_id               bigint      FK → barns
pen_id                bigint      FK → pens
allocated_pigs        int         ← ชื่อที่ถูกต้อง (ไม่ใช่ pig_amount)
current_quantity      int         ← เพิ่มใหม่
note                  text
created_at            timestamp
updated_at            timestamp
```

### **2. batches**
```
id                    bigint      PK
farm_id               bigint      FK → farms
batch_code            varchar
total_pig_weight      decimal
total_pig_amount      int         ← จำนวนเริ่มต้น
current_quantity      int         ← เพิ่มใหม่ (จำนวนปัจจุบัน)
total_pig_price       decimal
total_pig_death       int
status                varchar
note                  text
start_date            date
end_date              date
created_at            timestamp
updated_at            timestamp
```

---

## 🔨 ไฟล์ที่แก้ไขแล้ว

### ✅ 1. **app/Models/BatchPenAllocation.php**
```php
protected $fillable = [
    'batch_id',
    'barn_id',
    'pen_id',
    'allocated_pigs',      // ← เปลี่ยนจาก pig_amount
    'current_quantity',    // ← เพิ่มใหม่
    'move_date',
    'note',
];
```

### ✅ 2. **app/Models/Batch.php**
```php
protected $fillable = [
    'farm_id',
    'batch_code',
    'total_pig_weight',
    'total_pig_amount',
    'current_quantity',    // ← เพิ่มใหม่
    'total_pig_price',
    'total_pig_death',
    'status',
    'note',
    'start_date',
    'end_date'
];
```

### ✅ 3. **app/Helpers/PigInventoryHelper.php**
แก้ทุก reference จาก `pig_amount` เป็น `allocated_pigs`:

**addPigs() method:**
```php
// เก็บค่าเดิม
$oldAllocatedPigs = $allocation->allocated_pigs;
$oldCurrentQuantity = $allocation->current_quantity ?? $oldAllocatedPigs;

// อัปเดต
$allocation->allocated_pigs = $oldAllocatedPigs + $quantity;
$allocation->current_quantity = $oldCurrentQuantity + $quantity;

// สร้างใหม่
BatchPenAllocation::create([
    'allocated_pigs'   => $quantity,
    'current_quantity' => $quantity,
]);
```

**reducePigInventory() method:**
```php
$currentQuantity = $allocation->current_quantity ?? $allocation->allocated_pigs;
```

**increasePigInventory() method:**
```php
$currentQuantity = $allocation->current_quantity ?? $allocation->allocated_pigs;
$originalQuantity = $allocation->allocated_pigs;
```

**getPigsByBatch() method:**
```php
$currentQuantity = $allocation->current_quantity ?? $allocation->allocated_pigs;
'original_quantity' => $allocation->allocated_pigs,
```

**getAvailablePigs() method:**
```php
return $allocation->current_quantity ?? $allocation->allocated_pigs;
```

**getBatchInventorySummary() method:**
```php
$original = $allocation->allocated_pigs;
$current = $allocation->current_quantity ?? $allocation->allocated_pigs;
```

### ✅ 4. **app/Http/Controllers/PigEntryController.php**
```php
$allocatedInPen = DB::table('batch_pen_allocations')
    ->where('pen_id', $pen->id)
    ->sum('allocated_pigs');  // ← เปลี่ยนจาก pig_amount
```

---

## 🎯 สรุปการใช้งาน

### **allocated_pigs**
- จำนวนหมูที่จัดสรร**เริ่มต้น**ในเล้า-คอก
- ค่านี้**ไม่เปลี่ยน**หลังจากสร้าง
- ใช้เป็น "ฐาน" เปรียบเทียบกับ current_quantity

### **current_quantity (batch_pen_allocations)**
- จำนวนหมูที่มีอยู่**จริงปัจจุบัน**ในเล้า-คอก
- ค่านี้**เปลี่ยนได้**เมื่อขาย/ตาย/คัดทิ้ง
- ถ้ายังไม่มีค่า ให้ fallback ไปใช้ allocated_pigs

### **current_quantity (batches)**
- จำนวนหมูที่มีอยู่**จริงปัจจุบัน**ในรุ่น
- ค่านี้**เปลี่ยนได้**เมื่อขาย/ตาย/คัดทิ้ง
- ถ้ายังไม่มีค่า ให้ fallback ไปใช้ total_pig_amount

---

## 📝 ตัวอย่างการใช้งาน

### บันทึกหมูเข้า (PigEntry):
```php
// สร้าง batch_pen_allocation ใหม่
allocated_pigs: 100      // จำนวนเริ่มต้น
current_quantity: 100    // จำนวนปัจจุบัน (เท่ากับเริ่มต้น)
```

### ขายหมู 30 ตัว (PigSell):
```php
// ลด current_quantity
allocated_pigs: 100      // ไม่เปลี่ยน
current_quantity: 70     // ลดลง (100 - 30)
```

### ยกเลิกการขาย (Cancel):
```php
// เพิ่ม current_quantity กลับ
allocated_pigs: 100      // ไม่เปลี่ยน
current_quantity: 100    // เพิ่มขึ้น (70 + 30)
```

### ตรวจสอบจำนวน:
```php
// Helper จะเช็ค current_quantity ก่อน ถ้าไม่มีถึงใช้ allocated_pigs
$current = $allocation->current_quantity ?? $allocation->allocated_pigs;
```

---

## ✅ สิ่งที่ทำแล้ว

1. ✅ อัปเดต Migration ให้ใช้ `allocated_pigs` แทน `pig_amount`
2. ✅ แก้ Model BatchPenAllocation fillable
3. ✅ แก้ Model Batch เพิ่ม current_quantity
4. ✅ แก้ PigInventoryHelper ทุก method
5. ✅ แก้ PigEntryController ที่ใช้ sum()
6. ✅ Run Migration เรียบร้อย

---

## ⚠️ สิ่งที่ต้องระวัง

1. **ห้ามแก้ allocated_pigs** หลังจากสร้างแล้ว (ยกเว้นเพิ่มหมูเข้าเล้าเดิม)
2. **แก้เฉพาะ current_quantity** เมื่อมีการขาย/ตาย/คัดทิ้ง
3. **ใช้ Helper เสมอ** ห้ามแก้โดยตรงเพื่อป้องกัน Race Condition
4. **ตรวจสอบ current_quantity** ก่อนลดหมู (Helper จะเช็คให้อัตโนมัติ)

---

**วันที่อัปเดต:** 12 ตุลาคม 2025
**ผู้อัปเดต:** GitHub Copilot
**เวอร์ชัน:** 1.0
