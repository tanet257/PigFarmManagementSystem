# ระบบ Role & Permission - เสร็จสมบูรณ์ ✅

## 📋 สรุปสิ่งที่ทำเสร็จแล้ว

### 1. ✅ Database Structure
สร้างตารางครบทั้งหมด:
- `roles` - บทบาทผู้ใช้
- `permissions` - สิทธิ์การเข้าถึง
- `role_permission` - ความสัมพันธ์ระหว่าง role กับ permission
- `role_user` - ความสัมพันธ์ระหว่าง user กับ role (สร้างใหม่)

### 2. ✅ Roles (4 roles)
| ID | Role | ชื่อไทย | คำอธิบาย |
|----|------|---------|----------|
| 1 | admin | ผู้ดูแลระบบ | สามารถทำทุกอย่างได้ |
| 2 | manager | ผู้จัดการ | อนุมัติการขาย, ดูรายงาน |
| 3 | staff | พนักงาน | บันทึกข้อมูล |
| 4 | cashier | แคชเชียร์ | บันทึกการชำระเงิน |

### 3. ✅ Permissions (16 permissions)

#### 🐷 Pig Management (4)
1. `view_pig` - ดูข้อมูลหมู
2. `create_pig` - เพิ่มข้อมูลหมู
3. `edit_pig` - แก้ไขข้อมูลหมู
4. `delete_pig` - ลบข้อมูลหมู

#### 🌾 Feed & Medicine (2)
5. `manage_feed` - จัดการอาหาร
6. `manage_medicine` - จัดการยา

#### 💰 Pig Sales (5)
7. `view_sales` - ดูข้อมูลการขาย
8. `create_sales` - บันทึกการขาย
9. `approve_sales` - **อนุมัติการขาย** ⭐
10. `process_payment` - บันทึกการชำระเงิน
11. `cancel_sales` - ยกเลิกการขาย

#### 📊 Reports & Settings (5)
12. `view_reports` - ดูรายงาน
13. `manage_users` - จัดการผู้ใช้
14. `assign_roles` - กำหนดสิทธิ์
15. `manage_notifications` - จัดการการแจ้งเตือน
16. `access_settings` - เข้าถึงการตั้งค่า

### 4. ✅ Role-Permission Matrix

| Permission | Admin | Manager | Staff | Cashier |
|-----------|-------|---------|-------|---------|
| view_pig | ✅ | ✅ | ✅ | ❌ |
| create_pig | ✅ | ❌ | ✅ | ❌ |
| edit_pig | ✅ | ❌ | ✅ | ❌ |
| delete_pig | ✅ | ❌ | ❌ | ❌ |
| manage_feed | ✅ | ❌ | ✅ | ❌ |
| manage_medicine | ✅ | ❌ | ✅ | ❌ |
| **view_sales** | ✅ | ✅ | ✅ | ✅ |
| **create_sales** | ✅ | ✅ | ✅ | ❌ |
| **approve_sales** | ✅ | ✅ | ❌ | ❌ |
| **process_payment** | ✅ | ❌ | ❌ | ✅ |
| **cancel_sales** | ✅ | ✅ | ❌ | ❌ |
| view_reports | ✅ | ✅ | ❌ | ❌ |
| manage_users | ✅ | ❌ | ❌ | ❌ |
| assign_roles | ✅ | ❌ | ❌ | ❌ |
| manage_notifications | ✅ | ❌ | ❌ | ❌ |
| access_settings | ✅ | ✅ | ❌ | ❌ |

---

## 🔧 Technical Implementation

### Models Created
```php
// User.php - มีอยู่แล้ว พร้อมใช้งาน
public function roles()
{
    return $this->belongsToMany(Role::class);
}

public function hasRole($roleName)
{
    return $this->roles->contains('name', $roleName);
}

public function hasPermission($permissionName)
{
    foreach ($this->roles as $role) {
        if ($role->permissions->contains('name', $permissionName)) {
            return true;
        }
    }
    return false;
}
```

### Middleware Created
```php
// CheckPermission.php - ✅ สร้างเรียบร้อย
// ใช้ใน routes: ->middleware('permission:permission_name')
```

### Registered in Kernel.php
```php
protected $routeMiddleware = [
    // ... existing middleware
    'permission' => \App\Http\Middleware\CheckPermission::class,
];
```

---

## 📝 วิธีใช้งาน

