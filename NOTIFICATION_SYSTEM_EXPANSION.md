# ขยายระบบแจ้งเตือนอัตโนมัติ

## 📅 วันที่: 14 ตุลาคม 2025

## 🎯 วัตถุประสงค์
ขยายระบบแจ้งเตือนให้ครอบคลุมเหตุการณ์สำคัญทั้งหมดในระบบ รวมถึง:
- 💀 หมูตาย (Pig Death)
- 💊 การรักษาหมูป่วย (Batch Treatment)
- 💰 การขายหมู (Pig Sale)
- 📦 การเพิ่มสินค้าเข้าคลัง (Inventory Movement)

---

## ✅ การเปลี่ยนแปลงที่ทำ

### 1. เพิ่ม Methods ใน NotificationHelper

**ไฟล์:** `app/Helpers/NotificationHelper.php`

#### 1.1 `notifyAdminsPigDeath($pigDeath, $reportedBy)`
แจ้งเตือน Admin เมื่อมีหมูตาย

**ข้อมูลที่แจ้ง:**
- จำนวนหมูที่ตาย
- รุ่น (Batch)
- เล้า (Barn)
- คอก (Pen)
- สาเหตุการตาย

**ประเภท:** `pig_death`  
**Icon:** 💀  
**URL:** `url('view_pig_death')`

---

#### 1.2 `notifyAdminsBatchTreatment($batchTreatment, $reportedBy)`
แจ้งเตือน Admin เมื่อมีการรักษาหมูป่วย

**ข้อมูลที่แจ้ง:**
- รุ่น (Batch)
- เล้า (Barn)
- คอก (Pen)
- ชื่อยา
- ปริมาณและหน่วย

**ประเภท:** `batch_treatment`  
**Icon:** 💊  
**URL:** `url('view_batch_treatment')`

---

#### 1.3 `notifyAdminsPigSale($pigSale, $reportedBy)`
แจ้งเตือน Admin เมื่อมีการขายหมู

**ข้อมูลที่แจ้ง:**
- จำนวนหมูที่ขาย
- รุ่น (Batch)
- ราคารวม
- วันที่ขาย

**ประเภท:** `pig_sale`  
**Icon:** 💰  
**URL:** `route('pig_sale.index')`

---

#### 1.4 `notifyAdminsInventoryMovement($inventoryMovement, $reportedBy)`
แจ้งเตือน Admin เมื่อมีการเคลื่อนไหวสินค้าในคลัง

**ข้อมูลที่แจ้ง:**
- ประเภทการเคลื่อนไหว (เพิ่ม/เบิก/ปรับปรุง)
- รหัสสินค้า
- ประเภทสินค้า
- จำนวนที่เปลี่ยนแปลง
- จำนวนคงเหลือ

**ประเภท:** `inventory_movement`  
**Icon:** 📥 / 📤 / 🔄  
**URL:** `route('inventory_movements.index')`

---

### 2. เพิ่มการแจ้งเตือนใน Controllers

#### 2.1 AdminController - หมูตาย
**ไฟล์:** `app/Http/Controllers/AdminController.php`

**Method:** `upload_pig_death()`

```php
// เพิ่ม import
use App\Helpers\NotificationHelper;

// หลังจาก save PigDeath
NotificationHelper::notifyAdminsPigDeath($data, auth()->user());
```

---

#### 2.2 AdminController - การรักษาหมูป่วย
**ไฟล์:** `app/Http/Controllers/AdminController.php`

**Method:** `upload_batch_treatment()`

```php
// หลังจาก save BatchTreatment
NotificationHelper::notifyAdminsBatchTreatment($data, auth()->user());
```

---

#### 2.3 PigSaleController - การขายหมู
**ไฟล์:** `app/Http/Controllers/PigSaleController.php`

**Method:** `create()`

```php
// เพิ่ม import
use App\Helpers\NotificationHelper;

// หลังจาก save PigSale และ PigSaleDetail
NotificationHelper::notifyAdminsPigSale($pigSale, auth()->user());
```

---

