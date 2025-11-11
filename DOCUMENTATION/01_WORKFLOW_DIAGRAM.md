# Pig Farm Management System - Workflow Diagram

## 1. System Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                    PIG FARM MANAGEMENT SYSTEM                   │
│                       (Laravel 9 + MySQL)                       │
└─────────────────────────────────────────────────────────────────┘

┌──────────────────────┐   ┌──────────────────────┐   ┌──────────────────────┐
│   FARM MANAGEMENT    │   │  INVENTORY/STORAGE   │   │   FINANCIAL MGMT     │
│  - Farms             │   │  - StoreHouse        │   │  - Cost              │
│  - Barns             │   │  - Item Movement     │   │  - Revenue           │
│  - Pens              │   │  - Audit Log         │   │  - Payment           │
│  - Employees         │   │  - Stock Tracking    │   │  - Profit            │
└──────────────────────┘   └──────────────────────┘   └──────────────────────┘
         │                           │                          │
         └───────────────────────────┼──────────────────────────┘
                                     │
         ┌───────────────────────────┼──────────────────────────┐
         │                           │                          │
    ┌─────────────┐         ┌──────────────────┐      ┌──────────────────┐
    │   PIG BATCH │         │  DAIRY RECORDS   │      │   PIG SALES      │
    │  LIFECYCLE  │         │   (Daily Logs)   │      │  - Entry Records │
    │             │         │                  │      │  - Sale Records  │
    │ - Creation  │         │  - Weights       │      │  - Payments      │
    │ - Growth    │────────▶│  - Feed/Medicine │     │  - Approvals     │
    │ - Treatment │         │  - Health Status │      │                  │
    │ - Sale      │         │  - Deaths        │      │                  │
    │ - Close     │         └──────────────────┘      └──────────────────┘
    └─────────────┘
```

---

## 2. Main Workflows

### 2.1 Batch Creation & Management Flow

```
┌─────────────────┐
│  Start: Create  │
│  New Batch      │
└────────┬────────┘
         │
         ▼
┌────────────────────────┐
│ Enter Batch Details:   │
│ - Farm, Barn           │
│ - Start Date           │
│ - Initial Pigs (Qty)   │
│ - Starting Weight      │
│ - Target Weight        │
└────────┬───────────────┘
         │
         ▼
┌────────────────────────┐
│ Allocate Pigs to Pens  │
│ (BatchPenAllocation)   │
└────────┬───────────────┘
         │
         ▼
┌────────────────────────┐
│ STATUS: active         │
│ Begin Daily Tracking   │
│ (Dairy Records)        │
└────────┬───────────────┘
         │
         ├─────────────────────────────────────┐
         │                                     │
         ▼                                     ▼
┌──────────────────────┐          ┌──────────────────────┐
│ Daily Dairy Records  │          │ Record Costs         │
│ - Weight             │          │ - Feed               │
│ - Feed Consumed      │          │ - Medicine           │
│ - Sick Pigs          │          │ - Labor              │
│ - Dead Pigs          │◀────────▶│ - Utilities          │
│ - Treatment          │          │ - Other              │
└──────────────────────┘          └──────────────────────┘
         │                                     │
         ▼                                     ▼
    ┌──────────────────────────────────────────────┐
    │ Batch Status: In Progress                    │
    │ KPI Tracking:                                │
    │ - ADG (Daily Weight Gain)                    │
    │ - FCR (Feed Conversion Ratio)                │
    │ - FCG (Feed Cost per kg Gain)                │
    │ - Projected Profit                           │
    └──────────────────┬──────────────────────────┘
                       │
         ┌─────────────┴──────────────┐
         │                            │
         ▼                            ▼
    ┌─────────────┐          ┌──────────────────┐
    │  CONTINUE   │          │  READY TO SELL   │
    │  TRACKING   │          │  (Weight Reached)│
    │             │          └────────┬─────────┘
    └──────┬──────┘                   │
           │                          ▼
           │            ┌────────────────────────┐
           │            │ Create Pig Sales       │
           │            │ - Qty, Weight          │
           │            │ - Price/kg             │
           │            │ - Customer             │
           │            │ - Profit Calculation   │
           │            └────────┬───────────────┘
           │                     │
           │                     ▼
           │            ┌────────────────────────┐
           │            │ Record Revenue         │
           │            │ Auto-Approval:         │
           │            │ Cost ✓ + Revenue ✓     │
           │            │ = Profit Created       │
           │            └────────┬───────────────┘
           │                     │
           │                     ▼
           │            ┌────────────────────────┐
           │            │ Payment Recording &    │
           │            │ Approval               │
           │            └────────┬───────────────┘
           │                     │
           └─────────────┬───────┘
                        ▼
           ┌──────────────────────────┐
           │ STATUS: closed           │
           │ Final Profit Report      │
           │ - Total Revenue          │
           │ - Total Cost             │
           │ - Gross Profit           │
           │ - Profit Margin          │
           │ - Final KPI              │
           └──────────────────────────┘
