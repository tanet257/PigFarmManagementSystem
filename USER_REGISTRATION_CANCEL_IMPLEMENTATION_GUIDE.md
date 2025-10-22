# User Registration Cancel Workflow - Implementation Guide

## 🎯 Overview

This guide explains how the user registration cancellation workflow and email notifications work in the Pig Farm Management System.

## 📋 Key Components

### 1. Database Schema
```sql
-- Added to users table:
ALTER TABLE users ADD COLUMN cancellation_reason VARCHAR(255) NULLABLE;
ALTER TABLE users ADD COLUMN cancellation_requested_at TIMESTAMP NULLABLE;
```

### 2. User Model Methods

#### Check Cancellation Status
```php
$user->isCancelled()              // true if status = 'cancelled'
$user->hasCancellationRequest()   // true if cancellation_requested_at is set
```

### 3. Notification Types

| Type | Icon | Label | When Triggered |
|------|------|-------|-----------------|
| user_registered | 👤 fa-user-plus | ดูการลงทะเบียน | User registers |
| user_approved | ✅ fa-check-circle | ตรวจสอบการอนุมัติ | Admin approves |
| user_rejected | ❌ fa-times-circle | ตรวจสอบการปฏิเสธ | Admin rejects |
| user_registration_cancelled | ⛔ fa-ban | อนุมัติการยกเลิก | User/Admin cancels |

## 🔄 Workflow Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│ USER REGISTRATION CANCEL WORKFLOW                              │
└─────────────────────────────────────────────────────────────────┘

Step 1: User Requests Cancellation
┌──────────────────────┐
│ User Status: Approved│
│ Click: "ยกเลิก"      │
│ Enter: Reason        │
└──────────────────────┘
        │
        ▼
┌──────────────────────────────────────────┐
│ requestCancelRegistration()              │
│ ✓ Set cancellation_reason                │
│ ✓ Set cancellation_requested_at          │
│ ✓ Create notification                    │
└──────────────────────────────────────────┘
        │
        ▼
Step 2: Admin Receives Notification
┌──────────────────────────────────────────┐
│ Admin Dashboard                          │
│ Notification: "User X ขอยกเลิก"          │
│ Type: user_registration_cancelled       │
│ Route: user_management.index             │
└──────────────────────────────────────────┘
        │
        ├────────────────────┬─────────────────────┐
        ▼                    ▼                     ▼
    APPROVE              REJECT              (No Action)
        │                  │                       │
        ▼                  ▼                       │
┌─────────────┐    ┌─────────────┐               │
│ status='    │    │ Clear:      │               │
│ cancelled'  │    │ - reason    │               │
│             │    │ - timestamp │               │
│ Send Email: │    │             │               │
│ UserReg     │    │ User keeps  │               │
│ Cancelled   │    │ approved    │               │
└─────────────┘    │ status      │               │
        │          └─────────────┘               │
        ▼                │                        │
    User Notified    User Notified           User Remains
    Account Closed   (rejection)             Approved
```

## 🚀 API Endpoints

### 1. Request Cancellation
```http
POST /user_management/{id}/request_cancel

Request Body:
{
    "reason": "เหตุผลการขอยกเลิก"
}

Response:
- Success: Redirect to user_management.index with success message
- Error: Redirect with error message
```

### 2. Approve Cancellation
```http
PATCH /user_management/{id}/approve_cancel

Response:
- User status updated to 'cancelled'
- Email sent to user
- Admin notification updated
```

### 3. Reject Cancellation
```http
PATCH /user_management/{id}/reject_cancel

Response:
- Cancellation request cleared
- User status remains 'approved'
- User notified
```

## 📧 Email Configuration

### Required .env Settings
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

### Gmail App Password Setup

1. Go to https://myaccount.google.com/
2. Click "Security" on the left sidebar
3. Enable 2-Step Verification
4. Search "App passwords"
5. Select "Mail" and "Windows Computer"
6. Copy the generated password
7. Paste in `.env` as `MAIL_PASSWORD`

### Testing Email Locally

```php
// In tinker or test
Mail::to('test@example.com')->send(new UserRegistrationApproved($user, $approvedBy));
```

## 📨 Email Templates

### Approved Email
- Subject: "บัญชีของคุณได้รับการอนุมัติแล้ว"
- Template: `resources/views/emails/user_registration_approved.blade.php`
- Contains: Name, email, role, approval date

### Rejected Email
- Subject: "คำขอลงทะเบียนถูกปฏิเสธ"
- Template: `resources/views/emails/user_registration_rejected.blade.php`
- Contains: Rejection reason, approver name, date

### Cancelled Email
- Subject: "บัญชีของคุณถูกยกเลิกแล้ว"
- Template: `resources/views/emails/user_registration_cancelled.blade.php`
- Contains: Cancellation date, account closure notice

## 🔐 Security & Validation

### Validation Checks

```php
// Request Cancellation
if ($user->isCancelled()) {
    return error('ผู้ใช้นี้ถูกยกเลิกแล้ว');
}

if ($user->hasCancellationRequest()) {
    return warning('มีคำขอยกเลิกอยู่แล้ว');
}

