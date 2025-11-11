# Pig Farm Management System - Entity Relationship Diagram (ERD)

## 1. ERD - Full Schema

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         PIG FARM MANAGEMENT SYSTEM                          │
│                              Database Schema                                │
└─────────────────────────────────────────────────────────────────────────────┘

═══════════════════════════════════════════════════════════════════════════════
CORE MANAGEMENT ENTITIES
═══════════════════════════════════════════════════════════════════════════════

┌──────────────────┐
│      USERS       │
├──────────────────┤
│ id (PK)          │
│ name             │
│ email (UQ)       │
│ password         │
│ farm_id (FK)     │◄──────────────┐
│ role_id (FK)     │◄──────────┐   │
│ status           │           │   │
│ created_at       │           │   │
│ updated_at       │           │   │
└──────────────────┘           │   │
         │                     │   │
         │                     │   │
         │          ┌──────────────┐
         │          │    ROLES     │
         │          ├──────────────┤
         │          │ id (PK)      │
         │          │ name (UQ)    │
         │          │ description  │
         │          │ created_at   │
         │          └──────────────┘
         │
         ▼
┌──────────────────┐        ┌─────────────────┐
│     FARMS        │        │    PERMISSIONS  │
├──────────────────┤        ├─────────────────┤
│ id (PK)          │        │ id (PK)         │
│ farm_name        │        │ name (UQ)       │
│ owner_name       │        │ description     │
│ location         │        │ created_at      │
│ created_at       │        └─────────────────┘
│ updated_at       │
└──────────────────┘
       │
       │ 1:N
       ▼
┌──────────────────┐
│      BARNS       │
├──────────────────┤
│ id (PK)          │
│ farm_id (FK)     │
│ barn_code (UQ)   │
│ barn_name        │
│ capacity         │
│ location         │
│ created_at       │
└──────────────────┘
       │
       │ 1:N
       ▼
┌──────────────────┐
│      PENS        │
├──────────────────┤
│ id (PK)          │
│ barn_id (FK)     │
│ pen_code (UQ)    │
│ pen_name         │
│ capacity         │
│ location         │
│ created_at       │
└──────────────────┘


═══════════════════════════════════════════════════════════════════════════════
BATCH MANAGEMENT & TRACKING ENTITIES
═══════════════════════════════════════════════════════════════════════════════

┌──────────────────┐
│     BATCHES      │
├──────────────────┤
│ id (PK)          │
│ farm_id (FK)     │◄─────────────────────┐
│ batch_code (UQ)  │                      │
│ start_date       │                      │
│ expected_end     │                      │
│ initial_quantity │                      │
│ starting_weight  │                      │
│ target_weight    │                      │
│ status           │                      │
│ created_at       │                      │
└──────────────────┘                      │
       │ 1:N                              │
       │                                  │
       ├─────────────────────────┐        │
       │                         │        │
       ▼                         ▼        │
┌──────────────────────┐  ┌──────────────────────┐
│ BATCH_PEN_           │  │ BATCH_METRICS        │
│ ALLOCATION           │  ├──────────────────────┤
├──────────────────────┤  │ id (PK)              │
│ id (PK)              │  │ batch_id (FK)        │
│ batch_id (FK)        │  │ adg (avg daily gain) │
│ pen_id (FK)          │  │ fcr (feed conv ratio)│
│ quantity_allocated   │  │ fcg (feed cost/gain) │
│ allocated_date       │  │ mortality_rate       │
│ removed_date         │  │ morbidity_rate       │
│ created_at           │  │ ending_avg_weight    │
└──────────────────────┘  │ days_in_farm         │
                          │ updated_at           │
                          └──────────────────────┘
       
       │ 1:N
       ▼
┌──────────────────────┐
│   DAIRY_RECORDS      │
├──────────────────────┤
│ id (PK)              │
│ batch_id (FK)        │◄─────────────────────┐
│ record_date          │                      │
│ quantity_pigs        │                      │
│ avg_weight_per_pig   │                      │
│ feed_consumed_kg     │                      │
│ sick_count           │                      │
│ dead_count           │                      │
│ health_notes         │                      │
│ recorded_by (FK)     │                      │
│ created_at           │                      │
└──────────────────────┘                      │
       │ 1:N                                  │
       │                                      │
       ▼                                      │
