# 👥 User Role Management System - Implementation Summary

## ✅ Completed Updates

### 1. **User Model Enhancement** (`app/Models/User.php`)
- ✅ Added `usertype` field to fillable array
- ✅ Relationship: `roles()` -> belongs to many via `role_user` table

```php
protected $fillable = [
    'name',
    'email',
    'password',
    'phone',
    'address',
    'usertype',      // ← เพิ่มใหม่
    'status',
    'approved_by',
    'approved_at',
    'rejection_reason',
];
```

### 2. **UserManagementController Updates** (`app/Http/Controllers/UserManagementController.php`)

#### **approve() Method**
- ✅ Accepts `role_ids[]` as array from radio button (single value)
- ✅ Auto-sets `usertype` from selected role name
- ✅ Creates `role_user` relationship via `sync()`
- ✅ Updates success message with role information

```php
// ดึงชื่อ role แรก เพื่อใช้เป็น usertype
$roleIds = $validated['role_ids'];
$primaryRole = Role::find($roleIds[0]);
$usertype = $primaryRole ? $primaryRole->name : 'staff';

// อนุมัติ user พร้อมอัปเดท usertype
$user->update([
    'status' => 'approved',
    'usertype' => $usertype,  // ← ตั้งค่าจาก role
    'approved_by' => auth()->id(),
    'approved_at' => now(),
    'rejection_reason' => null,
]);

// กำหนด roles
$user->roles()->sync($validated['role_ids']);
```

#### **updateRoles() Method**
- ✅ Updates existing user's roles
- ✅ Automatically updates `usertype` from new role
- ✅ Syncs `role_user` relationship

```php
// ดึงชื่อ role แรก เพื่อใช้เป็น usertype
$roleIds = $validated['role_ids'];
$primaryRole = Role::find($roleIds[0]);
$usertype = $primaryRole ? $primaryRole->name : $user->usertype;

// อัพเดท usertype
$user->update([
    'usertype' => $usertype,
]);

// อัพเดท roles
$user->roles()->sync($validated['role_ids']);
```

#### **New Helper Methods**
- ✅ `getUserTypeOptions()` - API endpoint to get all available roles
- ✅ `getUserRoles()` - API endpoint to get user's current roles

```php
// GET /user_management/api/user_type_options
// Returns: { success: true, roles: [...] }

// GET /user_management/api/user_roles/{id}
// Returns: { success: true, user: { id, name, usertype, roles: [...] } }
```

### 3. **Routes Configuration** (`routes/web.php` & `routes/api.php`)

**Web Routes**
```php
Route::prefix('user_management')->middleware(['auth', 'prevent.cache', 'permission:manage_users'])->group(function () {
    Route::get('/', [UserManagementController::class, 'index']);
    Route::post('/{id}/approve', [UserManagementController::class, 'approve']);
    Route::post('/{id}/update_roles', [UserManagementController::class, 'updateRoles']);
    Route::get('/api/user_type_options', [UserManagementController::class, 'getUserTypeOptions']);
    Route::get('/api/user_roles/{id}', [UserManagementController::class, 'getUserRoles']);
    // ... other routes
});
```

### 4. **View Updates** (`resources/views/admin/user_management/index.blade.php`)

#### **Approve Modal**
- ✅ Changed to use radio buttons for role selection
- ✅ Single role selection only (enforced by HTML radio type)
- ✅ Sends `role_ids[]` with single value

```blade
@foreach ($roles as $role)
    <div class="form-check">
        <input class="form-check-input" type="radio" name="role_ids[]"
            value="{{ $role->id }}"
            id="role{{ $role->id }}_{{ $user->id }}">
        <label class="form-check-label"
            for="role{{ $role->id }}_{{ $user->id }}">
            <strong>{{ $role->name }}</strong>
        </label>
    </div>
@endforeach
```

#### **Update Role Modal**
- ✅ Same radio button implementation as approve modal
- ✅ Pre-checks user's current role

```blade
{{ $user->roles->contains($role->id) ? 'checked' : '' }}
```

