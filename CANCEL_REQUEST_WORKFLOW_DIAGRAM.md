# Cancel Request Workflow - Diagram

## Complete System Architecture

```
┌─────────────────────────────────────────────────────────────────────────┐
│                     PIG FARM MANAGEMENT SYSTEM                          │
│                    Cancel Request Approval Workflow                      │
└─────────────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────────────────────┐
│                         FARM STAFF INTERFACE                               │
├────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  Pig Sales List View                                                       │
│  ┌──────────────────────────────────────────────────────────────┐        │
│  │ Sale# 5234 │ 10 pigs │ Price: 2000 │ Status: Incomplete    │        │
│  │ [Payment]  [Cancel]  [Delete]                               │        │
│  └──────────────────────────────────────────────────────────────┘        │
│         ↓                                                                  │
│    Click Cancel Button                                                    │
│         ↓                                                                  │
│  Confirmation Dialog                                                      │
│  ┌──────────────────────────────────────────────────────────────┐        │
│  │ Confirm Cancellation                                         │        │
│  │ Reason: [Select dropdown or text]                           │        │
│  │ [Cancel] [Confirm]                                          │        │
│  └──────────────────────────────────────────────────────────────┘        │
│         ↓                                                                  │
│  Database: Notification created                                          │
│    - type = 'cancel_pig_sale'                                            │
│    - approval_status = 'pending'                                         │
│    - related_model_id = 5234                                             │
│    - message = [cancel reason]                                           │
│         ↓                                                                  │
│  UI: Brief flash message or redirect                                     │
│  "ขอยกเลิกการขายแล้ว รอการอนุมัติจากแอดมิน"                              │
│                                                                             │
│  ✓ Sale remains ACTIVE                                                   │
│  ✓ Payment still recordable                                              │
│  ✓ Cancel button still visible                                           │
│                                                                             │
└────────────────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────────────────────┐
│                    ADMIN APPROVAL DASHBOARD                                │
├────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  Payment Approvals Main Page                                              │
│  ┌──────────────────────────────────────────────────────────────┐        │
│  │ Status Summary Badges:                                       │        │
│  │ [Pending: 8] [Approved: 25] [Rejected: 3]                  │        │
│  │                                                              │        │
│  │ Tabs: [📥 Pending] [✅ Approved] [❌ Rejected]             │        │
│  └──────────────────────────────────────────────────────────────┘        │
│                                                                             │
│  PENDING TAB (Active)                                                     │
│  ┌──────────────────────────────────────────────────────────────┐        │
│  │ Regular Payment Approvals Table:                             │        │
│  │ ┌─────┬──────────┬──────────────────────────────────────┐  │        │
│  │ │ #   │ Type     │ Details          │ Recorder │ Action │  │        │
│  │ ├─────┼──────────┼──────────────────┼──────────┼────────┤  │        │
│  │ │ 1   │ Payment  │ Sale #5233...    │ John     │ [✓][✗] │  │        │
│  │ │ 2   │ Pig Entr │ Batch #2024...   │ Jane     │ [✓][✗] │  │        │
│  │ └─────┴──────────┴──────────────────┴──────────┴────────┘  │        │
│  │                                                              │        │
│  │ ─────────────────────────────────────────────────────────  │        │
│  │                                                              │        │
│  │ ⚠️ ขอยกเลิกการขายหมู [3]  ← NEW SECTION                   │        │
│  │ ┌─────┬────────┬──────┬──────────┬────────┬──────────┐    │        │
│  │ │ #   │ Sale#  │ Qty  │ Requester│ Date   │ Reason   │    │        │
│  │ ├─────┼────────┼──────┼──────────┼────────┼──────────┤    │        │
│  │ │ 1   │ #5234  │ 10ตัว│ David    │ 10/1   │ High price   │        │
│  │ │     │        │      │          │        │              │        │
│  │ │     │        │      │ [✓ Approve] [✗ Reject]           │        │
│  │ ├─────┼────────┼──────┼──────────┼────────┼──────────┤    │        │
│  │ │ 2   │ #5236  │ 5ตัว │ Sarah    │ 10/1   │ Buyer cancel │        │
│  │ │     │        │      │          │        │              │        │
│  │ │     │        │      │ [✓ Approve] [✗ Reject]           │        │
│  │ ├─────┼────────┼──────┼──────────┼────────┼──────────┤    │        │
│  │ │ 3   │ #5237  │ 8ตัว │ Mike     │ 10/1   │ Quality issue│        │
│  │ │     │        │      │          │        │              │        │
│  │ │     │        │      │ [✓ Approve] [✗ Reject]           │        │
│  │ └─────┴────────┴──────┴──────────┴────────┴──────────┘    │        │
│  └──────────────────────────────────────────────────────────────┘        │
│                                                                             │
└────────────────────────────────────────────────────────────────────────────┘

                    ┌───────────────┬───────────────┐
                    │               │               │
              APPROVE PATH    REJECT PATH
              ↓                 ↓
              
┌────────────────────────────┐  ┌────────────────────────────┐
│   APPROVE CANCEL MODAL     │  │   REJECT CANCEL MODAL      │
├────────────────────────────┤  ├────────────────────────────┤
│                            │  │                            │
│ ✅ อนุมัติการยกเลิก      │  │ ❌ ปฏิเสธการยกเลิก        │
│                            │  │                            │
│ ℹ️ เหตุผลการขอยกเลิก:    │  │ ⚠️ เหตุผลการขอยกเลิก:    │
│ ┌──────────────────────┐   │  │ ┌──────────────────────┐   │
│ │ High price / Buyer   │   │  │ │ High price / Buyer   │   │
│ │ cancelled            │   │  │ │ cancelled            │   │
│ └──────────────────────┘   │  │ └──────────────────────┘   │
│                            │  │                            │
│ 📝 หมายเหตุการอนุมัติ:    │  │ 📝 เหตุผลในการปฏิเสธ:   │
│ (Optional)                 │  │ (Required) *             │
│ ┌──────────────────────┐   │  │ ┌──────────────────────┐   │
│ │ [Text Area] (empty)  │   │  │ │ [Text Area] (must     │   │
│ │                      │   │  │ │  fill!)               │   │
│ └──────────────────────┘   │  │ └──────────────────────┘   │
│                            │  │                            │
│ [Cancel] [✓ Submit]        │  │ [Cancel] [✗ Submit]        │
│                            │  │                            │
└────────────────────────────┘  └────────────────────────────┘
          │                               │
          │                               │
          ↓ HTTP PATCH                    ↓ HTTP PATCH
    approveCancelSale()            rejectCancelSale()
          │                               │
          │                               │
┌─────────────────────────────┐  ┌──────────────────────────────┐
│ BACKEND APPROVAL PROCESSING │  │ BACKEND REJECTION PROCESSING │
├─────────────────────────────┤  ├──────────────────────────────┤
│                             │  │                              │
│ 1. Validate notification    │  │ 1. Validate rejection_reason │
│ 2. Update notification:     │  │ 2. Update notification:      │
│    - approval_status        │  │    - approval_status         │
│      = 'approved'           │  │      = 'rejected'            │
│    - approval_notes = notes │  │    - approval_notes = reason │
│ 3. Call confirmCancel()     │  │ 3. No changes to PigSale     │
│    - Prepare pig return     │  │ 4. Redirect to dashboard    │
│    - Update PigSale:        │  │                              │
│      status='ยกเลิก'        │  │ → Keep sale ACTIVE          │
│      payment_status=        │  │ → Keep buttons VISIBLE      │
│      'ยกเลิก'               │  │ → User can retry            │
│    - Return pigs            │  │                              │
│    - Update batch qty       │  │                              │
│ 4. Recalculate profit       │  │                              │
│    - RevenueHelper          │  │                              │
│    - Only active sales      │  │                              │
│ 5. Update notification:     │  │                              │
│    - Timestamp              │  │                              │
│    - Mark as done           │  │                              │
│ 6. Redirect to dashboard    │  │                              │
│                             │  │                              │
└─────────────────────────────┘  └──────────────────────────────┘
          │                               │
          │                               │
          ↓ SUCCESS                       ↓ SUCCESS
          │                               │
┌─────────────────────────────┐  ┌──────────────────────────────┐
│   MOVE TO APPROVED TAB      │  │  MOVE TO REJECTED TAB        │
├─────────────────────────────┤  ├──────────────────────────────┤
│                             │  │                              │
│ ✅ ยกเลิกการขายแล้ว [3]    │  │ ❌ ปฏิเสธการยกเลิก [2]     │
│                             │  │                              │
│ Table: Approved             │  │ Table: Rejected              │
│ ┌─────────────────────────┐ │  │ ┌──────────────────────────┐│
│ │ Sale #5234 │ 10 ตัว   │ │  │ │ Sale #5240│ 12 ตัว   │  ││
│ │ Approved: 10/1 14:30   │ │  │ │ Rejected: 09/30 16:45│  ││
│ │ [👁 View]              │ │  │ │ Reason: Data incomplete │ ││
│ │                         │ │  │ │ [👁 View]              │ ││
│ │ Sale #5236 │ 5 ตัว    │ │  │ │ Sale #5242│ 7 ตัว    │  ││
│ │ Approved: 10/1 14:25   │ │  │ │ Rejected: 09/30 15:20│  ││
│ │ [👁 View]              │ │  │ │ Reason: In processing │ ││
│ │                         │ │  │ │ [👁 View]              │ ││
│ └─────────────────────────┘ │  │ └──────────────────────────┘│
│                             │  │                              │
│ DATABASE:                   │  │ DATABASE:                    │
│ - Notification.approval     │  │ - Notification.approval      │
│   status = 'approved'       │  │   status = 'rejected'        │
│ - PigSale.status = '        │  │ - PigSale.status            │
│   ยกเลิกการขาย'            │  │   (UNCHANGED)                │
│ - PigSale.payment_status    │  │ - PigSale.payment_status    │
│   = 'ยกเลิกการขาย'         │  │   (UNCHANGED)                │
│ - Profit updated            │  │ - Profit (UNCHANGED)        │
│ - Pigs returned             │  │ - Pigs (UNCHANGED)          │
│                             │  │                              │
│ PIG SALES INDEX:            │  │ PIG SALES INDEX:             │
│ Sale still visible BUT       │  │ Sale still visible AND       │
│ - No payment button         │  │ - Payment button visible     │
│ - No cancel button          │  │ - Cancel button visible      │
│ - Greyed out / disabled     │  │ - Enabled                    │
│                             │  │ - User can record payment    │
│                             │  │ - User can request cancel    │
│                             │  │   again (new notification)   │
│                             │  │                              │
└─────────────────────────────┘  └──────────────────────────────┘
          │                               │
          │                               │
          ↓                               ↓
   PROCESS COMPLETE              USER CAN RETRY
   Sale cancelled                 Sale active
   Pigs returned                  Options:
   Profit updated               - Record payment
   Dashboard clean              - Request cancel again
                                - Ignore (admin feedback)


SYSTEM STATE COMPARISON:

BEFORE CANCEL REQUEST    AFTER APPROVAL      AFTER REJECTION
─────────────────────────────────────────────────────────────
Sale Status:             Sale Status:         Sale Status:
 incomplete        →      ยกเลิกการขาย  vs   incomplete

Payment Status:          Payment Status:      Payment Status:
 รอการอนุมัติ      →      ยกเลิกการขาย  vs   รอการอนุมัติ

Pigs Location:           Pigs Location:       Pigs Location:
 batch/pen         →      returned       vs   batch/pen

Profit:                  Profit:              Profit:
 (base calc)       →      (recalculated) vs   (base calc)

Payment Button:          Payment Button:      Payment Button:
 VISIBLE           →      HIDDEN        vs    VISIBLE

Cancel Button:           Cancel Button:       Cancel Button:
 VISIBLE           →      HIDDEN        vs    VISIBLE

User Action:             User Action:         User Action:
 pending           →      COMPLETE            CAN RETRY


COMPLETE WORKFLOW LOOP:

    ┌─────────────────────────────────────────┐
    │   USER REQUESTS CANCEL (PigSales)       │
    │   ↓                                      │
    │   Notification created (pending)         │
    │   ↓                                      │
    │   Admin sees in Payment Approvals        │
    │   ↓                                      │
    │   Admin clicks Approve/Reject            │
    │   ↓                                      │
    └──────┬──────────────────┬────────────────┘
           │                  │
    ┌──────▼──────┐    ┌──────▼──────┐
    │  APPROVED   │    │  REJECTED   │
    │             │    │             │
    │ Sale:       │    │ Sale:       │
    │ Cancelled   │    │ Active      │
    │ Pigs:       │    │ Pigs:       │
    │ Returned    │    │ Unchanged   │
    │ Profit:     │    │ Profit:     │
    │ Updated     │    │ Unchanged   │
    │             │    │             │
    │ Buttons:    │    │ Buttons:    │
    │ Hidden      │    │ Visible     │
    │             │    │             │
    │ UI:         │    │ UI:         │
    │ Complete    │    │ Ready for   │
    │ (Done)      │    │ Retry       │
    │             │    │ ↓           │
    │             │    │ (Loop back  │
    │             │    │  to start)  │
    │             │    │             │
    └──────┬──────┘    └──────┬──────┘
           │                  │
           └──────┬───────────┘
                  │
           Both move to respective
           Approved/Rejected tabs
                  │
           Dashboard shows history
                  │
              COMPLETE


BADGE/COUNT CALCULATION:

Status Card:                    All pending items:
Pending: 8                    = Payments (3)
                              + Cancels (3)
                              + Pig Entries (2)
                              = 8 total

Approved: 25                  = Payments (10)
                              + Cancels (5)
                              + Pig Entries (10)
                              = 25 total

Rejected: 3                   = Payments (1)
                              + Cancels (2)
                              + Pig Entries (0)
                              = 3 total
```

