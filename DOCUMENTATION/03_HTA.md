# Pig Farm Management System - HTA (Hierarchical Task Analysis)

## 1. Top-Level Tasks

```
┌────────────────────────────────────────────────────────────────┐
│          PIG FARM MANAGEMENT SYSTEM - MAIN TASKS              │
├────────────────────────────────────────────────────────────────┤
│ 1. Manage Farm Infrastructure                                 │
│ 2. Manage Pig Batches & Lifecycle                            │
│ 3. Track Daily Operations                                     │
│ 4. Manage Financial Transactions                             │
│ 5. Manage Inventory & Storage                                │
│ 6. Generate Reports & Analytics                             │
│ 7. Manage System Users & Permissions                        │
└────────────────────────────────────────────────────────────────┘
```

---

## 2. Detailed HTA Breakdown

### TASK 1: Manage Farm Infrastructure

```
1. MANAGE FARM INFRASTRUCTURE
├─ 1.1 Create Farm
│  ├─ Enter farm name
│  ├─ Enter location
│  ├─ Enter owner name
│  ├─ Set contact details
│  └─ Save farm
│
├─ 1.2 Create Barn (per farm)
│  ├─ Select farm
│  ├─ Enter barn code (unique)
│  ├─ Enter barn name
│  ├─ Set capacity
│  ├─ Enter location
│  └─ Save barn
│
├─ 1.3 Create Pens (per barn)
│  ├─ Select barn
│  ├─ Enter pen code (unique per barn)
│  ├─ Enter pen name
│  ├─ Set capacity
│  ├─ Enter location info
│  └─ Save pen
│
└─ 1.4 View Infrastructure
   ├─ View all farms (list)
   ├─ View barns per farm
   ├─ View pens per barn
   └─ View capacity summary
```

---

### TASK 2: Manage Pig Batches & Lifecycle

```
2. MANAGE PIG BATCHES & LIFECYCLE
│
├─ 2.1 Create New Batch
│  ├─ Select farm & barn
│  ├─ Enter batch code (auto-generate)
│  ├─ Enter start date
│  ├─ Set target weight
│  ├─ Set expected end date
│  ├─ Record initial pigs (qty & weight)
│  ├─ Allocate pigs to pens (BatchPenAllocation)
│  └─ Status: ACTIVE
│
├─ 2.2 Record Daily Operations
│  ├─ 2.2.1 Update Daily Dairy Record
│  │  ├─ Record date
│  │  ├─ Update current weight/pig
│  │  ├─ Record feed consumed
│  │  ├─ Record sick count
│  │  ├─ Record dead count
│  │  ├─ Record treatment details
│  │  ├─ Auto-calculate KPI (ADG, FCR, FCG)
│  │  └─ Save record
│  │
│  ├─ 2.2.2 Record Costs (Multiple Types)
│  │  ├─ Feed costs
│  │  ├─ Medicine costs
│  │  ├─ Labor costs
│  │  ├─ Utility costs
│  │  ├─ Other costs
│  │  ├─ Auto-approval (CostObserver)
│  │  └─ Create CostPayment record
│  │
│  ├─ 2.2.3 Record Deaths & Treatment
│  │  ├─ Record death date & quantity
│  │  ├─ Record cause
│  │  ├─ Record age at death
│  │  ├─ Update batch mortality rate
│  │  └─ Save death record
│  │
│  └─ 2.2.4 Record Treatment
│     ├─ Select treatment type
│     ├─ Record medication used
│     ├─ Record qty treated
│     ├─ Record dosage & method
│     ├─ Set treatment duration
│     └─ Save treatment log
│
├─ 2.3 Track Batch Performance
│  ├─ View daily dairy records (timeline)
│  ├─ Monitor KPI metrics
│  │  ├─ ADG (Average Daily Gain)
│  │  ├─ FCR (Feed Conversion Ratio)
│  │  ├─ FCG (Feed Cost per Gain)
│  │  └─ Mortality rate
│  ├─ View projected profit
│  ├─ Compare with target weight
│  └─ Alert if underperforming
│
├─ 2.4 Record Pig Sales
│  ├─ Select batch & pens
│  ├─ Enter sale date
│  ├─ Select customer
│  ├─ Record qty & weight
│  ├─ Enter price/kg
│  ├─ Calculate total & net
│  ├─ Upload receipt/proof
│  ├─ Auto-approve (if conditions met)
│  ├─ Status: PENDING → APPROVED
│  └─ Create Revenue record
│
├─ 2.5 Record Payment (Sale)
│  ├─ Select pig sale
│  ├─ Enter amount
│  ├─ Select payment method
│  ├─ Upload receipt/slip
│  ├─ Submit for approval
│  ├─ Status: PENDING
│  └─ Admin approves → Status: APPROVED
│
├─ 2.6 View Batch Summary
│  ├─ View financial summary
│  │  ├─ Total revenue
│  │  ├─ Total cost
│  │  ├─ Gross profit
│  │  └─ Profit margin
│  ├─ View KPI summary
│  ├─ View operational timeline
│  └─ Export to CSV/PDF
│
└─ 2.7 Close Batch
   ├─ All pigs sold
   ├─ All payments approved
   ├─ All costs approved
   ├─ Calculate final profit
   ├─ Status: CLOSED
   └─ Archive batch data
```

