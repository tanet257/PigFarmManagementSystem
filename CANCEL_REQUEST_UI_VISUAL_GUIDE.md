# Cancel Request Dashboard - Visual Guide

## Admin Payment Approvals Dashboard Layout

```
┌─────────────────────────────────────────────────────────────────┐
│ PAYMENT APPROVALS & CANCEL REQUESTS DASHBOARD                   │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  Status Summary Cards:                                            │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐           │
│  │ Pending: 5   │  │ Approved: 12 │  │ Rejected: 2  │           │
│  └──────────────┘  └──────────────┘  └──────────────┘           │
│                                                                   │
│  Tab Navigation:                                                  │
│  [📥 Pending] [✅ Approved] [❌ Rejected]                        │
│                                                                   │
│  ─────────────────────────────────────────────────────────────  │
│  PENDING TAB (Active)                                            │
│  ─────────────────────────────────────────────────────────────  │
│                                                                   │
│  Regular Payment Approvals Table:                                │
│  ┌─────┬──────────┬─────────┬──────────┬──────────┬────────┐    │
│  │ #   │ Type     │ Details │ Recorder │ Date     │ Action │    │
│  ├─────┼──────────┼─────────┼──────────┼──────────┼────────┤    │
│  │ 1   │ Payment  │ Record  │ John     │ 10/1/24  │ [✓][✗] │    │
│  │ 2   │ Pig Entry│ Payment │ Jane     │ 10/1/24  │ [✓][✗] │    │
│  ├─────┴──────────┴─────────┴──────────┴──────────┴────────┤    │
│  │ « Pagination Links »                                     │    │
│  └─────────────────────────────────────────────────────────┘    │
│                                                                   │
│  ─────────────────────────────────────────────────────────────  │
│  CANCEL REQUESTS SECTION                                        │
│  ─────────────────────────────────────────────────────────────  │
│                                                                   │
│  ⚠️ ขอยกเลิกการขายหมู [3]  (Yellow badge)                       │
│                                                                   │
│  Pending Cancel Requests Table:                                  │
│  ┌─────┬───────┬─────────┬──────────┬──────────┬──────┬────┐    │
│  │ #   │ Sale# │ Qty     │ Requester│ Date     │ Reason    │    │
│  ├─────┼───────┼─────────┼──────────┼──────────┼──────┼────┤    │
│  │ 1   │ #5234 │ 10 ตัว  │ David    │ 10/1/24  │ Price high   │
│  │ 2   │ #5235 │ 5 ตัว   │ Sarah    │ 10/1/24  │ Quality issue│
│  │ 3   │ #5236 │ 8 ตัว   │ Mike     │ 10/1/24  │ Buyer cancel │
│  ├─────┴───────┴─────────┴──────────┴──────────┴──────┴────┤    │
│  │ Each row has: [✓ Approve] [✗ Reject] buttons            │    │
│  └─────────────────────────────────────────────────────────┘    │
│                                                                   │
│  ─────────────────────────────────────────────────────────────  │
│  APPROVED TAB (Click to view)                                   │
│  ─────────────────────────────────────────────────────────────  │
│                                                                   │
│  Regular Approvals Table:                                        │
│  ┌─────┬──────────┬─────────┬──────────┬──────────┬────────┐    │
│  │ #   │ Type     │ Details │ Recorder │ Approved │ Action │    │
│  ├─────┼──────────┼─────────┼──────────┼──────────┼────────┤    │
│  │ 1   │ Payment  │ ...     │ John     │ 09/30/24 │ [👁 View]  │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                                   │
│  ✅ ยกเลิกการขายแล้ว [5]  (Green badge)                          │
│                                                                   │
│  Approved Cancellations Table:                                   │
│  ┌─────┬───────┬─────────┬──────────┬──────────┬────────┐       │
│  │ #   │ Sale# │ Qty     │ Requester│ Approved │ Action │       │
│  ├─────┼───────┼─────────┼──────────┼──────────┼────────┤       │
│  │ 1   │ #5221 │ 12 ตัว  │ David    │ 09/30/24 │ [👁]   │       │
│  │ 2   │ #5222 │ 7 ตัว   │ Sarah    │ 09/30/24 │ [👁]   │       │
│  │ 3   │ #5223 │ 9 ตัว   │ Mike     │ 09/30/24 │ [👁]   │       │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                                   │
│  ─────────────────────────────────────────────────────────────  │
│  REJECTED TAB (Click to view)                                   │
│  ─────────────────────────────────────────────────────────────  │
│                                                                   │
│  Regular Rejections Table:                                       │
│  ┌─────┬──────────┬─────────┬──────────┬──────────┬────────┐    │
│  │ #   │ Type     │ Details │ Recorder │ Rejected │ Action │    │
│  ├─────┼──────────┼─────────┼──────────┼──────────┼────────┤    │
│  │ 1   │ Payment  │ ...     │ John     │ 09/25/24 │ [👁 View]  │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                                   │
│  ❌ ปฏิเสธการยกเลิก [2]  (Red badge)                             │
│                                                                   │
│  Rejected Cancellation Requests Table:                            │
│  ┌─────┬───────┬─────────┬──────────┬──────────┬─────────┐      │
│  │ #   │ Sale# │ Qty     │ Requester│ Rejected │ Reason  │      │
│  ├─────┼───────┼─────────┼──────────┼──────────┼─────────┤      │
│  │ 1   │ #5215 │ 10 ตัว  │ David    │ 09/20/24 │ Price ok     │
│  │ 2   │ #5217 │ 5 ตัว   │ Jane     │ 09/20/24 │ In process   │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

## Modal Dialogs

### 1. Approve Cancel Modal
```
┌────────────────────────────────────────────────┐
│ ✅ อนุมัติการยกเลิกการขาย                       │
├────────────────────────────────────────────────┤
│                                                 │
│ ℹ️ เหตุผลการขอยกเลิก:                          │
│ ┌──────────────────────────────────────────┐  │
│ │ ราคาสินค้าสูงเกินไป / Buyer cancelled     │  │
│ └──────────────────────────────────────────┘  │
│                                                 │
│ 📝 หมายเหตุการอนุมัติ (ไม่จำเป็น):            │
│ ┌──────────────────────────────────────────┐  │
│ │ [Text Area for approval notes]           │  │
│ │                                          │  │
│ └──────────────────────────────────────────┘  │
│                                                 │
├────────────────────────────────────────────────┤
│ [Cancel Button] [✓ อนุมัติการยกเลิก Button]  │
└────────────────────────────────────────────────┘
```

### 2. Reject Cancel Modal
```
┌────────────────────────────────────────────────┐
│ ❌ ปฏิเสธการยกเลิกการขาย                       │
├────────────────────────────────────────────────┤
│                                                 │
│ ⚠️ เหตุผลการขอยกเลิก:                          │
│ ┌──────────────────────────────────────────┐  │
│ │ ราคาสินค้าสูงเกินไป / Buyer cancelled     │  │
│ └──────────────────────────────────────────┘  │
│                                                 │
│ 📝 เหตุผลในการปฏิเสธ *:                       │
│ ┌──────────────────────────────────────────┐  │
│ │ [Required Text Area]                     │  │
│ │ e.g. "ข้อมูลไม่ครบถ้วน", "กำลังดำเนินการ"│  │
│ │                                          │  │
│ └──────────────────────────────────────────┘  │
│                                                 │
├────────────────────────────────────────────────┤
│ [Cancel Button] [✗ ปฏิเสธ Button]             │
└────────────────────────────────────────────────┘
```

## Status Summary Card Calculation

```
Pending Badge Count:
  = (Pending Payments) + (Pending Cancel Requests) + (Pending Pig Entry Payments)
  = 5 + 3 + 2 = 10 in badge

