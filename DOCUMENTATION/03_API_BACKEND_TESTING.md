# 03 API Design, Backend Development, and Testing

## 3.5 ออกแบบ API

### 3.5.1 หลักการออกแบบ API
การออกแบบ API (Application Programming Interface) สำหรับระบบจัดการฟาร์มหมูใช้หลักการ RESTful API ที่เป็นมาตรฐานในการพัฒนา Web Service โดยใช้ HTTP Methods ที่เหมาะสมสำหรับแต่ละการดำเนินการ ได้แก่ GET สำหรับการดึงข้อมูล POST สำหรับการสร้างข้อมูลใหม่ PUT/PATCH สำหรับการแก้ไขข้อมูล และ DELETE สำหรับการลบข้อมูล การออกแบบ API ยึดหลัก CRUD Operations (Create, Read, Update, Delete) และมีการจัดการ Authentication ด้วย JWT (JSON Web Token) หรือ Laravel Sanctum Token เพื่อความปลอดภัยในการเข้าถึงข้อมูล

### 3.5.2 โครงสร้าง URL และ Endpoint
API ได้รับการออกแบบให้มีโครงสร้าง URL ที่ชัดเจนและเป็นระเบียบ โดยใช้ prefix /api สำหรับ endpoint ทั้งหมด และจัดกลุ่มตาม resource ต่างๆ ดังนี้

- /api/auth สำหรับการยืนยันตัวตน (login, register, logout)
- /api/farms สำหรับจัดการข้อมูลฟาร์ม
- /api/batches สำหรับจัดการรุ่นหมู
- /api/dairy-records สำหรับบันทึกข้อมูลประจำวัน
- /api/costs สำหรับจัดการต้นทุน
- /api/pig-sales สำหรับจัดการการขายหมู
- /api/revenue สำหรับจัดการรายได้
- /api/profit สำหรับดูผลกำไร
- /api/inventory หรือ /api/storehouse สำหรับจัดการสินค้าคงคลัง
- /api/users สำหรับจัดการผู้ใช้งาน
- /api/notifications สำหรับจัดการการแจ้งเตือน
- /api/dashboard สำหรับข้อมูล Dashboard
- /api/reports สำหรับรายงานต่างๆ

### 3.5.3 การจัดการ Authentication และ Authorization
ระบบใช้ Laravel Sanctum Token หรือ JWT Token สำหรับการยืนยันตัวตนผู้ใช้งาน โดย API /api/auth/login จะทำการตรวจสอบ email และ password แล้วส่ง Bearer Token กลับไปให้ client นำไปใช้ในการเข้าถึง endpoint อื่นๆ Token จะมีอายุตามการตั้งค่า (เช่น 24 ชั่วโมง) และมีข้อมูล user_id, username, farm_id และ role ฝังอยู่ภายใน

การ Authorization จะตรวจสอบให้แน่ใจว่า
- ผู้ใช้มี token ที่ valid
- ผู้ใช้สามารถเข้าถึงเฉพาะข้อมูลของฟาร์มตนเองเท่านั้น ผ่านการตรวจสอบ farm_id
- ผู้ใช้มีสิทธิ์ตามบทบาท (Admin, Staff, Manager) สำหรับแต่ละ endpoint
- Middleware ตรวจสอบ permissions ก่อนอนุญาตให้เข้าถึง endpoint

### 3.5.4 รูปแบบการส่งและรับข้อมูล
API ใช้รูปแบบ JSON (JavaScript Object Notation) ในการส่งและรับข้อมูลทั้งหมด โดย Response จะมีโครงสร้างที่สม่ำเสมอ

สำหรับการดึงข้อมูล (GET) จะส่งข้อมูลใน JSON Array หรือ Object กลับไป พร้อมกับ pagination info (ถ้ามี)

```json
{
  "success": true,
  "data": [...],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7
  }
}
```

สำหรับการสร้าง แก้ไข หรือลบ จะส่ง message แจ้งสถานะการทำงานและข้อมูลที่เกี่ยวข้อง

```json
{
  "success": true,
  "message": "บันทึกสำเร็จ",
  "data": { "id": 1 }
}
```

ในกรณีที่เกิดข้อผิดพลาด จะส่ง error message พร้อม HTTP Status Code ที่เหมาะสม

```json
{
  "success": false,
  "message": "Error message here",
  "errors": { "field_name": ["error detail"] }
}
```

HTTP Status Codes ที่ใช้
- 200 OK สำหรับสำเร็จ
- 201 Created สำหรับสร้างข้อมูลสำเร็จ
- 400 Bad Request สำหรับข้อมูลไม่ถูกต้อง
- 401 Unauthorized สำหรับไม่ได้รับอนุญาต
- 403 Forbidden สำหรับถูกห้าม
- 404 Not Found สำหรับไม่พบข้อมูล
- 422 Unprocessable Entity สำหรับการ validate ข้อมูลล้มเหลว
- 500 Internal Server Error สำหรับข้อผิดพลาดของเซิร์ฟเวอร์

### 3.5.5 การจัดการ CORS และความปลอดภัย
API มีการกำหนด CORS (Cross-Origin Resource Sharing) เพื่ออนุญาตให้ Frontend ที่รันบน localhost:3000 (development) หรือ domain ผลิตภัณฑ์ (production) สามารถเรียกใช้ API ได้

นอกจากนี้ยังมี
- การเข้ารหัสรหัสผ่านด้วย bcrypt ก่อนบันทึกลงฐานข้อมูล
- การตรวจสอบสิทธิ์การเข้าถึงในทุก endpoint ที่ต้องการ Authentication
- การใช้ Environment Variables สำหรับการตั้งค่าที่สำคัญ เช่น JWT Secret Key, Database credentials เพื่อความปลอดภัยในการใช้งานจริง
- Rate limiting เพื่อป้องกันการใช้ API แบบ abuse
- HTTPS required สำหรับการส่งข้อมูลที่ปลอดภัย

### 3.5.6 การจัดการข้อผิดพลาดและ Error Handling
ระบบมีการจัดการข้อผิดพลาดอย่างครอบคลุม โดย
- ใช้ try-catch block ในทุก endpoint เพื่อจัดการ exception ที่อาจเกิดขึ้น
- มีการส่ง error message ที่เป็นประโยชน์กลับไปยัง client พร้อมกับ HTTP Status Code ที่เหมาะสม
- การทำงานกับฐานข้อมูลมี transaction rollback ในกรณีที่เกิดข้อผิดพลาดระหว่างการสร้างข้อมูลที่เกี่ยวข้องกันหลายตาราง
- มีการ validate ข้อมูลก่อนประมวลผลเพื่อป้องกันข้อมูลที่ไม่ถูกต้องหรือไม่ครบถ้วนเข้าสู่ระบบ
- Logging ของข้อผิดพลาดสำหรับการแก้ไขปัญหา (debugging)

---

## ตารางที่ 3.11 API Endpoints - Authentication and Core

| URL Path | HTTP Method | รายละเอียดการทำงาน | พารามิเตอร์ | รหัสตอบกลับ |
|----------|------------|-------------------|-----------|-----------|
| /api/auth/login | POST | เข้าสู่ระบบ | email, password | 200, 401, 422 |
| /api/auth/register | POST | สมัครสมาชิก | name, email, password, role | 201, 400, 422 |
| /api/auth/logout | POST | ออกจากระบบ | - (Bearer token required) | 200 |
| /api/auth/me | GET | ดึงข้อมูลผู้ใช้ปัจจุบัน | - (Bearer token required) | 200, 401 |
| /api/farms | GET | ดึงรายการฟาร์มทั้งหมด | - | 200 |
| /api/farms | POST | เพิ่มฟาร์มใหม่ | farm_name, owner_name, location, phone, email | 201, 400, 422 |
| /api/farms/:id | GET | ดึงข้อมูลฟาร์มเดี่ยว | - | 200, 404 |
| /api/farms/:id | PUT | แก้ไขข้อมูลฟาร์ม | farm_name, owner_name, location, phone, email | 200, 404, 422 |
| /api/farms/:id | DELETE | ลบฟาร์ม | - | 200, 404 |

## ตารางที่ 3.12 API Endpoints - Batch Management

| URL Path | HTTP Method | รายละเอียดการทำงาน | พารามิเตอร์ | รหัสตอบกลับ |
|----------|------------|-------------------|-----------|-----------|
| /api/batches | GET | ดึงรายการรุ่นหมูทั้งหมด | farm_id, status (query) | 200 |
| /api/batches | POST | เพิ่มรุ่นหมูใหม่ | farm_id, batch_code, start_date, expected_end, initial_quantity, starting_weight, target_weight | 201, 400, 422 |
| /api/batches/:id | GET | ดึงข้อมูลรุ่นหมูเดี่ยว | - | 200, 404 |
| /api/batches/:id | PUT | แก้ไขข้อมูลรุ่นหมู | batch_code, expected_end, target_weight, status | 200, 404, 422 |
| /api/batches/:id | DELETE | ลบรุ่นหมู | - | 200, 404 |
| /api/batches/:id/close | POST | ปิดรุ่นหมู | - | 200, 404 |
| /api/batches/:id/metrics | GET | ดึง KPI ของรุ่นหมู | - | 200, 404 |

## ตารางที่ 3.13 API Endpoints - Daily Records and Treatment

| URL Path | HTTP Method | รายละเอียดการทำงาน | พารามิเตอร์ | รหัสตอบกลับ |
|----------|------------|-------------------|-----------|-----------|
| /api/dairy-records | GET | ดึงรายการบันทึกประจำวัน | batch_id, start_date, end_date (query) | 200 |
| /api/dairy-records | POST | บันทึกข้อมูลประจำวัน | batch_id, record_date, quantity_pigs, avg_weight_per_pig, feed_consumed_kg, sick_count, dead_count, health_notes | 201, 400, 422 |
| /api/dairy-records/:id | GET | ดึงบันทึกเดี่ยว | - | 200, 404 |
| /api/dairy-records/:id | PUT | แก้ไขบันทึกประจำวัน | record_date, quantity_pigs, avg_weight_per_pig, feed_consumed_kg, sick_count, dead_count | 200, 404, 422 |
| /api/dairy-records/:id | DELETE | ลบบันทึก | - | 200, 404 |
| /api/batch-treatments | GET | ดึงรายการรักษา | batch_id (query) | 200 |
| /api/batch-treatments | POST | เพิ่มบันทึกการรักษา | batch_id, treatment_date, treatment_type, quantity_treated, medicine_name, dosage, result, notes | 201, 400, 422 |
| /api/batch-treatments/:id | PUT | แก้ไขข้อมูลการรักษา | treatment_date, treatment_type, quantity_treated, medicine_name, dosage, result | 200, 404, 422 |
| /api/batch-treatments/:id | DELETE | ลบบันทึกการรักษา | - | 200, 404 |