---

### TASK 3: Track Daily Operations

```
3. TRACK DAILY OPERATIONS
│
├─ 3.1 Daily Dairy Record Entry
│  ├─ Select batch
│  ├─ Enter record date
│  ├─ Record pig count & average weight
│  ├─ Record feed consumed (kg)
│  ├─ Record sick/dead count
│  ├─ Record treatment applied
│  ├─ Auto-calc KPI metrics
│  └─ Save record
│
├─ 3.2 Cost Recording
│  ├─ 3.2.1 Feed Cost
│  │  ├─ Calculate from inventory (auto)
│  │  ├─ Or manual entry
│  │  └─ Auto-approve → CostPayment
│  │
│  ├─ 3.2.2 Medicine Cost
│  │  ├─ From inventory usage
│  │  ├─ Or manual entry
│  │  └─ Auto-approve → CostPayment
│  │
│  ├─ 3.2.3 Labor Cost
│  │  ├─ Manual entry
│  │  ├─ Verify amount
│  │  └─ Auto-approve
│  │
│  ├─ 3.2.4 Utility Cost
│  │  ├─ Manual entry
│  │  └─ Auto-approve
│  │
│  └─ 3.2.5 Other Costs
│     ├─ Manual entry
│     └─ Auto-approve
│
├─ 3.3 Inventory Movement Tracking
│  ├─ Record item usage (out)
│  ├─ Update stock level
│  ├─ Create cost record (if applicable)
│  ├─ Trigger low stock alert
│  └─ Record audit log
│
└─ 3.4 Treatment Management
   ├─ Create treatment plan
   ├─ Record daily treatment log
   ├─ Track medication usage
   ├─ Record response/recovery
   └─ Close treatment
```

---

### TASK 4: Manage Financial Transactions

```
4. MANAGE FINANCIAL TRANSACTIONS
│
├─ 4.1 Cost Approval Flow
│  ├─ View pending costs
│  ├─ Review cost details
│  ├─ Approve/Reject
│  │  ├─ APPROVED → CostPayment
│  │  └─ REJECTED → Notify, delete
│  └─ Update profit calculation
│
├─ 4.2 Payment Recording (Sale)
│  ├─ Record payment received
│  ├─ Upload receipt/proof
│  ├─ Select payment method
│  │  ├─ Cash
│  │  ├─ Transfer
│  │  └─ Cheque
│  ├─ Add reference number (for bank transfer)
│  ├─ Partial/Full payment
│  ├─ Submit for approval
│  └─ Status: PENDING
│
├─ 4.3 Payment Approval Flow
│  ├─ View pending payments
│  ├─ Review payment details
│  │  ├─ Verify amount
│  │  ├─ Check receipt
│  │  └─ Confirm method
│  ├─ Approve/Reject
│  │  ├─ APPROVED → Payment finalized
│  │  ├─ REJECTED → Return to recorder
│  │  └─ Auto-update KPI
│  └─ Update batch status
│
├─ 4.4 Profit Calculation
│  ├─ Calculate when:
│  │  ├─ Cost approved (CostObserver)
│  │  ├─ Revenue created (PigSale)
│  │  ├─ Payment approved (Admin)
│  │  └─ KPI updated
│  ├─ Formula:
│  │  ├─ Gross Profit = Revenue - Approved Costs
│  │  ├─ Margin = (Profit / Revenue) × 100
│  │  └─ ROI = (Profit / Total Cost) × 100
│  └─ Store in Profit model
│
├─ 4.5 Financial Reports
│  ├─ View by batch
│  │  ├─ Revenue summary
│  │  ├─ Cost breakdown
│  │  ├─ Profit analysis
│  │  └─ KPI metrics
│  ├─ View by farm
│  │  ├─ Farm total revenue
│  │  ├─ Farm total cost
│  │  ├─ Farm total profit
│  │  └─ Compare batches
│  └─ Export reports
│     ├─ CSV export
│     └─ PDF export
│
└─ 4.6 Payment Reconciliation
   ├─ Match received payments
   ├─ Reconcile with sales
   ├─ Identify pending/overdue
   ├─ Follow-up actions
   └─ Record notes
```