```

---

### 2.2 Daily Operations & Cost Recording Flow

```
┌──────────────────┐
│  Daily Entry     │
│  Dairy Record    │
└────────┬─────────┘
         │
         ▼
┌─────────────────────────────┐
│ Record Daily Data:          │
│ - Current Weight/Pig        │
│ - Feed Consumed             │
│ - Medicine Used             │
│ - Sick Count                │
│ - Dead Count                │
│ - Treatment Details         │
└────────┬────────────────────┘
         │
         ▼
┌─────────────────────────────┐
│ Auto-Calculate KPI:         │
│ - ADG = Δweight/days        │
│ - FCR = feed/weight_gain    │
│ - FCG = cost/weight_gain    │
└────────┬────────────────────┘
         │
         ▼
    ┌──────────────────────────────┐
    │ Cost Recording Flow          │
    └──────────────────────────────┘
    
    Multiple types:
    ┌──────────────┐  ┌──────────────┐  ┌──────────────┐
    │    FEED      │  │   MEDICINE   │  │    LABOR     │
    └──────┬───────┘  └──────┬───────┘  └──────┬───────┘
           │                 │                 │
           └─────────────────┼─────────────────┘
                             │
                             ▼
                 ┌────────────────────────┐
                 │  Cost Entry in System  │
                 │  - Amount              │
                 │  - Date                │
                 │  - Description         │
                 │  - Status: pending     │
                 └────────┬───────────────┘
                          │
                          ▼
                 ┌────────────────────────┐
                 │ Cost Auto-Approval     │
                 │ (CostObserver Logic)   │
                 │ If no issues detected: │
                 │ - Status → approved    │
                 │ - CostPayment created  │
                 │ - InventoryMovement    │
                 │   recorded             │
                 └────────┬───────────────┘
                          │
                          ▼
                 ┌────────────────────────┐
                 │ Inventory Updated      │
                 │ - StoreHouse adjusted  │
                 │ - Audit log recorded   │
                 │ - Stock level check    │
                 └────────────────────────┘
```

---

### 2.3 Pig Sales & Revenue Flow

```
┌──────────────────┐
│  Create Pig Sale │
│  Record          │
└────────┬─────────┘
         │
         ▼
┌────────────────────────────┐
│ Enter Sale Details:        │
│ - Batch                    │
│ - Qty & Weight             │
│ - Price/kg                 │
│ - Customer                 │
│ - Pen Selection            │
│ - Shipping Cost            │
└────────┬───────────────────┘
         │
         ▼
┌────────────────────────────┐
│ Calculate:                 │
│ - Total Price = Qty × Wt   │
│   × Price/kg               │
│ - Net Total = Total -      │
│   Shipping                 │
└────────┬───────────────────┘
         │
         ▼
┌────────────────────────────┐
│ Record Revenue Entry:      │
│ - Amount = Net Total       │
│ - Status: pending          │
│ - Notification sent        │
└────────┬───────────────────┘
         │
         ▼
┌────────────────────────────┐
│ Auto-Approval:             │
│ Cost ✓ + Revenue ✓         │
│ ⟹ Create Profit Record     │
└────────┬───────────────────┘
         │
         ▼
┌────────────────────────────┐
│ Profit Calculation:        │
│ - Gross Profit =           │
│   Revenue - Cost           │
│ - Profit Margin =          │
│   (Profit/Revenue) × 100   │
└────────┬───────────────────┘
         │
         ▼