## ตารางที่ 3.14 API Endpoints - Costs and Approvals

| URL Path | HTTP Method | รายละเอียดการทำงาน | พารามิเตอร์ | รหัสตอบกลับ |
|----------|------------|-------------------|-----------|-----------|
| /api/costs | GET | ดึงรายการต้นทุนทั้งหมด | batch_id, status, cost_type (query) | 200 |
| /api/costs | POST | เพิ่มต้นทุนใหม่ | farm_id, batch_id, cost_type, item_code, quantity, unit, price_per_unit, amount, total_price, receipt_file, note, date | 201, 400, 422 |
| /api/costs/:id | GET | ดึงต้นทุนเดี่ยว | - | 200, 404 |
| /api/costs/:id | PUT | แก้ไขข้อมูลต้นทุน | cost_type, item_code, quantity, unit, price_per_unit, amount, total_price, note, date | 200, 404, 422 |
| /api/costs/:id | DELETE | ลบต้นทุน | - | 200, 404 |
| /api/costs/:id/approve | POST | อนุมัติต้นทุน | - (Bearer token required, Manager/Admin) | 200, 404, 403 |
| /api/costs/:id/reject | POST | ปฏิเสธต้นทุน | reason (optional) | 200, 404, 403 |
| /api/cost-payments | GET | ดึงรายการจ่ายต้นทุน | cost_id, status (query) | 200 |
| /api/cost-payments | POST | เพิ่มการจ่ายต้นทุน | cost_id, amount, payment_date, payment_method, reference_number, bank_name, receipt_file, notes | 201, 400, 422 |

## ตารางที่ 3.15 API Endpoints - Sales and Revenue

| URL Path | HTTP Method | รายละเอียดการทำงาน | พารามิเตอร์ | รหัสตอบกลับ |
|----------|------------|-------------------|-----------|-----------|
| /api/pig-sales | GET | ดึงรายการขายทั้งหมด | batch_id, status, payment_status (query) | 200 |
| /api/pig-sales | POST | สร้างรายการขายใหม่ | batch_id, farm_id, customer_id, sale_number, date, quantity, total_weight, actual_weight, price_per_kg, price_per_pig, total_price, shipping_cost, net_total, payment_method, note | 201, 400, 422 |
| /api/pig-sales/:id | GET | ดึงรายการขายเดี่ยว | - | 200, 404 |
| /api/pig-sales/:id | PUT | แก้ไขข้อมูลการขาย | date, quantity, total_weight, actual_weight, price_per_kg, price_per_pig, total_price, shipping_cost, note | 200, 404, 422 |
| /api/pig-sales/:id/approve | POST | อนุมัติการขาย | - (Bearer token required, Manager/Admin) | 200, 404, 403 |
| /api/pig-sales/:id/reject | POST | ปฏิเสธการขาย | reason (optional) | 200, 404, 403 |
| /api/pig-sales/:id | DELETE | ลบรายการขาย | - | 200, 404 |
| /api/revenue | GET | ดึงรายได้ทั้งหมด | batch_id, status (query) | 200 |
| /api/revenue/:id | GET | ดึงรายได้เดี่ยว | - | 200, 404 |

## ตารางที่ 3.16 API Endpoints - Inventory and Profit

| URL Path | HTTP Method | รายละเอียดการทำงาน | พารามิเตอร์ | รหัสตอบกลับ |
|----------|------------|-------------------|-----------|-----------|
| /api/storehouse | GET | ดึงรายการสินค้าคงคลังทั้งหมด | farm_id, item_type (query) | 200 |
| /api/storehouse | POST | เพิ่มสินค้าใหม่ | farm_id, item_code, item_name, item_type, quantity, min_quantity, unit_price, supplier, status | 201, 400, 422 |
| /api/storehouse/:id | PUT | แก้ไขข้อมูลสินค้า | item_name, item_type, quantity, min_quantity, unit_price, supplier, status | 200, 404, 422 |
| /api/storehouse/:id | DELETE | ลบสินค้า | - | 200, 404 |
| /api/inventory-movement | GET | ดึงประวัติการเคลื่อนไหวสินค้า | storehouse_id, change_type, date_range (query) | 200 |
| /api/inventory-movement | POST | บันทึกการเคลื่อนไหวสินค้า | batch_id, storehouse_id, date, change_type (in/out), quantity_changed, cost_per_unit, total_cost, reason | 201, 400, 422 |
| /api/profit | GET | ดึงผลกำไรทั้งหมด | batch_id, farm_id (query) | 200 |
| /api/profit/:id | GET | ดึงผลกำไรของรุ่นเดี่ยว | - | 200, 404 |

## ตารางที่ 3.17 API Endpoints - Notifications and Dashboard

| URL Path | HTTP Method | รายละเอียดการทำงาน | พารามิเตอร์ | รหัสตอบกลับ |
|----------|------------|-------------------|-----------|-----------|
| /api/notifications | GET | ดึงการแจ้งเตือน | is_read (query, true/false) | 200 |
| /api/notifications/:id/read | POST | ทำเครื่องหมายการแจ้งเตือนว่าอ่านแล้ว | - | 200, 404 |
| /api/notifications/mark-all-read | POST | ทำเครื่องหมายทั้งหมดว่าอ่านแล้ว | - | 200 |
| /api/dashboard | GET | ดึงข้อมูล Dashboard | farm_id, period (week/month/year query) | 200, 422 |
| /api/dashboard/kpi | GET | ดึง KPI metrics | batch_id (query) | 200, 422 |
| /api/reports/financial | GET | ดึงรายงานการเงิน | start_date, end_date, farm_id (query) | 200, 422 |
| /api/reports/batch-performance | GET | ดึงรายงานประสิทธิภาพรุ่น | batch_id (query) | 200, 404 |
| /api/reports/export | POST | ส่งออกรายงาน | type (pdf/csv), report_type, start_date, end_date | 200, 400, 422 |

---

## 3.6 พัฒนาระบบฝั่งเซิร์ฟเวอร์

### 3.6.1 การพัฒนาระบบฝั่งเซิร์ฟเวอร์ (Backend)
การพัฒนาระบบฝั่งเซิร์ฟเวอร์สำหรับระบบจัดการฟาร์มหมูใช้ Laravel Framework 9.x ซึ่งเป็นฟ่รมเวิร์กที่มีความรัดกุมและรองรับ API development ได้ดี

ขั้นตอนการติดตั้ง

1. ติดตั้ง PHP 8.1 และ Composer
2. สร้างโปรเจกต์ Laravel: composer create-project laravel/laravel pig-farm-management
3. ติดตั้ง dependencies ที่จำเป็น
   - laravel/framework 9.19
   - laravel/sanctum 3.0 สำหรับ API authentication
   - laravel/jetstream 3.0 สำหรับ user management
   - livewire/livewire 2.11 สำหรับ interactive UI
   - barryvdh/laravel-dompdf 2.2 สำหรับ PDF export
   - cloudinary-labs/cloudinary-laravel 2.1 สำหรับ image upload
   - doctrine/dbal 3.10 สำหรับ database migration tools
   - guzzlehttp/guzzle 7.2

โครงสร้างของระบบฝั่งเซิร์ฟเวอร์

Models (app/Models/):
- Farm, Batch, BatchMetric, BatchPenAllocation, BatchTreatment
- Cost, CostPayment, Customer, DailyTreatmentLog
- DairyRecord, DairyRecordItem, DairyStorehouseUse, DairyTreatment
- InventoryMovement, Notification, Payment, Pen, Permission
- PigDeath, PigEntryDetail, PigEntryRecord, PigSale, PigSaleDetail
- Profit, ProfitDetail, Revenue, Role, StoreHouse, StoreHouseAuditLog, User

Controllers (app/Http/Controllers/):
- AdminController, BatchController, BatchEntryController, BatchPenAllocationController, BatchTreatmentController
- CostPaymentApprovalController, DailyTreatmentLogController, DairyController, DairyTreatmentController
- InventoryMovementController, NotificationController, PaymentApprovalController, PaymentController
- PigEntryController, PigPriceController, PigSaleController, ProfitController, StoreHouseController
- TreatmentController, UserApprovalController, UserManagementController

Helpers (app/Helpers/):
- BatchRestoreHelper - บูรณะข้อมูลรุ่นหมู
- BatchTreatmentHelper - จัดการข้อมูลการรักษา
- NotificationHelper - ส่งการแจ้งเตือน
- PaymentApprovalHelper - อนุมัติการจ่าย
- PigInventoryHelper - จัดการสินค้าคงคลัง
- RevenueHelper - คำนวณรายได้
- StoreHouseHelper - จัดการโกดัง

Observers (app/Observers/):
- CostObserver - ตรวจสอบและอนุมัติต้นทุนอัตโนมัติตามกฎ
- InventoryMovementObserver - คำนวณ KPI เมื่อมีการเคลื่อนไหวสินค้า
- PigDeathObserver - บันทึกอัตราการตายของหมูและส่ง alert

Services (app/Services/):
- PaymentService - จัดการการชำระเงิน
- RevenueService - คำนวณรายได้จากการขาย
- และอื่นๆ

Middleware (app/Http/Middleware/):
- Authentication middleware ตรวจสอบ user session
- Authorization middleware ตรวจสอบ permissions และ roles
- CORS middleware จัดการ cross-origin requests

Routes (routes/web.php):
- ใช้ Laravel Routing system
- Group routes by prefix (admin, staff, manager)
- Protected routes ด้วย auth middleware

ระบบ Authentication และ Authorization

- ใช้ Laravel Jetstream สำหรับ user authentication
- ใช้ Laravel Sanctum สำหรับ API tokens (ถ้ามี API endpoint)
- รหัสผ่านผู้ใช้ถูกเข้ารหัสด้วย bcrypt ก่อนบันทึก
- ระบบ roles: Admin (11 permissions), Manager (7 permissions), Staff (6 permissions)
- Database permissions table เก็บรายการสิทธิ์ของแต่ละ role
- Middleware ตรวจสอบ permissions ก่อนให้เข้าถึง routes

