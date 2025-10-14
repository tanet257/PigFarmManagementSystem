# ✅ ระบบอนุมัติการลงทะเบียน (Simple User Registration Approval)

**วันที่:** 14 ตุลาคม 2568  
**สถานะ:** ✅ **เสร็จสมบูรณ์ - เรียบง่าย ไม่ซับซ้อน**

---

## 🎯 แนวคิดหลัก

**"Register ครั้งแรกรออนุมัติ - Login ครั้งต่อไปไม่ต้องรออีก"**

- ✅ ผู้ใช้ Register → สถานะ "pending" (รออนุมัติ)
- ✅ Admin อนุมัติ → สถานะ "approved" + กำหนด Role
- ✅ ผู้ใช้ Login → ถ้าอนุมัติแล้วเข้าได้เลย ไม่ต้องตรวจสอบทุกครั้ง
- ❌ **ไม่มี** middleware ที่ตรวจสอบทุก request
- ❌ **ไม่มี** การบังคับ logout หลัง login สำเร็จ

---

## 📊 Workflow

```
[ผู้ใช้ Register]
    ↓
status = 'pending'
    ↓
แจ้งเตือน: "รอการอนุมัติ"
    ↓
[Admin อนุมัติ + เลือก Role]
    ↓
status = 'approved'
approved_by = admin_id
approved_at = now()
    ↓
[ผู้ใช้ Login]
    ↓
ตรวจสอบ status ตอน Login
    ↓
✅ approved → Login ได้เลย!
❌ pending → แจ้ง "รอการอนุมัติ"
❌ rejected → แจ้ง "ถูกปฏิเสธ: เหตุผล"
```

---

## 🗄️ Database Schema

### Migration: `add_approval_fields_to_users_table`

```sql
ALTER TABLE users ADD (
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);
```

### ฟิลด์ที่เพิ่ม:

| ฟิลด์ | ประเภท | รายละเอียด |
|------|--------|-----------|
| `status` | enum | 'pending', 'approved', 'rejected' |
| `approved_by` | foreignId | FK → users.id (ผู้อนุมัติ) |
| `approved_at` | timestamp | วันเวลาที่อนุมัติ |
| `rejection_reason` | text | เหตุผลที่ปฏิเสธ |

---

## 🔧 Backend Implementation

### 1. **User Model** (`app/Models/User.php`)

เพิ่ม methods:

```php
// ตรวจสอบสถานะ
public function isApproved() { return $this->status === 'approved'; }
public function isPending() { return $this->status === 'pending'; }
public function isRejected() { return $this->status === 'rejected'; }

// ความสัมพันธ์
public function approvedBy() { 
    return $this->belongsTo(User::class, 'approved_by'); 
}
```

### 2. **CreateNewUser** (`app/Actions/Fortify/CreateNewUser.php`)

สร้าง user ใหม่ด้วย status = 'pending':

```php
$user = User::create([
    'name' => $input['name'],
    'email' => $input['email'],
    'password' => Hash::make($input['password']),
    'status' => 'pending', // รอการอนุมัติ
]);

session()->flash('info', 'ลงทะเบียนสำเร็จ! กรุณารอการอนุมัติจากผู้ดูแลระบบ');
```

### 3. **FortifyServiceProvider** (`app/Providers/FortifyServiceProvider.php`)

ตรวจสอบสถานะตอน Login:

```php
Fortify::authenticateUsing(function (Request $request) {
    $user = User::where('email', $request->email)->first();

    if ($user && Hash::check($request->password, $user->password)) {
        // ตรวจสอบสถานะ
        if ($user->isPending()) {
            throw ValidationException::withMessages([
                'email' => ['บัญชีของคุณกำลังรอการอนุมัติจากผู้ดูแลระบบ'],
            ]);
        }

        if ($user->isRejected()) {
            throw ValidationException::withMessages([
                'email' => ['บัญชีของคุณถูกปฏิเสธ: ' . $user->rejection_reason],
            ]);
        }

        return $user; // approved → login ได้
    }

    return null;
});
```

**จุดสำคัญ:** ตรวจสอบแค่ตอน **Login** เท่านั้น ไม่ใช่ทุก request!

### 4. **UserManagementController**

