# 📋 Payment Approval UI - Visual Guide

## Payment Approvals Page Layout (After Fix)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│  💼 PAYMENT APPROVALS                                         [Search] [Filter] │
└─────────────────────────────────────────────────────────────────────────────┘

Tabs Navigation:
┌──────────────────┬──────────────────┬────────────────┬──────────────────┬─────────┐
│ 💳 รอการอนุมัติ  │ ⏳ รอการอนุมัติ  │ ✅ อนุมัติแล้ว  │ ❌ ปฏิเสธแล้ว     │ ❌ ยกเลิก│
│ ชำระเงิน        │ ขาย             │                 │                   │         │
│ [5 badge]       │ [2 badge]       │ [12 badge]      │ [1 badge]         │ [0]     │
└──────────────────┴──────────────────┴────────────────┴──────────────────┴─────────┘
     ↑ ACTIVE TAB (Default)

PENDING PAYMENTS TAB CONTENT:
═══════════════════════════════════════════════════════════════════════════════════

Table Headers:
┌────────────────┬─────────────────┬───────────────┬─────────────┬──────────────────┐
│ เลขที่เอกสาร   │ เกษตรกร/ฟาร์ม  │ ชื่อแบทช์     │ จำนวนเงิน   │ วิธีชำระเงิน    │
├────────────────┼─────────────────┼───────────────┼─────────────┼──────────────────┤
│ วันที่ชำระ     │ ไฟล์ใบเสร็จ    │ บันทึกโดย     │ การกระทำ    │
└────────────────┴─────────────────┴───────────────┴─────────────┴──────────────────┘

Sample Row:
┌────────────┬──────────────────┬──────────────┬──────────────┬───────────────────┐
│ PAY-00123  │ สวนสยามฟาร์ม    │ พวง 20/4-1   │ ฿ 50,000.00  │ 🟢 สด (สด)        │
│ (Primary)  │ เกษตรกร: ส. เขต │             │ (Bold Right) │ (Green Badge)     │
├────────────┼──────────────────┼──────────────┼──────────────┼───────────────────┤
│ 22/10/2024 │ ✅ ดู (PDF link) │ สมชาย        │ [✅ อนุมัติ] │
│            │                  │ 22/10 14:30  │ [❌ ปฏิเสธ]  │
└────────────┴──────────────────┴──────────────┴──────────────┴───────────────────┘

Sample Row 2:
┌────────────┬──────────────────┬──────────────┬──────────────┬───────────────────┐
│ PAY-00122  │ สุขสันต์ฟาร์ม    │ ซ้าย 15/3-1  │ ฿ 35,500.00  │ 🔵 โอน (Blue)    │
├────────────┼──────────────────┼──────────────┼──────────────┼───────────────────┤
│ 20/10/2024 │ -                │ อรุณ         │ [✅ อนุมัติ] │
│            │ (No receipt)     │ 20/10 09:15  │ [❌ ปฏิเสธ]  │
└────────────┴──────────────────┴──────────────┴──────────────┴───────────────────┘

Pagination:
┌────────────────────────────────────────────────────────────────────────────────┐
│  แสดง 1 ถึง 2 จากทั้งหมด 5 รายการ    [← Previous] [1] [2] [3] [Next →]       │
└────────────────────────────────────────────────────────────────────────────────┘
```

---

## Payment Approval Button Interaction

### Before Approve:
```
Status: pending ⏳
┌──────────────────────────────┐
│ [✅ อนุมัติ] [❌ ปฏิเสธ]   │ ← Both enabled
└──────────────────────────────┘
```

### User Clicks "Approve":
```
1. Form submitted to: /payment_approvals/{id}/approve-payment
2. HTTP Method: PATCH
3. Data sent:
   - _token: (CSRF token)
   - _method: PATCH
```

### After Approve - Server Response:
```
✅ Alert: "อนุมัติการชำระเงินสำเร็จ (บันทึก Profit แล้ว)"

Database Changes:
├─ Payment.status: 'pending' → 'approved'
├─ Payment.approved_by: {admin_id}
├─ Payment.approved_at: {current_timestamp}
├─ Profit table: New record created
│  └─ total_revenue: includes this payment
│  └─ total_cost: from costs
│  └─ profit: revenue - cost
└─ Notification: Created for payment recorder

Page Action:
└─ Redirect back to Payment Approvals page
   └─ Payment no longer in "Pending Payments" tab (moved to completed)
   └─ Profit values updated in dashboard
```

---

## Payment Method Badges

```
Payment Method Display:

🟢 cash (สด)
├─ Badge Color: bg-success (Green)
└─ Shows: "สด"

🔵 transfer (โอน)
├─ Badge Color: bg-info (Blue)  
└─ Shows: "โอน"

🟡 cheque (เช็ค)
├─ Badge Color: bg-warning (Yellow)
└─ Shows: "เช็ค"

⚪ other
├─ Badge Color: bg-secondary (Gray)
└─ Shows: actual method value
```

---

## Receipt File Display

```
If receipt_file exists:
┌─────────────────────────┐
│ 📄 ดู (blue link button)│  ← Links to storage/{receipt_file}
└─────────────────────────┘     Opens in new tab