┌────────────────────────────┐
│ Wait for Payment Recording │
│ - Admin records payment    │
│ - Upload receipt/proof     │
│ - Select payment method    │
└────────┬───────────────────┘
         │
         ▼
┌────────────────────────────┐
│ Payment Approval:          │
│ - Status: pending          │
│ - Admin approves           │
│ - Status: approved         │
│ - Notification sent        │
└────────┬───────────────────┘
         │
         ▼
┌────────────────────────────┐
│ Sale Complete              │
│ - Payment recorded         │
│ - Profit finalized         │
│ - Dashboard updated        │
└────────────────────────────┘
```

---

### 2.4 Payment Approval Flow

```
Two Types of Payments:

A) COST PAYMENT (Auto)
   ┌─────────────────┐
   │  Cost Created   │
   │  (pending)      │
   └────────┬────────┘
            │
            ▼
   ┌─────────────────┐
   │ Auto-Check:     │
   │ - No duplicates │
   │ - Valid amount  │
   │ - Within range  │
   └────────┬────────┘
            │
         YES/NO
         │    │
         ▼    ▼
      AUTO  REJECTED
      APP.   (admin
    (CostP  reviews)
     ayment)
    
B) SALES PAYMENT (Manual Admin)
   ┌──────────────────┐
   │ Payment Recorded │
   │ (pending)        │
   │ + Receipt        │
   │ + Method         │
   └────────┬─────────┘
            │
            ▼
   ┌──────────────────┐
   │ Admin Review     │
   │ - Check receipt  │
   │ - Verify amount  │
   │ - Approve/Reject │
   └────────┬─────────┘
            │
         APP./REJ.
         │    │
         ▼    ▼
      APPROVED REJECTED
      (final)  (notify
              admin)
```

---

### 2.5 Inventory Management Flow

```
┌────────────────────────┐
│  Inventory Item Entry  │
│  - Medicine            │
│  - Feed                │
│  - Supplies            │
└────────┬───────────────┘
         │
         ▼
┌────────────────────────┐
│ Set Initial Stock:     │
│ - Quantity             │
│ - Min Quantity Alert   │
│ - Unit Price           │
│ - Supplier             │
└────────┬───────────────┘
         │
         ▼
   ┌─────────────────────────────────┐
   │  Daily Usage Tracking           │
   │  (from Dairy Records)           │
   │  - Medicine used in treatment   │
   │  - Feed consumed (recorded)     │
   └──────────┬──────────────────────┘
              │
              ▼
   ┌─────────────────────────────────┐
   │  Record Inventory Movement      │
   │  - Type: "out" (usage)          │
   │  - Qty, Cost                    │
   │  - Date, Reason                 │
   └──────────┬──────────────────────┘
              │
              ▼
   ┌─────────────────────────────────┐
   │  Update Stock Level             │
   │  - Current = Initial - Used     │
   └──────────┬──────────────────────┘
              │
              ▼
   ┌─────────────────────────────────┐
   │  Check Min Quantity Alert?      │
   │  If Current < Min:              │
   │  - Alert notification           │
   │  - Color: Warning               │
   └──────────┬──────────────────────┘
              │
              ├─────────────────────┐
              │                     │
              ▼                     ▼
         CONTINUE          RESTOCK ORDER
         (Normal)          - New "in" movement
                           - Update stock
```

---

### 2.6 User Management & Approval Flow

```
┌──────────────────────┐
│  New User Register   │
│  (Self-Service)      │
└────────┬─────────────┘
         │
         ▼
┌──────────────────────┐
│ Enter Profile:       │
│ - Name, Email        │
│ - Password           │
│ - Farm               │
│ - Position           │
└────────┬─────────────┘
         │
         ▼
┌──────────────────────┐
│ Status: pending      │
│ (Awaiting Approval)  │
│ Admin Notified       │
└────────┬─────────────┘
         │
         ▼
┌──────────────────────┐
│ Admin Review:        │
│ - Check Details      │
│ - Assign Role        │
│ - Verify Farm        │
└────────┬─────────────┘
         │
      APPROVE/REJECT
      │            │
      ▼            ▼
   ┌────────┐  ┌────────┐
   │APPROVED│  │REJECTED│
   │Roles:  │  │Notify  │
   │-Admin  │  │User    │
   │-Manager│  │        │
   │-Staff  │  │Reason  │
   │-Viewer │  │sent    │
   │Account │  │        │
   │ACTIVE  │  │Acct    │
   │        │  │INACTIVE│
   └────────┘  └────────┘