#### 2.4 StoreHouseController - เพิ่มสินค้าเข้าคลัง
**ไฟล์:** `app/Http/Controllers/StoreHouseController.php`

**Method:** `recordStorehouse()`

```php
// เพิ่ม import
use App\Helpers\NotificationHelper;

// หลังจาก create InventoryMovement
$inventoryMovement = InventoryMovement::create([...]);
NotificationHelper::notifyAdminsInventoryMovement($inventoryMovement, auth()->user());
```

---

## 🔧 คุณสมบัติพิเศษ

### Auto-load Relationships
ทุก method มีการตรวจสอบและโหลด relationships อัตโนมัติ:

```php
if (!$pigDeath->relationLoaded('batch')) {
    $pigDeath->load('batch', 'barn', 'pen');
}
```

**ประโยชน์:**
- ป้องกัน N+1 Query Problem
- ทำงานได้แม้ไม่ได้โหลด relationships ล่วงหน้า
- เพิ่มความยืดหยุ่นในการใช้งาน

---

## 📊 ประเภทการแจ้งเตือนทั้งหมด

| ประเภท | Icon | ชื่อเหตุการณ์ | URL |
|--------|------|---------------|-----|
| `user_registered` | 🆕 | ผู้ใช้ใหม่ลงทะเบียน | `/user-management` |
| `user_approved` | ✅ | บัญชีได้รับการอนุมัติ | `/dashboard` |
| `user_rejected` | ❌ | บัญชีถูกปฏิเสธ | - |
| `pig_death` | 💀 | รายงานหมูตาย | `/view_pig_death` |
| `batch_treatment` | 💊 | การรักษาหมูป่วย | `/view_batch_treatment` |
| `pig_sale` | 💰 | การขายหมู | `/pig-sale` |
| `inventory_movement` | 📥📤🔄 | การเคลื่อนไหวสินค้า | `/inventory-movements` |

---

## 🎨 รูปแบบข้อความแจ้งเตือน

### 1. หมูตาย
```
💀 รายงานหมูตาย

มีหมูตาย 5 ตัว
รุ่น: B001
เล้า: BR01
คอก: PN03
สาเหตุ: โรคระบาดทางเดินหายใจ
```

### 2. การรักษาหมูป่วย
```
💊 บันทึกการรักษาหมูป่วย

มีการบันทึกการรักษา
รุ่น: B001
เล้า: BR01
คอก: PN03
ยา: Amoxicillin
จำนวน: 500 ml
```

### 3. การขายหมู
```
💰 บันทึกการขายหมู

มีการขายหมู 50 ตัว
รุ่น: B001
ราคารวม: 250,000.00 บาท
วันที่ขาย: 2025-10-14
```

### 4. เพิ่มสินค้าเข้าคลัง
```
📥 เพิ่มสินค้าเข้าคลัง

รหัสสินค้า: FD001
ประเภท: feed
จำนวน: 1000 kg
คงเหลือ: 2500 kg
```

---

## 🔔 การทำงานของระบบ

### Flow การแจ้งเตือน

1. **เหตุการณ์เกิดขึ้น**
   - ผู้ใช้บันทึกหมูตาย/การรักษา/การขาย/เพิ่มสินค้า

2. **Controller ประมวลผล**
   - บันทึกข้อมูลลง Database
   - เรียก `NotificationHelper::notify...()`

3. **NotificationHelper สร้างการแจ้งเตือน**
   - หา Admin ทั้งหมด
   - Load relationships ที่จำเป็น
   - สร้าง Notification records

4. **Admin ได้รับการแจ้งเตือน**
   - แสดงใน Notification dropdown (Header)
   - แสดงใน Notification page
   - แสดง badge จำนวนที่ยังไม่ได้อ่าน

---

## 🧪 การทดสอบ

### Test Case 1: หมูตาย
1. ไปที่ "Add Pig Death"
2. เลือกฟาร์ม, รุ่น, เล้า, คอก
3. กรอกจำนวนหมูที่ตาย
4. บันทึก
5. ✅ Admin ต้องได้รับการแจ้งเตือนทันที