// Approve/Reject
if (!$user->hasCancellationRequest()) {
    return error('ไม่มีคำขอยกเลิก');
}
```

### Permission Checks

- Only authenticated users can request cancellation
- Only admin users can approve/reject
- User cannot cancel their own account (must request first)

## 🎨 UI Components

### Notification Button
```blade
<!-- Notification appears in notifications dashboard -->
<a href="{{ route('notifications.mark_and_navigate', $notification->id) }}"
   class="btn btn-sm {{ $notification->is_read ? 'btn-info' : 'btn-warning' }}">
    <i class="fa fa-ban"></i>
    {{ $notification->type === 'user_registration_cancelled' ? 'อนุมัติการยกเลิก' : '...' }}
</a>
```

### User Management View
```blade
@if ($user->hasCancellationRequest())
    <!-- Approve/Reject Buttons -->
    <form action="{{ route('user_management.approve_cancel', $user->id) }}" method="POST">
        @csrf
        @method('PATCH')
        <button type="submit" class="btn btn-sm btn-warning">
            <i class="bi bi-check"></i> อนุมัติยกเลิก
        </button>
    </form>
    
    <form action="{{ route('user_management.reject_cancel', $user->id) }}" method="POST">
        @csrf
        @method('PATCH')
        <button type="submit" class="btn btn-sm btn-secondary">
            <i class="bi bi-x"></i> ปฏิเสธยกเลิก
        </button>
    </form>
@elseif ($user->status === 'cancelled')
    <!-- Show Cancelled Badge -->
    <span class="badge bg-danger">
        <i class="bi bi-ban"></i> ปิดใช้งาน
    </span>
@endif
```

## 🧪 Testing Checklist

- [ ] User can request cancellation
- [ ] Cancellation reason is required
- [ ] Admin receives notification
- [ ] Admin can approve cancellation
- [ ] Admin can reject cancellation
- [ ] Cancelled user cannot login
- [ ] Email sent on approval
- [ ] Email sent on rejection
- [ ] Email sent on cancellation
- [ ] Notification routes correctly
- [ ] User status updates correctly
- [ ] Cancellation fields cleared on rejection

## 📊 Database Queries

### Find Pending Cancellations
```sql
SELECT * FROM users 
WHERE status = 'approved' 
AND cancellation_requested_at IS NOT NULL;
```

### Find Cancelled Users
```sql
SELECT * FROM users 
WHERE status = 'cancelled';
```

### Check Notification History
```sql
SELECT * FROM notifications 
WHERE type = 'user_registration_cancelled' 
ORDER BY created_at DESC;
```

## 🔧 Troubleshooting

### Issue: Email Not Sending
**Solution**: Check MAIL_DRIVER in .env
```env
MAIL_DRIVER=smtp  # Not 'mail' or 'sendmail'
```

### Issue: Notification Not Appearing
**Solution**: Check route registration
```php
# In routes/web.php
Route::patch('/{id}/approve_cancel', [UserManagementController::class, 'approveCancelRegistration'])
    ->name('user_management.approve_cancel');
```

### Issue: User Can't Request Cancellation
**Solution**: Check user status
```php
if ($user->status !== 'approved') {
    // User must be approved to request cancellation
}
```

## 📞 Related Documentation

- [Cancel Request UI Update](./CANCEL_REQUEST_UI_UPDATE.md)
- [Payment Approval System](./APPROVAL_SYSTEM_COMPLETE.md)
- [Notification System Expansion](./NOTIFICATION_SYSTEM_EXPANSION.md)
- [Role Permission System](./ROLE_PERMISSION_SYSTEM.md)

## 📝 File Structure

```
app/
├── Http/Controllers/
│   ├── UserManagementController.php (updated)
│   └── NotificationController.php (updated)
├── Mail/
│   ├── UserRegistrationApproved.php (new)
│   ├── UserRegistrationRejected.php (new)
│   └── UserRegistrationCancelled.php (new)
├── Models/
│   └── User.php (updated)
├── Helpers/
│   └── NotificationHelper.php (updated)

database/
└── migrations/
    └── 2025_10_22_030218_add_cancellation_fields_to_users.php (new)

resources/views/
├── emails/
│   ├── user_registration_approved.blade.php (new)
│   ├── user_registration_rejected.blade.php (new)
│   └── user_registration_cancelled.blade.php (new)
└── admin/
    ├── notifications/index.blade.php (updated)
    └── user_management/index.blade.php (updated)

routes/
└── web.php (updated - 3 new routes)
```

## ✅ Implementation Status

- [x] Database migration created and run
- [x] User model updated with new methods
- [x] UserManagementController enhanced with 3 new methods
- [x] NotificationHelper updated with email methods
- [x] NotificationController routing updated
- [x] 3 Mail classes created
- [x] 3 Email templates created
- [x] Routes added
- [x] UI buttons added to user management
- [x] Notification icons updated
- [x] All syntax validated
- [x] Documentation created

## 🎉 Ready for Testing!

The user registration cancellation workflow is now fully implemented. Test it in your environment and adjust email templates as needed.
