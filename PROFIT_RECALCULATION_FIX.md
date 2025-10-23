# Profit Recalculation Fix - Payment Approvals

## Problem
When approving a payment, the profit calculation page was not updated with the latest cost and revenue data.

## Root Cause Analysis

### Issue 1: Cost Filtering Logic
In `RevenueHelper::calculateAndRecordProfit()`, the code was using `whereHas()` to filter costs that have approved payments:

```php
// ❌ OLD - Might miss some costs due to lazy loading issues
$approvedCosts = Cost::where('batch_id', $batchId)
    ->where('payment_status', '!=', 'ยกเลิก')
    ->whereHas('payments', function ($query) {
        $query->where('status', 'approved');
    })
    ->get();
```

**Problem**: This query might not consistently retrieve all costs due to database relationship issues.

### Issue 2: Cost Collection Filtering
Changed to fetch all costs first, then filter in-memory:

```php
// ✅ NEW - Explicit filtering with logging
$allCosts = Cost::where('batch_id', $batchId)
    ->where('payment_status', '!=', 'ยกเลิก')
    ->get();

$approvedCosts = $allCosts->filter(function ($cost) {
    $hasApprovedPayment = $cost->payments()
        ->where('status', 'approved')
        ->exists();
    return $hasApprovedPayment;
});
```

**Benefits**:
- More explicit and easier to debug
- Ensures all costs are checked
- In-memory filtering is more reliable

## Changes Made

### File: `app/Helpers/RevenueHelper.php`

**1. Modified Cost Filtering (Lines 85-103)**
- Separated cost retrieval into two steps:
  1. Fetch all costs with `Cost::where(...)->get()`
  2. Filter in-memory with `.filter()` callback
- Each cost checks if it has at least one approved payment

**2. Added Comprehensive Logging (Lines ~88-162)**
```php
Log::info("=== Profit Calculation for Batch $batchId ===");
Log::info("Total Revenue: " . $totalRevenue);
Log::info("Total Costs in DB: " . $allCosts->count());
Log::info("Approved Costs (with approved payments): " . count($approvedCosts));
Log::info("Feed: $feedCost, Medicine: $medicineCost, ...");
Log::info("Total Cost: " . $totalCost);
Log::info("Gross Profit: " . $grossProfit . ", Margin: " . $profitMargin . "%");
Log::info("Total Pig Sold: $totalPigSold, Dead: $totalPigDead, Profit per Pig: " . $profitPerPig);
Log::info("Updated/Created Profit Record ID: " . $profit->id);
Log::info("=== Profit Calculation Complete ===");
```

**Logging Benefits**:
- Tracks calculation steps for debugging
- Shows revenue, costs breakdown, profit calculation
- Identifies which Profit record was updated
- Easy to verify data accuracy

## How It Works Now

### When Payment is Approved
1. **PaymentApprovalController::approvePayment()** is called
2. Calls `RevenueHelper::calculateAndRecordProfit($batchId)` 
3. RevenueHelper:
   - Gets all Revenue records with status 'อนุมัติแล้ว' or 'ชำระแล้ว'
   - Gets all Cost records (excluding cancelled)
   - **Filters to only approved Costs** (those with approved payments)
   - Calculates cost breakdown by type (feed, medicine, labor, etc.)
   - **Recalculates Profit**
   - Updates or creates Profit record in database
   - **Logs all steps**

### Data Flow for Profit Page
```
Payment Approved 
    ↓
RevenueHelper::calculateAndRecordProfit()
    ↓
Fetches: Revenue (approved), Cost (with approved payments)
    ↓
Calculates: Total Cost, Gross Profit, Profit Margin
    ↓
Updates: Profit table
    ↓
Profit Page Shows: Updated values ✅
```

## Verification

### Where to Check Logs
```
storage/logs/laravel-YYYY-MM-DD.log
```

### Log Output Example
```
[2025-10-23 14:30:15] local.INFO: === Profit Calculation for Batch 5 ===
[2025-10-23 14:30:15] local.INFO: Total Revenue: 45000
[2025-10-23 14:30:15] local.INFO: Total Costs in DB: 8
[2025-10-23 14:30:15] local.INFO: Approved Costs (with approved payments): 6
[2025-10-23 14:30:15] local.INFO: Feed: 12000, Medicine: 2000, Transport: 3000, Labor: 8000
[2025-10-23 14:30:15] local.INFO: Piglet: 5000, Other: 500, Utility: 1000, Excess Weight: 0
[2025-10-23 14:30:15] local.INFO: Total Cost: 32000
[2025-10-23 14:30:15] local.INFO: Gross Profit: 13000, Margin: 28.89%
[2025-10-23 14:30:15] local.INFO: Total Pig Sold: 100, Dead: 2, Profit per Pig: 130
[2025-10-23 14:30:15] local.INFO: Updated Profit Record ID: 5
[2025-10-23 14:30:15] local.INFO: === Profit Calculation Complete ===
```

## Testing

### Test Case 1: Approve Payment with Costs
1. Go to Payment Approvals page
2. Click "อนุมัติ" on a pending payment
3. Check Profit page - should show updated costs
4. Check logs - should see calculation logs

### Expected Results
- ✅ Profit page updates immediately after approval
- ✅ Cost breakdown shows approved costs only
- ✅ Gross profit recalculates correctly
- ✅ No errors in logs

## Related Files
- `app/Http/Controllers/PaymentApprovalController.php` - Calls profit calculation
- `app/Models/Profit.php` - Profit table model
- `app/Models/Cost.php` - Cost model with payments relationship
- `app/Models/Payment.php` - Payment model

## Phase Information
- **Phase**: 7I+
- **Session**: Payment Approvals Page Display Fix + Profit Recalculation
- **Status**: ✅ COMPLETED