```php
// อนุมัติผู้ใช้
public function approve(Request $request, $id) {
    $user->update([
        'status' => 'approved',
        'approved_by' => auth()->id(),
        'approved_at' => now(),
    ]);
    
    $user->roles()->sync($request->role_ids); // กำหนด roles
}

// ปฏิเสธผู้ใช้
public function reject(Request $request, $id) {
    $user->update([
        'status' => 'rejected',
        'approved_by' => auth()->id(),
        'approved_at' => now(),
        'rejection_reason' => $request->rejection_reason,
    ]);
}

// อัพเดท roles
public function updateRoles(Request $request, $id) {
    $user->roles()->sync($request->role_ids);
}
```

---

## 🎨 Frontend (Admin Panel)

### หน้า User Management (`/user_management`)

**Features:**
- ✅ สรุปจำนวนผู้ใช้ตาม status (pending/approved/rejected)
- ✅ กรองตาม status
- ✅ ค้นหาตามชื่อและอีเมล
- ✅ แสดงรายละเอียดผู้ใช้
- ✅ อนุมัติพร้อมเลือก Role
- ✅ ปฏิเสธพร้อมระบุเหตุผล
- ✅ จัดการ Role ของผู้ใช้ที่อนุมัติแล้ว
- ✅ ลบผู้ใช้

**Modals:**
1. **Approve Modal** - เลือก roles ก่อนอนุมัติ
2. **Reject Modal** - ระบุเหตุผลที่ปฏิเสธ
3. **Update Role Modal** - แก้ไข roles ของผู้ใช้ที่อนุมัติแล้ว
4. **View Modal** - ดูรายละเอียดผู้ใช้

---

## 🔐 Routes

```php
Route::prefix('user_management')
    ->middleware(['permission:manage_users'])
    ->group(function () {
        Route::get('/', [UserManagementController::class, 'index']);
        Route::post('/{id}/approve', [UserManagementController::class, 'approve']);
        Route::post('/{id}/reject', [UserManagementController::class, 'reject']);
        Route::post('/{id}/update-roles', [UserManagementController::class, 'updateRoles']);
        Route::delete('/{id}', [UserManagementController::class, 'destroy']);
    });
```

**Permission Required:** `manage_users` (เฉพาะ Admin)

---

## 🚀 การใช้งาน

### สำหรับผู้ใช้ใหม่:

1. **ไปที่หน้า Register** (`/register`)
2. **กรอกข้อมูล:**
   - ชื่อ
   - อีเมล
   - รหัสผ่าน (ต้องตาม Password Policy)
   - เบอร์โทร (ถ้ามี)
   - ที่อยู่ (ถ้ามี)
3. **กดลงทะเบียน**
4. **เห็นข้อความ:** "ลงทะเบียนสำเร็จ! กรุณารอการอนุมัติจากผู้ดูแลระบบ"
5. **รอ Admin อนุมัติ**
6. **เมื่อได้รับการอนุมัติ → Login ได้เลย!**

### สำหรับ Admin:

1. **ไปที่** `/user_management`
2. **เห็นผู้ใช้ที่รอการอนุมัติ** (status = pending)
3. **คลิก "อนุมัติ"**
4. **เลือก Role** (admin, manager, staff, cashier)
5. **กดยืนยัน**
6. **ผู้ใช้สามารถ Login ได้แล้ว!**

---

## 🎭 User Experience

### ผู้ใช้ Register:
```
[กรอกข้อมูล] → [กดลงทะเบียน]
    ↓
✅ "ลงทะเบียนสำเร็จ! กรุณารอการอนุมัติ"
```

### ผู้ใช้ Login (รออนุมัติ):
```
[กรอก Email/Password] → [กด Login]
    ↓
❌ "บัญชีของคุณกำลังรอการอนุมัติจากผู้ดูแลระบบ กรุณารอสักครู่..."
```

### ผู้ใช้ Login (อนุมัติแล้ว):
```
[กรอก Email/Password] → [กด Login]
    ↓
✅ เข้าสู่ระบบสำเร็จ!
```

### ผู้ใช้ Login (ถูกปฏิเสธ):
```
[กรอก Email/Password] → [กด Login]
    ↓
❌ "บัญชีของคุณถูกปฏิเสธ: [เหตุผล]"
```

---

## 📁 ไฟล์ที่สร้าง/แก้ไข

### Database:
1. ✅ `database/migrations/2025_10_14_003413_add_approval_fields_to_users_table.php`

