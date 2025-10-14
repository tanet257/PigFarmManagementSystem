# ✅ สรุปการตรวจสอบ Models vs Database Columns

## 1️⃣ **PigSell Model** ✅ สมบูรณ์

### Columns ที่มีใน Database:
```php
✅ customer_id              // Foreign key -> customers
✅ sale_number              // เลขที่ใบขาย (SELL-2025-001)
✅ farm_id                  // Foreign key -> farms
✅ batch_id                 // Foreign key -> batches
✅ pig_loss_id              // Foreign key -> pig_deaths
✅ sell_date                // วันที่ขาย
✅ sell_type                // ประเภทการขาย
✅ quantity                 // จำนวน
✅ total_weight             // น้ำหนักรวมเดิม
✅ estimated_weight         // น้ำหนักประมาณการ
✅ actual_weight            // น้ำหนักชั่งจริง
✅ avg_weight_per_pig       // น้ำหนักเฉลี่ย/ตัว
✅ price_per_kg             // ราคาต่อกก.
✅ total_price              // ราคารวม
✅ discount                 // ส่วนลด
✅ shipping_cost            // ค่าขนส่ง
✅ net_total                // ราคาสุทธิ (total_price - discount + shipping_cost)
✅ payment_method           // เงินสด/โอนเงิน/เช็ค/เครดิต
✅ payment_term             // จำนวนวันเครดิต
✅ payment_status           // ชำระแล้ว/ชำระบางส่วน/รอชำระ/เกินกำหนด
✅ paid_amount              // ยอดที่ชำระแล้ว
✅ balance                  // ยอดคงเหลือ
✅ due_date                 // วันครบกำหนดชำระ
✅ paid_date                // วันที่ชำระจริง
✅ invoice_number           // เลขที่ใบกำกับภาษี
✅ receipt_number           // เลขที่ใบเสร็จ
✅ buyer_name               // ชื่อผู้ซื้อ
✅ note                     // หมายเหตุ
✅ sale_status              // รอยืนยัน/ยืนยันแล้ว/เสร็จสิ้น/ยกเลิก
✅ receipt_file             // ไฟล์สลิปชำระเงิน
✅ date                     // วันที่สร้างรายการ
✅ cpf_reference_price      // ราคาอ้างอิง CPF
✅ cpf_reference_date       // วันที่ของราคาอ้างอิง
✅ created_by               // Foreign key -> users (ผู้สร้าง)
✅ approved_by              // Foreign key -> users (ผู้อนุมัติ)
✅ approved_at              // วันที่อนุมัติ
```

### Columns ที่ลบออกแล้ว:
```php
❌ delivery_date           // วันที่ส่งมอบ (ไม่ใช้ - นายหน้าจัดการขนส่งเอง)
❌ delivery_time           // เวลาส่งมอบ (ไม่ใช้)
❌ price_per_pig           // ราคาต่อตัว (ไม่ใช้)
❌ vehicle_number          // ทะเบียนรถ (ไม่ใช้)
❌ driver_name             // ชื่อคนขับ (ไม่ใช้)
❌ distance_km             // ระยะทาง (ไม่ใช้)
```

### Relationships:
```php
✅ customer()              -> belongsTo(Customer::class)
✅ farm()                  -> belongsTo(Farm::class)
✅ batch()                 -> belongsTo(Batch::class)
✅ pigLoss()               -> belongsTo(PigDeath::class)
✅ payments()              -> hasMany(Payment::class)
✅ creator()               -> belongsTo(User::class, 'created_by')
✅ approver()              -> belongsTo(User::class, 'approved_by')
```

### Helper Methods:
```php
✅ generateSaleNumber()    // สร้างเลขที่ใบขาย (SELL-2025-001)
✅ calculateNetTotal()     // คำนวณราคาสุทธิ
✅ calculateBalance()      // คำนวณยอดคงเหลือ
✅ isPaid()                // ตรวจสอบชำระครบแล้วหรือไม่
✅ isOverdue()             // ตรวจสอบเกินกำหนดหรือไม่
```

---

## 2️⃣ **Customer Model** ✅ สมบูรณ์

