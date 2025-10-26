# ✅ Combined Payment Approvals Tab

## Changes Made

### 1. Merged "รอการอนุมัติชำระเงิน" Tab into "รอการอนุมัติ" Tab

**File:** `resources/views/admin/payment_approvals/index.blade.php`

**Before:**
- Tab 1: "รอการอนุมัติชำระเงิน" (Pending Payment Approvals)
- Tab 2: "รอการอนุมัติขาย" (Pending Sales Approvals)

**After:**
- Tab 1: "รอการอนุมัติ" (Combined Pending Approvals)
  - Shows BOTH pending payment records AND pending pig sales records in one table
  - Badge shows total count of both types: `{{ ($pendingPayments->total()) + ($pendingPigSales->total()) }}`
  - Other tabs unchanged: "อนุมัติแล้ว", "ปฏิเสธแล้ว", "คำขอยกเลิก"

### 2. Combined Table Shows Both Types

**Pending Payments + Pending Pig Sales in One Table:**

| Column | Description |
|--------|-------------|
| **ประเภท (Type)** | Badge showing "ชำระเงิน" (Payment) or "หมูปกติ"/"หมูตาย" (Regular/Dead Pig) |
| **รายละเอียด (Details)** | - For payment: เลขที่, วิธีชำระ (payment method) |
| | - For pig sale: ผู้ซื้อ (buyer), จำนวน (quantity) |
| **ฟาร์ม/รุ่น (Farm/Batch)** | Farm name + Batch code |
| **จำนวนเงิน (Amount)** | Baht amount (for payments and sales) |
| **บันทึกโดย (Recorded by)** | User who recorded the transaction |
| **วันที่ (Date)** | Created date and time |
| **การกระทำ (Actions)** | Approve/Reject buttons with correct routing |

### 3. Action Buttons

**For Payment Records:**
- ✅ **Approve:** Routes to `payment_approvals.approve_payment`
- ❌ **Reject:** Routes to `payment_approvals.reject_payment`
- All inline (no modal)

**For Pig Sale Records:**
- ✅ **Approve:** Opens modal, routes to `payment_approvals.approve_pig_sale`
- ❌ **Reject:** Opens modal with reason field, routes to `payment_approvals.reject_pig_sale`

### 4. Benefits

✅ **Simpler Navigation** - Only ONE "รอการอนุมัติ" tab to check for all pending approvals  
✅ **Efficient Review** - Admin sees payments and sales side-by-side  
✅ **Clear Distinction** - Type badge shows immediately which type of record it is  
✅ **Less Confusion** - No need to switch between two tabs for related items  
✅ **Consistent UX** - All pending items show in one place  

## Implementation Details

### Tab Navigation
```blade
<li class="nav-item">
    <a class="nav-link active" id="pending-tab" data-bs-toggle="tab" href="#pending" role="tab">
        <i class="bi bi-hourglass-split"></i> รอการอนุมัติ
        <span class="badge bg-warning ms-2">
            {{ ($pendingPayments->total() ?? 0) + ($pendingPigSales->total() ?? 0) }}
        </span>
    </a>
</li>
```

### Combined Table Loop
```blade
{{-- Pending Payments --}}
@forelse($pendingPayments as $payment)
    <tr>
        <td><span class="badge bg-info">ชำระเงิน</span></td>
        <!-- ... payment details ... -->
    </tr>
@empty @endforelse

{{-- Pending Pig Sales --}}
@forelse($pendingPigSales as $pigSale)
    <tr>
        <td><span class="badge bg-{{ $pigSale->sell_type === 'หมูตาย' ? 'danger' : 'info' }}">
            {{ $pigSale->sell_type }}
        </span></td>
        <!-- ... pig sale details ... -->
    </tr>
@empty @endforelse
```

## Files Modified

- ✅ `resources/views/admin/payment_approvals/index.blade.php`
  - Removed separate "Pending Payments" tab
  - Merged tab content into "Pending Approvals"
  - Combined table shows both payment and pig sale records
  - Updated tab badge to show total count

## Status Summary

- ✅ Payment and Pig Sales records now show in ONE "รอการอนุมัติ" tab
- ✅ Type badges clearly show what kind of record (Payment vs Pig Sale)
- ✅ Correct action buttons for each type
- ✅ Modals work for pig sales (reason field, etc.)
- ✅ Inline forms work for payments
- ✅ Admin approval workflow maintained

## Next Step

**Optional:** Align `cost_payment_approvals/index.blade.php` to use the same theme and button styles for consistency across the entire app.

---

🎉 **Combined tabs completed! No need to switch between tabs for payment and sales approvals anymore.**