### Test Case 2: การรักษาหมูป่วย
1. ไปที่ "Add Batch Treatment"
2. เลือกฟาร์ม, รุ่น, เล้า, คอก
3. กรอกข้อมูลยาและปริมาณ
4. บันทึก
5. ✅ Admin ต้องได้รับการแจ้งเตือนทันที

### Test Case 3: การขายหมู
1. ไปที่ "Pig Sale"
2. เลือกรุ่นและคอก
3. กรอกข้อมูลการขาย
4. บันทึก
5. ✅ Admin ต้องได้รับการแจ้งเตือนทันที

### Test Case 4: เพิ่มสินค้าเข้าคลัง
1. ไปที่ "Store House Record"
2. เลือกฟาร์มและรุ่น
3. เพิ่มสินค้าใหม่หรืออัพเดทสต็อก
4. บันทึก
5. ✅ Admin ต้องได้รับการแจ้งเตือนทันที

---

## 📝 หมายเหตุสำคัญ

### 1. การแจ้งเตือนเฉพาะ Admin
ระบบจะแจ้งเตือนเฉพาะผู้ใช้ที่มี Role = `admin` เท่านั้น

```php
$admins = User::whereHas('roles', function ($query) {
    $query->where('name', 'admin');
})->get();
```

### 2. ผู้รายงาน (Reporter)
ผู้ที่ทำการบันทึกข้อมูลจะถูกเก็บเป็น `related_user_id` เพื่อระบุตัวตน

```php
'related_user_id' => $reportedBy->id
```

### 3. URL Navigation
การคลิกการแจ้งเตือนจะนำไปยังหน้าที่เกี่ยวข้อง:
- หมูตาย → View Pig Death
- การรักษา → View Batch Treatment
- การขาย → Pig Sale Index
- สินค้าคลัง → Inventory Movements

### 4. Real-time Updates
Notification dropdown ใน header มีการ auto-refresh ทุก 30 วินาที

---

## 🚀 ขั้นตอนการใช้งาน

### สำหรับ Admin:

1. **ดูการแจ้งเตือน**
   - คลิก 🔔 ที่ Header
   - ดู Badge จำนวนที่ยังไม่อ่าน

2. **อ่านรายละเอียด**
   - คลิกที่การแจ้งเตือน
   - ระบบจะ mark as read อัตโนมัติ
   - นำทางไปยังหน้าที่เกี่ยวข้อง

3. **จัดการการแจ้งเตือน**
   - ไปที่ "การแจ้งเตือน" ในเมนู
   - Mark all as read
   - Clear read notifications
   - ดูประวัติทั้งหมด

---

## 🔮 การพัฒนาในอนาคต

### ฟีเจอร์ที่อาจเพิ่มเติม:
- [ ] แจ้งเตือนผ่าน Email
- [ ] แจ้งเตือนผ่าน LINE Notify
- [ ] แจ้งเตือนแบบ Real-time (WebSocket)
- [ ] กำหนดระดับความสำคัญของการแจ้งเตือน
- [ ] แจ้งเตือนตามเงื่อนไข (เช่น หมูตายเกิน 10 ตัว)
- [ ] ตั้งค่าการแจ้งเตือนแบบ Custom
- [ ] รายงานสถิติการแจ้งเตือน

---

## 📞 การแก้ปัญหา

### ปัญหา: ไม่ได้รับการแจ้งเตือน
**วิธีแก้:**
1. ตรวจสอบว่าผู้ใช้มี Role = `admin`
2. ตรวจสอบว่ามีการเรียก `NotificationHelper` ใน Controller
3. ดู Laravel Log ที่ `storage/logs/laravel.log`

### ปัญหา: Error "Undefined property"
**วิธีแก้:**
- ตรวจสอบว่า field name ตรงกับ database
- ตรวจสอบ relationships ใน Model

### ปัญหา: Notification ซ้ำ
**วิธีแก้:**
- ตรวจสอบว่าไม่ได้เรียก `notify...()` หลายครั้ง
- ใช้ Database Transaction เพื่อป้องกัน

---

**สถานะ:** ✅ ใช้งานได้แล้ว - พร้อมทดสอบ!