---

### TASK 5: Manage Inventory & Storage

```
5. MANAGE INVENTORY & STORAGE
│
├─ 5.1 Create Inventory Item
│  ├─ Enter item code (unique)
│  ├─ Enter item name
│  ├─ Select item type
│  │  ├─ Feed
│  │  ├─ Medicine
│  │  ├─ Supplies
│  │  └─ Equipment
│  ├─ Set initial quantity
│  ├─ Set unit price
│  ├─ Set minimum quantity
│  ├─ Set supplier info
│  └─ Save inventory item
│
├─ 5.2 Record Inventory Movement (IN)
│  ├─ Select item
│  ├─ Enter quantity received
│  ├─ Enter date & supplier
│  ├─ Enter cost/unit
│  ├─ Verify receipt
│  ├─ Update stock level
│  ├─ Create cost record (auto)
│  └─ Log audit entry
│
├─ 5.3 Record Inventory Movement (OUT)
│  ├─ Select item
│  ├─ Record quantity used
│  ├─ Enter usage reason/purpose
│  ├─ Update stock level
│  ├─ Check if below minimum
│  ├─ Trigger alert if low
│  ├─ Create cost record (auto)
│  └─ Log audit entry
│
├─ 5.4 Monitor Stock Levels
│  ├─ View current inventory
│  │  ├─ By item type
│  │  ├─ By farm
│  │  └─ By status
│  ├─ Identify low stock items
│  ├─ Alert for reorder
│  ├─ Historical usage analysis
│  └─ Forecast usage
│
├─ 5.5 Physical Count & Verification
│  ├─ Physical count
│  ├─ Compare with system
│  ├─ Record discrepancies
│  ├─ Adjust inventory
│  ├─ Document reason
│  └─ Approve adjustment
│
└─ 5.6 Generate Inventory Reports
   ├─ Stock level report
   ├─ Movement history
   ├─ Usage analysis
   ├─ Low stock alert
   └─ Export CSV/PDF
```

---

### TASK 6: Generate Reports & Analytics

```
6. GENERATE REPORTS & ANALYTICS
│
├─ 6.1 Batch Performance Report
│  ├─ Select batch
│  ├─ View financial summary
│  │  ├─ Revenue
│  │  ├─ Costs (breakdown)
│  │  ├─ Profit
│  │  └─ Margin %
│  ├─ View operational metrics
│  │  ├─ ADG (Daily gain)
│  │  ├─ FCR (Feed ratio)
│  │  ├─ FCG (Feed cost/gain)
│  │  └─ Mortality rate
│  ├─ View timeline
│  └─ Export to PDF
│
├─ 6.2 Projected Profit Dashboard
│  ├─ Active batches
│  ├─ Current KPI per batch
│  ├─ Projected final weight
│  ├─ Projected profit
│  ├─ Profit margin %
│  ├─ Compare current vs projected
│  ├─ Visual charts
│  └─ Batch comparison
│
├─ 6.3 Financial Summary Report
│  ├─ By farm
│  ├─ By period
│  │  ├─ Daily
│  │  ├─ Monthly
│  │  └─ Annual
│  ├─ Revenue summary
│  ├─ Cost breakdown
│  │  ├─ Feed
│  │  ├─ Medicine
│  │  ├─ Labor
│  │  ├─ Utilities
│  │  └─ Other
│  ├─ Profit analysis
│  └─ Trend analysis
│
├─ 6.4 Inventory Report
│  ├─ Current stock levels
│  ├─ Movement history
│  ├─ Usage statistics
│  ├─ Low stock alerts
│  ├─ Supplier comparison
│  └─ Cost analysis
│
├─ 6.5 Treatment & Health Report
│  ├─ Treatment history
│  ├─ Medication usage
│  ├─ Cost per treatment
│  ├─ Effectiveness (recovery rate)
│  ├─ Mortality analysis
│  └─ Preventive measures
│
└─ 6.6 Dashboard Analytics
   ├─ KPI cards (summary)
   ├─ Profit charts
   │  ├─ Current vs Projected
   │  ├─ Trend line
   │  └─ Batch comparison
   ├─ Financial charts
   │  ├─ Revenue distribution
   │  └─ Cost breakdown
   ├─ Operational charts
   │  ├─ ADG trend
   │  ├─ FCR trend
   │  └─ Mortality rate
   └─ Alerts & notifications
```

