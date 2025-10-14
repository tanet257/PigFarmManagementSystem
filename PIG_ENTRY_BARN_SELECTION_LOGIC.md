# 🐷 PigEntry: allocated_pigs vs current_quantity

## ❓ คำถาม
**"ตอนเลือกเล้าใน PigEntry ควรดูจาก `allocated_pigs` หรือ `current_quantity`?"**

---

## 📊 เปรียบเทียบ 2 วิธี

### **1. ใช้ `allocated_pigs` (วิธีเดิม - ผิด!)**

```php
$allocated = DB::table('batch_pen_allocations')
    ->where('barn_id', $barn->id)
    ->sum('allocated_pigs');

$barn->remaining = $barn->pig_capacity - $allocated;
```

#### ปัญหา:
```
สถานการณ์จริง:
- เล้า A มีความจุ 200 ตัว
- รุ่น 001: allocated_pigs = 100, current_quantity = 30 (ขายไป 70 ตัว)
- รุ่น 002: allocated_pigs = 50, current_quantity = 50

คำนวณด้วย allocated_pigs:
remaining = 200 - (100 + 50) = 50 ตัว ❌

จริงๆ มีหมูอยู่:
occupied = 30 + 50 = 80 ตัว
remaining จริง = 200 - 80 = 120 ตัว ✅
```

**ผลกระทบ:**
- ❌ บอกว่าเล้าเต็ม (มีที่ว่าง 50) แต่จริงๆ มีที่ว่าง 120 ตัว
- ❌ ไม่ให้เลือกเล้าที่ยังใช้งานได้
- ❌ ต้องสร้างเล้าใหม่ทั้งที่เล้าเก่ายังไม่เต็ม

---

### **2. ใช้ `current_quantity` (วิธีใหม่ - ถูกต้อง!)**

```php
$currentOccupied = DB::table('batch_pen_allocations')
    ->where('barn_id', $barn->id)
    ->sum(DB::raw('COALESCE(current_quantity, allocated_pigs)'));

$barn->remaining = $barn->pig_capacity - $currentOccupied;
```

#### ข้อดี:
```
สถานการณ์เดียวกัน:
- เล้า A มีความจุ 200 ตัว
- รุ่น 001: current_quantity = 30 (ขายไป 70)
- รุ่น 002: current_quantity = 50

คำนวณด้วย current_quantity:
occupied = 30 + 50 = 80 ตัว
remaining = 200 - 80 = 120 ตัว ✅
```

**ผลลัพธ์:**
- ✅ แสดงที่ว่างจริงของเล้า
- ✅ ใช้พื้นที่ได้เต็มประสิทธิภาพ
- ✅ ตรงกับสถานการณ์จริงในฟาร์ม

---

## 🎯 สรุปการใช้งาน

### **allocated_pigs**
- 📌 **ความหมาย:** จำนวนหมูที่จัดสรรเข้าเล้า**ตอนเริ่มต้น**
- 📌 **คงที่:** ไม่เปลี่ยนแปลง (ยกเว้นเพิ่มหมูเข้าเล้าเดิม)
- 📌 **ใช้เมื่อ:** ต้องการดูจำนวนหมูที่เคยมีทั้งหมด
- ❌ **ไม่ควรใช้:** คำนวณที่ว่างในเล้า

### **current_quantity**
- 📌 **ความหมาย:** จำนวนหมูที่มีอยู่**จริงปัจจุบัน**
- 📌 **เปลี่ยนได้:** ลดเมื่อขาย/ตาย, เพิ่มเมื่อยกเลิกการขาย
- 📌 **ใช้เมื่อ:** ต้องการดูจำนวนหมูที่มีจริงตอนนี้
- ✅ **ควรใช้:** คำนวณที่ว่างในเล้า

---

## 🔄 ตัวอย่างการใช้งานจริง

### **วันที่ 1: รับหมูเข้า**
```
เล้า A (capacity: 200)
- รุ่น 001: รับหมู 150 ตัว
  allocated_pigs = 150
  current_quantity = 150

ที่ว่างในเล้า = 200 - 150 = 50 ตัว ✅
```

