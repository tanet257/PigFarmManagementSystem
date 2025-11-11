# Pig Farm Management System - Architecture, Observers, Services & Helpers

## 1. Application Architecture Overview

```
┌──────────────────────────────────────────────────────────────────────┐
│                    PIG FARM MANAGEMENT SYSTEM                        │
│                     Architecture Layers (Laravel)                    │
└──────────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────────────┐
│  PRESENTATION LAYER (Views & Frontend)                            │
│  - Blade Templates (resources/views/)                             │
│  - Bootstrap UI Components                                        │
│  - Chart.js for visualizations                                   │
│  - Form validations & modals                                     │
└────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌────────────────────────────────────────────────────────────────────┐
│  API LAYER (Routes & Controllers)                                 │
│  - Web Routes (routes/web.php)                                    │
│  - API Routes (routes/api.php)                                    │
│  - Controllers (app/Http/Controllers/)                            │
│  - Middleware (authentication, permissions)                      │
│  - Request Validation                                             │
└────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌────────────────────────────────────────────────────────────────────┐
│  BUSINESS LOGIC LAYER                                             │
│                                                                     │
│  ┌─ Services (app/Services/)                                      │
│  │  ├─ PaymentService (payment handling)                          │
│  │  ├─ BarnPenSelectionService (pen/barn selection)              │
│  │  ├─ PigPriceService (pricing)                                 │
│  │  └─ UploadService (file uploads)                              │
│  │                                                                 │
│  ├─ Helpers (app/Helpers/)                                        │
│  │  ├─ RevenueHelper (profit calculation)                         │
│  │  ├─ NotificationHelper (notifications)                         │
│  │  ├─ PaymentApprovalHelper (payment workflow)                   │
│  │  ├─ PigInventoryHelper (pig inventory)                         │
│  │  ├─ StoreHouseHelper (storage management)                      │
│  │  ├─ BatchRestoreHelper (batch restoration)                     │
│  │  └─ BatchTreatmentHelper (treatment management)                │
│  │                                                                 │
│  └─ Observers (app/Observers/) [Event-Driven]                    │
│     ├─ CostObserver (auto-approval of costs)                      │
│     ├─ InventoryMovementObserver (KPI calculation)                │
│     └─ PigDeathObserver (death tracking)                          │
│                                                                     │
└────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌────────────────────────────────────────────────────────────────────┐
│  DATA LAYER (Models)                                              │
│  - Eloquent Models (app/Models/)                                  │
│  - Database Relationships                                         │
│  - Query Scopes                                                   │
│  - Model Events                                                   │
└────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌────────────────────────────────────────────────────────────────────┐
│  DATABASE LAYER (MySQL)                                           │
│  - Tables with relationships                                      │
│  - Indexes & constraints                                          │
│  - Migrations (database/migrations/)                              │
│  - Seeders (database/seeders/)                                    │
└────────────────────────────────────────────────────────────────────┘
```

---

## 2. Observer Pattern Implementation

### 2.1 CostObserver (Auto-Approval System)

**File:** `app/Observers/CostObserver.php`

**Purpose:** Automatically approve certain costs and create payment records

**Events Handled:**
- `created`: When a new Cost is recorded
- `updated`: When a Cost is modified
- `deleted`: When a Cost is deleted

**Logic Flow:**

```php
public function created(Cost $cost)
{
    // Auto-approve these types:
    $autoApproveCostTypes = [
        'feed',           // Food costs (from inventory)
        'medicine',       // Medicine costs (from inventory)
        'wage',           // Labor costs
        'electric_bill',  // Utility costs
        'water_bill',     // Utility costs
        'other',          // Miscellaneous
        'shipping'        // Transport costs
    ];
    
    if (in_array($cost->cost_type, $autoApproveCostTypes)) {
        // ✅ Auto-approve
        CostPayment::create([
            'cost_id'      => $cost->id,
            'cost_type'    => $cost->cost_type,
            'status'       => 'approved',
            'amount'       => $cost->total_price,
            'approved_by'  => auth()->id() ?? 1,
            'approved_date'=> now(),
        ]);
        
        // ✅ Recalculate Profit
        if ($cost->batch_id) {
            RevenueHelper::calculateAndRecordProfit($cost->batch_id);
        }
    } else if ($cost->cost_type === 'piglet') {
        // ❌ Manual approval needed
        CostPayment::create([
            'cost_id'  => $cost->id,
            'cost_type'=> $cost->cost_type,
            'status'   => 'pending',  // Waiting for admin
            'amount'   => $cost->amount,
        ]);
    }
}
```