### 3.6.2 การพัฒนาระบบฝั่งไคลเอนต์ (Frontend)
การพัฒนาระบบฝั่งไคลเอนต์ใช้ Blade Template (Laravel Templating Engine) เป็นหลัก พร้อมกับ Bootstrap 5 สำหรับ styling และ Vanilla JavaScript/AJAX สำหรับ interactivity

ขั้นตอนการตั้งค่า

1. ติดตั้ง Node.js 16 และสูงกว่า
2. ติดตั้ง dependencies ที่จำเป็น
   - axios 1.1.2 สำหรับ AJAX requests
   - bootstrap-icons 1.13.1 สำหรับ icons
   - flatpickr 4.6.13 สำหรับ date picker
   - tailwindcss 3.4.17 สำหรับ utility CSS
   - vite 4.0.0 สำหรับ asset bundling
   - laravel-vite-plugin 0.7.2 สำหรับ Laravel integration
   - lodash 4.17.19 สำหรับ utility functions
   - alpinejs 3.0.6 สำหรับ lightweight interactivity

โครงสร้างของ Frontend

resources/views/:
├── layouts/
│   └── app.blade.php (main layout)
├── admin/
│   ├── dashboard.blade.php (admin dashboard)
│   ├── body.blade.php (page content)
│   ├── header.blade.php (navigation header)
│   ├── sidebar.blade.php (left sidebar menu)
│   ├── css.blade.php (custom styles)
│   ├── js.blade.php (custom scripts)
│   ├── batch/
│   ├── costs/
│   ├── dairy_records/
│   ├── pig_sales/
│   ├── storehouses/
│   ├── dashboard/
│   ├── notifications/
│   └── อื่นๆ
├── auth/ (login, register pages)
├── components/ (reusable Blade components)
└── emails/ (email templates)

resources/css/:
- app.css (main stylesheet)
- bootstrap customization

resources/js/:
- app.js (main JavaScript entry point)
- bootstrap.js (Axios configuration)

หน้าหลัก (Pages)

Admin Dashboard Pages:
- Dashboard: แสดงข้อมูล KPI, profit summary, recent activities
- Batch Management: สร้าง, ดู, แก้ไข, ลบ รุ่นหมู
- Daily Records: ดูข้อมูลประจำวัน บันทึกข้อมูล
- Cost Management: บันทึกต้นทุน อนุมัติ/ปฏิเสธ
- Pig Sales: บันทึกการขาย อนุมัติ/ปฏิเสธ
- Inventory/Storehouse: จัดการสินค้า ดูประวัติการเคลื่อนไหว
- Reports: ดูรายงานทางการเงิน ส่งออกข้อมูล
- User Management: จัดการผู้ใช้ กำหนด roles
- Notifications: ดูการแจ้งเตือน อ่านข้อความ

Staff Pages:
- Daily Entry: บันทึกข้อมูลประจำวัน (fast entry form)
- Sale Entry: บันทึกการขายหมู
- Treatment Entry: บันทึกการรักษาหมู
- Notifications: ดูการแจ้งเตือนที่เกี่ยวข้อง

### 3.6.3 การเชื่อมต่อระหว่าง Frontend และ Backend ด้วย AJAX
การส่งข้อมูลระหว่าง Frontend และ Backend ใช้ Axios ร่วมกับ AJAX calls

ไฟล์ resources/js/bootstrap.js

```javascript
import axios from 'axios';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Setup CSRF token
const token = document.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

// Handle 401 errors
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);
```

ตัวอย่าง AJAX Call จาก Blade Template:

```javascript
// บันทึกข้อมูลประจำวัน
document.getElementById('saveDailyRecord').addEventListener('click', function() {
    const formData = new FormData(document.getElementById('dailyRecordForm'));
    
    axios.post('/dairy-records', Object.fromEntries(formData))
        .then(response => {
            alert('บันทึกสำเร็จ');
            location.reload();
        })
        .catch(error => {
            alert('บันทึกไม่สำเร็จ: ' + error.response.data.message);
        });
});

// ดึงรายการต้นทุนที่รอการอนุมัติ
axios.get('/costs?status=pending')
    .then(response => {
        const costs = response.data;
        // render table
    });

// อนุมัติต้นทุน
function approveCost(costId) {
    axios.post(`/costs/${costId}/approve`)
        .then(() => {
            alert('อนุมัติสำเร็จ');
            location.reload();
        });
}
```

### 3.6.4 Models, Helpers และ Observers ในระบบ

Models:
- ทุก model มี relationships ที่ชัดเจน
- ตัวอย่าง: Batch model มี relationships กับ DairyRecord, Cost, PigSale
- Models มี protected $fillable array เพื่อ mass assignment
- Timestamps (created_at, updated_at) ใช้ automatic

Observers:
- CostObserver: เมื่อ Cost สร้างใหม่ จะ auto-check กฎการอนุมัติ
  - ถ้าเป็นเครื่องมือ/วัสดุ สามารถอนุมัติเองได้
  - ถ้าเป็นเศษ/ยา ต้องรอ Manager อนุมัติ
- InventoryMovementObserver: เมื่อมีการเคลื่อนไหวสินค้า
  - คำนวณ KPI ของ batch
  - บันทึกประวัติสินค้า
- PigDeathObserver: เมื่อมีการบันทึกการตายของหมู
  - อัพเดตจำนวนหมูในรุ่น
  - คำนวณอัตราการตาย
  - ส่ง alert ถ้าอัตราสูง

Helpers:
- NotificationHelper: ส่งการแจ้งเตือนไปยัง users ที่เกี่ยวข้อง
- RevenueHelper: คำนวณรายได้รวม profit ของแต่ละ batch
- StoreHouseHelper: ตรวจสอบ stock levels และส่ง alert ถ้าต่ำกว่า minimum
- PaymentApprovalHelper: ประมวลผล workflow อนุมัติการจ่าย
- PigInventoryHelper: จัดการ inventory transactions

### 3.6.5 การทำงานของระบบสำหรับพนักงาน (Staff)

Daily Entry Flow:

1. พนักงานเข้าสู่ระบบและไปหน้า Daily Entry
2. ระบบแสดงรายการ Batch ที่กำลังทำงาน (status = active)
3. พนักงานเลือก Batch
4. ระบบดึงข้อมูล DailyRecord ล่าสุดแบบ AJAX และแสดงค่า default
5. พนักงานกรอกข้อมูลใหม่ (average weight, feed consumed, dead count)
6. JavaScript validate ข้อมูล (client-side):
   - weight > 0
   - dead_count <= current_quantity
   - date >= batch start_date
7. พนักงานกดปุ่ม "Save"
8. JavaScript ส่ง POST request ผ่าน Axios ไปที่ route /dairy-records
9. Backend (DairyController) ประมวลผล:
   - Validate ข้อมูลอีกครั้ง (server-side)
   - Insert ลงตาราง dairy_records
   - Trigger InventoryMovementObserver (ถ้ามี)
   - Trigger PigDeathObserver (ถ้า dead_count > 0)
   - Observer auto-send notification ถ้าอัตราการตายสูง
   - Return JSON response
10. Frontend แสดง success message และ refresh ข้อมูล

Sale Entry Flow:

1. พนักงานไปหน้า Sale Entry
2. เลือก Batch ที่จะขาย ระบบ AJAX fetch ข้อมูล current quantity, avg weight
3. กรอกข้อมูล (quantity, total_weight, price_per_kg)
4. JavaScript auto-calculate total_price = quantity * price_per_kg
5. เลือก customer จาก dropdown (loaded ด้วย AJAX)
6. กดปุ่ม "Submit Sale"
7. Frontend ส่ง POST ไปที่ /pig-sales
8. Backend (PigSaleController) ทำการ:
   - Validate ข้อมูล
   - Insert pig_sales record
   - Trigger Observer สร้าง Revenue record
   - Trigger Observer สร้าง Payment record (status=pending)
   - NotificationHelper ส่ง notification ให้ Manager รอการอนุมัติ
   - Return sale_number และ success message
9. Frontend แสดง Sale Number พร้อม confirm button

### 3.6.6 การทำงานของระบบสำหรับเจ้าของฟาร์ม (Owner/Manager)

Dashboard Flow:

1. Manager เข้าสู่ระบบหน้า Dashboard
2. ระบบ AJAX fetch ข้อมูลจาก backend endpoint
3. Backend (AdminController) aggregate ข้อมูล:
   - Total revenue จากทุก batch ในเดือนนี้
   - Total cost รวม
   - Gross profit = revenue - cost
   - KPI metrics (ADG, FCR, mortality rate)
   - Recent activities/transactions
4. Frontend render:
   - KPI cards แสดงตัวเลขใหญ่
   - Charts: profit trend, batch status breakdown
   - Recent transactions table
5. Manager สามารถ click เพื่อ drill-down ไปยัง batch detail

Cost Approval Workflow:

1. Manager เปิดหน้า "Pending Approvals"
2. Frontend AJAX fetch /costs?status=pending
3. Backend ส่ง list ต้นทุนที่รอการอนุมัติ
4. Frontend render table พร้อม approve/reject buttons
5. Manager review รายละเอียด cost
6. Manager click approve หรือ reject
7. Frontend ส่ง POST /costs/{id}/approve หรือ POST /costs/{id}/reject
8. Backend (CostPaymentApprovalController):
   - Update cost status
   - ถ้า approve: Trigger CostObserver สร้าง cost_payment record
   - Trigger Observer recalculate profit ของ batch
   - NotificationHelper ส่ง notification ให้ staff ว่าได้รับอนุมัติ
9. Frontend refresh table ลบรายการที่ approve/reject ไป

Reports Export:

1. Manager ไปหน้า Reports
2. เลือก report type (financial, batch performance, inventory)
3. เลือก date range ด้วย Flatpickr date picker
4. กดปุ่ม "Generate Report"
5. Frontend ส่ง POST /reports/generate
6. Backend aggregate ข้อมูลตามเงื่อนไข
7. ส่ง data กลับในรูป JSON
8. Frontend render charts (ถ้ามี JavaScript chart library)
9. Manager สามารถ export เป็น PDF ได้ ผ่าน Laravel DomPDF

### 3.6.7 การจัดการ Error และ User Experience

Backend Error Handling:

- ทุก controller method ครอบด้วย try-catch block
- ส่ง JSON response พร้อม HTTP status code
- ตัวอย่าง: 
  ```php
  try {
      // logic
  } catch (Exception $e) {
      return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
  }
  ```