---

### TASK 7: Manage System Users & Permissions

```
7. MANAGE SYSTEM USERS & PERMISSIONS
│
├─ 7.1 User Registration
│  ├─ New user registration
│  ├─ Enter personal info
│  │  ├─ Full name
│  │  ├─ Email
│  │  ├─ Password
│  │  ├─ Select farm
│  │  └─ Select position
│  ├─ Status: PENDING
│  ├─ Notify admin
│  └─ Await approval
│
├─ 7.2 User Approval Process
│  ├─ View pending registrations
│  ├─ Review user details
│  ├─ Assign role & permissions
│  │  ├─ Admin
│  │  ├─ Manager
│  │  ├─ Staff
│  │  └─ Viewer
│  ├─ Approve/Reject
│  │  ├─ APPROVED → Account ACTIVE
│  │  └─ REJECTED → Notify user
│  ├─ Send notification
│  └─ Account ready to login
│
├─ 7.3 Role & Permission Management
│  ├─ Define roles
│  │  ├─ Admin (all access)
│  │  ├─ Manager (manage operations)
│  │  ├─ Staff (record operations)
│  │  └─ Viewer (read-only)
│  ├─ Assign permissions to roles
│  ├─ Assign roles to users
│  ├─ Update role permissions
│  └─ Audit access changes
│
├─ 7.4 User Access Control
│  ├─ Farm-level access
│  ├─ Feature-level access
│  ├─ Data-level restrictions
│  ├─ Batch-level permissions
│  └─ Cost-level permissions
│
├─ 7.5 User Profile Management
│  ├─ View profile
│  ├─ Update profile info
│  ├─ Change password
│  ├─ View activity log
│  └─ Manage preferences
│
└─ 7.6 User Audit & Monitoring
   ├─ Track user actions
   ├─ Record login history
   ├─ Monitor data changes
   ├─ Generate audit reports
   └─ Identify suspicious activity
```

---

## 3. HTA Task Duration Estimates

| Task | Frequency | Duration | Notes |
|------|-----------|----------|-------|
| Create Batch | Monthly | 15 min | One-time |
| Daily Dairy Record | Daily | 20-30 min | Per batch |
| Cost Recording | Daily | 15 min | Per batch |
| Payment Recording | Per sale | 10 min | Multiple per batch |
| Inventory Check | Daily | 10 min | Quick check |
| Physical Count | Monthly | 1-2 hours | Quarterly deep check |
| Report Generation | On-demand | 5-10 min | Automated |
| Payment Approval | Daily | 5-10 min per item | Batch process |
| Profit Review | Weekly | 20 min | Analysis & review |

---

## 4. HTA Critical Decision Points

```
BATCH CREATION
    │
    ├─ Has farm selected? ➜ YES → Proceed
    │                      ➜ NO → Error, ask user
    │
    ├─ Valid start date? ➜ YES → Proceed
    │                   ➜ NO → Error, set today
    │
    └─ Valid pig quantity? ➜ YES → Create batch
                         ➜ NO → Error, ask again

DAILY DAIRY RECORD
    │
    ├─ Weight > previous? ➜ YES → Calculate ADG ✓
    │                    ➜ NO → Alert (weight loss)
    │
    ├─ Feed recorded? ➜ YES → Calculate FCR
    │                ➜ NO → Ask to enter
    │
    └─ Deaths recorded? ➜ YES → Update mortality %
                      ➜ NO → No update

COST APPROVAL
    │
    ├─ Cost type auto-approve? ➜ YES → Auto-approve
    │                          ➜ NO → Pending (piglet)
    │
    ├─ Unique cost? ➜ YES → Create new
    │              ➜ NO → Update existing
    │
    └─ Create CostPayment? ➜ YES → Record in system
                          ➜ NO → Notify admin

PAYMENT APPROVAL
    │
    ├─ Amount valid? ➜ YES → Check receipt
    │               ➜ NO → Error, ask again
    │
    ├─ Receipt valid? ➜ YES → Approve
    │                ➜ NO → Request resubmit
    │
    └─ Calculate profit? ➜ YES → Update dashboard
                       ➜ NO → Wait for data
```

---

**Last Updated:** November 8, 2025
**Version:** 1.0