**Cost Types:**
```
1. feed → Auto-approve (from inventory in)
2. medicine → Auto-approve (from inventory in)
3. wage → Auto-approve (from pig entry)
4. electric_bill → Auto-approve
5. water_bill → Auto-approve
6. other → Auto-approve
7. shipping → Auto-approve (with piglet entry)
8. piglet ❌ → Manual approval required
```

---

### 2.2 InventoryMovementObserver (KPI Calculation)

**File:** `app/Observers/InventoryMovementObserver.php`

**Purpose:** Update KPI metrics when inventory is used

**Logic:**

```php
public function created(InventoryMovement $inventoryMovement)
{
    // If item is used (OUT):
    if ($inventoryMovement->change_type === 'out' && $inventoryMovement->batch_id) {
        $batch = $inventoryMovement->batch;
        // Calculate KPI: ADG, FCR, FCG
        RevenueHelper::calculateKPIMetrics($batch);
    } else {
        // If item added (IN):
        // Create Cost record for the inventory purchase
        RevenueHelper::recordStorehouseCost($inventoryMovement);
    }
}
```

---

### 2.3 PigDeathObserver (Mortality Tracking)

**File:** `app/Observers/PigDeathObserver.php`

**Purpose:** Update batch mortality rate when pigs die

**Logic:**
```php
public function created(PigDeath $pigDeath)
{
    // Update batch mortality metrics
    // Recalculate KPI metrics
}
```

---

## 3. Service Layer

### 3.1 PaymentService (Payment Management)

**File:** `app/Services/PaymentService.php`

**Main Methods:**

#### recordCostPayment()
```php
/**
 * Record payment for batch costs (piglet feed entry)
 * - Create/Update Cost record
 * - Create/Update CostPayment record
 * - Handle file uploads
 * - Send notifications
 */
public static function recordCostPayment(Request $request, $batchId)
{
    // Validate
    // Upload receipt (Cloudinary)
    // Create/Update Cost
    // Create CostPayment (pending)
    // Notify admin
    // Return result
}
```

#### recordSalePayment()
```php
/**
 * Record payment for pig sales
 * - Validate amount
 * - Check remaining balance
 * - Create Payment record
 * - Upload receipt
 * - Send notifications
 */
public static function recordSalePayment(Request $request)
{
    // Validate input
    // Check if amount exceeds remaining
    // Upload receipt (Cloudinary)
    // Create Payment (pending)
    // Notify admin for approval
    // Return result
}
```

### 3.2 BarnPenSelectionService (Infrastructure Selection)

**Purpose:** Get available barns/pens for farm and batch

```php
/**
 * Get pens for farm and batch
 * Used by: Treatments, Dairy Records, Pig Sales
 */
public static function getPensByFarmAndBatch($farmId, $batchId)
{
    // Group by barn
    // Filter by batch allocation
    // Return: [barn_code => [pen_code, ...]]
}
```

### 3.3 PigPriceService (Pricing)

**Purpose:** Get latest pig prices

```php
public static function getLatestPrice()
{
    // Get latest price from CPF or market
    // Return: [date, price_per_kg]
}
```

### 3.4 UploadService (File Management)

**Purpose:** Upload files to Cloudinary

```php
public static function uploadToCloudinary($file)
{
    // Upload to Cloudinary
    // Return: [success, url, error]
}
```

---

## 4. Helper Layer

### 4.1 RevenueHelper (Profit Calculation Engine)

**File:** `app/Helpers/RevenueHelper.php`

**Key Methods:**

