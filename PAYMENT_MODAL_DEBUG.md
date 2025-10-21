# Payment Modal Debugging Guide

## ปัญหา: กดปุ่มบันทึกในการชำระเงินไม่ได้

### ขั้นตอน Debug

#### 1. เปิด Browser Console
```
Press: F12 (หรือ Ctrl+Shift+I)
Go to: Console Tab
```

#### 2. ทดสอบการปรากฏตัว Modal
```javascript
// ในหน้า pig_entry_records ให้ run command นี้
document.querySelectorAll('[id^="paymentModal"]').length
// ควรแสดง หมายเลข > 0
```

#### 3. กดปุ่มบันทึก และดู Console Logs
เมื่อกดปุ่มบันทึก ควรเห็น logs:
```
✓ "Payment form submit triggered for modal: paymentModal{ID}"
✓ "Validation check: { paid_amount: ..., payment_method: ..., receipt_files: ... }"
✓ "Validation passed, allowing form submission" หรือ "Validation errors found: [...]"
```

#### 4. หากเห็น Validation Errors
- ✗ "จำนวนเงินต้องมากกว่า 0" → กรุณาเลือกจำนวนเงิน > 0
- ✗ "กรุณาเลือกวิธีชำระเงิน" → กรุณาคลิก dropdown และเลือกวิธีชำระเงิน
- ✗ "กรุณาอัปโหลดหลักฐาน" → กรุณาเลือกไฟล์ PDF หรือรูปภาพ

#### 5. ตรวจสอบ Form Element ที่ Hidden
```javascript
// ดูว่า hidden input มีค่าหรือไม่
document.getElementById('paymentMethod1').value  // เปลี่ยน 1 เป็น ID ของ record

// ควรแสดง "เงินสด" หรือ "โอนเงิน"
```

#### 6. ตรวจสอบ Dropdown Button Text
```javascript
// ดูว่า dropdown button แสดง text ถูกต้องหรือไม่
document.getElementById('paymentMethodDropdownBtn1').textContent
// ควรแสดง "เงินสด" หรือ "โอนเงิน" หรือ "-- เลือกวิธีชำระเงิน --"
```

#### 7. หาก Validation ผ่าน แต่ Form ยังไม่ submit
```javascript
// ตรวจสอบว่ามี JavaScript Error อื่น
// ดูใน Console Tab หาข้อความ error สีแดง

// ลอง submit form manually
document.querySelector('#paymentModal1 form').submit()
```

#### 8. ตรวจสอบ Network Request
```
1. กลับไป Console tab
2. คลิก Network tab
3. กดปุ่มบันทึก
4. ดูว่ามี request ส่งไป server หรือไม่
5. ถ้ามี request ให้ดูรายละเอียด Response code
   - 200 = Success
   - 422 = Validation Error
   - 500 = Server Error
```

## Common Issues & Solutions

### Issue 1: Modal ไม่ปรากฏ
**Symptom**: ไม่มี modal หรือ modal มั่ว
**Solution**:
- ตรวจสอบว่า loop `@foreach ($records as $record)` มีรายการ
- ตรวจสอบว่า `$record->id` มีค่า

### Issue 2: Dropdown ไม่ทำงาน
**Symptom**: คลิก dropdown แล้วไม่มีตัวเลือกปรากฏ
**Solution**:
- ลอง reload หน้า
- ตรวจสอบ `updatePaymentMethod()` function ใน Console:
  ```javascript
  typeof updatePaymentMethod  // ควรแสดง "function"
  ```

### Issue 3: File upload ไม่ทำงาน
**Symptom**: เลือกไฟล์แล้วแต่ไม่มี filename ปรากฏ
**Solution**:
- ตรวจสอบใน input element:
  ```javascript
  document.querySelector('input[name="receipt_file"]').files.length
  ```

### Issue 4: Form Submit แต่ Error ปรากฏ
**Symptom**: ทีสดมาแรก form submit แล้ว redirect หรือ snackbar error
**Solution**:
- ดูใน Console → Network ตรวจสอบ Response
- ถ้า 422: ดูว่า error message คือ อะไร
- ถ้า 500: ตรวจสอบ Laravel logs:
  ```bash
  tail -f storage/logs/laravel.log
  ```

## Authorization Check

ถ้าเห็น HTTP 403 (Forbidden) ให้ตรวจสอบ:
1. User คือ admin หรือไม่
2. ตรวจสอบ middleware ใน route (routes/web.php line 87)
3. ตรวจสอบ Policy/Authorization ใน Controller

ปัจจุบันไม่มี authorization check ใน `update_payment()` method ดังนั้นควรเป็น 200 หรือ 422

## Laravel Server Side Debug

หากต้องการดู log จาก server:
```bash
# Terminal 1: Run tail
tail -f storage/logs/laravel.log

# Terminal 2: Clear cache และ restart
php artisan config:clear
php artisan cache:clear
php artisan serve
```

## CSRF Token Check

ตรวจสอบว่า Form มี CSRF token:
```html
<!-- ควรมีบรรทัดนี้ใน form -->
@csrf
@method('PUT')
```

ถ้าหายไป → เพิ่มเข้าไป ด้านบน form element
