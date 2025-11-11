# Pig Farm Management System - Data Dictionary

## Table of Contents

1. [Core Tables](#core-tables)
2. [Batch Management Tables](#batch-management-tables)
3. [Financial Tables](#financial-tables)
4. [Inventory Tables](#inventory-tables)
5. [System Tables](#system-tables)
6. [Status & Type Definitions](#status--type-definitions)

---

## Core Tables

### USERS
**Purpose:** Store user accounts and authentication

| Field | Type | Constraint | Description |
|-------|------|-----------|-------------|
| id | bigint | PK | User ID |
| name | varchar(255) | NOT NULL | Full name |
| email | varchar(255) | UQ | Email address (unique) |
| password | varchar(255) | NOT NULL | Hashed password |
| farm_id | bigint | FK → farms.id | Assigned farm |
| role_id | bigint | FK → roles.id | User role |
| status | enum | pending/active/inactive | Account status |
| email_verified_at | timestamp | NULL | Email verification |
| created_at | timestamp | - | Creation time |
| updated_at | timestamp | - | Last update |

**Indexes:**
- UQ: email
- FK: farm_id, role_id

---

### FARMS
**Purpose:** Store farm information

| Field | Type | Constraint | Description |
|-------|------|-----------|-------------|
| id | bigint | PK | Farm ID |
| farm_name | varchar(255) | NOT NULL | Farm name |
| owner_name | varchar(255) | NOT NULL | Farm owner |
| location | text | NULL | Physical location |
| phone | varchar(20) | NULL | Contact phone |
| email | varchar(255) | NULL | Contact email |
| created_at | timestamp | - | Creation time |
| updated_at | timestamp | - | Last update |

---

### BARNS
**Purpose:** Store barn information per farm

| Field | Type | Constraint | Description |
|-------|------|-----------|-------------|
| id | bigint | PK | Barn ID |
| farm_id | bigint | FK → farms.id | Farm |
| barn_code | varchar(50) | UQ | Barn code (unique) |
| barn_name | varchar(255) | NOT NULL | Barn name |
| capacity | int | NOT NULL | Total capacity |
| location | text | NULL | Location in farm |
| created_at | timestamp | - | Creation time |
| updated_at | timestamp | - | Last update |

**Indexes:**
- UQ: (farm_id, barn_code)
- FK: farm_id

---

### PENS
**Purpose:** Store individual pens (pigsty) in barns

| Field | Type | Constraint | Description |
|-------|------|-----------|-------------|
| id | bigint | PK | Pen ID |
| barn_id | bigint | FK → barns.id | Barn |
| pen_code | varchar(50) | NOT NULL | Pen code |
| pen_name | varchar(255) | NOT NULL | Pen name |
| capacity | int | NOT NULL | Pig capacity |
| location | text | NULL | Location in barn |
| created_at | timestamp | - | Creation time |
| updated_at | timestamp | - | Last update |

**Indexes:**
- UQ: (barn_id, pen_code)
- FK: barn_id

---

## Batch Management Tables

### BATCHES
**Purpose:** Store pig batch information

| Field | Type | Constraint | Description |
|-------|------|-----------|-------------|
| id | bigint | PK | Batch ID |
| farm_id | bigint | FK → farms.id | Farm |
| batch_code | varchar(100) | UQ | Batch code (format: F{farm_id}-B{number}) |
| start_date | date | NOT NULL | Start date |
| expected_end | date | NULL | Expected end date |
| initial_quantity | int | NOT NULL | Initial pig count |
| starting_weight | decimal(8,2) | NOT NULL | Starting weight/pig (kg) |
| target_weight | decimal(8,2) | NOT NULL | Target weight (kg) |
| status | enum | active/closed/cancelled | Batch status |
| created_at | timestamp | - | Creation time |
| updated_at | timestamp | - | Last update |

**Indexes:**
- UQ: batch_code
- FK: farm_id
- Status: For filtering active/closed

---

### BATCH_PEN_ALLOCATION
**Purpose:** Track pig allocation to pens

| Field | Type | Constraint | Description |
|-------|------|-----------|-------------|
| id | bigint | PK | Allocation ID |
| batch_id | bigint | FK → batches.id | Batch |
| pen_id | bigint | FK → pens.id | Pen |
| quantity_allocated | int | NOT NULL | Pigs in pen |
| allocated_date | date | NOT NULL | Allocation date |
| removed_date | date | NULL | Date removed |
| created_at | timestamp | - | Creation time |

**Indexes:**
- FK: batch_id, pen_id
- Composite: (batch_id, pen_id)

---

### DAIRY_RECORDS
**Purpose:** Daily tracking of batch

| Field | Type | Constraint | Description |
|-------|------|-----------|-------------|
| id | bigint | PK | Record ID |
| batch_id | bigint | FK → batches.id | Batch |
| record_date | date | NOT NULL | Record date |
| quantity_pigs | int | NOT NULL | Current pig count |
| avg_weight_per_pig | decimal(8,2) | NOT NULL | Average weight (kg) |
| feed_consumed_kg | decimal(10,2) | NOT NULL | Feed consumed (kg) |
| sick_count | int | DEFAULT 0 | Sick pigs |
| dead_count | int | DEFAULT 0 | Dead pigs |
| health_notes | text | NULL | Health observations |
| recorded_by | bigint | FK → users.id | Recorder |
| created_at | timestamp | - | Creation time |
| updated_at | timestamp | - | Last update |

**Indexes:**
- FK: batch_id, recorded_by
- (batch_id, record_date) for quick lookup

---

### BATCH_METRICS
**Purpose:** Calculated KPI metrics per batch

| Field | Type | Constraint | Description |
|-------|------|-----------|-------------|
| id | bigint | PK | Metric ID |
| batch_id | bigint | FK → batches.id | Batch (unique) |
| adg | decimal(6,3) | NOT NULL | Average Daily Gain (kg/day) |
| fcr | decimal(6,3) | NOT NULL | Feed Conversion Ratio |
| fcg | decimal(8,2) | NOT NULL | Feed Cost per kg Gain |
| mortality_rate | decimal(5,2) | NOT NULL | Mortality % |
| morbidity_rate | decimal(5,2) | NULL | Morbidity % |
| ending_avg_weight | decimal(8,2) | NULL | Final avg weight |
| days_in_farm | int | DEFAULT 0 | Days raised |
| updated_at | timestamp | - | Last update |

**Indexes:**
- UQ: batch_id
- FK: batch_id

---

### PIG_ENTRY_RECORD
**Purpose:** Initial pig entry into batch

| Field | Type | Constraint | Description |
|-------|------|-----------|-------------|
| id | bigint | PK | Entry ID |
| batch_id | bigint | FK → batches.id | Batch |
| farm_id | bigint | FK → farms.id | Farm |
| pig_entry_date | date | NOT NULL | Entry date |
| total_pig_amount | int | NOT NULL | Total pigs |
| total_pig_weight | decimal(10,2) | NOT NULL | Total weight (kg) |
| total_pig_price | decimal(12,2) | NOT NULL | Total price (฿) |
| weight_per_pig | decimal(8,2) | NOT NULL | Average weight/pig |
| unit_price | decimal(10,2) | NOT NULL | Price per pig |
| supplier_name | varchar(255) | NULL | Supplier |
| created_by | bigint | FK → users.id | Created by |
| created_at | timestamp | - | Creation time |
| updated_at | timestamp | - | Last update |

**Indexes:**
- FK: batch_id, farm_id, created_by

---

## Financial Tables

### COSTS
**Purpose:** Store all batch expenses

| Field | Type | Constraint | Description |
|-------|------|-----------|-------------|
| id | bigint | PK | Cost ID |
| farm_id | bigint | FK → farms.id | Farm |
| batch_id | bigint | FK → batches.id | Batch |
| pig_entry_record_id | bigint | FK (nullable) | Pig entry reference |
| cost_type | enum | (see below) | Type of cost |
| item_code | varchar(100) | NULL | Item code |
| quantity | int | NULL | Quantity |
| unit | varchar(50) | NULL | Unit (kg, ตัว, etc.) |
| price_per_unit | decimal(10,2) | NULL | Unit price |
| amount | decimal(12,2) | NOT NULL | Amount (฿) |
| total_price | decimal(12,2) | NOT NULL | Total (฿) |
| receipt_file | text | NULL | Receipt URL |
| note | text | NULL | Notes |
| date | date | NOT NULL | Cost date |
| status | enum | pending/approved/rejected | Status |
| created_by | bigint | FK → users.id | Created by |
| created_at | timestamp | - | Creation time |
| updated_at | timestamp | - | Last update |

**Indexes:**
- FK: farm_id, batch_id
- Status: For filtering
- cost_type: For type-based filtering

**Cost Types:**
- feed: Animal feed
- medicine: Medicines/vaccines
- wage: Labor costs
- electric_bill: Electricity
- water_bill: Water
- shipping: Transport costs
- other: Miscellaneous
- piglet: Pig entry (requires manual approval)

---

### COST_PAYMENT
**Purpose:** Payment records for costs

| Field | Type | Constraint | Description |
|-------|------|-----------|-------------|
| id | bigint | PK | Payment ID |
| cost_id | bigint | FK → costs.id | Cost |
| payment_number | varchar(100) | NOT NULL | Payment reference |
| amount | decimal(12,2) | NOT NULL | Amount (฿) |
| payment_date | date | NOT NULL | Payment date |
| payment_method | varchar(50) | NULL | cash/transfer/cheque |
| reference_number | varchar(100) | NULL | Bank reference |
| bank_name | varchar(255) | NULL | Bank name |
| status | enum | pending/approved/rejected | Status |
| receipt_file | text | NULL | Receipt URL |
| notes | text | NULL | Notes |
| cost_type | varchar(50) | NULL | Duplicate of cost.cost_type |
| recorded_by | bigint | FK → users.id | Recorded by |
| approved_by | bigint | FK → users.id | Approved by |
| approved_date | date | NULL | Approval date |
| created_at | timestamp | - | Creation time |

**Indexes:**
- FK: cost_id, recorded_by, approved_by
- Status: For filtering

---

### PIG_SALES
**Purpose:** Record pig sales transactions

| Field | Type | Constraint | Description |
|-------|------|-----------|-------------|
| id | bigint | PK | Sale ID |
| batch_id | bigint | FK → batches.id | Batch (nullable) |
| farm_id | bigint | FK → farms.id | Farm |
| pen_id | bigint | FK → pens.id | Pen (nullable) |
| customer_id | bigint | FK → customers.id | Customer |
| sale_number | varchar(100) | NOT NULL | Sale invoice # |
| date | date | NOT NULL | Sale date |
| quantity | int | NOT NULL | Pig count |
| total_weight | decimal(10,2) | NOT NULL | Total weight (kg) |
| actual_weight | decimal(10,2) | NULL | Actual weight if measured |
| price_per_kg | decimal(10,2) | NOT NULL | Price/kg |
| price_per_pig | decimal(12,2) | NULL | Price/pig |
| total_price | decimal(12,2) | NOT NULL | Total (฿) |
| shipping_cost | decimal(10,2) | DEFAULT 0 | Shipping (฿) |
| net_total | decimal(12,2) | NOT NULL | Net total (฿) |
| payment_method | varchar(50) | NULL | Payment method |
| payment_status | enum | (see below) | Payment status |
| status | enum | pending/approved/rejected | Sale status |
| receipt_file | text | NULL | Receipt URL |
| note | text | NULL | Notes |
| created_by | bigint | FK → users.id | Created by |
| approved_by | bigint | FK → users.id | Approved by |
| approved_at | datetime | NULL | Approval time |
| created_at | timestamp | - | Creation time |
| updated_at | timestamp | - | Last update |

**Indexes:**
- FK: batch_id, farm_id, pen_id, customer_id
- Status: For filtering

**Payment Status:**
- รอชำระ: Awaiting payment
- ชำระแล้ว: Paid
- ชำระบางส่วน: Partial payment
- เกินกำหนด: Overdue
- ยกเลิกการขาย: Cancelled

---

### PAYMENT
**Purpose:** Payment records for pig sales

| Field | Type | Constraint | Description |
|-------|------|-----------|-------------|
| id | bigint | PK | Payment ID |
| pig_sale_id | bigint | FK → pig_sales.id | Pig sale |
| payment_number | varchar(100) | NOT NULL | Reference # |
| amount | decimal(12,2) | NOT NULL | Amount (฿) |
| payment_date | date | NOT NULL | Payment date |
| payment_method | varchar(50) | NOT NULL | cash/transfer/cheque |
| reference_number | varchar(100) | NULL | Bank reference |
| bank_name | varchar(255) | NULL | Bank name |
| receipt_file | text | NULL | Receipt URL |
| note | text | NULL | Notes |
| status | enum | pending/approved/rejected | Status |
| recorded_by | bigint | FK → users.id | Recorded by |
| approved_by | bigint | FK → users.id | Approved by |
| approved_date | date | NULL | Approval date |
| rejected_by | bigint | FK → users.id | Rejected by |
| rejected_date | date | NULL | Rejection date |
| created_at | timestamp | - | Creation time |

**Indexes:**
- FK: pig_sale_id, recorded_by, approved_by
- Status: For filtering

---

### REVENUE
**Purpose:** Revenue records from sales

| Field | Type | Constraint | Description |
|-------|------|-----------|-------------|
| id | bigint | PK | Revenue ID |
| farm_id | bigint | FK → farms.id | Farm |
| batch_id | bigint | FK → batches.id | Batch |
| pig_sale_id | bigint | FK → pig_sales.id | Sale (unique) |
| revenue_type | varchar(50) | - | Type (pig_sale) |
| quantity | int | NOT NULL | Quantity |
| unit_price | decimal(10,2) | NOT NULL | Unit price |
| total_revenue | decimal(12,2) | NOT NULL | Total (฿) |
| discount | decimal(10,2) | DEFAULT 0 | Discount |
| net_revenue | decimal(12,2) | NOT NULL | Net (฿) |
| revenue_date | date | NOT NULL | Revenue date |
| status | enum | pending/approved | Status |
| note | text | NULL | Notes |
| created_at | timestamp | - | Creation time |
| updated_at | timestamp | - | Last update |

**Indexes:**
- UQ: pig_sale_id
- FK: batch_id, farm_id

---

### PROFIT
**Purpose:** Profit calculations per batch

| Field | Type | Constraint | Description |
|-------|------|-----------|-------------|
| id | bigint | PK | Profit ID |
| farm_id | bigint | FK → farms.id | Farm |
| batch_id | bigint | FK → batches.id | Batch (unique) |
| revenue_id | bigint | FK → revenue.id | Revenue |
| total_revenue | decimal(12,2) | NOT NULL | Total revenue (฿) |
| total_cost | decimal(12,2) | NOT NULL | Total cost (฿) |
| gross_profit | decimal(12,2) | NOT NULL | Profit (฿) |
| profit_margin | decimal(8,2) | NOT NULL | Margin % |
| adg | decimal(6,3) | DEFAULT 0 | KPI: ADG |
| fcr | decimal(6,3) | DEFAULT 0 | KPI: FCR |
| fcg | decimal(8,2) | DEFAULT 0 | KPI: FCG |
| ending_avg_weight | decimal(8,2) | NULL | Final weight |
| days_in_farm | int | DEFAULT 0 | Days raised |
| profit_status | varchar(50) | NULL | Final/Projected |
| created_at | timestamp | - | Creation time |
| updated_at | timestamp | - | Last update |

**Indexes:**
- UQ: batch_id
- FK: batch_id, farm_id

---

## Inventory Tables

### STOREHOUSE
**Purpose:** Inventory items management

| Field | Type | Constraint | Description |
|-------|------|-----------|-------------|
| id | bigint | PK | Item ID |
| farm_id | bigint | FK → farms.id | Farm |
| item_code | varchar(100) | NOT NULL | Item code (unique) |
| item_name | varchar(255) | NOT NULL | Item name |
| item_type | enum | (see below) | Type |
| quantity | int | NOT NULL | Current quantity |
| min_quantity | int | NOT NULL | Minimum alert level |
| unit_price | decimal(10,2) | NOT NULL | Unit price (฿) |
| supplier | varchar(255) | NULL | Supplier |
| status | enum | active/inactive | Status |
| last_updated | date | NULL | Last update date |
| created_at | timestamp | - | Creation time |
| updated_at | timestamp | - | Last update |

**Indexes:**
- UQ: (farm_id, item_code)
- FK: farm_id
- item_type: For filtering

**Item Types:**
- feed: Animal feed
- medicine: Medicines/vaccines
- supplies: General supplies
- equipment: Farm equipment
- other: Other items

---

### INVENTORY_MOVEMENT
**Purpose:** Track inventory in/out movements

| Field | Type | Constraint | Description |
|-------|------|-----------|-------------|
| id | bigint | PK | Movement ID |
| batch_id | bigint | FK → batches.id | Batch (nullable) |
| storehouse_id | bigint | FK → storehouse.id | Item |
| date | date | NOT NULL | Movement date |
| change_type | enum | in/out | In or Out |
| quantity_changed | int | NOT NULL | Quantity |
| cost_per_unit | decimal(10,2) | NOT NULL | Unit cost |
| total_cost | decimal(12,2) | NOT NULL | Total (฿) |
| reason | varchar(255) | NULL | Reason |
| recorded_by | bigint | FK → users.id | Recorded by |
| created_at | timestamp | - | Creation time |

**Indexes:**
- FK: batch_id, storehouse_id, recorded_by
- change_type: For in/out filtering

---

### STOREHOUSE_AUDIT_LOG
**Purpose:** Audit trail for inventory changes

| Field | Type | Constraint | Description |
|-------|------|-----------|-------------|
| id | bigint | PK | Log ID |
| storehouse_id | bigint | FK → storehouse.id | Item |
| old_quantity | int | NULL | Previous quantity |
| new_quantity | int | NOT NULL | New quantity |
| change_type | enum | in/out/adjust | Type |
| reason | varchar(255) | NULL | Reason |
| changed_by | bigint | FK → users.id | Changed by |
| changed_at | datetime | NOT NULL | Change time |
| created_at | timestamp | - | Creation time |

**Indexes:**
- FK: storehouse_id, changed_by

---

## System Tables

### ROLES
**Purpose:** User role definitions

| Field | Type | Constraint | Description |
|-------|------|-----------|-------------|
| id | bigint | PK | Role ID |
| name | varchar(255) | UQ | Role name |
| description | text | NULL | Description |
| created_at | timestamp | - | Creation time |
| updated_at | timestamp | - | Last update |

**Standard Roles:**
- Admin: Full system access
- Manager: Operation management
- Staff: Data entry
- Viewer: Read-only access

---

### PERMISSIONS
**Purpose:** Permission definitions

| Field | Type | Constraint | Description |
|-------|------|-----------|-------------|
| id | bigint | PK | Permission ID |
| name | varchar(255) | UQ | Permission name |
| description | text | NULL | Description |
| created_at | timestamp | - | Creation time |

**Example Permissions:**
- manage_users
- manage_costs
- approve_payments
- view_reports
- manage_inventory

---

### NOTIFICATIONS
**Purpose:** User notification messages

| Field | Type | Constraint | Description |
|-------|------|-----------|-------------|
| id | bigint | PK | Notification ID |
| user_id | bigint | FK → users.id | Recipient |
| type | varchar(50) | NOT NULL | Type |
| title | varchar(255) | NOT NULL | Title |
| message | text | NOT NULL | Message |
| is_read | boolean | DEFAULT false | Read status |
| related_model | varchar(100) | NULL | Model type |
| related_id | bigint | NULL | Record ID |
| created_at | timestamp | - | Creation time |

**Indexes:**
- FK: user_id
- (user_id, is_read): For unread queries

**Notification Types:**
- cost_created
- cost_approved
- payment_recorded
- payment_approved
- batch_alert
- inventory_low
- system_alert

---

## Status & Type Definitions

### Common Status Enums

```
Cost Status:
- pending: Awaiting approval
- approved: Approved
- rejected: Rejected

Payment Status:
- pending: Awaiting approval
- approved: Approved
- rejected: Rejected

Sale Status:
- pending: Initial
- approved: Approved
- rejected: Rejected
- ยกเลิก: Cancelled

Batch Status:
- active: In progress
- closed: Completed
- cancelled: Terminated

User Status:
- pending: Awaiting approval
- active: Active account
- inactive: Inactive
```

---

## Relationships Summary

```
Farm (1) ──────→ (N) Barns
Farm (1) ──────→ (N) Batches
Farm (1) ──────→ (N) Users
Farm (1) ──────→ (N) StoreHouse

Barn (1) ──────→ (N) Pens
Barn (1) ──────→ (N) Batches

Batch (1) ──────→ (N) DairyRecords
Batch (1) ──────→ (N) Costs
Batch (1) ──────→ (N) PigSales
Batch (1) ──────→ (1) BatchMetrics
Batch (1) ──────→ (1) Profit

Cost (1) ──────→ (1) CostPayment
Revenue (1) ────→ (1) Profit
PigSale (1) ────→ (N) Payments
PigSale (1) ────→ (1) Revenue

StoreHouse (1) ─→ (N) InventoryMovements
```

---

**Last Updated:** November 8, 2025
**Version:** 1.0
