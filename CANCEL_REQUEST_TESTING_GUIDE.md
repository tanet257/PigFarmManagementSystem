# Cancel Request UI - Testing Guide

## Pre-Testing Checklist

- [x] `PaymentApprovalController::approveCancelSale()` exists
- [x] `PaymentApprovalController::rejectCancelSale()` exists
- [x] Routes registered: `approve-cancel-sale` and `reject-cancel-sale`
- [x] View updated: `payment_approvals/index.blade.php`
- [x] Controller `index()` passes all cancel request data to view
- [x] PHP syntax validated: No errors

## Test Scenarios

### Test 1: Display Pending Cancel Requests
**Objective**: Verify cancel requests appear in Pending tab

**Steps**:
1. Go to http://localhost/admin/pig-sales
2. Click cancel button on any pig sale record
3. Confirm cancellation request (should create Notification)
4. Navigate to http://localhost/admin/payment-approvals
5. Click "Pending" tab

**Expected Results**:
- [x] See "⚠️ ขอยกเลิกการขายหมู [count]" section
- [x] Table shows:
  - Order number (loop iteration)
  - Sale ID from related PigSale
  - Quantity of pigs
  - Requester name (user who requested cancel)
  - Request date/time
  - Original cancel reason
  - Two buttons: Approve (green) and Reject (red)
- [x] Badge shows yellow background with count

**Verify Data Accuracy**:
```sql
SELECT * FROM notifications 
WHERE type = 'cancel_pig_sale' 
AND approval_status = 'pending'
ORDER BY created_at DESC;
```

---

### Test 2: Approve Cancel Request
**Objective**: Admin approves cancellation and sale gets cancelled

**Steps**:
1. In Pending tab, click green "Approve" button on a cancel request
2. Modal opens: "อนุมัติการยกเลิกการขาย"
3. Enter optional approval notes (e.g., "อนุมัติครับ")
4. Click "อนุมัติการยกเลิก" button
5. Wait for page to reload
6. Click "Approved" tab

**Expected Results**:
- [x] Modal closed without error
- [x] Notification status changes to 'approved'
- [x] Record moves to Approved tab
- [x] Shows in "✅ ยกเลิกการขายแล้ว" section
- [x] PigSale status = 'ยกเลิกการขาย'
- [x] PigSale payment_status = 'ยกเลิกการขาย'
- [x] Profit recalculated (check Profit table)
- [x] Pigs returned to batch/pen

**Verify Backend**:
```sql
-- Check notification status
SELECT id, approval_status, approval_notes, updated_at 
FROM notifications 
WHERE type = 'cancel_pig_sale' 
AND id = [notificationId]
LIMIT 1;

-- Check PigSale status
SELECT id, status, payment_status, quantity 
FROM pig_sales 
WHERE id = [pigSaleId]
LIMIT 1;

-- Check Profit recalculated
SELECT id, gross_profit, profit_margin_percent, updated_at 
FROM profits 
WHERE batch_id = [batchId]
LIMIT 1;
```

---

### Test 3: Reject Cancel Request
**Objective**: Admin rejects cancellation, sale remains active

**Steps**:
1. In Pending tab, click red "Reject" button on a cancel request
2. Modal opens: "ปฏิเสธการยกเลิกการขาย"
3. Enter rejection reason (REQUIRED) - e.g., "ข้อมูลไม่ครบถ้วน"
4. Click "ปฏิเสธ" button
5. Wait for page to reload
6. Click "Rejected" tab

**Expected Results**:
- [x] Modal closed without error
- [x] Notification status changes to 'rejected'
- [x] approval_notes set to rejection reason
- [x] Record moves to Rejected tab
- [x] Shows in "❌ ปฏิเสธการยกเลิก" section
- [x] PigSale status UNCHANGED (still 'incomplete' or whatever it was)
- [x] PigSale payment_status UNCHANGED
- [x] Profit NOT recalculated
- [x] Payment modal still visible on pig_sales/index
- [x] Cancel button still visible on pig_sales/index

**Verify Backend**:
```sql
-- Check notification status
SELECT id, approval_status, approval_notes, updated_at 
FROM notifications 
WHERE type = 'cancel_pig_sale' 
AND id = [notificationId]
LIMIT 1;

-- Check PigSale unchanged
SELECT id, status, payment_status 
FROM pig_sales 
WHERE id = [pigSaleId]
LIMIT 1;
-- Should show: status NOT 'ยกเลิกการขาย', payment_status NOT 'ยกเลิกการขาย'
```

