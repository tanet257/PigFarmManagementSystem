# User Registration Cancel Workflow & Email Notifications

## สรุป
ระบบจัดการฟาร์มหมูได้ถูกอัพเดทเพื่อให้สามารถยกเลิกการลงทะเบียนผู้ใช้และส่งการแจ้งเตือนทางอีเมล เมื่อมีการอนุมัติ/ปฏิเสธ/ยกเลิกการลงทะเบียน

## Features ที่เพิ่มเติม

### 1. User Registration Cancel Workflow
- **User ขอยกเลิก**: สามารถขอยกเลิกการลงทะเบียนและบันทึกเหตุผล
- **Admin Notification**: Admin จะได้รับแจ้งเตือนเมื่อมีผู้ใช้ขอยกเลิก
- **Admin Approval/Rejection**: Admin สามารถอนุมัติหรือปฏิเสธคำขอยกเลิก
- **Status Update**: เมื่ออนุมัติ status จะเป็น "cancelled"

### 2. Email Notifications
เพิ่มเติม 3 Mailable classes สำหรับส่งอีเมล:

#### **UserRegistrationApproved.php**
- ส่งเมื่อ: การลงทะเบียนได้รับการอนุมัติ
- เนื้อหา: ยินดีต้องรับและแนะนำวิธีเข้าสู่ระบบ
- Template: `emails/user_registration_approved.blade.php`

#### **UserRegistrationRejected.php**
- ส่งเมื่อ: การลงทะเบียนถูกปฏิเสธ
- เนื้อหา: แสดงเหตุผลในการปฏิเสธ
- Template: `emails/user_registration_rejected.blade.php`

#### **UserRegistrationCancelled.php**
- ส่งเมื่อ: บัญชีถูกยกเลิก
- เนื้อหา: แจ้งว่าบัญชีปิดใช้งาน
- Template: `emails/user_registration_cancelled.blade.php`

### 3. Notification Types & Icons

เพิ่มเติม notification types ใน notification view:

```
Type                          | Icon               | Label
------------------------------|-------------------|----------------------
user_registered              | fa-user-plus      | ดูการลงทะเบียน
user_approved                | fa-check-circle   | ตรวจสอบการอนุมัติ
user_rejected                | fa-times-circle   | ตรวจสอบการปฏิเสธ
user_registration_cancelled  | fa-ban            | อนุมัติการยกเลิก
```

## Database Changes

### Migration: `2025_10_22_030218_add_cancellation_fields_to_users.php`

เพิ่มเติม 2 columns ใน `users` table:

```sql
ALTER TABLE users ADD COLUMN cancellation_reason VARCHAR(255) NULLABLE;
ALTER TABLE users ADD COLUMN cancellation_requested_at TIMESTAMP NULLABLE;
```

### User Model Updates

เพิ่มเติม properties:

```php
protected $fillable = [
    // ... existing
    'cancellation_reason',
    'cancellation_requested_at',
];

protected $casts = [
    // ... existing
    'cancellation_requested_at' => 'datetime',
];

// New methods
public function isCancelled()
public function hasCancellationRequest()
```

## Controller Updates

### UserManagementController

เพิ่มเติม 3 methods:

#### **1. requestCancelRegistration($id)**
```php
POST /user_management/{id}/request_cancel
```
- ผู้ใช้ขอยกเลิก
- บันทึก cancellation_reason และ cancellation_requested_at
- สร้าง notification ให้ user และ admin

#### **2. approveCancelRegistration($id)**
```php
PATCH /user_management/{id}/approve_cancel
```
- Admin อนุมัติการยกเลิก
- ตั้ง status = "cancelled"
- ส่งอีเมล UserRegistrationCancelled
- ทำให้ account ไม่สามารถ login ได้

#### **3. rejectCancelRegistration($id)**
```patch
PATCH /user_management/{id}/reject_cancel
```
- Admin ปฏิเสธการยกเลิก
- ล้าง cancellation_reason และ cancellation_requested_at
- account ยังคงสามารถใช้งานได้

### NotificationController

อัพเดท `markAndNavigate()`:

```php
// สำหรับ user registration types ให้ router ไป user_management.index
if (in_array($notification->type, [
    'user_registered', 
    'user_approved', 
    'user_rejected', 
    'user_registration_cancelled'
])) {
    return redirect()->route('user_management.index');
}
```

### NotificationHelper

เพิ่มเติม 3 email methods:

```php
public static function sendUserApprovedEmail(User $user, User $approvedBy)
public static function sendUserRejectedEmail(User $user, User $rejectedBy, $reason)
public static function sendUserCancelledEmail(User $user, $reason = null)
```

## Routes

เพิ่มเติม routes ใน `routes/web.php`:

