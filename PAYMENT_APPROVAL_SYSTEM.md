# ระบบการแจ้งเตือนและอนุมัติการชำระเงิน (Payment Notification & Approval System)

## ภาพรวม (Overview)

ระบบนี้ถูกออกแบบมาเพื่อให้เมื่อมีการบันทึกการชำระเงินในระบบ Pig Entry หรือ Pig Sale จะมีการส่งแจ้งเตือนไปให้ Admin เพื่อรอการอนุมัติ

## ส่วนประกอบหลัก (Components)

### 1. NotificationHelper (`app/Helpers/NotificationHelper.php`)

#### Methods ใหม่:

**a) `notifyAdminsPigEntryPaymentRecorded($pigEntryRecord, User $recordedBy)`**
- ส่งแจ้งเตือนไปให้ Admin ทุกคนเมื่อมีการบันทึกการชำระเงินการรับเข้าหมู
- บันทึกข้อมูลในตาราง `notifications` พร้อมกำหนดสถานะเป็น `pending`

**b) `notifyAdminsPigSalePaymentRecorded($pigSale, User $recordedBy)`**
- ส่งแจ้งเตือนไปให้ Admin ทุกคนเมื่อมีการบันทึกการชำระเงินการขายหมู
- บันทึกข้อมูลในตาราง `notifications` พร้อมกำหนดสถานะเป็น `pending`

### 2. PigEntryController Updates

#### Method: `update_payment(Request $request, $id)`
```php
// เมื่อมีการบันทึกการชำระเงิน:
// 1. บันทึก Cost record
// 2. เรียก NotificationHelper::notifyAdminsPigEntryPaymentRecorded()
// 3. ส่ง message: "บันทึกการชำระเงินเรียบร้อยแล้ว - รอ admin อนุมัติ"
```

### 3. PigSaleController Updates

#### Method: `uploadReceipt(Request $request, $id)`
```php
// เมื่อมีการบันทึกการชำระเงิน:
// 1. อัปเดท payment information
// 2. เรียก NotificationHelper::notifyAdminsPigSalePaymentRecorded()
// 3. ส่ง message พร้อมระบุว่า "รอ admin อนุมัติ"
```

### 4. PaymentApprovalController (ใหม่)

ไฟล์: `app/Http/Controllers/PaymentApprovalController.php`

#### Methods:

**a) `index()`**
- แสดงรายการการแจ้งเตือนการชำระเงินที่แบ่งตาม 3 สถานะ:
  - `pending` - รอการอนุมัติ
  - `approved` - อนุมัติแล้ว
  - `rejected` - ปฏิเสธแล้ว

**b) `approve(Request $request, $notificationId)`**
- Admin อนุมัติการชำระเงิน
- อัปเดท notification status เป็น `approved`
- บันทึกหมายเหตุ (ถ้ามี)

**c) `reject(Request $request, $notificationId)`**
- Admin ปฏิเสธการชำระเงิน
- อัปเดท notification status เป็น `rejected`
- บันทึกเหตุผลในการปฏิเสธ (จำเป็น)

**d) `detail($notificationId)`**
- แสดงรายละเอียดของการชำระเงินแต่ละรายการพร้อมข้อมูลที่เกี่ยวข้อง

### 5. Routes Configuration (`routes/web.php`)

```php
Route::prefix('payment_approvals')->middleware(['auth', 'prevent.cache'])->group(function () {
    Route::get('/', [PaymentApprovalController::class, 'index'])->name('payment_approvals.index');
    Route::get('/{notificationId}/detail', [PaymentApprovalController::class, 'detail'])->name('payment_approvals.detail');
    Route::post('/{notificationId}/approve', [PaymentApprovalController::class, 'approve'])->name('payment_approvals.approve');
    Route::post('/{notificationId}/reject', [PaymentApprovalController::class, 'reject'])->name('payment_approvals.reject');
});
```

### 6. Views

**a) `resources/views/admin/payment_approvals/index.blade.php`**
- หน้าแสดงรายการการแจ้งเตือนการชำระเงินทั้งหมด
- แบ่งการแสดงผลตามสถานะใน 3 tabs
- มี modal สำหรับอนุมัติ/ปฏิเสธ