┌─────────────────────────┐                   │
│  DAIRY_RECORD_ITEMS     │                   │
├─────────────────────────┤                   │
│ id (PK)                 │                   │
│ dairy_record_id (FK)    │                   │
│ item_type               │                   │
│ quantity                │                   │
│ unit                    │                   │
│ cost                    │                   │
│ created_at              │                   │
└─────────────────────────┘                   │
                                              │
       ┌──────────────────────────────────────┘
       │
       ▼
┌─────────────────────────┐
│     PIG_ENTRY_RECORD    │
├─────────────────────────┤
│ id (PK)                 │
│ batch_id (FK)           │
│ farm_id (FK)            │
│ pig_entry_date          │
│ total_pig_amount        │
│ total_pig_weight        │
│ total_pig_price         │
│ weight_per_pig          │
│ unit_price              │
│ supplier_name           │
│ created_by (FK)         │
│ created_at              │
└─────────────────────────┘
       │ 1:N
       ▼
┌─────────────────────────┐
│   PIG_ENTRY_DETAIL      │
├─────────────────────────┤
│ id (PK)                 │
│ pig_entry_record_id(FK) │
│ unit_number             │
│ weight                  │
│ health_status           │
│ noted_issues            │
│ created_at              │
└─────────────────────────┘


═══════════════════════════════════════════════════════════════════════════════
SALES & CUSTOMER ENTITIES
═══════════════════════════════════════════════════════════════════════════════

┌──────────────────┐
│   CUSTOMERS      │
├──────────────────┤
│ id (PK)          │
│ customer_name    │
│ phone            │
│ email            │
│ address          │
│ created_at       │
└──────────────────┘
       │ 1:N
       │
       ▼
┌──────────────────────┐
│    PIG_SALES         │
├──────────────────────┤
│ id (PK)              │
│ batch_id (FK)        │◄────────┐
│ farm_id (FK)         │         │
│ pen_id (FK)          │         │
│ customer_id (FK)     │         │
│ sale_number          │         │
│ date                 │         │
│ quantity             │         │
│ total_weight         │         │
│ actual_weight        │         │
│ price_per_kg         │         │
│ price_per_pig        │         │
│ total_price          │         │
│ shipping_cost        │         │
│ net_total            │         │
│ payment_method       │         │
│ payment_status       │         │
│ status               │         │
│ receipt_file         │         │
│ created_by (FK)      │         │
│ created_at           │         │
└──────────────────────┘         │
       │ 1:N                      │
       │                          │
       ▼                          │
┌─────────────────────────┐       │
│  PIG_SALE_DETAIL        │       │
├─────────────────────────┤       │
│ id (PK)                 │       │
│ pig_sale_id (FK)        │       │
│ item_number             │       │
│ quantity                │       │
│ weight                  │       │
│ unit_price              │       │
│ amount                  │       │
│ created_at              │       │
└─────────────────────────┘       │
                                  │
       ┌──────────────────────────┘
       │
       ▼
┌──────────────────────┐
│    REVENUE           │
├──────────────────────┤
│ id (PK)              │
│ batch_id (FK)        │
│ pig_sale_id (FK)     │
│ amount               │
│ revenue_date         │
│ status (pending/app) │
│ created_at           │
└──────────────────────┘


═══════════════════════════════════════════════════════════════════════════════
COST & FINANCIAL ENTITIES
═══════════════════════════════════════════════════════════════════════════════

┌──────────────────┐
│      COSTS       │
├──────────────────┤
│ id (PK)          │
│ batch_id (FK)    │◄──────────────────────┐
│ cost_type        │                       │
│ description      │                       │
│ amount           │                       │
│ cost_date        │                       │
│ status (pending) │                       │
│ created_by (FK)  │                       │
│ created_at       │                       │
│ updated_at       │                       │
└──────────────────┘                       │
       │ 1:N                               │
       │                                   │
       ├────────────────────┐              │
       │                    │              │
       ▼                    │              │