---

### Test 4: Retry Cancel Request After Rejection
**Objective**: User can request cancellation again after admin rejects

**Steps**:
1. Go to pig_sales/index
2. Find the sale that was rejected
3. Verify cancel button is visible (not hidden)
4. Click cancel button again
5. Create new cancellation request (with different reason if desired)
6. Go back to payment_approvals
7. Verify new notification in Pending tab

**Expected Results**:
- [x] Cancel button is visible (conditional: payment_status != 'ยกเลิกการขาย')
- [x] New Notification created (type='cancel_pig_sale', status='pending')
- [x] New request appears in Pending tab
- [x] Different notification ID from rejected one

**Verify**:
```sql
SELECT id, approval_status, message, created_at 
FROM notifications 
WHERE type = 'cancel_pig_sale' 
AND related_model_id = [pigSaleId]
ORDER BY created_at DESC
LIMIT 5;
-- Should show: rejected request + new pending request
```

---

### Test 5: Cancelled Sale Controls Hidden
**Objective**: Verify payment/cancel controls hidden on cancelled sales

**Steps**:
1. Go to pig_sales/index
2. Find a sale with payment_status = 'ยกเลิกการขาย'
3. Look for payment modal button
4. Look for cancel button

**Expected Results**:
- [x] NO payment modal button visible (hidden by conditional)
- [x] NO cancel button visible (hidden by conditional)
- [x] Row shows sale as cancelled
- [x] No action buttons for this sale

**Code Check**:
```blade
<!-- In pig_sales/index.blade.php around line 303 -->
@if ($sell->payment_status != 'ชำระแล้ว' && $sell->payment_status != 'ยกเลิกการขาย')
    <!-- Payment button shown -->
@endif

@if ($sell->payment_status != 'ยกเลิกการขาย')
    <!-- Cancel button shown -->
@endif
```

---

### Test 6: Status Summary Counts
**Objective**: Verify badge counts include cancel requests

**Steps**:
1. Go to payment_approvals dashboard
2. Check status summary cards at top:
   - Pending count
   - Approved count
   - Rejected count

**Expected Results**:
- [x] Pending = (pending payments) + (pending cancels) + (pending pig_entry payments)
- [x] Approved = (approved payments) + (approved cancels) + (approved pig_entry payments)
- [x] Rejected = (rejected payments) + (rejected cancels) + (rejected pig_entry payments)

**Example**:
```
If:
  - 3 pending payments
  - 2 pending cancel requests
  - 1 pending pig_entry payment
  
Then Pending badge should show: 6
```

**Code Check**:
```blade
<!-- Status summary in index.blade.php -->
{{ ($pendingPayments->total() ?? 0) + ($pendingCancelRequests->total() ?? 0) + ($pendingNotifications->total() ?? 0) }}
```

---

### Test 7: Tab Content Switching
**Objective**: Verify all tabs display correct data

**Steps**:
1. Click "Pending" tab
2. Verify cancel requests and payments visible
3. Click "Approved" tab
4. Verify approved items visible in their sections
5. Click "Rejected" tab
6. Verify rejected items visible in their sections

**Expected Results**:
- [x] "Pending" tab shows:
  - Payment approvals table
  - Cancel requests section (if any)
- [x] "Approved" tab shows:
  - Approved payments table
  - Approved cancellations section (if any)
- [x] "Rejected" tab shows:
  - Rejected payments table
  - Rejected cancellations section (if any)
- [x] Pagination works independently for each section

---

### Test 8: Modal Form Validation
**Objective**: Verify form validation in modals

**Steps for Approve Modal**:
1. Click Approve button
2. Leave approval_notes empty (should be OK - optional)
3. Click "อนุมัติการยกเลิก"
4. Verify it submits successfully

**Steps for Reject Modal**:
1. Click Reject button
2. Leave rejection_reason empty (should be REQUIRED)
3. Click "ปฏิเสธ" 
4. Verify browser/form validation prevents submission
5. Fill in reason
6. Click "ปฏิเสธ"
7. Verify it submits successfully

**Expected Results**:
- [x] Approve form: rejection_reason optional, can submit empty
- [x] Reject form: rejection_reason required, cannot submit empty
- [x] Client-side validation shows error message for required field

**Code Check**:
```blade
<!-- Approve Modal -->
<textarea name="approval_notes" class="form-control" rows="3"></textarea>
<!-- No required attribute -->

<!-- Reject Modal -->
<textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
<!-- Has required attribute -->
```

