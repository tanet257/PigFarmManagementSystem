# 📖 Payment Approval System - User Guide

## للمستخدمين (For Users)

### Pig Entry Payment Workflow

#### Step 1: Record Payment
1. Navigate to `/pigentryrecord` (บันทึกหมูเข้า)
2. Find a Pig Entry Record
3. Click Payment Button (💰 ชำระเงิน)
4. In the Payment Modal:
   - Enter **Paid Amount** (จำนวนเงินที่ชำระ)
   - Select **Payment Method** (วิธีชำระเงิน)
     - เงินสด (Cash)
     - โอนเงิน (Transfer)
   - Upload **Receipt** (optional)
   - Add **Notes** (optional)
5. Click "บันทึกการชำระเงิน" (Record Payment)

**Success Message**: "บันทึกการชำระเงินเรียบร้อยแล้ว - รอ admin อนุมัติ"
↓ Notification automatically sent to all Admins ↓

---

### Pig Sale Payment Workflow

#### Step 1: Record Payment
1. Navigate to `/pig_sales` (การขายหมู)
2. Find a Pig Sale Record
3. Click Payment Button (💰 ชำระเงิน) 
4. In the Payment Modal:
   - Enter **Paid Amount** (จำนวนเงินที่ชำระ)
   - Select **Payment Method** (วิธีชำระเงิน)
   - Upload **Receipt** (optional)
   - Add **Notes** (optional)
5. Click "บันทึกการชำระเงิน" (Record Payment)

**Success Message**: "บันทึกการชำระเงินสำเร็จ - ชำระแล้ว X บาท (รอ admin อนุมัติ)"
↓ Notification automatically sent to all Admins ↓

---

## สำหรับ Admin Users (For Admin Users)

### Access Payment Approvals

#### Method 1: Direct URL
```
http://yoursite.com/payment_approvals
```

#### Method 2: Sidebar Menu
```
Left Sidebar → "อนุมัติการชำระเงิน" (Payment Approvals)
```

#### Method 3: From Notification
```
Notification Bell → Click payment notification → Go to details
```

---

### Payment Approval Interface

#### Main Page View

**Three Tabs Available:**

| Tab | Status | Count | Action |
|-----|--------|-------|--------|
| ⏳ รอการอนุมัติ | Pending | 15 | View & Approve/Reject |
| ✅ อนุมัติแล้ว | Approved | 8 | View Only |
| ❌ ปฏิเสธแล้ว | Rejected | 2 | View Only |

---

### Approve Payment

#### Step 1: Review Pending Payments
1. Go to `/payment_approvals`
2. Click **"รอการอนุมัติ"** tab (should be active by default)
3. Review the list of pending payments

#### Step 2: View Details
1. Click **"ดู"** button (Eye icon) to view full details

**Details Include:**
- Payment Type (Pig Entry or Pig Sale)
- Farm Name
- Batch Code
- Payment Date
- Quantity & Weight
- Payment Amount
- Total Balance

#### Step 3: Approve Payment
1. Click **"อนุมัติ"** button (Green checkmark)
2. In Modal:
   - (Optional) Add approval notes
3. Click **"อนุมัติ"** button in modal

**Result:**
- ✅ Notification status changed to "approved"
- ✅ Message shows "อนุมัติเรียบร้อยแล้ว"
- ✅ Moved to "อนุมัติแล้ว" tab

---

### Reject Payment

#### Step 1: Review Payment
1. Go to `/payment_approvals`
2. Click **"รอการอนุมัติ"** tab
3. Click **"ดู"** button to review details

#### Step 2: Reject Payment
1. Click **"ปฏิเสธ"** button (Red X)
2. In Modal:
   - Enter **Rejection Reason** (required) ⚠️
   - Include specific reason for rejection
3. Click **"ปฏิเสธ"** button in modal

**Example Reasons:**
- "ใบเสร็จไม่ชัดเจน"
- "จำนวนเงินไม่ตรงกับรายการ"
- "ขาดข้อมูล"
- "รอบันทึกเพิ่มเติม"

**Result:**
- ❌ Notification status changed to "rejected"
- ❌ Message shows "ปฏิเสธแล้ว"
- ❌ Moved to "ปฏิเสธแล้ว" tab
- Reason stored in database

---

### View Approval History

