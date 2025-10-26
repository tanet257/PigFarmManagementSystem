# 🧪 TEST PROFIT FIX - Dead Pig Sale Revenue Bug

## 📋 Test Case: ขายหมูตาย 2 ตัว ราคา ฿1,000/ตัว

### Step 1️⃣: สร้าง Pig Entry และ Batch
- Farm: Test Farm
- Batch: BATCH-TEST-001
- Barn: BARN-01
- Pen: PEN-01
- Total Pigs: 20
- **Expected**: Batch สร้างเสร็จ

### Step 2️⃣: บันทึก Pig Death (3 ตัว)
- Quantity: 3
- Cause: Test
- **Expected**: Death recorded, status = 'recorded'

### Step 3️⃣: ขายหมูตาย 2 ตัว
- Sale Type: หมูตาย (Dead Pig)
- Quantity: 2
- Price per Pig: ฿1,000
- **Total Price: ฿2,000** ← สำคัญ!
- Buyer: Test Buyer
- **Expected**: 
  - PigSale created
  - Status: 'pending'
  - **NO Revenue recorded yet!** (ยังไม่ approve payment)
  - **NO Profit calculated yet!** ← ต้องแก้ไข

### Step 4️⃣: Approve Pig Sale
- Click: Approve
- **Expected**:
  - Status: 'approved'
  - **Still NO Revenue** (รอ Payment approval)
  - **Still NO Profit** ← ต้องแก้ไข

### Step 5️⃣: Record Payment (Pending)
- Amount: ฿2,000
- Payment Method: Cash
- Status: Pending
- **Expected**:
  - Payment created
  - Status: 'pending'
  - **Still NO Revenue**
  - **Still NO Profit**

### Step 6️⃣: Approve Payment ← 🔑 CRITICAL STEP
- Click: Approve Payment
- **Expected**:
  - Payment status: 'approved'
  - ✅ **Revenue RECORDED: ฿2,000** 
  - ✅ **Profit CALCULATED: Profit page shows ฿2,000**

### Step 7️⃣: ตรวจสอบ Profit Page
Go to: Profits → Select Farm & Batch

**Expected Values:**
```
Total Revenue: ฿2,000
Total Cost: ฿X,XXX (จากค่าใช้จ่ายอื่น)
Gross Profit: ฿2,000 - ฿X,XXX
Total Pig Sold: 2
Dead Pigs Sold: 2
```

**🔴 BUG (Before Fix):**
```
Total Revenue: ฿4,000 ← DOUBLE! ❌
```

**✅ EXPECTED (After Fix):**
```
Total Revenue: ฿2,000 ✅
```

### Step 8️⃣: ยกเลิกการขายหมูตาย
- Click: Request Cancel
- Wait for Admin Approval
- Click: Approve Cancel

**Expected**:
- Sale status: 'ยกเลิกการขาย'
- PigDeath status: 'recorded' (back to normal)
- PigDeath quantity_sold_total: 0
- **Revenue DELETED** (ควรลบ)
- **Profit UPDATED** (ควรคำนวณใหม่)
- Profit page shows ฿0 revenue

---

## 🔍 Validation Checklist

### ✅ Before Fix (Bug Expected)
- [ ] Step 3: Revenue NOT shown (correct)
- [ ] Step 4: Profit NOT shown (correct)
- [ ] Step 6: Profit shows ฿4,000 ← **BUG!**

### ✅ After Fix (Should Pass)
- [ ] Step 3: Revenue NOT recorded ✅
- [ ] Step 4: Profit NOT calculated ✅
- [ ] Step 5: Revenue still NOT recorded ✅
- [ ] Step 6: Revenue recorded = ฿2,000 ✅
- [ ] Step 6: Profit calculated = ฿2,000 ✅
- [ ] Step 7: Profit page shows ฿2,000 ✅
- [ ] Step 8: After cancel, Profit = ฿0 ✅

---

## 📊 Data to Check in Database

### revenues table
```sql
SELECT * FROM revenues WHERE batch_id = ? AND pig_sale_id = ?;
```
**Expected**: 1 record with net_revenue = 2000 (after approve payment)

### profits table
```sql
SELECT * FROM profits WHERE batch_id = ?;
```
**Expected**: total_revenue = 2000 (not 4000)

### pig_deaths table
```sql
SELECT * FROM pig_deaths WHERE batch_id = ?;
```
**Expected**: 
- quantity_sold_total = 0 (after cancel)
- status = 'recorded' (after cancel)

---

## 🐛 Root Causes Fixed

1. ❌ **PigDeathObserver.created()** → removed `calculateAndRecordProfit()`
2. ❌ **PigDeathObserver.updated()** → removed `calculateAndRecordProfit()`
3. ❌ **RevenueHelper.calculateAndRecordProfit()** → removed `$deadPigRevenue` duplication
4. ❌ **PigSaleController.approve()** → removed early profit calculation
5. ✅ **PaymentApprovalController.approvePayment()** → kept `calculateAndRecordProfit()` (only here!)

---

## 💡 Expected Behavior

### Timeline
```
1. Create Pig Entry ──┐
                      ├─ Batch created, NO Profit
2. Record Cost ────────┤
                      ├─ Profit has cost info, but NO revenue yet
3. Create Sale ────────┤
                      ├─ Sale pending, NO Revenue, NO Profit change
4. Approve Sale ───────┤
                      ├─ Still NO Revenue (waiting for payment)
5. Record Payment ─────┤
                      ├─ Still NO Revenue (pending payment)
6. Approve Payment ────┤
                      ├─ ✅ Revenue recorded!
                      ├─ ✅ Profit calculated!
7. View Profits ───────┤
                      └─ Shows correct amount (no double counting)
```

### Profit Calculation (Only Once)
```
Step 6: Approve Payment
  ├─ recordPigSaleRevenue() → Create Revenue record
  ├─ calculateAndRecordProfit()
  │   ├─ Get approved Payment IDs
  │   ├─ Sum Revenue (from Revenue table) → ฿2,000
  │   ├─ Sum approved Costs
  │   ├─ Calculate Profit = Revenue - Cost
  │   └─ Update Profit table
  └─ DONE (profit calculated only ONCE)
```

---

## 🎯 Success Criteria

✅ **All pass when:**
1. Profit NOT calculated on Save/Approve Pig Sale
2. Profit calculated ONLY on Approve Payment
3. Revenue amount matches Sale total (no doubling)
4. Cancel Sale removes Revenue completely
5. Profit recalculated correctly after cancel

**If any fail → Bug still exists!**