### 1. ตรวจสอบ Permission ใน Controller
```php
// ตรวจสอบด้วย middleware ใน route
Route::post('/pig-sales', [PigSaleController::class, 'create'])
    ->middleware('permission:create_sales');

// หรือตรวจสอบใน Controller
public function approve($id)
{
    if (!auth()->user()->hasPermission('approve_sales')) {
        abort(403, 'คุณไม่มีสิทธิ์อนุมัติการขาย');
    }
    
    // ... logic การอนุมัติ
}
```

### 2. ตรวจสอบ Permission ใน Blade
```blade
@if(auth()->user()->hasPermission('approve_sales'))
    <button class="btn btn-success" onclick="approveSale({{ $sale->id }})">
        <i class="bi bi-check-circle"></i> อนุมัติ
    </button>
@endif

@if(auth()->user()->hasPermission('process_payment'))
    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#paymentModal">
        <i class="bi bi-cash"></i> บันทึกการชำระเงิน
    </button>
@endif
```

### 3. ตรวจสอบ Role
```php
// ตรวจสอบ role
if (auth()->user()->hasRole('manager')) {
    // ทำอะไรสักอย่าง
}

// กำหนด role ให้ user
$user->roles()->attach($roleId);

// ลบ role
$user->roles()->detach($roleId);

// Sync roles (เปลี่ยนทั้งหมด)
$user->roles()->sync([1, 2]); // ให้เป็น admin และ manager
```

---

## 🎯 ขั้นตอนต่อไป: เพิ่มระบบอนุมัติการขาย

### Step 1: เพิ่ม Route
```php
// routes/web.php
Route::post('/pig-sales/{id}/approve', [PigSaleController::class, 'approve'])
    ->name('pig_sale.approve')
    ->middleware('permission:approve_sales');
```

### Step 2: เพิ่ม Method ใน Controller
```php
// PigSaleController.php
public function approve($id)
{
    DB::beginTransaction();
    try {
        $pigSale = PigSale::findOrFail($id);
        
        // ตรวจสอบว่ายังไม่ได้อนุมัติ
        if ($pigSale->approved_at) {
            return redirect()->back()->with('error', 'การขายนี้ได้รับการอนุมัติแล้ว');
        }
        
        $pigSale->update([
            'approved_by' => auth()->user()->name,
            'approved_at' => now(),
            'sale_status' => 'อนุมัติแล้ว'
        ]);
        
        DB::commit();
        
        return redirect()->route('pig_sale.index')
            ->with('success', 'อนุมัติการขายสำเร็จ');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
    }
}
```

### Step 3: เพิ่มปุ่มอนุมัติใน View
```blade
<!-- pig_sales/index.blade.php -->
@if(auth()->user()->hasPermission('approve_sales') && !$sell->approved_at)
    <button type="button" class="btn btn-sm btn-success" 
            data-bs-toggle="modal" 
            data-bs-target="#approveModal{{ $sell->id }}">
        <i class="bi bi-check-circle"></i> อนุมัติ
    </button>
@endif

<!-- Approve Modal -->
<div class="modal fade" id="approveModal{{ $sell->id }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">ยืนยันการอนุมัติ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('pig_sale.approve', $sell->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>คุณต้องการอนุมัติการขายนี้หรือไม่?</p>
                    <table class="table table-sm">
                        <tr>
                            <td><strong>เลขที่:</strong></td>
                            <td>{{ $sell->sale_number }}</td>
                        </tr>
                        <tr>
                            <td><strong>ลูกค้า:</strong></td>
                            <td>{{ $sell->customer->customer_name ?? $sell->buyer_name }}</td>
                        </tr>
                        <tr>
                            <td><strong>ยอดรวม:</strong></td>
                            <td><strong>{{ number_format($sell->net_total, 2) }} บาท</strong></td>
                        </tr>
                        <tr>
                            <td><strong>บันทึกโดย:</strong></td>
                            <td>{{ $sell->created_by }}</td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> อนุมัติ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
```

### Step 4: แสดงสถานะการอนุมัติ
```blade
<!-- ในตารางหลัก -->
<td class="text-center">
    @if($sell->approved_at)
        <span class="badge bg-success">
            <i class="bi bi-check-circle"></i> อนุมัติแล้ว
        </span>
        <small class="d-block text-muted">
            {{ $sell->approved_by }}
        </small>
    @else
        <span class="badge bg-warning">
            <i class="bi bi-clock"></i> รออนุมัติ
        </span>
    @endif
</td>

<!-- ใน Modal รายละเอียด -->
@if($sell->approved_at)
    <tr>
        <td><strong>อนุมัติโดย:</strong></td>
        <td>
            <i class="bi bi-check-circle text-success"></i> 
            {{ $sell->approved_by }}
        </td>
    </tr>
    <tr>
        <td><strong>วันที่อนุมัติ:</strong></td>
        <td>{{ \Carbon\Carbon::parse($sell->approved_at)->format('d/m/Y H:i') }}</td>
    </tr>
@else
    <tr>
        <td colspan="2">
            <span class="badge bg-warning">
                <i class="bi bi-clock"></i> รออนุมัติ
            </span>
        </td>
    </tr>
@endif
```