## Data Model Relationships

```
User
  ├─ Notification (user_id)
  │   ├─ type: 'cancel_pig_sale'
  │   ├─ approval_status: 'pending'/'approved'/'rejected'
  │   ├─ related_model: 'PigSale'
  │   ├─ related_model_id: [id]
  │   └─ message: [cancel reason]
  │
  └─ PigSale (user_id = who recorded it)
      ├─ status: 'incomplete' → 'ยกเลิกการขาย'
      ├─ payment_status: 'รอการอนุมัติ' → 'ยกเลิกการขาย'
      ├─ Batch (has many)
      │   └─ Quantity (updated on return)
      └─ Revenue
          └─ Profit (recalculated)
```

## API Endpoints

```
REQUEST:
  POST /pig-sales/{id}
  X-HTTP-METHOD-OVERRIDE: DELETE
  → PigSaleController::destroy()
  → Creates Notification

APPROVAL:
  PATCH /payment_approvals/{notificationId}/approve-cancel-sale
  {csrf_token, approval_notes}
  → PaymentApprovalController::approveCancelSale()
  → Calls PigSaleController::confirmCancel()

REJECTION:
  PATCH /payment_approvals/{notificationId}/reject-cancel-sale
  {csrf_token, rejection_reason}
  → PaymentApprovalController::rejectCancelSale()
  → Updates notification only
```

## Error Handling Flow

```
ERROR SCENARIOS:

1. Invalid Notification ID
   ↓
   Route not found / Notification::notFound()
   ↓
   Redirect to dashboard with error message

2. Unauthorized Access
   ↓
   Middleware check: Not admin
   ↓
   Redirect to home / 403 Forbidden

3. Database Error
   ↓
   Try/catch in controller
   ↓
   Log error
   ↓
   Redirect with generic error message

4. Validation Error (Reject without reason)
   ↓
   Validation fails on required field
   ↓
   Form returns with error message
   ↓
   User can correct and retry
```