### Models:
2. ✅ `app/Models/User.php` (เพิ่ม methods และ fillable)

### Actions:
3. ✅ `app/Actions/Fortify/CreateNewUser.php` (เพิ่ม status = 'pending')

### Providers:
4. ✅ `app/Providers/FortifyServiceProvider.php` (เพิ่มการตรวจสอบตอน login)

### Controllers:
5. ✅ `app/Http/Controllers/UserManagementController.php` (NEW)

### Routes:
6. ✅ `routes/web.php` (เพิ่ม user management routes)

### Views:
7. ✅ `resources/views/admin/user_management/index.blade.php` (NEW)

### Kernel:
8. ✅ `app/Http/Kernel.php` (ลบ CheckUserApproved middleware ออก)

---

## ✅ สิ่งที่ทำไว้แล้ว

1. ✅ สร้าง migration เพิ่มฟิลด์ approval
2. ✅ รัน migration สำเร็จ
3. ✅ อัพเดท User model
4. ✅ แก้ไข CreateNewUser ให้สร้าง user ด้วย status = 'pending'
5. ✅ เพิ่มการตรวจสอบสถานะตอน Login ใน FortifyServiceProvider
6. ✅ สร้าง UserManagementController
7. ✅ เพิ่ม routes สำหรับ user management
8. ✅ สร้างหน้า User Management (index.blade.php)
9. ✅ อัพเดท user ที่มีอยู่ให้เป็น 'approved'
10. ✅ ลบ CheckUserApproved middleware (ไม่จำเป็น)

---

## 🧪 การทดสอบ

### Test Case 1: Register ใหม่
```
1. ไปที่ /register
2. กรอกข้อมูล
3. กดลงทะเบียน
4. เห็นข้อความ "รอการอนุมัติ" ✅
```

### Test Case 2: Login ก่อนอนุมัติ
```
1. ลอง Login ด้วยบัญชีที่ pending
2. เห็นข้อความ "รอการอนุมัติ" ✅
3. ไม่สามารถเข้าสู่ระบบได้ ✅
```

### Test Case 3: Admin อนุมัติ
```
1. Admin ไปที่ /user_management
2. เห็นผู้ใช้ที่รอการอนุมัติ
3. คลิก "อนุมัติ"
4. เลือก Role
5. กดยืนยัน
6. User status = 'approved' ✅
```

### Test Case 4: Login หลังอนุมัติ
```
1. ลอง Login ด้วยบัญชีที่อนุมัติแล้ว
2. เข้าสู่ระบบสำเร็จ ✅
3. ใช้งานได้ปกติ ✅
```

### Test Case 5: Login ครั้งต่อไป
```
1. Logout
2. Login อีกครั้ง
3. เข้าได้เลยโดยไม่ต้องรออนุมัติอีก ✅
```

---

## 💡 ข้อดีของแนวทางนี้

1. ✅ **เรียบง่าย** - ไม่ซับซ้อน ไม่ต้องตรวจสอบทุก request
2. ✅ **ประสิทธิภาพดี** - ตรวจสอบแค่ตอน login เท่านั้น
3. ✅ **UX ดี** - ผู้ใช้ไม่ถูก logout กะทันหัน
4. ✅ **ความปลอดภัย** - ยังคงป้องกันการเข้าถึงของผู้ใช้ที่ไม่ได้รับอนุมัติ
5. ✅ **ยืดหยุ่น** - Admin สามารถจัดการ Role ได้ตอนอนุมัติหรือทีหลัง
6. ✅ **เหมาะกับฟาร์ม** - ไม่ต้องการระบบที่ซับซ้อน

---

## 🎯 สรุป

**ระบบนี้เหมาะกับฟาร์มขนาดเล็ก-กลาง ที่ต้องการ:**
- ✅ ควบคุมการเข้าถึงของผู้ใช้ใหม่
- ✅ กำหนด Role ให้ผู้ใช้โดย Admin
- ✅ ไม่ต้องการความซับซ้อนมากเกินไป
- ✅ ประสิทธิภาพและ UX ที่ดี

**ผู้ใช้ Register ครั้งเดียว → Admin อนุมัติ → ใช้งานได้ตลอด!** 🎉

---

**สถานะ:** ✅ **พร้อมใช้งาน**  
**ผู้พัฒนา:** GitHub Copilot  
**วันที่:** 14 ตุลาคม 2568