---

## 🔒 Security Best Practices

### 1. ป้องกันการแก้ไขข้อมูลที่อนุมัติแล้ว
```php
// ใน Controller ก่อน update/delete
if ($pigSale->approved_at && !auth()->user()->hasRole('admin')) {
    return redirect()->back()->with('error', 'ไม่สามารถแก้ไขรายการที่อนุมัติแล้ว');
}
```

### 2. ป้องกันการอนุมัติตัวเอง
```php
// ใน approve method
if ($pigSale->created_by === auth()->user()->name && !auth()->user()->hasRole('admin')) {
    return redirect()->back()->with('error', 'ไม่สามารถอนุมัติรายการของตัวเองได้');
}
```

### 3. Log การทำงาน
```php
use Illuminate\Support\Facades\Log;

Log::info('Sale approved', [
    'sale_id' => $pigSale->id,
    'sale_number' => $pigSale->sale_number,
    'approved_by' => auth()->user()->name,
    'approved_at' => now(),
]);
```

---

## 📊 Workflow การขายหมูแบบเต็ม

```
1. พนักงาน (Staff)
   ↓ บันทึกการขาย
   └─ created_by = "Staff Name"
   └─ sale_status = "รออนุมัติ"

2. ผู้จัดการ (Manager)
   ↓ อนุมัติการขาย
   └─ approved_by = "Manager Name"
   └─ approved_at = "2025-10-13 15:30:00"
   └─ sale_status = "อนุมัติแล้ว"
   └─ payment_status = "รอชำระ"

3. แคชเชียร์ (Cashier)
   ↓ บันทึกการชำระเงิน
   └─ paid_amount += จำนวนเงิน
   └─ balance -= จำนวนเงิน
   └─ payment_status = "ชำระแล้ว" / "ชำระบางส่วน"
   └─ receipt_file = "url_to_file"
```

---

## 🎓 Commands สำหรับการจัดการ

### ตรวจสอบข้อมูล
```bash
# ดู roles ทั้งหมด
php artisan tinker
>>> App\Models\Role::all();

# ดู permissions ทั้งหมด
>>> App\Models\Permission::all();

# ดู user พร้อม roles
>>> App\Models\User::with('roles')->get();

# ตรวจสอบ permission ของ user
>>> $user = App\Models\User::find(1);
>>> $user->hasPermission('approve_sales');
```

### กำหนด Role ให้ User
```bash
php artisan tinker
>>> $user = App\Models\User::find(1);
>>> $user->roles()->attach(2); # ให้เป็น manager
>>> $user->roles()->sync([1, 2]); # ให้เป็น admin และ manager
```

### Reset ข้อมูล
```bash
# Reset seeders
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=RolePermissionSeeder
```

---

## ✅ Checklist ระบบสำเร็จแล้ว

- [x] สร้างตาราง roles, permissions, role_permission, role_user
- [x] สร้าง Models พร้อม relationships
- [x] สร้าง Seeders สำหรับข้อมูลเริ่มต้น
- [x] สร้าง Middleware สำหรับตรวจสอบ permission
- [x] ลงทะเบียน Middleware ใน Kernel.php
- [x] บันทึก created_by เมื่อสร้างการขาย
- [x] เตรียม fields approved_by, approved_at สำหรับการอนุมัติ

---

## 🚀 พร้อมใช้งาน!

ระบบ Role & Permission พร้อมแล้ว! ขั้นต่อไปคือ:
1. ✅ เพิ่มฟีเจอร์อนุมัติการขาย (approve sales)
2. เพิ่มหน้าจัดการ users & roles
3. เพิ่ม logs สำหรับ audit trail
4. เพิ่มการแจ้งเตือนเมื่อมีรายการรออนุมัติ

---

**สร้างเมื่อ:** 13 ตุลาคม 2025  
**สถานะ:** ✅ เสร็จสมบูรณ์  
**พร้อมใช้งาน:** ✅ Yes
