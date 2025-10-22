# ✅ Cancel Request UI Display - COMPLETED

## Summary
Updated `payment_approvals/index.blade.php` to display cancel requests alongside payment approvals in the admin dashboard.

## Changes Made

### 1. **Pending Tab** - Added Cancel Requests Section
- Displays all pending cancellation requests with status badge
- Shows: Order #, Sale ID, Quantity, Requester, Request Date, Reason
- Two action buttons:
  - **Approve Button** (`btn-success`) - Opens approval modal
  - **Reject Button** (`btn-danger`) - Opens rejection modal

#### Approve Cancel Modal
```blade
Form Route: payment_approvals.approve_cancel_sale
POST Data:
  - approval_notes (optional)
Action: Calls PaymentApprovalController::approveCancelSale()
Effect: Marks notification as approved, calls confirmCancel(), returns pigs, recalculates profit
```

#### Reject Cancel Modal
```blade
Form Route: payment_approvals.reject_cancel_sale
POST Data:
  - rejection_reason (required)
Action: Calls PaymentApprovalController::rejectCancelSale()
Effect: Marks notification as rejected, sale remains active, user can retry
```

### 2. **Approved Tab** - Added Approved Cancellations Section
- Shows all completed cancellations with success badge
- Displays: Order #, Sale ID, Quantity, Requester, Approval Date
- View-only with detail link
- Separated from regular payment approvals by `<hr>` divider

### 3. **Rejected Tab** - Added Rejected Cancellations Section
- Shows all rejected cancellation requests with danger badge
- Displays: Order #, Sale ID, Quantity, Requester, Rejection Date, Rejection Reason
- View-only with detail link
- Separated from regular payment rejections by `<hr>` divider

## Controller Data Mapping

### Data Passed from PaymentApprovalController::index()

```php
// Pending Section
$pendingCancelRequests = Notification::where('approval_status', 'pending')
    ->where('type', 'cancel_pig_sale')
    ->paginate();

// Approved Section
$approvedCancelRequests = Notification::where('approval_status', 'approved')
    ->where('type', 'cancel_pig_sale')
    ->paginate();

// Rejected Section
$rejectedCancelRequests = Notification::where('approval_status', 'rejected')
    ->where('type', 'cancel_pig_sale')
    ->paginate();
```

## Display Logic

### Conditional Display
All three sections only display if they have records:
```blade
@if ($pendingCancelRequests && $pendingCancelRequests->count() > 0)
    {{-- Display table --}}
@endif
```

### Data Access
Uses `relatedModel` and `related_model_id` to fetch PigSale details:
```blade
{{ $cancelRequest->relatedModel === 'PigSale'
    ? \App\Models\PigSale::find($cancelRequest->related_model_id)?->quantity
    : '-' }}
```

## User Workflows

### ✅ Approval Workflow
```
User requests cancel (destroy → creates Notification)
  ↓
Admin sees in Pending Tab with badge count
  ↓
Admin clicks "Approve" button
  ↓
Modal opens, admin enters optional approval notes
  ↓
Admin confirms → approveCancelSale() called
  ↓
Notification status = 'approved'
  ↓
Move to Approved Tab
  ↓
Pigs returned, sale soft-deleted, profit recalculated
```

### ✅ Rejection Workflow
```
User requests cancel
  ↓
Admin sees in Pending Tab
  ↓
Admin clicks "Reject" button
  ↓
Modal opens, admin enters REQUIRED rejection reason
  ↓
Admin confirms → rejectCancelSale() called
  ↓
Notification status = 'rejected'
  ↓
Move to Rejected Tab
  ↓
Sale remains active, payment/cancel buttons still visible
  ↓
User can request cancel again or process payment
```

## UI Components

### Status Badges
- **Pending**: `<span class="badge bg-warning text-dark">`
- **Approved**: `<span class="badge bg-success">`
- **Rejected**: `<span class="badge bg-danger">`

### Table Styling
- **Pending**: `table-warning` background
- **Approved**: `table-success` background
- **Rejected**: `table-danger` background

### Buttons
- **Approve**: `btn-success` with checkmark icon
- **Reject**: `btn-danger` with X icon
- **Detail**: `btn-info` with eye icon

## Modals

### Cancel Approve Modal
- Title: "อนุมัติการยกเลิกการขาย" (Approve Cancellation)
- Header: Green (bg-success)
- Shows: Original cancellation reason in alert box
- Field: approval_notes (optional textarea)
- Submit Button: Green "อนุมัติการยกเลิก" (Approve Cancellation)

### Cancel Reject Modal
- Title: "ปฏิเสธการยกเลิกการขาย" (Reject Cancellation)
- Header: Red (bg-danger)
- Shows: Original cancellation reason in alert box
- Field: rejection_reason (required textarea)
- Submit Button: Red "ปฏิเสธ" (Reject)

## Integration Points

### Routes Required
```php
Route::patch('/{notificationId}/approve-cancel-sale',
    [PaymentApprovalController::class, 'approveCancelSale'])
    ->name('payment_approvals.approve_cancel_sale');

Route::patch('/{notificationId}/reject-cancel-sale',
    [PaymentApprovalController::class, 'rejectCancelSale'])
    ->name('payment_approvals.reject_cancel_sale');
```

### Controller Methods Required
- `PaymentApprovalController::approveCancelSale($notificationId)`
- `PaymentApprovalController::rejectCancelSale(Request $request, $notificationId)`

### Helper Methods
- `PigSaleController::confirmCancel($id)` - Called after approval

## File Changes Summary
- **File**: `resources/views/admin/payment_approvals/index.blade.php`
- **Lines Added**: ~180 lines
- **Sections Added**: 3 (pending, approved, rejected cancel request sections)
- **Modals Added**: 2 (approve/reject cancel modals)
- **PHP Syntax**: ✅ No errors

## Testing Checklist

- [ ] View payment_approvals page and see cancel request counts
- [ ] Request cancellation from pig_sales/index
- [ ] Verify notification appears in Pending tab
- [ ] Click Approve button and fill form
- [ ] Verify sale moves to Approved tab after approval
- [ ] Verify profit recalculated
- [ ] Request cancellation on different sale
- [ ] Click Reject button and fill form
- [ ] Verify sale moves to Rejected tab
- [ ] Verify payment/cancel buttons still visible on rejected sale
- [ ] Request cancellation again on rejected sale
- [ ] Verify new request shows in Pending tab

## Status
✅ **COMPLETE** - Cancel request UI fully integrated and ready for testing