- Log ข้อผิดพลาดไปยัง storage/logs

Frontend Error Handling:

- Axios interceptor catch errors
- แสดง alert หรือ toast notification ให้ผู้ใช้ทราบ
- Auto-redirect ไปยัง login ถ้า 401 error
- Loading spinner บน buttons ระหว่าง processing
- Disable buttons ระหว่าง submission

User Experience Features:

- Responsive design: Bootstrap grid system
- Modal dialogs สำหรับ confirmation/forms
- Form validation: client-side ด้วย HTML5 + JavaScript, server-side ด้วย Laravel Validation
- Toast notifications สำหรับ success/error messages
- Loading indicators สำหรับ async operations
- Pagination สำหรับ large datasets
- Search/filter functionality บนทุก list pages
- Export buttons (PDF/Excel)
- Breadcrumb navigation

---

## 3.7 ทดสอบความถูกต้องของระบบ

### การทดสอบ Unit Testing
- ทดสอบแต่ละ API endpoint ด้วยเครื่องมือทดสอบ API เช่น Postman หรือ Thunder Client
- ตรวจสอบการรับส่งข้อมูล JSON แบบถูกต้อง
- ตรวจสอบ HTTP response codes ว่าถูกต้องตามสถานการณ์
- ทดสอบ validation logic ว่าปฏิเสธข้อมูลที่ไม่ถูกต้อง

ตัวอย่าง test cases

- Create Batch: POST /api/batches with valid data returns 201 with batch data
- Create Batch: POST /api/batches with invalid data (duplicate batch_code) returns 422 with error
- Get Batch: GET /api/batches/999 (not exist) returns 404
- Update Batch: PUT /api/batches/:id returns 200 with updated data
- Unauthorized access: GET /api/admin-only without token returns 401

### การทดสอบ Integration Testing
- ทดสอบการทำงานร่วมกันของ Frontend และ Backend
- ทดสอบ authentication flow: login get token use token on protected routes
- ทดสอบ approval workflow: create cost (pending) approve cost_payment created profit recalculated
- ทดสอบ notification triggers: create high-mortality record notification created
- ทดสอบ data consistency: create sale revenue created profit updated

### การทดสอบฐานข้อมูล
- ใช้ DBeaver เพื่อตรวจสอบการ insert, update, delete ข้อมูล
- ตรวจสอบ foreign key constraints ทำงานถูกต้อง
- ตรวจสอบ data integrity (e.g., revenue plus cost equals profit)
- ตรวจสอบ indexes ทำงานและ query performance ดี
- ทดสอบ cascade delete logic

### การทดสอบ User Interface
- ทดสอบบนเบราว์เซอร์หลัก: Chrome, Edge, Safari
- ทดสอบทั้งบนคอมพิวเตอร์ (desktop) และมือถือ (mobile)
- ตรวจสอบ responsive design ในแต่ละหน้า
- ทดสอบ form validation และ error messages
- ทดสอบ pagination, sorting, filtering
- ทดสอบ accessibility (color contrast, keyboard navigation)

### การทดสอบ Security
- ทดสอบ JWT token: expired token should be rejected
- ทดสอบ permission: staff should not access admin-only pages
- ทดสอบ password encryption: saved password should be bcrypt hashed
- ทดสอบ CORS: request from unauthorized origin should be rejected
- ทดสอบ SQL injection protection: special characters in input should be escaped
- ทดสอบ XSS protection: HTML/JS in input should be sanitized

### การทดสอบ Performance
- ใช้ browser DevTools เพื่อวัด page load time
- ทดสอบ Dashboard load time: target less than 2 seconds สำหรับ 10k plus rows aggregated
- ทดสอบ API response time: target less than 500ms ต่อ request
- ทดสอบด้วย large datasets: 10,000 plus batches, 100,000 plus daily records
- ทดสอบ concurrent users: พร้อมกัน 5-10 users
- ตรวจสอบ database query optimization ว่ามีการใช้ indexes อย่างเหมาะสม

### การทดสอบ Error Handling
- ทดสอบการตัดการเชื่อมต่อฐานข้อมูล: system should show error gracefully
- ทดสอบการสิ้นสุดของ API server: client should show timeout error
- ทดสอบการส่ง invalid data: system should return validation errors
- ทดสอบ transaction rollback: ถ้าเกิดข้อผิดพลาดระหว่างการสร้าง cost และ cost_payment
- ทดสอบ email/notification delivery failures

### ผลการทดสอบและการปรับปรุง

ผลการทดสอบพบว่า

- ระบบสามารถทำงานได้ตามที่กำหนดไว้
- มีความเสถียรในการใช้งาน
- สามารถจัดการข้อมูลได้อย่างถูกต้องแม่นยำ
- API response time อยู่ในเกณฑ์ที่ยอมรับได้
- Role-based access control ทำงานถูกต้อง
- Notifications ถูกส่งตรงเวลา

ข้อบกพร่องที่พบระหว่างการทดสอบ

- Dashboard performance ต้องเพิ่ม database caching (Redis)
- KPI calculation ช้าสำหรับ large batches ปรับเป็น background job
- Notification delivery ช้า ใช้ queue system (Laravel Queue)

การปรับปรุง

1. เพิ่ม Redis caching สำหรับ Dashboard data
2. ย้าย KPI calculation ไปเป็น background job (Laravel Jobs plus Queue)
3. ใช้ Laravel Queue สำหรับ notification delivery
4. เพิ่ม database indexes ที่ยังขาด
5. Implement API rate limiting

หลังจากปรับปรุงตามข้อเสนอแนะ ระบบพร้อมใช้งานจริง

---

## 3.8 จัดทำรูปเล่มปัญหาพิเศษ

การจัดทำรูปเล่มปัญหาพิเศษเป็นขั้นตอนสุดท้ายของการดำเนินงาน โดยใช้ระยะเวลาในการพัฒนาและจัดทำเอกสารทั้งสิ้น 5 เดือน ตั้งแต่เดือนกรกฎาคม ถึงเดือนพฤศจิกายน พ.ศ. 2568 ในระหว่างช่วงเวลาดังกล่าวได้ดำเนินการรวบรวมข้อมูล วิเคราะห์ผลการพัฒนา และจัดเรียงเนื้อหาให้เป็นไปตามรูปแบบของรายงานปัญหาพิเศษ

### ผลลัพธ์ที่สำคัญของการพัฒนา

จากการดำเนินงานตลอด 5 เดือนที่ผ่านมา ได้ผลลัพธ์ที่สำคัญหลายประการ ได้แก่

ระบบจัดการฟาร์มหมูแบบครบวงจรที่ทำงานได้อย่างสมบูรณ์ ประกอบด้วย
- ส่วน Frontend ที่พัฒนาด้วย Laravel Blade Template พร้อม Bootstrap 5 styling
- ส่วน Backend ที่พัฒนาด้วย Laravel Framework 9 ด้วย PHP 8.1
- ระบบ Authentication ใช้ Laravel Jetstream และ Livewire

ระบบสามารถปฏิบัติการได้ดังนี้
- บันทึกข้อมูลหมูประจำวัน (daily records) แบบเรียลไทม์
- จัดการรุ่นหมู (batches) ด้วย KPI metrics ที่คำนวณอัตโนมัติ
- ระบบบันทึกต้นทุนและอนุมัติโดยอัตโนมัติตามกฎ
- ระบบจัดการการขายหมู พร้อมการคำนวณ profit และรายได้
- ระบบจัดการสินค้าคงคลัง (storehouse/inventory)
- ระบบแจ้งเตือนแบบ real-time เมื่อมีปัญหา
- ระบบรายงานทางการเงินที่ครอบคลุม

ฐานข้อมูลที่ออกแบบอย่างมีประสิทธิภาพ ประกอบด้วย
- 30+ ตารางหลักที่มีความสัมพันธ์กันอย่างเหมาะสม
- 25+ foreign key relationships
- 40+ database indexes สำหรับ query optimization
- ระบบ Cascade Delete ที่ปลอดภัย

API ที่ออกแบบตาม RESTful principles จำนวน 58 endpoints ที่ครอบคลุม
- Authentication and Core operations (9 endpoints)
- Batch Management (7 endpoints)
- Daily Records and Treatment (9 endpoints)
- Costs and Approvals (9 endpoints)
- Sales and Revenue (9 endpoints)
- Inventory and Profit (8 endpoints)
- Notifications and Dashboard (8 endpoints)

เอกสารทางเทคนิคที่สมบูรณ์ รวมถึง
- Workflow diagrams และ process documentation
- ER diagrams ที่แสดงความสัมพันธ์ของ database
- Hierarchical Task Analysis (HTA) สำหรับทุก major processes
- Data dictionary พร้อมการอธิบาย enum values
- API routes documentation
- Project structure documentation
- Troubleshooting guide
- การวิเคราะห์ benefits, problems, และ roles comparison

### ขอบเขตของรูปเล่มปัญหาพิเศษ

การจัดทำรูปเล่มปัญหาพิเศษครอบคลุมเนื้อหา ดังนี้

1. บทที่ 1: บทนำ
   - วัตถุประสงค์ของการพัฒนา
   - ปัญหาและความต้องการ
   - ขอบเขตของโปรเจกต์

2. บทที่ 2: ระเบียบวิธีและการศึกษาเอกสาร
   - การศึกษาปัญหาการจัดการฟาร์มหมูแบบดั้งเดิม
   - การศึกษาเทคโนโลยี (Laravel, PHP, MySQL, Bootstrap)
   - การวิเคราะห์ความต้องการระบบ
   - การกำหนด requirements

3. บทที่ 3: การออกแบบและการพัฒนา
   - การออกแบบสถาปัตยกรรมระบบ
   - ER diagram ของฐานข้อมูล
   - Workflow diagrams ของ major processes
   - API design ตาม RESTful principles
   - Backend development: Models, Controllers, Services, Helpers, Observers
   - Frontend development: Blade Templates, Bootstrap layouts, AJAX integration
   - Authentication และ Authorization system

4. บทที่ 4: ขั้นตอนการติดตั้งและปรับใช้
   - ข้อกำหนดของ hardware/software
   - ขั้นตอนการติดตั้ง PHP, MySQL, Composer, Node.js
   - ขั้นตอนการตั้งค่าฐานข้อมูล
   - ขั้นตอนการรันระบบ development/production