```

---

## 3. Key Process Integrations

### 3.1 Batch Lifecycle & Profit Tracking

```
BATCH CREATION
    │
    ├─ BatchPenAllocation ◀──── Pens (location)
    │
    ├─ Initial Dairy Record (Day 0)
    │   └─ Weight, Feed allocation
    │
    ├─ Daily Cost Recording
    │   ├─ Feed Cost ──┐
    │   ├─ Medicine    ├─ Cost Model
    │   ├─ Labor       │   │
    │   └─ Other ──────┘   │
    │                      │
    ├─ Dairy Records (Daily)
    │   ├─ Weight tracking ◀─── KPI Calculation
    │   ├─ Feed consumption    │   (ADG, FCR, FCG)
    │   └─ Treatment/Deaths    │
    │                          │
    ├─ When SALE:
    │   ├─ PigSale ─ Revenue Record
    │   ├─ Payment Recording
    │   └─ Revenue Model ──────▶│
    │                          │
    └─ Profit Calculation ◀────┘
       ├─ Total Revenue (from sales)
       ├─ Total Cost (approved costs)
       ├─ Gross Profit = Revenue - Cost
       └─ Margin = (Profit / Revenue) × 100
```

---

## 4. System Status & Notifications

### 4.1 Status Flow

```
COST STATUS:
pending ──approval──▶ approved ──payment──▶ paid
            │                              │
            └──────────▶ rejected ─────────┘

PIG SALE STATUS:
pending ──approval──▶ approved ──payment──▶ paid
            │             
            └──────────▶ rejected/cancelled

PAYMENT STATUS:
pending ──approval──▶ approved
            │
            └──────────▶ rejected

BATCH STATUS:
active ──────────────▶ closed
  │
  ├─ In progress (daily tracking)
  └─ Can be cancelled
```

### 4.2 Notification Triggers

```
ADMIN NOTIFICATIONS:
✓ New Cost Created (pending approval)
✓ New Payment Recording (pending approval)
✓ Inventory Low (< min quantity)
✓ Batch Status Change
✓ New User Registration (pending)

USER NOTIFICATIONS:
✓ Cost/Payment Approved
✓ Cost/Payment Rejected
✓ Batch Sale Complete
✓ Role Assignment

SYSTEM ALERTS:
✓ Inventory Shortage
✓ Stock Below Minimum
✓ Dead Pigs Recorded
✓ Batch Ending Soon (projected)
```

---

## 5. Data Flow Summary

```
INPUT
  │
  ├─ Farm Management (Setup)
  ├─ Daily Dairy Records (Tracking)
  ├─ Cost Recording (Expenses)
  └─ Pig Sales (Revenue)
  │
  ▼
PROCESSING
  │
  ├─ Auto-Approval Logic (Observer Pattern)
  ├─ KPI Calculation
  ├─ Profit Calculation
  ├─ Inventory Update
  └─ Notification Dispatch
  │
  ▼
OUTPUT/DASHBOARD
  │
  ├─ Batch Summary
  ├─ Projected Profit
  ├─ Financial Reports
  ├─ Inventory Status
  ├─ Notifications
  └─ Approvals Pending
```

---

## 6. System Integration Points

```
┌─────────────────────────────────────────────────────────┐
│              EXTERNAL INTEGRATIONS                      │
└─────────────────────────────────────────────────────────┘

1. CLOUDINARY (Image Storage)
   ├─ Receipt uploads (payments)
   └─ Farm/Batch photos

2. EMAIL (Notifications)
   ├─ User registration status
   ├─ Approval confirmations
   ├─ System alerts
   └─ Daily reports

3. PDF EXPORT (DomPDF)
   ├─ Financial reports
   ├─ Sale invoices
   ├─ Batch summaries
   └─ Audit reports

4. DATABASE QUERIES
   ├─ Real-time KPI calculations
   ├─ Profit projections
   ├─ Inventory tracking
   └─ Historical analysis
```

---

**Last Updated:** November 8, 2025
**Version:** 1.0