**b) `resources/views/admin/payment_approvals/detail.blade.php`**
- แสดงรายละเอียดของการชำระเงินแต่ละรายการ
- แสดงข้อมูล Pig Entry หรือ Pig Sale ที่เกี่ยวข้อง
- ปุ่มอนุมัติ/ปฏิเสธสำหรับ Admin

## Database Migrations

ระบบนี้ใช้ migration ที่มีอยู่แล้ว: `2025_10_21_add_payment_approval_to_notifications.php`

### Notification Table Columns ที่ใช้:
- `type` - ประเภทการแจ้งเตือน ('payment_recorded_pig_entry', 'payment_recorded_pig_sale')
- `user_id` - Admin ที่รับแจ้งเตือน
- `related_user_id` - ผู้บันทึกการชำระเงิน
- `title` - หัวข้อการแจ้งเตือน
- `message` - ข้อความการแจ้งเตือน
- `url` - URL ไปยังหน้าการอนุมัติ
- `related_model` - ชนิดของ model ('PigEntryRecord' หรือ 'PigSale')
- `related_model_id` - ID ของ PigEntryRecord หรือ PigSale
- `approval_status` - สถานะการอนุมัติ ('pending', 'approved', 'rejected')
- `approval_notes` - หมายเหตุ/เหตุผล

## การไหลของข้อมูล (Data Flow)

### สำหรับ Pig Entry Payment:
```
1. ผู้บันทึก → บันทึกการชำระเงิน (Pig Entry Payment Modal)
2. PigEntryController::update_payment() 
3. → บันทึก Cost record
4. → เรียก NotificationHelper::notifyAdminsPigEntryPaymentRecorded()
5. → สร้าง Notification records สำหรับ Admin ทั้งหมด (status: pending)
6. → Admin ได้รับแจ้งเตือน
7. Admin → PaymentApprovalController::approve() หรือ reject()
8. → อัปเดท notification status
```

### สำหรับ Pig Sale Payment:
```
1. ผู้บันทึก → บันทึกการชำระเงิน (Pig Sale Payment Modal)
2. PigSaleController::uploadReceipt()
3. → อัปเดท Pig Sale payment info
4. → เรียก NotificationHelper::notifyAdminsPigSalePaymentRecorded()
5. → สร้าง Notification records สำหรับ Admin ทั้งหมด (status: pending)
6. → Admin ได้รับแจ้งเตือน
7. Admin → PaymentApprovalController::approve() หรือ reject()
8. → อัปเดท notification status
```

## Access Point

### สำหรับ Admin:
- Navigate to: `/payment_approvals`
- ดูรายการแจ้งเตือนการชำระเงินที่รอการอนุมัติ
- คลิก "ดู" เพื่อดูรายละเอียด
- คลิก "อนุมัติ" หรือ "ปฏิเสธ" ตามต้องการ

## Future Enhancements

1. **Email Notifications**: เพิ่มการส่ง Email ไปให้ Admin เมื่อมีการบันทึกการชำระเงิน
2. **SMS Notifications**: ส่ง SMS ไปให้ Admin เมื่อเกินเวลาที่ตั้งไว้
3. **Approval Workflow**: เพิ่มขั้นตอนการอนุมัติหลายชั้น (หลายคน)
4. **Automatic Approval**: อนุมัติโดยอัตโนมัติหลังจาก X ชั่วโมง ถ้าไม่มีใครปฏิเสธ
5. **Payment Status Tracking**: แสดงสถานะการอนุมัติในหน้า Pig Entry/Sale
6. **Report Generation**: สร้าง report ของการชำระเงินที่อนุมัติแล้ว

## Testing Checklist

- [ ] ทดสอบบันทึกการชำระเงิน Pig Entry → แจ้งเตือนไปให้ Admin
- [ ] ทดสอบบันทึกการชำระเงิน Pig Sale → แจ้งเตือนไปให้ Admin
- [ ] ทดสอบ Admin อนุมัติการชำระเงิน
- [ ] ทดสอบ Admin ปฏิเสธการชำระเงิน
- [ ] ทดสอบแสดงรายละเอียดของการชำระเงิน
- [ ] ทดสอบการ filter notification ตามสถานะ
- [ ] ทดสอบการเปลี่ยนแปลง message หลังจากอนุมัติ/ปฏิเสธ
- [ ] ทดสอบ pagination ของรายการแจ้งเตือน