#### Approved Payments
1. Click **"อนุมัติแล้ว"** tab
2. View all previously approved payments
3. Click **"ดู"** to see approval details
4. Includes approval notes (if added)

#### Rejected Payments
1. Click **"ปฏิเสธแล้ว"** tab
2. View all rejected payments
3. Click **"ดู"** to see:
   - Rejection reason
   - Payment details
   - Original payment information

---

## 📊 Status Indicators

### Color Coding

```
🟡 Pending (รอการอนุมัติ)
   - Status: In review
   - Action: Can approve/reject
   - Badge: Badge bg-warning

✅ Approved (อนุมัติแล้ว)
   - Status: Completed
   - Action: View only
   - Badge: Badge bg-success

❌ Rejected (ปฏิเสธแล้ว)
   - Status: Rejected
   - Action: View only
   - Badge: Badge bg-danger
```

---

## 🔍 Filtering & Searching

### Tabs Filtering
- Click on tab to filter by status
- Count shows number of items in each tab
- Pagination available for each tab

### Sorting
- Default: Newest first (created_at desc)
- Click column headers to sort (if available)

### Pagination
- Shows 15 items per page
- Navigate using pagination controls
- Each tab has separate pagination

---

## 💡 Tips & Best Practices

### For Users Recording Payments

✅ **DO:**
- Upload clear receipt images
- Add descriptive notes if needed
- Double-check payment amount
- Use correct payment method

❌ **DON'T:**
- Leave required fields blank
- Upload blurry images
- Pay more than balance amount
- Mix payment amounts

### For Admins Approving

✅ **DO:**
- Review details before approving
- Check receipt is clear
- Verify amounts match
- Add notes for clarity
- Reject if anything unclear

❌ **DON'T:**
- Approve without reviewing
- Skip verification steps
- Ignore discrepancies
- Leave reject reason blank

---

## ⚠️ Common Issues & Solutions

### Issue 1: Payment notification not appearing
**Solution:**
1. Verify user has 'admin' role
2. Check notification record in database
3. Refresh browser
4. Check notification settings

### Issue 2: Cannot approve/reject
**Solution:**
1. Verify you are logged in as Admin
2. Check user has proper role
3. Clear browser cache
4. Try different browser

### Issue 3: Amount exceeds balance
**Solution:**
1. Check remaining balance
2. Enter amount ≤ balance
3. Contact Finance for clarification

### Issue 4: File upload fails
**Solution:**
1. Check file size < 5MB
2. Use supported formats: JPG, PNG, PDF
3. Try uploading different file
4. Check internet connection

---

## 📋 Checklist for Complete Payment Flow

### Recording Payment
- [ ] Navigate to correct record (Pig Entry or Sale)
- [ ] Click Payment button
- [ ] Enter paid amount
- [ ] Select payment method
- [ ] (Optional) Upload receipt
- [ ] (Optional) Add notes
- [ ] Click "บันทึก" button
- [ ] See success message
- [ ] Notification sent to Admin

### Admin Approval
- [ ] Admin accesses `/payment_approvals`
- [ ] Reviews pending payments in first tab
- [ ] Clicks "ดู" to see details
- [ ] Reviews all information
- [ ] Decides to approve or reject
- [ ] For approval: Click "อนุมัติ"
- [ ] For rejection: Click "ปฏิเสธ" + add reason
- [ ] Confirms in modal
- [ ] Payment status updated
- [ ] Tab updated automatically

---

## 🎯 Key Information to Remember

| Item | Details |
|------|---------|
| **Access URL** | `/payment_approvals` |
| **Who can use** | Admin users only |
| **Default sort** | Newest first |
| **Items per page** | 15 |
| **Status options** | Pending, Approved, Rejected |
| **Required fields** | Paid amount, Payment method |
| **Optional fields** | Receipt, Notes |
| **Rejection required** | Rejection reason (mandatory) |
| **Approval notes** | Optional |

---

## 📞 Support

### If you need help:

1. **Check this guide** - Look for similar issue
2. **Contact IT Team** - Report the problem
3. **Check Database** - Verify notification record
4. **Review Logs** - Check application logs

### Information to provide when reporting issues:
- Your username
- Payment ID (if available)
- Date and time of issue
- Error message (if any)
- Browser and OS information

---

**Last Updated**: October 21, 2025
**Version**: 1.0