```php
// User Registration Cancellation
POST   /user_management/{id}/request_cancel
PATCH  /user_management/{id}/approve_cancel
PATCH  /user_management/{id}/reject_cancel
```

## Views Updates

### notifications/index.blade.php

อัพเดท button logic เพื่อสนับสนุน 7+ notification types:

```blade
@if ($notification->url || $notification->type === 'user_registration_cancelled')
    <a href="{{ route('notifications.mark_and_navigate', $notification->id) }}"
        class="btn btn-sm {{ $notification->is_read ? 'btn-info' : 'btn-warning' }}">
        <!-- Icon ที่เหมาะสมต่อ type -->
        <!-- Label ที่เหมาะสมต่อ type -->
    </a>
@endif
```

## Email Templates

สร้าง 3 email templates:

```
resources/views/emails/
├── user_registration_approved.blade.php
├── user_registration_rejected.blade.php
└── user_registration_cancelled.blade.php
```

## Implementation Checklist

- ✅ User Model - เพิ่ม fields และ methods
- ✅ Database Migration - เพิ่ม columns
- ✅ UserManagementController - เพิ่ม 3 methods
- ✅ NotificationHelper - เพิ่ม email methods
- ✅ NotificationController - อัพเดท routing
- ✅ Routes - เพิ่ม 3 routes
- ✅ Notification View - สนับสนุน 7+ types
- ✅ Email Templates - สร้าง 3 templates
- ✅ Mail Classes - สร้าง 3 Mailable classes
- ✅ Syntax Validation - ✅ No errors

## Configuration Required

### .env Email Settings

ตรวจสอบว่า `.env` มี email configuration:

```env
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_NAME="Pig Farm System"
MAIL_FROM_ADDRESS=your-email@gmail.com
```

สำหรับ Gmail:
1. Enable 2-Step Verification
2. Generate App Password
3. ใช้ App Password แทน password ปกติ

## Testing

### Test Cases

1. **User Request Cancel**
   - ผู้ใช้ขอยกเลิก
   - Admin ได้รับ notification
   - Notification type = `user_registration_cancelled`

2. **Admin Approve Cancel**
   - Admin อนุมัติ
   - User status = `cancelled`
   - Email ส่งถึง user
   - User ไม่สามารถ login ได้

3. **Admin Reject Cancel**
   - Admin ปฏิเสธ
   - User status ยังเป็น `approved`
   - cancellation_reason ถูกล้าง
   - User สามารถ login ได้

4. **Email Notifications**
   - Approve: ส่ง UserRegistrationApproved
   - Reject: ส่ง UserRegistrationRejected
   - Cancel: ส่ง UserRegistrationCancelled

## Notification Types Summary

| Type | Who | Action | Email? |
|------|-----|--------|--------|
| user_registered | Admin | User registered | ❌ |
| user_approved | User | Status changed | ✅ |
| user_rejected | User | Status changed | ✅ |
| user_registration_cancelled | Both | Cancellation | ✅ |

## Related Files Modified

### Controllers
- `app/Http/Controllers/UserManagementController.php` - เพิ่ม 3 methods
- `app/Http/Controllers/NotificationController.php` - อัพเดท routing

### Models
- `app/Models/User.php` - เพิ่ม properties และ methods

### Helpers
- `app/Helpers/NotificationHelper.php` - เพิ่ม email methods

### Mail Classes
- `app/Mail/UserRegistrationApproved.php` - NEW
- `app/Mail/UserRegistrationRejected.php` - NEW
- `app/Mail/UserRegistrationCancelled.php` - NEW

### Views
- `resources/views/admin/notifications/index.blade.php` - อัพเดท
- `resources/views/emails/user_registration_approved.blade.php` - NEW
- `resources/views/emails/user_registration_rejected.blade.php` - NEW
- `resources/views/emails/user_registration_cancelled.blade.php` - NEW

### Routes
- `routes/web.php` - เพิ่ม 3 routes

### Migrations
- `database/migrations/2025_10_22_030218_add_cancellation_fields_to_users.php` - NEW

## Future Enhancements

1. **SMS Notifications** - เพิ่มการส่ง SMS นอกเหนือจาก Email
2. **User Profile Page** - สร้างหน้า profile ให้ user ขอยกเลิก
3. **Cancellation History** - บันทึกประวัติการยกเลิก
4. **Appeal System** - ยินยอมให้ user ขอเปิดใช้งานบัญชีใหม่

## Notes

- Email sending ต้องเซ็ตอัพ `.env` อย่างถูกต้อง
- ทำให้ใช้เดียวกันกับ cancel_pig_sale workflow
- Notification icons สามารถปรับปรุงได้ตามต้องการ
- ระบบทำให้ soft delete ผู้ใช้ (ไม่ลบจากฐานข้อมูล)