### Columns:
```php
✅ customer_code           // รหัสลูกค้า (CUST-001)
✅ customer_name           // ชื่อลูกค้า/บริษัท
✅ customer_type           // นายหน้า/โรงชำแหละ/ผู้บริโภค
✅ phone                   // เบอร์โทร
✅ line_id                 // LINE ID
✅ address                 // ที่อยู่
✅ tax_id                  // เลขผู้เสียภาษี (nullable - ถ้าขอใบกำกับ)
✅ branch                  // สาขา (nullable)
✅ credit_days             // จำนวนวันเครดิต
✅ credit_limit            // วงเงินเครดิต
✅ total_purchased         // ยอดซื้อสะสม
✅ total_outstanding       // ยอดค้างชำระ
✅ total_orders            // จำนวนครั้งที่ซื้อ
✅ last_purchase_date      // วันที่ซื้อล่าสุด
✅ note                    // หมายเหตุ
✅ is_active               // สถานะใช้งาน
```

### Relationships:
```php
✅ pigSells()              -> hasMany(PigSell::class)
```

### Scopes:
```php
✅ scopeActive()           // เฉพาะลูกค้าที่ active
✅ scopeBrokers()          // เฉพาะลูกค้าประเภทนายหน้า
```

### Helper Methods:
```php
✅ hasAvailableCredit()    // ตรวจสอบวงเงินเครดิต
✅ updatePurchaseStats()   // อัพเดทสถิติการซื้อ
✅ addOutstanding()        // เพิ่มยอดค้างชำระ
✅ reduceOutstanding()     // ลดยอดค้างชำระ
✅ generateCustomerCode()  // สร้างรหัสลูกค้า
```

---

## 3️⃣ **Payment Model** ✅ สมบูรณ์

### Columns:
```php
✅ pig_sell_id             // Foreign key -> pig_sells
✅ payment_number          // เลขที่การชำระ (PAY-2025-001)
✅ payment_date            // วันที่ชำระ
✅ amount                  // จำนวนเงิน
✅ payment_method          // เงินสด/โอนเงิน
✅ reference_number        // เลขที่โอน (nullable)
✅ bank_name               // ธนาคาร (nullable)
✅ receipt_file            // สลิปโอน (nullable)
✅ note                    // หมายเหตุ
✅ recorded_by             // Foreign key -> users (ผู้บันทึก)
```

### Columns ที่ลบออกแล้ว:
```php
❌ cheque_date             // วันที่เช็ค (ไม่ใช้เช็ค)
```

### Relationships:
```php
✅ pigSell()               -> belongsTo(PigSell::class)
✅ recordedBy()            -> belongsTo(User::class, 'recorded_by')
```

### Helper Methods:
```php
✅ generatePaymentNumber() // สร้างเลขที่การชำระ (PAY-2025-001)
```

---

## 📊 สรุป

### ✅ ทุก Model ตรงกับ Database แล้ว!

| Model | Status | Columns | Relationships | Methods |
|-------|--------|---------|---------------|---------|
| PigSell | ✅ | 37 | 7 | 5 |
| Customer | ✅ | 16 | 1 | 6 |
| Payment | ✅ | 10 | 2 | 1 |

### 🎯 สิ่งที่ลบออกเพราะไม่เหมาะกับกรณีของคุณ:

1. **ข้อมูลการจัดส่ง** (นายหน้าจัดการขนส่งเอง)
   - ❌ delivery_date, delivery_time
   - ❌ vehicle_number, driver_name, distance_km

2. **การชำระด้วยเช็ค** (ใช้แค่เงินสด/โอนเงิน)
   - ❌ cheque_date

3. **ราคาต่อตัว** (ขายตามน้ำหนัก ไม่ได้ขายต่อตัว)
   - ❌ price_per_pig

### 🚀 พร้อมใช้งานแล้ว!

Migration รันสำเร็จแล้ว (Exit Code: 0)
Models อัพเดทให้ตรงกับ database แล้ว
ความสัมพันธ์ระหว่าง tables ถูกต้องครบถ้วน