If NO receipt_file:
┌──────────────┐
│    -         │  ← Gray text
└──────────────┘
```

---

## Comparison: Old vs New UI

### ❌ OLD (Bug)
```
Tabs:
┌──────────────┬────────────┬────────────┬────────────────┐
│ รอการอนุมัติ │ อนุมัติแล้ว│ ปฏิเสธแล้ว │ คำขอยกเลิก    │
│ (2 Pig Sales)│            │            │                │
└──────────────┴────────────┴────────────┴────────────────┘
  ↑ Shows ONLY PigSale records
  ❌ Payment records NOT displayed
```

### ✅ NEW (Fixed)
```
Tabs:
┌──────────────┬──────────────┬────────────┬────────────┬────────────────┐
│ รอการอนุมัติ │ รอการอนุมัติ │ อนุมัติแล้ว│ ปฏิเสธแล้ว │ คำขอยกเลิก    │
│ ชำระเงิน    │ ขาย         │            │            │                │
│ (5 Payments)│ (2 Pig Sales)│            │            │                │
└──────────────┴──────────────┴────────────┴────────────┴────────────────┘
  ↑ Shows Payment records   ✅ FIXED
```

---

## Data Flow in UI

```
1. Admin loads Payment Approvals page
   ↓
2. PaymentApprovalController::index()
   ├─ $pendingPayments = Payment::where('status', 'pending')->get()
   ├─ $pendingPigSales = PigSale::where('status', 'pending')->get()
   └─ Pass to view
   ↓
3. index.blade.php renders tabs
   ├─ Pending Payments tab (with $pendingPayments)
   ├─ Pending PigSales tab (with $pendingPigSales)
   ├─ Approved tab
   ├─ Rejected tab
   └─ Cancel Requests tab
   ↓
4. Admin sees Payment records in table
   ├─ payment_number, amount, payment_date
   ├─ payment_method (with badge)
   ├─ receipt_file (with download link)
   └─ recordedBy (user who recorded)
   ↓
5. Admin clicks "Approve" button
   ├─ Form action: route('payment_approvals.approve_payment', $payment->id)
   ├─ HTTP Method: PATCH
   └─ Submit
   ↓
6. PaymentApprovalController::approvePayment($paymentId)
   ├─ Find Payment
   ├─ Update status to 'approved'
   ├─ Set approved_by, approved_at
   ├─ Calculate & record Profit/Revenue
   ├─ Create notification
   └─ Redirect back with success message
   ↓
7. Page refreshes, showing updated state
   ├─ Success alert displayed
   ├─ Payment removed from Pending tab
   ├─ Profit dashboard shows new values
   └─ Recorder receives notification
```

---

## Complete User Story - After Implementation

### Actor: Farm Admin
### Goal: Approve payment and see profit calculation

#### Steps:
1. **Navigate to Dashboard**
   - Click: "Payment Approvals" from menu
   - See: 5 pending payments with badge

2. **Review Payment Details**
   - See: Payment number, farm, batch, amount, method, date
   - Download: Receipt PDF if available
   - Check: Who recorded (with timestamp)

3. **Approve Payment**
   - Click: Green "✅ อนุมัติ" button
   - Confirm: Popup asks "อนุมัติการชำระเงินนี้ใช่หรือไม่?"
   - Submit: Sends PATCH request

4. **See Success**
   - Alert: "อนุมัติการชำระเงินสำเร็จ (บันทึก Profit แล้ว)"
   - Payment: Removed from Pending Payments tab
   - Profit: Updated in dashboard with new revenue

5. **Verify Profit Calculation**
   - Go to: Revenue/Profit dashboard
   - See: Total revenue includes this payment
   - See: Profit calculated (revenue - cost)

#### Expected Outcome: ✅
- Payment status changed to 'approved'
- Profit/Revenue calculated and recorded
- Recorder notified
- Audit trail complete (approved_by, approved_at)

---

## Error Handling

### Scenario 1: Try to approve already-approved payment
```
Admin manually navigates: /payment_approvals/123/approve-payment
But Payment.status already = 'approved'

Response:
❌ Alert: "สามารถอนุมัติได้เฉพาะการชำระเงินที่รอการอนุมัติเท่านั้น"
```

### Scenario 2: Reject payment
```
Admin clicks: ❌ Reject button
System:
├─ Sets status = 'rejected'
├─ Sets approved_by, approved_at (for audit)
├─ Sends notification
└─ Removes from Pending tab

✅ Alert: "ปฏิเสธการชำระเงินสำเร็จ"
```

---

## Audit Trail

```
For each Payment:

Before Approval:
├─ Payment.status: 'pending'
├─ Payment.recorded_by: {user_id}
├─ Payment.created_at: {timestamp}
└─ Payment.approved_by: NULL
└─ Payment.approved_at: NULL

After Approval:
├─ Payment.status: 'approved'
├─ Payment.recorded_by: {user_id} (unchanged)
├─ Payment.created_at: {timestamp} (unchanged)
├─ Payment.approved_by: {admin_id}
└─ Payment.approved_at: {current_timestamp}

In Notification:
├─ Type: 'payment_approved'
├─ Title: '✅ การชำระเงินของคุณได้รับการอนุมัติ'
├─ Message: Shows amount, payment number, approver name
└─ Related to: Payment ID
```

---

This completes the Payment Approval workflow UI implementation! 🎉