5. บทที่ 5: การทดสอบและการประเมินผล
   - Unit testing ของ API endpoints
   - Integration testing ของ Frontend-Backend
   - Database testing
   - User Interface testing
   - Security testing
   - Performance testing
   - ผลการทดสอบและการปรับปรุง

6. บทที่ 6: คู่มือการใช้งาน
   - คู่มือสำหรับ Admin users
   - คู่มือสำหรับ Staff users
   - คู่มือสำหรับ Manager users
   - การทำงานของ major features

7. บทที่ 7: สรุปและข้อเสนอแนะ
   - สรุปผลลัพธ์ของการพัฒนา
   - ประเมินระดับความสำเร็จตามวัตถุประสงค์
   - ปัญหาและข้อเสนอแนะสำหรับการพัฒนาเพิ่มเติม
   - ทิศทางในการพัฒนาในอนาคต

### เนื้อหาเอกสารที่มีอยู่

เอกสารชุดนี้ได้จัดเรียงเนื้อหาให้ครบถ้วนตามโครงสร้างรูปเล่มปัญหาพิเศษ ประกอบด้วย

ส่วนแรก (Introduction):
- บทนำ
- วัตถุประสงค์
- ปัญหาและแนวทางแก้ไข
- ขอบเขตของการศึกษา

ส่วนที่สอง (Literature Review):
- สภาพการจัดการฟาร์มหมูในปัจจุบัน
- การศึกษางานวิจัยที่เกี่ยวข้อง
- การศึกษาเทคโนโลยี web application
- การศึกษา database design

ส่วนที่สาม (System Development):
- 3.1 User Analysis - ผู้ใช้งานระบบและความต้องการ
- 3.2 Requirements - ความต้องการการทำงาน (functional) และไม่ใช่การทำงาน (non-functional)
- 3.3 UX/UI Design - การออกแบบ user experience และ interface
- 3.4 QA and Test Cases - กรณีการทดสอบ
- 3.5 API Design - ออกแบบ API endpoints ตาม RESTful
- 3.6 Backend/Frontend Development - รายละเอียดการพัฒนา
- 3.7 Testing Procedures - ขั้นตอนการทดสอบ
- 3.8 Special Issue Documentation - รูปเล่มปัญหาพิเศษ

### ลักษณะของรูปเล่มปัญหาพิเศษ

รูปเล่มปัญหาพิเศษนี้เป็นเอกสารที่สะท้อนถึงความสำเร็จของการพัฒนาระบบจัดการฟาร์มหมู ดังนี้

1. ความครบถ้วน
   - ครอบคลุมทุกขั้นตอนตั้งแต่ปัญหา ความต้องการ ออกแบบ พัฒนา ทดสอบ ถึงการใช้งาน
   - มีตัวอย่างรูปภาพ แผนภาพ และสกรีนชอต
   - มีคู่มือการใช้งานรายละเอียด

2. ความเป็นระบบ
   - ปฏิบัติตามหลักการพัฒนา software engineering
   - ใช้ design patterns ที่เหมาะสม (MVC, Observer pattern, Helper pattern)
   - ใช้ best practices ของ Laravel Framework

3. ความเป็นปฏิบัติการ
   - ระบบพร้อมใช้งานจริงสำหรับฟาร์มหมู
   - มีการบันทึก daily data ได้อย่างถูกต้อง
   - มีการอนุมัติและบันทึกต้นทุนเชื่อถือได้
   - มีการคำนวณ profit และรายได้ที่แม่นยำ

4. ความปลอดภัยและเสถียรภาพ
   - ระบบ Authentication และ Authorization ที่มั่นคง
   - Data validation ทั้งฝั่ง client และ server
   - Transaction handling เพื่อ data consistency
   - Error handling ที่ครบถ้วน

5. ความสะดวกในการใช้งาน
   - User Interface ที่เรียบง่ายและใช้งานได้ง่าย
   - AJAX integration เพื่อให้ responsive
   - Real-time notifications เมื่อมีเหตุการณ์สำคัญ
   - Reports ที่ให้ข้อมูลที่มีประโยชน์

### ทิศทางการพัฒนาในอนาคต

นอกจากความสำเร็จในการพัฒนาระบบแล้ว ยังมีพื้นที่สำหรับการปรับปรุงเพิ่มเติม ดังนี้

1. การเพิ่มคุณสมบัติ
   - ระบบพยากรณ์ราคาหมูโดยใช้ machine learning
   - ระบบ mobile application สำหรับบันทึกข้อมูลนอกสถานที่
   - ระบบ IoT สำหรับติดตามสภาพแวดล้อมในคอก
   - ระบบ e-commerce สำหรับการขายผ่านออนไลน์

2. การปรับปรุง performance
   - Caching layer (Redis) สำหรับ dashboard
   - Background job processing สำหรับ report generation
   - Database query optimization
   - Frontend asset optimization

3. การเพิ่มความมั่นคง
   - SSL/HTTPS implementation
   - Two-factor authentication
   - Audit logging ของทุก transaction
   - Backup and disaster recovery

4. การปรับปรุง UX
   - Dark mode support
   - Multi-language support (Thai, English)
   - Advanced search และ filtering
   - Custom dashboard widgets

### สรุป

ระบบจัดการฟาร์มหมูที่ได้รับการพัฒนา เป็นแวดวรรค์ที่สามารถนำไปใช้งานจริงได้อย่างมีประสิทธิภาพ ด้วยการออกแบบที่ดี การพัฒนาตามหลัก software engineering และการทดสอบที่ครบถ้วน ระบบนี้สามารถช่วยให้ผู้จัดการฟาร์มหมูทำการตัดสินใจทางธุรกิจได้อย่างถูกต้องและรวดเร็ว ด้วยข้อมูลที่เชื่อถือได้และทันเวลา

---

เอกสารนี้อธิบายการจัดทำรูปเล่มปัญหาพิเศษสำหรับระบบจัดการฟาร์มหมู แบบครบถ้วนและพร้อมใช้งาน

---

## บทที่ 4: ขั้นตอนการใช้งานระบบ

### 4.1 ฟังก์ชันที่ใช้ได้สำหรับผู้ใช้แต่ละประเภท

#### 4.1.1 ฟังก์ชันของ User (Staff/Operator)

Authentication:
- Login (เข้าสู่ระบบด้วย email และ password)
- Logout (ออกจากระบบ)
- Forgot Password (ขอรีเซ็ตรหัสผ่าน)

Daily Operations:
- บันทึกข้อมูลประจำวัน (Daily Records)
- บันทึกการรักษาหมู (Treatment Records)
- บันทึกการขายหมู (Pig Sales)
- ดูการแจ้งเตือน (View Notifications)
- ดูข้อมูลประจำวัน (View Daily Data)

#### 4.1.2 ฟังก์ชันของ Admin (Administrator)

Authentication:
- Login (เข้าสู่ระบบด้วย email และ password)
- Logout (ออกจากระบบ)
- Forgot Password (ขอรีเซ็ตรหัสผ่าน)

Management Functions:
- จัดการผู้ใช้ (Create, Edit, Delete users)
- จัดการ Roles และ Permissions
- จัดการฟาร์ม (Farms)
- จัดการรุ่นหมู (Batches)
- ดูและอนุมัติต้นทุน (Approve/Reject Costs)
- ดูและอนุมัติการขาย (Approve/Reject Sales)
- จัดการสินค้าคงคลัง (Inventory Management)
- ดู Dashboard และ Reports
- ตั้งค่าระบบ (System Configuration)
- ดูการแจ้งเตือน (View Notifications)

### 4.3 ข้อจำกัดที่พบ

#### 4.3.1 ระบบไม่สามารถทำงานแบบออฟไลน์ได้
ระบบจัดการฟาร์มหมูในปัจจุบันต้องการการเชื่อมต่ออินเทอร์เน็ตอย่างต่อเนื่องเพื่อดำเนินการทั้งหมด หากการเชื่อมต่อขาดหายไป ผู้ใช้จะไม่สามารถบันทึกข้อมูลหรือเข้าถึงข้อมูลจากฐานข้อมูลได้

#### 4.3.2 ไม่มีระบบ upload และจัดเก็บไฟล์รูปภาพภายในระบบ
ระบบยังไม่มีความสามารถในการอัพโหลดและจัดเก็บรูปภาพของหมูหรือเอกสารต่างๆ ทำให้ไม่สามารถใช้รูปภาพเพื่อประเมินสุขภาพและคุณภาพของสัตว์ได้

#### 4.3.3 ไม่มีระบบจัดการสมาชิกลูกค้าและการสะสมแต้ม
ระบบปัจจุบันเน้นการจัดการภายใน ยังไม่มีระบบสำหรับจัดการข้อมูลลูกค้า โปรแกรมสะสมแต้ม หรือระบบสมาชิกภาพ

#### 4.3.4 รูปแบบการชำระเงินจำกัดเฉพาะเงินสดและ QR Code
ระบบในปัจจุบันรองรับเพียงการชำระเงินแบบเงินสดและ QR Code เท่านั้น ยังไม่มีการรองรับการชำระเงินด้วยบัตรเครดิต e-wallet หรือวิธีการชำระเงินอื่นๆ

#### 4.3.5 ไม่มีระบบ backup ข้อมูลอัตโนมัติ
ระบบในปัจจุบันไม่มีการ backup ข้อมูลโดยอัตโนมัติ ซึ่งเป็นความเสี่ยงต่อการสูญหายของข้อมูลในกรณีฉุกเฉิน

#### 4.3.6 ประสิทธิภาพยังไม่เหมาะสมสำหรับผู้ใช้จำนวนมาก
ในปัจจุบันระบบได้รับการทดสอบและปรับแต่งสำหรับผู้ใช้ปกติ หากจำนวนผู้ใช้เพิ่มขึ้นอย่างมากหรือการใช้งานพร้อมกันหลายคน ระบบอาจประสบปัญหาด้านความเร็วและความมั่นคง

### 4.4 แนวทางการพัฒนาต่อ

#### 4.4.1 พัฒนาระบบให้รองรับการทำงานแบบออฟไลน์ด้วย Service Worker
เพิ่ม Service Worker เพื่อให้ระบบสามารถทำงานแบบออฟไลน์ได้ ผู้ใช้สามารถบันทึกข้อมูลในช่วงที่ไม่มีการเชื่อมต่ออินเทอร์เน็ต และข้อมูลจะถูกซิงโครไนซ์เมื่อการเชื่อมต่อกลับมาใช้งานได้