#### **JavaScript Validation**
- ✅ `validateRoleSelection()` - Ensures radio button is selected before approve
- ✅ `validateUpdateRoleSelection()` - Ensures radio button is selected before update
- ✅ `showSnackbar()` - Displays validation feedback

```javascript
function validateRoleSelection(button) {
    const form = button.closest('form');
    const selectedRole = form.querySelector('input[name="role_ids[]"]:checked');
    
    if (!selectedRole) {
        showSnackbar('⚠️ กรุณาเลือก Role ก่อนอนุมัติ', 'warning');
        return false;
    }
    return true;
}
```

### 5. **Database Structure**

**users table** (existing + new)
```
id
name
email
email_verified_at
usertype          ← เพิ่มใหม่
phone
address
last_login_at
password
status
approved_by
approved_at
rejection_reason
created_at
updated_at
```

**role_user table** (existing - many-to-many)
```
id
user_id
role_id
created_at
updated_at
```

**roles table** (existing)
```
id (1=admin, 2=staff, 3=manager)
name
description
created_at
updated_at
```

## 🔄 User Approval Flow

```
1. Admin views pending users
   ↓
2. Admin clicks "อนุมัติ" button → Approve Modal opens
   ↓
3. Admin selects ONE role (radio button enforces single selection)
   ↓
4. Admin clicks "ยืนยันอนุมัติ" 
   ↓
5. Validation checks that role is selected
   ↓
6. Form submits to POST /user_management/{id}/approve
   ↓
7. Controller:
   - Creates role_user relationship (user ← → role via sync())
   - Sets usertype = role.name
   - Updates user status to 'approved'
   - Sends notification to user
   ↓
8. Success message displays with user name and role
```

## 🔄 User Role Update Flow

```
1. Admin views approved users
   ↓
2. Admin clicks "จัดการ Role" button → Update Role Modal opens
   ↓
3. Modal displays user info and current role (pre-checked)
   ↓
4. Admin selects NEW role (radio button)
   ↓
5. Admin clicks "บันทึก"
   ↓
6. Validation checks that role is selected
   ↓
7. Form submits to POST /user_management/{id}/update_roles
   ↓
8. Controller:
   - Updates usertype = new role.name
   - Updates role_user relationship (replaces old role)
   - Shows success message
   ↓
9. User's role is changed
```

## 📊 Available Roles

Based on database:
1. **admin** - Administrator (full access)
2. **staff** - Staff member (limited access)
3. **manager** - Manager (moderate access)

## ✨ Key Features

✅ **Single Role per User** - Radio buttons enforce one selection
✅ **Automatic usertype** - Set from selected role name
✅ **Dual Update** - Updates both `users.usertype` and `role_user` table
✅ **Validation** - Client-side checks before submission
✅ **Snackbar Feedback** - User-friendly notifications
✅ **Transaction Safety** - Database rollback on error
✅ **Audit Trail** - Records approved_by and approved_at
✅ **API Endpoints** - Get role options and user roles via AJAX

## 🛠️ How to Use

### Approve New User
1. Go to `/user_management`
2. Find pending user
3. Click "อนุมัติ" button
4. Select role (radio button)
5. Click "ยืนยันอนุมัติ"
6. ✅ User approved with role

### Update User Role
1. Go to `/user_management`
2. Find approved user
3. Click "จัดการ Role" button
4. Select new role
5. Click "บันทึก"
6. ✅ User role updated and usertype changed

### Check User Info
- **usertype**: Primary role (from roles.name)
- **status**: 'pending', 'approved', or 'rejected'
- **roles**: Many-to-many relationship (via role_user table)

## 🔐 Security

- ✅ Protected by `permission:manage_users` middleware
- ✅ Transaction rollback on error
- ✅ Input validation
- ✅ Audit trail with approved_by
- ✅ Error logging

## 📝 Notes

- User must have at least ONE role after approval
- usertype field stores only the primary role name (not ID)
- Radio buttons ensure only one role is selected
- role_user table maintains many-to-many relationship
- Changing role automatically updates usertype field

---

**Status**: ✅ Ready for Production
**Last Updated**: 2025-10-22