---

### Test 9: Data Persistence
**Objective**: Verify data saves and persists correctly

**Steps**:
1. Approve a cancel request
2. Refresh page manually
3. Go back to Approved tab
4. Verify record still there with same data

**Expected Results**:
- [x] Record persists after page refresh
- [x] Data unchanged (same sale ID, quantity, requester, date)
- [x] No errors in logs

**Check Logs**:
```bash
tail -20 storage/logs/laravel.log
# Should not show any errors related to payment_approvals or cancel requests
```

---

### Test 10: Mobile Responsive
**Objective**: Verify UI works on mobile devices

**Steps**:
1. Open payment_approvals page on mobile (or browser DevTools mobile view)
2. Verify tables are responsive
3. Click approve/reject buttons
4. Verify modals display correctly

**Expected Results**:
- [x] Tables have horizontal scroll or collapse
- [x] Buttons are clickable (not too small)
- [x] Modals fit screen
- [x] Text is readable
- [x] No horizontal overflow

---

## Regression Tests

### Existing Payment Approval Flow Still Works
**Steps**:
1. Go to pig_sales/index
2. Record a payment (non-cancel approval)
3. Go to payment_approvals dashboard
4. Approve the payment
5. Verify profit updates

**Expected Results**:
- [x] Old payment approval workflow still works
- [x] "Pending" tab shows both payments and cancels
- [x] Payment approvals separate section from cancels
- [x] Profit recalculates on payment approval

---

### Existing Pig Entry Approval Flow Still Works
**Steps**:
1. Record a pig entry
2. Mark as paid
3. Go to payment_approvals dashboard
4. Approve the pig_entry payment

**Expected Results**:
- [x] Pig entry payments show in pending tab
- [x] Can be approved/rejected
- [x] Badge counts include them

---

## Error Scenarios to Test

### Test E1: Invalid Notification ID
**Steps**:
1. Manually navigate to: `/payment_approvals/99999/approve-cancel-sale`
2. Try to submit POST request

**Expected Results**:
- [x] Error message or redirect
- [x] No database errors in logs
- [x] User-friendly error page

---

### Test E2: Database Errors
**Steps**:
1. Simulate database connection failure
2. Try to approve/reject cancel request
3. Check error handling

**Expected Results**:
- [x] Error logged in `storage/logs/laravel.log`
- [x] User sees friendly error message
- [x] No raw error details exposed to user

---

### Test E3: Permission/Authorization
**Steps**:
1. Log in as non-admin user
2. Try to access payment_approvals page
3. Try to manually call approve/reject routes

**Expected Results**:
- [x] Access denied or redirected
- [x] Only admins can approve/reject
- [x] Non-admins cannot access form

---

## Performance Tests

### Test P1: Large Dataset
**Objective**: Dashboard loads with many cancel requests

**Setup**:
1. Create 100+ cancel requests in database

**Steps**:
1. Go to payment_approvals page
2. Measure page load time
3. Click through tabs
4. Navigate pagination

**Expected Results**:
- [x] Page loads in < 2 seconds
- [x] Pagination works smoothly
- [x] No N+1 query problems
- [x] Queries use eager loading (with relationships)

**Check Queries**:
```php
// In controller - should use with()
$pendingCancelRequests = Notification::where(...)
    ->with('relatedUser')  // ← Eager loading
    ->paginate(15);
```

---

## Browser Compatibility

- [x] Chrome/Chromium (latest)
- [x] Firefox (latest)
- [x] Safari (latest)
- [x] Edge (latest)
- [ ] IE 11 (legacy - may not support all features)

---

## Final Verification Checklist

- [ ] All 10 test scenarios passed
- [ ] All regression tests passed
- [ ] All error scenarios handled gracefully
- [ ] Performance acceptable
- [ ] Mobile responsive working
- [ ] Cross-browser compatible
- [ ] Database data consistent
- [ ] Logs clean (no errors)
- [ ] UI matches design mockups
- [ ] User can complete full workflow:
  - Request cancel
  - Admin sees request
  - Admin approves/rejects
  - Result matches expectation
  - User can retry if rejected

---

## Sign-Off

**Tested By**: _______________  
**Date**: _______________  
**Status**: ☐ PASS ☐ FAIL  
**Issues Found**: 
- [ ] None
- [ ] Minor issues only
- [ ] Critical issues requiring fixes

**Approval**: _______________