┌──────────────────┐        │              │
│ COST_PAYMENT     │        │              │
├──────────────────┤        │              │
│ id (PK)          │        │              │
│ cost_id (FK)     │        │              │
│ payment_number   │        │              │
│ amount_paid      │        │              │
│ payment_date     │        │              │
│ payment_method   │        │              │
│ reference_number │        │              │
│ bank_name        │        │              │
│ status (pending) │        │              │
│ receipt_file     │        │              │
│ notes            │        │              │
│ created_at       │        │              │
└──────────────────┘        │              │
                            │              │
       ┌────────────────────┘              │
       │                                   │
       ▼                                   │
┌──────────────────────────┐               │
│      PAYMENT             │               │
├──────────────────────────┤               │
│ id (PK)                  │               │
│ pig_sale_id (FK)         │               │
│ payment_number           │               │
│ amount                   │               │
│ payment_date             │               │
│ payment_method           │               │
│ reference_number         │               │
│ bank_name                │               │
│ status (pending/app/rej) │               │
│ receipt_file             │               │
│ notes                     │               │
│ created_by (FK)          │               │
│ created_at               │               │
└──────────────────────────┘               │
                                           │
       ┌───────────────────────────────────┘
       │
       ▼
┌──────────────────┐
│     PROFIT       │
├──────────────────┤
│ id (PK)          │
│ batch_id (FK)    │
│ revenue_id (FK)  │
│ total_revenue    │
│ total_cost       │
│ gross_profit     │
│ profit_margin    │
│ adg              │
│ fcr              │
│ fcg              │
│ profit_status    │
│ created_at       │
│ updated_at       │
└──────────────────┘
       │ 1:N
       ▼
┌─────────────────────────┐
│    PROFIT_DETAIL        │
├─────────────────────────┤
│ id (PK)                 │
│ profit_id (FK)          │
│ detail_type             │
│ description             │
│ amount                  │
│ notes                   │
│ created_at              │
└─────────────────────────┘


═══════════════════════════════════════════════════════════════════════════════
TREATMENT & HEALTH ENTITIES
═══════════════════════════════════════════════════════════════════════════════

┌────────────────────────┐
│   BATCH_TREATMENT      │
├────────────────────────┤
│ id (PK)                │
│ batch_id (FK)          │
│ treatment_name         │
│ treatment_date         │
│ veterinarian_name      │
│ treatment_cost         │
│ notes                  │
│ created_by (FK)        │
│ created_at             │
└────────────────────────┘
       │ 1:N
       ▼
┌────────────────────────────────┐
│  BATCH_TREATMENT_DETAIL        │
├────────────────────────────────┤
│ id (PK)                        │
│ batch_treatment_id (FK)        │
│ medication_name                │
│ quantity                       │
│ dosage                         │
│ administration_method          │
│ start_date                     │
│ end_date                       │
│ notes                          │
│ created_at                     │
└────────────────────────────────┘


┌─────────────────────────────┐
│  DAIRY_TREATMENT            │
├─────────────────────────────┤
│ id (PK)                     │
│ dairy_record_id (FK)        │
│ treatment_type              │
│ medication                  │
│ quantity_treated            │
│ dosage                      │
│ method                      │
│ duration                    │
│ notes                       │
│ recorded_by (FK)            │
│ created_at                  │
└─────────────────────────────┘


┌─────────────────────────────┐
│     PIG_DEATH               │
├─────────────────────────────┤
│ id (PK)                     │
│ batch_id (FK)               │
│ pen_id (FK)                 │
│ death_date                  │
│ quantity                    │
│ cause_of_death              │
│ weight_at_death             │
│ age_days                    │
│ notes                       │
│ recorded_by (FK)            │
│ created_at                  │
└─────────────────────────────┘