Approved Badge Count:
  = (Approved Payments) + (Approved Cancel Requests) + (Approved Pig Entry Payments)
  = 12 + 5 + 3 = 20 in badge

Rejected Badge Count:
  = (Rejected Payments) + (Rejected Cancel Requests) + (Rejected Pig Entry Payments)
  = 2 + 2 + 1 = 5 in badge
```

## Data Flow

### Approve Cancel Request Flow
```
Admin clicks Approve button
           ↓
Approve Modal opens (shows original cancel reason)
           ↓
Admin enters optional approval notes
           ↓
Admin clicks "อนุมัติการยกเลิก"
           ↓
POST to: /payment_approvals/{notificationId}/approve-cancel-sale
           ↓
PaymentApprovalController::approveCancelSale() runs:
  - Update Notification: approval_status = 'approved'
  - Call PigSaleController::confirmCancel()
    - Return pigs to batch/pen
    - Soft delete sale: status = 'ยกเลิกการขาย'
    - Update payment_status = 'ยกเลิกการขาย'
    - Recalculate profit
           ↓
Notification moves to Approved Tab
           ↓
Sale record marked as cancelled
           ↓
Profit dashboard updates
```

### Reject Cancel Request Flow
```
Admin clicks Reject button
           ↓
Reject Modal opens (shows original cancel reason)
           ↓
Admin MUST enter rejection reason
           ↓
Admin clicks "ปฏิเสธ"
           ↓
POST to: /payment_approvals/{notificationId}/reject-cancel-sale
           ↓
PaymentApprovalController::rejectCancelSale() runs:
  - Validate rejection_reason (required)
  - Update Notification:
    - approval_status = 'rejected'
    - approval_notes = rejection_reason
           ↓
Notification moves to Rejected Tab
           ↓
Sale remains ACTIVE
           ↓
Payment modal still visible on pig_sales/index
           ↓
Cancel button still visible on pig_sales/index
           ↓
User can:
  - Record payment now
  - Request cancellation again later
```

## Color Scheme

| Status | Color | Badge | Table | Icon |
|--------|-------|-------|-------|------|
| Pending | Yellow | `bg-warning text-dark` | `table-warning` | ⚠️ |
| Approved | Green | `bg-success` | `table-success` | ✅ |
| Rejected | Red | `bg-danger` | `table-danger` | ❌ |

## Pagination

- Each section (pending payments, pending cancels, approved payments, approved cancels, etc.) has independent pagination
- Default: 15 items per page
- Users can navigate through pages for each section separately

## Responsive Design

- Tables use `table-responsive` class
- Buttons scale down on mobile devices
- Modal dialogs are responsive
- Column spacing optimized for small screens

## Accessibility

- All buttons have clear icons and labels
- Modal titles are clear and descriptive
- Form fields have proper labels
- Table headers are centered and highlighted
- Color-blind friendly: Uses icons + colors (not color-only indicators)