#### calculateAndRecordProfit()
```php
/**
 * Main profit calculation method
 * Called when:
 * - Cost is approved (CostObserver)
 * - Revenue is recorded (PigSale)
 * - Payment is approved (Admin)
 * 
 * Formula:
 * - Gross Profit = Total Revenue - Total Approved Costs
 * - Margin = (Profit / Revenue) × 100
 */
public static function calculateAndRecordProfit($batchId)
{
    // Get all approved costs
    $totalCost = Cost::where('batch_id', $batchId)
                    ->where('status', 'approved')
                    ->sum('total_price');
    
    // Get total revenue
    $totalRevenue = PigSale::where('batch_id', $batchId)
                          ->where('status', 'approved')
                          ->sum('net_total');
    
    // Calculate profit
    $profit = $totalRevenue - $totalCost;
    $margin = ($profit / $totalRevenue) * 100;
    
    // Update Profit record
    Profit::updateOrCreate(
        ['batch_id' => $batchId],
        [
            'gross_profit' => $profit,
            'profit_margin' => $margin,
            'total_revenue' => $totalRevenue,
            'total_cost' => $totalCost,
        ]
    );
}
```

#### calculateKPIMetrics()
```php
/**
 * Calculate KPI for batch
 * - ADG (Average Daily Gain) = Total Weight Gain / Days
 * - FCR (Feed Conversion Ratio) = Feed Consumed / Weight Gain
 * - FCG (Feed Cost per Gain) = Feed Cost / Weight Gain
 * - Mortality Rate = Dead / Initial × 100
 */
public static function calculateKPIMetrics($batch)
{
    // Get dairy records
    // Calculate totals
    // Store in BatchMetrics
}
```

### 4.2 NotificationHelper (Notification System)

**File:** `app/Helpers/NotificationHelper.php`

**Key Methods:**

#### notifyAdminsPaymentChannelRecorded()
```php
/**
 * Notify admins when payment is recorded
 * For: Cost payments, Sale payments
 * Triggers: Payment approval workflow
 */
public static function notifyAdminsPaymentChannelRecorded($payment, $user)
```

#### notifyAdminsCostCreated()
```php
/**
 * Notify admins for pending cost approvals
 */
```

### 4.3 PaymentApprovalHelper (Payment Workflow)

**Purpose:** Handle payment approval workflow

### 4.4 PigInventoryHelper (Pig Stock Tracking)

**Purpose:** Track pig movements and inventory

### 4.5 StoreHouseHelper (Inventory Management)

**Purpose:** Manage storehouse operations

### 4.6 BatchRestoreHelper (Batch Restoration)

**Purpose:** Restore deleted batches

### 4.7 BatchTreatmentHelper (Treatment Management)

**Purpose:** Manage batch treatments

---

## 5. Provider Registration

**File:** `app/Providers/AppServiceProvider.php`

```php
public function boot()
{
    // Register Observers
    Cost::observe(CostObserver::class);
    InventoryMovement::observe(InventoryMovementObserver::class);
    PigDeath::observe(PigDeathObserver::class);
}
```

---

## 6. Event Flow Diagram

### Cost Creation Flow
```
AdminCreatesCharge
    ↓
Cost Model created event
    ↓
CostObserver::created()
    ├─ Check cost_type
    ├─ Auto-approve? (if in list)
    │  └─ Create CostPayment (approved)
    └─ piglet? (if piglet)
       └─ Create CostPayment (pending)
    ↓
RevenueHelper::calculateAndRecordProfit()
    ├─ Get approved costs
    ├─ Get revenue
    ├─ Calculate profit
    └─ Update Profit model
    ↓
Database Updated
    ↓
Dashboard Refreshed
```

### Payment Approval Flow
```
UserRecordsPayment
    ↓
PaymentService::recordSalePayment()
    ├─ Validate input
    ├─ Upload receipt
    ├─ Create Payment (pending)
    └─ Notify admin
    ↓
NotificationHelper::notifyAdminsPaymentChannelRecorded()
    └─ Send alert
    ↓
Admin Reviews Payment
    ↓
Admin Approves/Rejects
    ├─ Approve → Payment status = approved
    └─ Reject → Payment status = rejected
    ↓
RevenueHelper::calculateAndRecordProfit()
    └─ Update profit if approved
    ↓
Notification to User
```

---

## 7. Data Flow Integration

```
PigSale Created
    ↓
├─ Create Revenue (auto)
├─ Create Payment (pending)
├─ Notify admin for approval
│
└─ Admin Approves Payment
   ├─ Update Payment status
   ├─ CostObserver auto-approves costs
   ├─ RevenueHelper calculates profit
   ├─ Update Profit record
   ├─ Update dashboard
   └─ Notify user
```

---

**Last Updated:** November 8, 2025
**Version:** 1.0