### **วันที่ 30: ขายหมูไป**
```
เล้า A (capacity: 200)
- รุ่น 001: ขายไป 100 ตัว
  allocated_pigs = 150 (ไม่เปลี่ยน)
  current_quantity = 50 (ลดลง)

ที่ว่างในเล้า (ใช้ allocated_pigs):
= 200 - 150 = 50 ตัว ❌ ผิด!

ที่ว่างในเล้า (ใช้ current_quantity):
= 200 - 50 = 150 ตัว ✅ ถูก!
```

### **วันที่ 31: รับหมูรุ่นใหม่**
```
เล้า A (capacity: 200)
- รุ่น 001: current_quantity = 50
- รุ่น 002: รับหมูเข้า 100 ตัว (ใช้ที่ว่าง 150)
  allocated_pigs = 100
  current_quantity = 100

ที่ว่างในเล้า (current_quantity):
= 200 - (50 + 100) = 50 ตัว ✅
```

---

## 💡 Code ที่แก้ไขแล้ว

### **ก่อนแก้ (ผิด):**
```php
$allocated = DB::table('batch_pen_allocations')
    ->where('barn_id', $barn->id)
    ->sum('allocated_pigs');  // ← ใช้ allocated_pigs

$barn->remaining = $barn->pig_capacity - $allocated;
```

### **หลังแก้ (ถูก):**
```php
$currentOccupied = DB::table('batch_pen_allocations')
    ->where('barn_id', $barn->id)
    ->sum(DB::raw('COALESCE(current_quantity, allocated_pigs)'));  // ← ใช้ current_quantity

$barn->remaining = $barn->pig_capacity - $currentOccupied;
```

**หมายเหตุ:** ใช้ `COALESCE(current_quantity, allocated_pigs)` เพราะ:
- ถ้ามี `current_quantity` → ใช้ค่านี้ (ข้อมูลล่าสุด)
- ถ้าไม่มี `current_quantity` (NULL) → fallback ไปใช้ `allocated_pigs` (ข้อมูลเก่า)

---

## 🎯 คำตอบคำถาม

**"เหมาะสมกับการใช้จริงมั้ย?"**

❌ **ไม่เหมาะสม!** ถ้าใช้ `allocated_pigs`
- เล้าจะ "เต็ม" เร็วกว่าความจริง
- ไม่สามารถใช้พื้นที่ที่ว่างได้เต็มที่
- ต้องสร้างเล้าใหม่ทั้งที่เล้าเก่ายังมีที่

✅ **เหมาะสม!** ถ้าใช้ `current_quantity`
- แสดงที่ว่างจริงของเล้า
- ใช้พื้นที่ได้เต็มประสิทธิภาพ
- ตรงกับการทำงานจริงในฟาร์ม

---

## 📝 สรุป

| เกณฑ์ | allocated_pigs | current_quantity |
|-------|----------------|------------------|
| ใช้คำนวณที่ว่างในเล้า | ❌ ไม่เหมาะสม | ✅ เหมาะสม |
| สะท้อนสถานการณ์จริง | ❌ ไม่ตรง | ✅ ตรง |
| ประสิทธิภาพการใช้พื้นที่ | ❌ ต่ำ | ✅ สูง |
| ความซับซ้อน | ✅ ง่าย | ✅ ง่าย (เหมือนกัน) |

---

**การแก้ไขนี้สำคัญมาก!** เพราะมันส่งผลต่อ:
1. การจัดการพื้นที่เล้าอย่างมีประสิทธิภาพ
2. ต้นทุนการสร้างเล้าใหม่ที่ไม่จำเป็น
3. ความถูกต้องของข้อมูลในระบบ

---

**วันที่อัปเดต:** 12 ตุลาคม 2025  
**ผู้อัปเดต:** GitHub Copilot  
**เวอร์ชัน:** 1.0