#### 4.4.2 เพิ่มระบบจัดการและเก็บไฟล์รูปภาพภายในระบบ
พัฒนาระบบ media storage ที่สามารถเก็บและจัดการรูปภาพของหมู เอกสารทางการแพทย์ และไฟล์อื่นๆ เพื่อให้ผู้จัดการมีข้อมูลภาพที่ครบถ้วนสำหรับการประเมินสุขภาพสัตว์

#### 4.4.3 เพิ่มระบบจัดการสมาชิกลูกค้าและโปรแกรมสะสมแต้ม
พัฒนาโมดูลสำหรับจัดการข้อมูลลูกค้า ประวัติการซื้อ และโปรแกรมสะสมแต้มเพื่อให้ลูกค้าได้รับสิทธิพิเศษและส่งเสริมการซื้อซ้ำ

#### 4.4.4 เพิ่มรูปแบบการชำระเงิน เช่น บัตรเครดิต e-wallet
ติดตั้ง Payment Gateway เช่น Omise, Stripe หรือ 2C2P เพื่อให้รองรับการชำระเงินด้วยบัตรเครดิต e-wallet และการชำระเงินแบบอื่นๆ

#### 4.4.5 พัฒนา Mobile Application สำหรับ iOS และ Android
สร้าง Native Mobile Application หรือ Cross-platform Application (เช่น React Native, Flutter) เพื่อให้ผู้ใช้สามารถเข้าถึงระบบและบันทึกข้อมูลได้ทุกที่ทุกเวลา

#### 4.4.6 เพิ่มระบบ backup ข้อมูลอัตโนมัติและ disaster recovery
พัฒนาระบบ automated backup ที่ทำการ backup ข้อมูลโดยอัตโนมัติเป็นประจำวัน และมีระบบ disaster recovery สำหรับการกู้คืนข้อมูลในกรณีฉุกเฉิน

#### 4.4.7 ปรับปรุงประสิทธิภาพระบบสำหรับรองรับผู้ใช้จำนวนมาก
ทำการ optimization ของฐานข้อมูล เพิ่ม Caching layer (เช่น Redis) ปรับปรุง Query Performance และใช้ Load Balancing เพื่อให้ระบบรองรับผู้ใช้จำนวนมากได้อย่างมีประสิทธิภาพ

#### 4.4.8 เพิ่มระบบจัดการหลายสาขาในระบบเดียว
พัฒนาให้ระบบสามารถจัดการหลายสาขาของฟาร์มหมูในระบบเดียวได้ พร้อมกับการรายงานและการวิเคราะห์แยกตามสาขา

---

สรุป

ระบบจัดการฟาร์มหมูที่ได้รับการพัฒนา เป็นแวดวรรค์ที่สามารถนำไปใช้งานจริงได้อย่างมีประสิทธิภาพ ด้วยการออกแบบที่ดี การพัฒนาตามหลัก software engineering และการทดสอบที่ครบถ้วน ระบบนี้สามารถช่วยให้ผู้จัดการฟาร์มหมูทำการตัดสินใจทางธุรกิจได้อย่างถูกต้องและรวดเร็ว ด้วยข้อมูลที่เชื่อถือได้และทันเวลา

นอกจากนี้ ยังมีแนวทางการพัฒนาต่อที่ชัดเจน เพื่อให้ระบบมีความสามารถและความทำได้สูงขึ้นในอนาคต

---

## บทที่ 5: สรุปผล ปัญหา และข้อเสนอแนะ

### 5.1 สรุปผลการดำเนินงาน

การพัฒนาระบบจัดการฟาร์มหมูแบบครบวงจรได้ดำเนินการเสร็จสิ้นตามวัตถุประสงค์ที่กำหนดไว้ภายในระยะเวลา 5 เดือน ระบบที่พัฒนาขึ้นสามารถตอบสนองความต้องการของผู้ใช้งานได้อย่างครบถ้วน ประกอบด้วยระบบสำหรับพนักงานที่สามารถบันทึกข้อมูลประจำวัน บันทึกการรักษาหมู และบันทึกการขายหมูได้ และระบบสำหรับผู้ดูแลระบบที่สามารถจัดการผู้ใช้ อนุมัติต้นทุน อนุมัติการขาย จัดการสินค้าคงคลัง และดูรายงานทางการเงินได้

ระบบได้รับการพัฒนาด้วยเทคโนโลยี Full-Stack Web Development โดยใช้ Laravel Blade Templates และ Bootstrap 5 สำหรับ Frontend พร้อมด้วย AJAX/Axios สำหรับการทำงานแบบ Asynchronous และ PHP 8.1 + Laravel 9 สำหรับ Backend ร่วมกับฐานข้อมูล MySQL 8.0+ ซึ่งทำงานได้อย่างมีเสถียรภาพ การออกแบบ User Interface เป็น Responsive Design ที่รองรับการใช้งานบนอุปกรณ์หลากหลาย ระบบมี 178 endpoints (11 API routes + 167 web routes) ที่ครอบคลุมการทำงานทั้งหมด มี 32 Models (ตารางฐานข้อมูล) 138 relationships ระหว่างตารางที่ซับซ้อน และมีระบบ Authentication ด้วย Laravel Jetstream และ Livewire เพื่อความปลอดภัย

ผลการทดสอบพบว่าระบบสามารถเพิ่มประสิทธิภาพในการจัดการฟาร์มหมู ลดเวลาในการบันทึกข้อมูล ลดความผิดพลาดในการบันทึกข้อมูล และช่วยให้ผู้จัดการสามารถติดตามสถานะการดำเนินงานได้แบบเรียลไทม์ ระบบรายงานทางการเงินช่วยในการตัดสินใจเชิงธุรกิจ การคำนวณกำไรและขาดทุนอัตโนมัติ และการส่งออกข้อมูลในรูปแบบต่างๆ เอื้อต่อการวิเคราะห์เพิ่มเติม

### 5.2 ปัญหาและอุปสรรค

ในระหว่างการพัฒนาพบปัญหาและอุปสรรคหลายประการ

ปัญหาด้านเทคนิค ได้แก่ การออกแบบโครงสร้างฐานข้อมูลให้มีประสิทธิภาพและรองรับการขยายตัวในอนาคต การจัดการความสัมพันธ์ระหว่างตารางที่ซับซ้อน การเพิ่มประสิทธิภาพของ Query เมื่อจำนวนข้อมูลเพิ่มขึ้น การจัดการ Transaction เพื่อให้ข้อมูลคงที่และถูกต้องในกรณีการทำงานพร้อมกัน

ปัญหาด้านการออกแบบ UI/UX พบความท้าทายในการสร้าง Interface ที่ใช้งานง่ายสำหรับผู้ใช้ที่มีความสามารถด้านเทคโนโลยีแตกต่างกัน การจัดการ Responsive Design ให้แสดงผลได้ดีทั้งบนมือถือและคอมพิวเตอร์ การออกแบบ Workflow ที่เหมาะสมสำหรับการบันทึกข้อมูลและการตัดสินใจ และการจัดการการแจ้งเตือนเพื่อให้ผู้ใช้ทราบข้อมูลที่สำคัญทันท่วงที

ปัญหาด้านการทดสอบ การจัดการกรณีทดสอบที่หลากหลายเนื่องจากมีฟีเจอร์มากมาย การทดสอบ API ที่มีจำนวนมากและมีการขึ้นต่อกันเป็นซีรี่ส์ การทำ Integration Testing เพื่อให้แน่ใจว่าระบบทั้งหมดทำงานร่วมกันได้อย่างถูกต้อง

### 5.3 ข้อเสนอแนะ

สำหรับการพัฒนาระบบในลักษณะนี้ต่อไป ข้อเสนอแนะด้านเทคนิคคือ ควรเริ่มต้นด้วยการออกแบบ Database Schema ให้ละเอียดและครอบคลุมก่อนเริ่มเขียนโค้ด การใช้ Normalized Database Design เพื่อลดความซ้ำซ้อนของข้อมูล การใช้ Indexing อย่างชาญฉลาดเพื่อเพิ่มประสิทธิภาพของ Query และควรมีการเขียน Unit Tests และ Integration Tests ตั้งแต่เริ่มต้นเพื่อให้มั่นใจในความถูกต้องของโค้ด

ด้านการออกแบบ UI/UX แนะนำให้ทำ User Research และ Usability Testing กับผู้ใช้จริงก่อนเริ่มพัฒนา การสร้าง Wireframe และ Prototype ที่ละเอียดช่วยลดการแก้ไขในภายหลัง และควรคำนึงถึง Accessibility สำหรับผู้ใช้ที่มีความต้องการพิเศษ การใช้ Design System เพื่อให้ Interface สม่ำเสมอทั้งระบบ

ด้านการทดสอบ แนะนำให้ใช้ Test-Driven Development (TDD) ซึ่งเขียนการทดสอบก่อนเขียนโค้ด การใช้ Automated Testing Tools เช่น PHPUnit สำหรับ PHP หรือ Jest สำหรับ JavaScript เพื่อให้การทดสอบเป็นไปอย่างรวดเร็ว การทำ Load Testing เพื่อทราบว่าระบบรองรับผู้ใช้จำนวนกี่คนพร้อมกัน และการทำ Security Testing เพื่อตรวจสอบช่องโหว่ด้านความปลอดภัย

สำหรับการพัฒนาต่อยอด แนะนำให้เพิ่มระบบ Monitoring และ Logging เพื่อติดตามการทำงานของระบบ การใช้ Cloud Services เช่น AWS หรือ Azure เพื่อรองรับการขยายตัว การพัฒนา Mobile Application เพื่อเพิ่มความสะดวกในการใช้งาน และการเพิ่มฟีเจอร์ Advanced Analytics เพื่อช่วยในการตัดสินใจทางธุรกิจ

ท้ายที่สุด ระบบจัดการฟาร์มหมูที่พัฒนาขึ้นนี้เป็นตัวอย่างที่ดีของการนำเทคโนโลยีสารสนเทศมาใช้ในการแก้ปัญหาทางธุรกิจ ลดต้นทุนการดำเนินงาน เพิ่มประสิทธิภาพการทำงาน และช่วยให้การตัดสินใจทางธุรกิจมีความแม่นยำมากขึ้น สามารถเป็นแนวทางสำหรับการพัฒนาระบบที่คล้ายกันในอนาคตได้

---

## ข. วิธีใช้งานเว็บไซต์ระบบจัดการฟาร์มหมู