┌──────────────────────────┐
│  DAILY_TREATMENT_LOG     │
├──────────────────────────┤
│ id (PK)                  │
│ batch_id (FK)            │
│ treatment_date           │
│ treatment_type           │
│ quantity_treated         │
│ medication_used          │
│ dosage                   │
│ administration_method    │
│ duration_days            │
│ veterinarian_notes       │
│ cost                     │
│ recorded_by (FK)         │
│ created_at               │
└──────────────────────────┘


═══════════════════════════════════════════════════════════════════════════════
INVENTORY & STORAGE ENTITIES
═══════════════════════════════════════════════════════════════════════════════

┌─────────────────────────┐
│    STORE_HOUSE          │
├─────────────────────────┤
│ id (PK)                 │
│ farm_id (FK)            │
│ item_code (UQ)          │
│ item_name               │
│ item_type               │
│ quantity                │
│ min_quantity            │
│ unit_price              │
│ supplier                │
│ last_updated            │
│ created_at              │
└─────────────────────────┘
       │ 1:N
       ▼
┌─────────────────────────────┐
│  INVENTORY_MOVEMENT         │
├─────────────────────────────┤
│ id (PK)                     │
│ batch_id (FK)               │
│ storehouse_id (FK)          │
│ date                        │
│ change_type (in/out)        │
│ quantity_changed            │
│ cost_per_unit               │
│ total_cost                  │
│ reason                      │
│ recorded_by (FK)            │
│ created_at                  │
└─────────────────────────────┘


┌──────────────────────────────┐
│  DAIRY_STOREHOUSE_USE        │
├──────────────────────────────┤
│ id (PK)                      │
│ dairy_record_id (FK)         │
│ storehouse_id (FK)           │
│ quantity_used                │
│ unit                         │
│ cost_per_unit                │
│ total_cost                   │
│ usage_purpose                │
│ created_at                   │
└──────────────────────────────┘


┌──────────────────────────────┐
│  STOREHOUSE_AUDIT_LOG        │
├──────────────────────────────┤
│ id (PK)                      │
│ storehouse_id (FK)           │
│ old_quantity                 │
│ new_quantity                 │
│ change_type (in/out/adjust)  │
│ reason                       │
│ changed_by (FK)              │
│ changed_at                   │
│ created_at                   │
└──────────────────────────────┘


═══════════════════════════════════════════════════════════════════════════════
SYSTEM ENTITIES
═══════════════════════════════════════════════════════════════════════════════

┌──────────────────┐
│   NOTIFICATION   │
├──────────────────┤
│ id (PK)          │
│ user_id (FK)     │
│ type             │
│ title            │
│ message          │
│ is_read          │
│ related_model    │
│ related_id       │
│ created_at       │
└──────────────────┘
```

---

## 2. Table Relationships Summary

### One-to-Many (1:N) Relationships

| Parent | Child | Foreign Key | Notes |
|--------|-------|-------------|-------|
| FARMS | BARNS | farm_id | Each farm has multiple barns |
| BARNS | PENS | barn_id | Each barn has multiple pens |
| FARMS | BATCHES | farm_id | Each farm has multiple batches |
| BATCHES | BATCH_PEN_ALLOCATION | batch_id | Pigs allocated to multiple pens |
| BATCHES | DAIRY_RECORDS | batch_id | Daily records per batch |
| BATCHES | PIG_ENTRY_RECORD | batch_id | Entry records for batch |
| BATCHES | COSTS | batch_id | Multiple costs per batch |
| BATCHES | PIG_SALES | batch_id | Multiple sales per batch |
| BATCHES | BATCH_TREATMENT | batch_id | Multiple treatments |
| BATCHES | PIG_DEATH | batch_id | Death records |
| BATCHES | PROFIT | batch_id | Profit calculations |
| BATCHES | DAILY_TREATMENT_LOG | batch_id | Treatment logs |
| BATCHES | INVENTORY_MOVEMENT | batch_id | Inventory usage |
| FARMS | STOREHOUSE | farm_id | Farm's inventory items |
| STOREHOUSE | INVENTORY_MOVEMENT | storehouse_id | Item movements |
| STOREHOUSE | AUDIT_LOG | storehouse_id | Change history |
| CUSTOMERS | PIG_SALES | customer_id | Customer purchases |
| PIG_SALES | PAYMENT | pig_sale_id | Payments for sales |
| PIG_SALES | PIG_SALE_DETAIL | pig_sale_id | Sale line items |
| COSTS | COST_PAYMENT | cost_id | Cost payments |
| DAIRY_RECORDS | DAIRY_RECORD_ITEMS | dairy_record_id | Items used daily |
| DAIRY_RECORDS | DAIRY_TREATMENT | dairy_record_id | Treatments recorded |
| DAIRY_RECORDS | DAIRY_STOREHOUSE_USE | dairy_record_id | Inventory usage |
| USERS | (Admin user relations) | - | Creator/updater fields |
| ROLES | USERS | role_id | User role assignment |

### Unique Constraints (UQ)

- **USERS**: email
- **FARMS**: - (implied unique by business logic)
- **BARNS**: (farm_id, barn_code)
- **PENS**: (barn_id, pen_code)
- **BATCHES**: batch_code
- **CUSTOMERS**: - (multiple records can have same name)
- **STOREHOUSE**: (farm_id, item_code)
- **ROLES**: name
- **PERMISSIONS**: name

---

## 3. Key Field Descriptions

### Status Fields

```
COST.status:
- pending: Awaiting approval
- approved: Auto-approved by CostObserver
- rejected: Denied by admin
- paid: CostPayment recorded