### ข.1 หน้าเข้าสู่ระบบและการสมัครสมาชิก

#### ข.1.1 การเข้าสู่ระบบ

หน้าเข้าสู่ระบบเป็นหน้าแรกที่ผู้ใช้งานจะพบเมื่อเข้าใช้ระบบ ผู้ใช้งานจำเป็นต้องระบุข้อมูลการเข้าสู่ระบบดังต่อไปนี้:

**ข้อมูลที่ต้องกรอก:**
- อีเมล (Email) - ใช้สำหรับการเข้าสู่ระบบและติดต่อ
- รหัสผ่าน (Password) - ต้องเก็บเป็นความลับ

**ขั้นตอนการเข้าสู่ระบบ:**
1. ไปยังหน้า Login ของระบบ
2. กรอกอีเมล ที่ใช้สมัครสมาชิก
3. กรอกรหัสผ่าน
4. คลิกปุ่ม "เข้าสู่ระบบ"
5. ระบบจะตรวจสอบข้อมูลและนำทำให้เข้าสู่ Dashboard

**รูป 1 หน้าเข้าสู่ระบบ**

[ใส่ภาพ Login page ที่นี่]

#### ข.1.2 การสมัครสมาชิกใหม่

สำหรับผู้ใช้ที่เป็นครั้งแรกและยังไม่มีบัญชี สามารถสมัครสมาชิกได้ตามขั้นตอนต่อไปนี้:

**ขั้นตอนการสมัครสมาชิก:**
1. คลิกที่ลิงก์ "ยังไม่มีบัญชี? สมัครสมาชิก" บนหน้า Login
2. กรอกข้อมูลส่วนบุคคลดังต่อไปนี้:
   - ชื่อ (Full Name)
   - อีเมล (Email) - ต้องใช้ Gmail (@gmail.com) เท่านั้น
   - เบอร์โทรศัพท์ (Phone Number)
   - ที่อยู่ (Address)
   - รหัสผ่าน (Password) - ต้องมีความยาวอย่างน้อย 8 ตัวอักษร
   - ยืนยันรหัสผ่าน (Confirm Password)
3. ตกลงตามเงื่อนไขการใช้งาน
4. คลิกปุ่ม "สมัครสมาชิก"
5. ระบบจะส่งขอการอนุมัติไปยังผู้ดูแลระบบ

**สถานะการสมัครสมาชิก:**
- **รอการอนุมัติ (Pending)** - หลังจากส่งฟอร์ม
- **อนุมัติแล้ว (Approved)** - ผู้ดูแลได้ตรวจสอบและอนุมัติ
- **ปฏิเสธ (Rejected)** - ผู้ดูแลปฏิเสธคำขอ (ติดต่อผู้ดูแลระบบ)

**รูป 2 หน้าสมัครสมาชิก**

[ใส่ภาพ Registration page ที่นี่]

#### ข.1.3 การรีเซ็ตรหัสผ่าน

ในกรณีที่ผู้ใช้ลืมรหัสผ่าน:

**ขั้นตอนการรีเซ็ตรหัสผ่าน:**
1. คลิกที่ลิงก์ "ลืมรหัสผ่าน?" ในหน้า Login
2. กรอกอีเมลของบัญชี
3. ระบบจะส่งลิงก์รีเซ็ตรหัสผ่านไปยังอีเมลของคุณ
4. ตรวจสอบอีเมล (รวมถึง Spam folder)
5. คลิกลิงก์ที่ส่งมาแล้วทำตามขั้นตอนเพื่อตั้งรหัสผ่านใหม่
6. เข้าสู่ระบบด้วยรหัสผ่านใหม่

---

### ข.2 Dashboard (แดชบอร์ดหลัก)

Dashboard เป็นหน้าแรกที่ผู้ใช้เห็นหลังเข้าสู่ระบบสำเร็จ โดยแสดงข้อมูลสรุปสำคัญของฟาร์มหมู

**ข้อมูลที่แสดงบน Dashboard:**

| ข้อมูล | รายละเอียด |
|------|----------|
| จำนวนหมูทั้งหมด | สรุปจำนวนหมูในแต่ละเล้า/คอก |
| รุ่นหมูกำลังดำเนินการ | จำนวนรุ่น (Batch) ที่อยู่ในช่วงเลี้ยง |
| รายได้ปัจจุบัน | ยอดรวมจากการขายหมูในเดือนปัจจุบัน |
| ต้นทุนปัจจุบัน | ยอดรวมต้นทุนที่เบิกใช้ในเดือนปัจจุบัน |
| กำไรสุทธิ | รายได้ลบต้นทุน |
| ประสิทธิภาพการเลี้ยง | ADG, FCR, FCG ของรุ่นปัจจุบัน |
| กราฟแนวโน้ม | แสดงกราฟรายได้ และต้นทุนรายเดือน |
| ประมาณการกำไร | คาดการณ์กำไรของรุ่นที่กำลังดำเนินการ |

**รูป 3 Dashboard หลัก**

[ใส่ภาพ Dashboard page ที่นี่]

---

### ข.3 บันทึกข้อมูลประจำวัน (Daily Records)

บันทึกข้อมูลสุขภาพและการดูแลหมูในแต่ละวันเป็นหน้าที่สำคัญของพนักงาน

**ขั้นตอนการบันทึกข้อมูลประจำวัน:**

1. จากเมนูด้านข้าง คลิก "การบันทึก" > "ข้อมูลประจำวัน"
2. เลือกฟาร์มที่ต้องการบันทึก
3. เลือกวันที่ที่ต้องการบันทึก (ค่าเริ่มต้นคือวันปัจจุบัน)
4. กรอกข้อมูลต่อไปนี้:

| ข้อมูล | หมายเหตุ |
|------|--------|
| จำนวนหมูทั้งหมด | ณ เวลาการบันทึก |
| จำนวนหมูที่ป่วย | ปัญหาด้านสุขภาพและอาการ |
| จำนวนหมูที่ตาย | เหตุผลการตายหากทราบ |
| น้ำหนักเฉลี่ยปัจจุบัน | ประมาณการหรือชั่งน้ำหนัก |
| ปริมาณอาหารที่ให้ | กิโลกรัมต่อวัน |
| ปริมาณน้ำ | ลิตรต่อวัน (ถ้าจำเป็น) |
| หมายเหตุเพิ่มเติม | ข้อมูลสำคัญอื่น ๆ |

5. คลิกปุ่ม "บันทึก"

**รูป 4 หน้าบันทึกข้อมูลประจำวัน**

[ใส่ภาพ Daily Records page ที่นี่]

---

### ข.4 บันทึกการรักษาหมู (Treatments)

บันทึกการให้ยาและการรักษาหมูที่ป่วย

**ขั้นตอนการบันทึกการรักษา:**

1. จากเมนูด้านข้าง คลิก "การบันทึก" > "การรักษา"
2. ดูรายการการรักษาที่กำลังดำเนินการ
3. เพื่อเพิ่มการรักษาใหม่ คลิก "เพิ่มการรักษา"
4. เลือกข้อมูลดังต่อไปนี้:
   - รุ่นหมู (Batch)
   - เล้า (Barn)
   - คอก (Pen)
5. เลือกยาที่จะให้ จากคลังเก็บ
6. กรอกข้อมูล:

| ข้อมูล | รายละเอียด |
|------|----------|
| ปริมาณยา | จำนวนหน่วยที่จะให้ |
| หน่วย | กรัม/มิลลิลิตร/เม็ด |
| วิธีการให้ยา | ทางปาก/ฉีด/หยดในน้ำ |
| วันที่เริ่ม | วันที่เริ่มให้ยา |
| วันที่สิ้นสุด | วันที่คาดว่าจะสิ้นสุด |
| หมายเหตุ | เหตุผลการรักษา อาการ |

7. คลิกปุ่ม "บันทึก"

**ติดตามสถานะการรักษา:**
- ดูรายการการรักษาทั้งหมดในหน้า "การรักษา"
- สถานะการรักษา: เริ่มแล้ว / กำลังดำเนินการ / เสร็จสิ้น / ยกเลิก
- สามารถแก้ไขหรือลบการรักษาที่ยังไม่เสร็จได้

**รูป 5 หน้าบันทึกการรักษา**

[ใส่ภาพ Treatment page ที่นี่]

---

### ข.5 บันทึกการขายหมู (Pig Sales)

บันทึกการขายหมูให้ลูกค้า

**ขั้นตอนการบันทึกการขาย:**

1. จากเมนูด้านข้าง คลิก "การขาย" > "การขายหมู"
2. ดูรายการการขายทั้งหมด
3. เพื่อเพิ่มการขายใหม่ คลิก "เพิ่มการขาย"
4. เลือกข้อมูลดังต่อไปนี้:
   - ฟาร์ม
   - เล้า/คอก (ที่มีหมูพร้อมขาย)
5. กรอกข้อมูลการขาย:

| ข้อมูล | รายละเอียด |
|------|----------|
| จำนวนหมู | ตัวที่จะขาย |
| น้ำหนักรวม | กิโลกรัม |
| ราคาต่อหน่วย | บาท/กิโลกรัม หรือ บาท/ตัว |
| ยอดรวม | การคำนวณจะอัตโนมัติ |
| ชื่อลูกค้า | บุคคล หรือ บริษัท |
| เบอร์โทรศัพท์ลูกค้า | เพื่อติดต่อ |
| วันที่ขาย | วันที่จะส่งมอบ |
| วิธีการชำระเงิน | เงินสด / โอนธนาคาร / เช็ค |

6. คลิกปุ่ม "บันทึก"

**ขั้นตอนหลังบันทึกการขาย:**
- ระบบจะส่งให้ผู้ดูแลตรวจสอบและอนุมัติ
- สถานะการขาย: รอการอนุมัติ / อนุมัติแล้ว / ปฏิเสธ
- หลังจากอนุมัติ รายการการชำระเงินจะปรากฏ
- เมื่อชำระเงินเสร็จสมบูรณ์ สถานะจะเป็น "เสร็จสิ้น"

**รูป 6 หน้าบันทึกการขายหมู**

[ใส่ภาพ Pig Sales page ที่นี่]

---

### ข.6 จัดการสินค้าคงคลัง (Inventory Management)

ติดตามสต็อกอาหาร ยา วัสดุ และสินค้าอื่นๆ

**ขั้นตอนการเบิกสินค้า:**

1. จากเมนูด้านข้าง คลิก "การเบิก" > "คลังเก็บ"
2. ดูรายการสินค้าทั้งหมดพร้อมจำนวนสต็อก
3. เพื่อบันทึกการเบิกสินค้า คลิก "เพิ่มการเบิก"
4. กรอกข้อมูล:

| ข้อมูล | รายละเอียด |
|------|----------|
| ประเภทสินค้า | อาหาร / ยา / วัสดุ / อื่น |
| ชื่อสินค้า | เลือกจากรายการ |
| ปริมาณเบิก | จำนวนที่เบิก |
| หน่วย | กิโลกรัม / แพ็ก / ลิตร / อื่น |
| ราคาต่อหน่วย | บาท (อ้างอิง) |
| หมายเหตุ | วัตถุประสงค์การใช้งาน |
| วันที่เบิก | ค่าเริ่มต้นคือวันปัจจุบัน |

5. คลิกปุ่ม "บันทึก"

**ดูประวัติการใช้งาน:**
- ในหน้า "การเคลื่อนย้ายสินค้า" จะแสดงประวัติการเบิก-ใช้งานทั้งหมด
- สามารถ Export ข้อมูลเป็น CSV หรือ PDF ได้
- สามารถค้นหาและกรองข้อมูลตามวันที่ หรือ ประเภทสินค้า

**รูป 7 หน้าจัดการสินค้าคงคลัง**

[ใส่ภาพ Inventory Management page ที่นี่]

---

### ข.7 จัดการต้นทุน (Cost Management)

บันทึกและติดตามต้นทุนการเลี้ยงหมู

**ขั้นตอนการบันทึกต้นทุน:**

1. จากเมนูด้านข้าง คลิก "การเบิก" > "ต้นทุน"
2. ดูรายการต้นทุนทั้งหมด
3. เพื่อเพิ่มต้นทุนใหม่ คลิก "เพิ่มต้นทุน"
4. เลือกประเภทต้นทุน:
   - อาหาร
   - ยา/อุปกรณ์แพทย์
   - แรงงาน
   - ไฟฟ้า/น้ำ
   - ซ่อมแซม
   - อื่นๆ
5. กรอกข้อมูล:

| ข้อมูล | รายละเอียด |
|------|----------|
| ฟาร์ม | เลือกฟาร์มที่เกี่ยวข้อง |
| รุ่นหมู | (ถ้าเกี่ยวข้อง) |
| คำอธิบาย | รายละเอียดต้นทุน |
| จำนวนเงิน | บาท |
| วันที่ | วันที่เกิดต้นทุน |
| ใบเสร็จ/เอกสาร | (ถ้ามี) |

6. คลิกปุ่ม "บันทึก"

**อนุมัติต้นทุน:**
- ผู้ดูแลระบบจะตรวจสอบและอนุมัติต้นทุน
- สถานะ: รอการอนุมัติ / อนุมัติแล้ว / ปฏิเสธ
- ต้นทุนที่อนุมัติแล้วจะนำไปคำนวณกำไร

**รูป 8 หน้าจัดการต้นทุน**

[ใส่ภาพ Cost Management page ที่นี่]

---

### ข.8 ดูรายงาน (Reports)

ระบบมีรายงานต่างๆ เพื่อช่วยในการวิเคราะห์และตัดสินใจ

**ประเภทของรายงาน:**

| รายงาน | เนื้อหา | ประโยชน์ |
|--------|-------|--------|
| รายงานกำไร-ขาดทุน | กำไรสุทธิของแต่ละรุ่นหมู | วิเคราะห์ความสำเร็จของรุ่น |
| รายงานประสิทธิภาพ | ADG, FCR, FCG | ประเมินประสิทธิภาพการเลี้ยง |
| รายงานต้นทุน | ต้นทุนแยกตามประเภท | วิเคราะห์ค่าใช้จ่าย |
| รายงานการขาย | รายการขายและรายได้ | ติดตามช่องทางการขาย |
| รายงานสินค้าคงคลัง | สต็อกปัจจุบันของแต่ละสินค้า | วางแผนการสั่งซื้อ |

**ขั้นตอนการดูรายงาน:**

1. จากเมนูด้านข้าง คลิก "รายงาน" > เลือกประเภทรายงาน
2. เลือกเงื่อนไข (ฟาร์ม, วันที่, รุ่นหมู)
3. คลิกปุ่ม "ดูรายงาน"

**การ Export ข้อมูล:**
- คลิกปุ่ม "Export CSV" - ไฟล์ Excel
- คลิกปุ่ม "Export PDF" - ไฟล์ PDF
- ระบบจะสร้างไฟล์สำหรับดาวน์โหลด

**รูป 9 หน้าดูรายงาน**

[ใส่ภาพ Reports page ที่นี่]

---

### ข.9 การแจ้งเตือน (Notifications)

ระบบจะส่งการแจ้งเตือนเมื่อเกิดเหตุการณ์สำคัญ

**ประเภทของการแจ้งเตือน:**

| เหตุการณ์ | หมายเหตุ |
|---------|--------|
| การอนุมัติ/ปฏิเสธการขาย | สถานะการขายเปลี่ยนแปลง |
| การอนุมัติ/ปฏิเสธต้นทุน | สถานะต้นทุนเปลี่ยนแปลง |
| มีหมูป่วย/ตาย | ต้องให้ความสำคัญ |
| สินค้าใกล้หมด | จำเป็นต้องสั่งซื้อใหม่ |
| การสิ้นสุดรุ่นหมู | รุ่นหมูเสร็จสิ้นการเลี้ยง |

**ขั้นตอนการดูการแจ้งเตือน:**

1. คลิกที่ไอคอนกระดิ่ง (🔔) ในส่วนหัวของเพจ
2. ดูรายการแจ้งเตือนล่าสุด (จำนวน 10 รายการล่าสุด)
3. คลิกที่การแจ้งเตือนเพื่อไปยังรายการที่เกี่ยวข้อง
4. หรือคลิก "ดูทั้งหมด" เพื่อไปยังหน้าการแจ้งเตือนแบบเต็ม

**การจัดการการแจ้งเตือน:**
- ทำเครื่องหมายว่าอ่านแล้ว
- ลบการแจ้งเตือนที่ไม่จำเป็น
- กรองตามประเภท

**รูป 10 หน้าการแจ้งเตือน**

[ใส่ภาพ Notifications page ที่นี่]

---

### ข.10 การจัดการ Admin (สำหรับผู้ดูแลระบบ)

ผู้ดูแลระบบมีสิทธิในการจัดการและควบคุมระบบทั้งหมด

#### ข.10.1 จัดการผู้ใช้งาน

**ขั้นตอนการจัดการผู้ใช้:**

1. จากเมนูด้านข้าง คลิก "การจัดการ" > "ผู้ใช้งาน"
2. ดูรายการผู้ใช้ทั้งหมด
3. **อนุมัติการสมัครสมาชิก:**
   - คลิกแท็บ "รอการอนุมัติ"
   - ตรวจสอบข้อมูลผู้สมัคร
   - คลิก "อนุมัติ" หรือ "ปฏิเสธ"
4. **กำหนดบทบาท:**
   - คลิกไปยังรายการผู้ใช้
   - เลือกบทบาท: Staff / Manager / Admin
   - กดบันทึก
5. **ลบผู้ใช้:**
   - เลือกผู้ใช้ที่จะลบ
   - คลิกปุ่ม "ลบ"

**รูป 11 หน้าจัดการผู้ใช้งาน**

[ใส่ภาพ User Management page ที่นี่]

#### ข.10.2 จัดการข้อมูลฟาร์ม

**ขั้นตอนการจัดการฟาร์ม:**

1. จากเมนูด้านข้าง คลิก "การจัดการ" > "ฟาร์ม"
2. **เพิ่มฟาร์มใหม่:**
   - คลิก "เพิ่มฟาร์ม"
   - กรอกข้อมูล: ชื่อ, ที่อยู่, เบอร์โทร
   - กดบันทึก
3. **แก้ไขข้อมูลฟาร์ม:**
   - คลิกที่ชื่อฟาร์มที่ต้องการ
   - แก้ไขข้อมูล
   - กดบันทึก
4. **จัดการเล้า (Barn):**
   - เลือกฟาร์ม
   - คลิก "เพิ่มเล้า"
   - กรอกชื่อและจำนวนคอก
5. **จัดการคอก (Pen):**
   - เลือกเล้า
   - คลิก "เพิ่มคอก"
   - กรอกหมายเลขและความจุ

**รูป 12 หน้าจัดการฟาร์ม**

[ใส่ภาพ Farm Management page ที่นี่]

#### ข.10.3 จัดการรุ่นหมู

**ขั้นตอนการจัดการรุ่นหมู:**

1. จากเมนูด้านข้าง คลิก "การจัดการ" > "รุ่นหมู"
2. **สร้างรุ่นหมูใหม่:**
   - คลิก "สร้างรุ่นใหม่"
   - กรอกข้อมูล: รหัสรุ่น, ฟาร์ม, จำนวนหมู
   - เลือกวันที่เริ่ม
   - กดบันทึก
3. **แก้ไขข้อมูลรุ่น:**
   - คลิกที่รหัสรุ่นที่ต้องการ
   - แก้ไขข้อมูล
   - กดบันทึก
4. **ปิดรุ่นที่เสร็จสิ้น:**
   - เลือกรุ่น
   - คลิก "ปิดรุ่น"
   - ระบบจะคำนวณกำไรสุทธิ

**รูป 13 หน้าจัดการรุ่นหมู**

[ใส่ภาพ Batch Management page ที่นี่]

#### ข.10.4 อนุมัติการทำรายการ

**ขั้นตอนการอนุมัติ:**

1. จากเมนูด้านข้าง คลิก "การอนุมัติ"
2. **อนุมัติการขาย:**
   - ดูรายการรอการอนุมัติ
   - ตรวจสอบข้อมูล
   - คลิก "อนุมัติ" หรือ "ปฏิเสธ"
3. **อนุมัติต้นทุน:**
   - ตรวจสอบใบเสร็จและจำนวนเงิน
   - คลิก "อนุมัติ" หรือ "ปฏิเสธ"
4. **อนุมัติการเบิกสินค้า:**
   - ตรวจสอบปริมาณและสินค้า
   - คลิก "อนุมัติ" หรือ "ปฏิเสธ"

**รูป 14 หน้าการอนุมัติการทำรายการ**

[ใส่ภาพ Approval page ที่นี่]

---

```