PIG_SALE.status:
- pending: Initial entry
- approved: Sale approved
- rejected: Sale cancelled
- ยกเลิก: Sale cancelled

PIG_SALE.payment_status:
- รอชำระ: Awaiting payment
- ชำระแล้ว: Paid
- ชำระบางส่วน: Partial payment
- เกินกำหนด: Overdue
- ยกเลิกการขาย: Cancelled

PAYMENT.status:
- pending: Awaiting approval
- approved: Payment confirmed
- rejected: Payment declined

BATCH.status:
- active: Currently raising
- closed: Sale complete & profit calculated
- cancelled: Batch terminated

REVENUE.status:
- pending: Awaiting profit calculation
- approved: Profit record created
```

### Cost Types

```
COSTS.cost_type:
- feed: Feed costs
- medicine: Medicine/vaccine costs
- labor: Labor/salary costs
- utilities: Electricity, water, etc.
- other: Other miscellaneous costs
- excess_weight_cost: Extra weight charge
- transport_cost: Transport costs
```

### Item Types

```
STOREHOUSE.item_type:
- feed: Animal feed
- medicine: Medicines/vaccines
- supplies: General supplies
- equipment: Farm equipment
- other: Other items
```

---

## 4. Key Aggregations & Calculations

```
BATCH-level Aggregations:
├─ total_revenue = SUM(PIG_SALES.net_total)
├─ total_cost = SUM(COSTS.amount where status='approved')
├─ gross_profit = total_revenue - total_cost
├─ profit_margin = (gross_profit / total_revenue) × 100
├─ adg (ADG) = BATCH_METRICS.adg
├─ fcr (FCR) = BATCH_METRICS.fcr
├─ fcg (FCG) = total_cost / total_weight_gain
├─ mortality_rate = SUM(PIG_DEATH.quantity) / initial_quantity
└─ avg_weight = AVG(DAIRY_RECORDS.avg_weight_per_pig)

FARM-level Aggregations:
├─ active_batches = COUNT(BATCHES where status='active')
├─ total_pigs = SUM(initial_quantity) for all batches
├─ current_inventory_value = SUM(STOREHOUSE.quantity × unit_price)
└─ total_profit = SUM(PROFIT.gross_profit for closed batches)

INVENTORY Calculations:
├─ available_quantity = initial_quantity - SUM(INVENTORY_MOVEMENT.quantity where change_type='out')
├─ stock_status = available_quantity < min_quantity ? 'LOW' : 'OK'
└─ inventory_value = available_quantity × unit_price
```

---

**Last Updated:** November 8, 2025
**Version:** 1.0
