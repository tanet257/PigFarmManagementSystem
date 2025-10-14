# 📦 PigInventoryHelper - คู่มือการใช้งาน

## 🎯 จุดประสงค์

Helper นี้ทำหน้าที่จัดการจำนวนหมูในระบบให้ถูกต้องและสอดคล้องกันทั้ง 2 ตาราง:
1. **`batch_pen_allocations`** - จำนวนหมูในแต่ละเล้า-คอก
2. **`batches`** - จำนวนหมูรวมของรุ่น

---

## 📊 โครงสร้างข้อมูล

### ตาราง `batches`
```
id | batch_code | total_pig_amount | current_quantity
1  | BATCH-001  | 500             | 450
```
- **`total_pig_amount`** = จำนวนหมูทั้งหมดที่ซื้อเข้ามา (ไม่เปลี่ยน)
- **`current_quantity`** = จำนวนหมูที่เหลืออยู่ปัจจุบัน (เปลี่ยนตามการขาย/ตาย)

### ตาราง `batch_pen_allocations`
```
id | batch_id | barn_id | pen_id | pig_amount | current_quantity
1  | 1        | 1       | 1      | 100        | 95
2  | 1        | 1       | 2      | 150        | 145
3  | 1        | 2       | 3      | 250        | 210
```
- **`pig_amount`** = จำนวนหมูที่เริ่มต้นใส่เข้าเล้า-คอก (ไม่เปลี่ยน)
- **`current_quantity`** = จำนวนหมูที่เหลืออยู่ในเล้า-คอก (เปลี่ยนตามการขาย/ตาย)

---

## 🛠️ ฟังก์ชันที่มีให้ใช้

### 1. `addPigs()` - เพิ่มหมูเข้าระบบ

**ใช้เมื่อ:** ซื้อหมูเข้ามาใหม่

**พารามิเตอร์:**
- `$batchId` (int) - รหัสรุ่น
- `$barnId` (int) - รหัสเล้า
- `$penId` (int) - รหัสคอก
- `$quantity` (int) - จำนวนหมูที่ต้องการเพิ่ม

**ตัวอย่างการใช้งาน:**

```php
use App\Helpers\PigInventoryHelper;

// เพิ่มหมู 50 ตัวเข้าเล้า 1 คอก 1 ของรุ่น 1
$result = PigInventoryHelper::addPigs(
    batchId: 1,
    barnId: 1,
    penId: 1,
    quantity: 50
);

if ($result['success']) {
    echo $result['message']; // ✅ เพิ่มหมูในเล้า-คอกเดิม (50 ตัว)
    // หรือ ✅ สร้างข้อมูลเล้า-คอกใหม่ (50 ตัว)
} else {
    echo $result['message']; // ❌ เกิดข้อผิดพลาด: ...
}
```

**ผลลัพธ์:**
```php
[
    'success' => true,
    'message' => '✅ สร้างข้อมูลเล้า-คอกใหม่ (50 ตัว)',
    'data' => [
        'quantity_added' => 50,
        'allocation_id' => 4,
        'batch' => [
            'total_pig_amount' => 550,
            'current_quantity' => 500
        ]
    ]
]
```

---

### 2. `reducePigInventory()` - ลดหมูจากระบบ

**ใช้เมื่อ:** ขายหมู, หมูตาย, คัดหมูทิ้ง

**พารามิเตอร์:**
- `$batchId` (int) - รหัสรุ่น
- `$penId` (int) - รหัสเล้า-คอก
- `$quantity` (int) - จำนวนหมูที่ต้องการลด
- `$reason` (string) - เหตุผล: `'sale'`, `'death'`, `'culling'`

**ตัวอย่างการใช้งาน:**

```php
use App\Helpers\PigInventoryHelper;

// ขายหมู 30 ตัวจากเล้า-คอก 1 ของรุ่น 1
$result = PigInventoryHelper::reducePigInventory(
    batchId: 1,
    penId: 1,
    quantity: 30,
    reason: 'sale'
);

if ($result['success']) {
    echo $result['message']; // ✅ ลดจำนวนหมูเรียบร้อย (30 ตัว)
    
    $data = $result['data'];
    echo "เล้า-คอกเหลือ: " . $data['pen_allocation']['remaining'] . " ตัว";
    echo "รุ่นเหลือ: " . $data['batch']['remaining'] . " ตัว";
} else {
    echo $result['message']; // ❌ หมูในเล้า-คอกไม่เพียงพอ
}
```

**ผลลัพธ์สำเร็จ:**
```php
[
    'success' => true,
    'message' => '✅ ลดจำนวนหมูเรียบร้อย (30 ตัว)',
    'data' => [
        'reason' => 'sale',
        'quantity_reduced' => 30,
        'pen_allocation' => [
            'before' => 100,
            'after' => 70,
            'remaining' => 70
        ],
        'batch' => [
            'before' => 500,
            'after' => 470,
            'remaining' => 470
        ]
    ]
]
```

**ผลลัพธ์ล้มเหลว (หมูไม่พอ):**
```php
[
    'success' => false,
    'message' => '❌ หมูในเล้า-คอกไม่เพียงพอ (มีอยู่ 70 ตัว ต้องการ 100 ตัว)',
    'data' => [
        'available' => 70,
        'requested' => 100,
        'shortage' => 30
    ]
]
```

---

### 3. `increasePigInventory()` - เพิ่มหมูกลับเข้าระบบ

**ใช้เมื่อ:** ยกเลิกการขาย

**พารามิเตอร์:**
- `$batchId` (int) - รหัสรุ่น
- `$penId` (int) - รหัสเล้า-คอก
- `$quantity` (int) - จำนวนหมูที่ต้องการเพิ่มกลับ

**ตัวอย่างการใช้งาน:**

```php
use App\Helpers\PigInventoryHelper;

// ยกเลิกการขาย - คืนหมู 30 ตัวกลับเล้า-คอก 1
$result = PigInventoryHelper::increasePigInventory(
    batchId: 1,
    penId: 1,
    quantity: 30
);

if ($result['success']) {
    echo $result['message']; // ✅ เพิ่มจำนวนหมูกลับเรียบร้อย (30 ตัว)
} else {
    echo $result['message']; // ⚠️ จำนวนหมูจะเกินจำนวนเริ่มต้น
}
```

---

### 4. `getPigsByBatch()` - ดึงข้อมูลหมูทั้งหมดของรุ่น

**ใช้เมื่อ:** ต้องการดูข้อมูลหมูแยกตามเล้า-คอก

**พารามิเตอร์:**
- `$batchId` (int) - รหัสรุ่น

**ตัวอย่างการใช้งาน:**

```php
use App\Helpers\PigInventoryHelper;

$pigs = PigInventoryHelper::getPigsByBatch(1);

echo "รุ่นนี้มีหมูทั้งหมด: " . $pigs['summary']['total_pigs'] . " ตัว\n";
echo "เหลืออยู่: " . $pigs['summary']['current_pigs'] . " ตัว\n";

foreach ($pigs['allocations'] as $allocation) {
    echo "เล้า: {$allocation['barn_name']}, คอก: {$allocation['pen_name']}\n";
    echo "มีหมู: {$allocation['current_quantity']} / {$allocation['pig_amount']} ตัว\n";
}
```

**ผลลัพธ์:**
```php
[
    'batch_id' => 1,
    'batch_code' => 'BATCH-001',
    'allocations' => [
        [
            'allocation_id' => 1,
            'barn_id' => 1,
            'barn_name' => 'โรงเรือน A',
            'pen_id' => 1,
            'pen_name' => 'คอก A1',
            'pig_amount' => 100,
            'current_quantity' => 70,
            'reduced' => 30
        ],
        // ...
    ],
    'summary' => [
        'total_pigs' => 500,
        'current_pigs' => 450,
        'total_reduced' => 50
    ]
]
```

---

### 5. `getPigsByPen()` - ดึงข้อมูลหมูในเล้า-คอกเดียว

**ใช้เมื่อ:** ต้องการดูจำนวนหมูในเล้า-คอกใดเล้า-คอกหนึ่ง

**พารามิเตอร์:**
- `$batchId` (int) - รหัสรุ่น
- `$penId` (int) - รหัสเล้า-คอก

**ตัวอย่างการใช้งาน:**

```php
use App\Helpers\PigInventoryHelper;

$result = PigInventoryHelper::getPigsByPen(1, 1);

if ($result['success']) {
    $data = $result['data'];
    echo "เล้า-คอกนี้มีหมู: {$data['current_quantity']} ตัว\n";
    echo "ความจุ: {$data['pen_capacity']} ตัว\n";
    echo "ว่าง: {$data['available_space']} ตัว\n";
}
```

**ผลลัพธ์:**
```php
[
    'success' => true,
    'data' => [
        'allocation_id' => 1,
        'batch_id' => 1,
        'barn_id' => 1,
        'pen_id' => 1,
        'pig_amount' => 100,
        'current_quantity' => 70,
        'pen_capacity' => 100,
        'available_space' => 30,
        'utilization_rate' => 70.0
    ]
]
```

---

### 6. `getAvailablePensByBatch()` - ดูเล้า-คอกที่มีที่ว่าง

**ใช้เมื่อ:** ต้องการหาเล้า-คอกที่สามารถเพิ่มหมูได้

**พารามิเตอร์:**
- `$batchId` (int) - รหัสรุ่น
- `$requiredSpace` (int, optional) - จำนวนที่ต้องการ

**ตัวอย่างการใช้งาน:**

```php
use App\Helpers\PigInventoryHelper;

// หาเล้า-คอกที่มีที่ว่างอย่างน้อย 50 ตัว
$pens = PigInventoryHelper::getAvailablePensByBatch(1, 50);

foreach ($pens as $pen) {
    echo "เล้า: {$pen['barn_name']}, คอก: {$pen['pen_name']}\n";
    echo "ว่าง: {$pen['available_space']} ตัว\n";
}
```

---

## 🔄 Workflow การใช้งานจริง

### สถานการณ์ที่ 1: ซื้อหมูเข้ามาใหม่

```php
// ใน PigEntryController@upload_pig_entry_record

use App\Helpers\PigInventoryHelper;

// วนเพิ่มหมูเข้าแต่ละเล้า-คอก
foreach ($pens as $pen) {
    $result = PigInventoryHelper::addPigs(
        batchId: $batch->id,
        barnId: $barn->id,
        penId: $pen->id,
        quantity: $allocateToPen
    );
    
    if (!$result['success']) {
        throw new Exception($result['message']);
    }
}
```

**สิ่งที่เกิดขึ้นอัตโนมัติ:**
1. ✅ สร้าง/อัปเดตข้อมูลใน `batch_pen_allocations`
2. ✅ อัปเดต `batches.total_pig_amount` และ `current_quantity`
3. ✅ ใช้ Transaction ป้องกันข้อมูลผิดพลาด

---

### สถานการณ์ที่ 2: ขายหมู

```php
// ใน PigSellController@create

use App\Helpers\PigInventoryHelper;

// ลดหมูจากเล้า-คอกที่เลือก
$result = PigInventoryHelper::reducePigInventory(
    batchId: $batch->id,
    penId: $penId,
    quantity: $quantity,
    reason: 'sale'
);

if (!$result['success']) {
    return redirect()->back()->with('error', $result['message']);
}

// บันทึกการขาย
PigSell::create([
    'batch_id' => $batch->id,
    'pen_id' => $penId,
    'quantity' => $quantity,
    // ... ฟิลด์อื่นๆ
]);
```

**สิ่งที่เกิดขึ้นอัตโนมัติ:**
1. ✅ ตรวจสอบว่าหมูเพียงพอหรือไม่
2. ✅ ลด `batch_pen_allocations.current_quantity`
3. ✅ ลด `batches.current_quantity`
4. ✅ Return ข้อมูลจำนวนก่อน-หลัง

---

### สถานการณ์ที่ 3: ยกเลิกการขาย

```php
// ใน PigSellController@cancel

use App\Helpers\PigInventoryHelper;

// เพิ่มหมูกลับเข้าเล้า-คอก
$result = PigInventoryHelper::increasePigInventory(
    batchId: $sell->batch_id,
    penId: $sell->pen_id,
    quantity: $sell->quantity
);

if (!$result['success']) {
    return redirect()->back()->with('error', $result['message']);
}

// อัปเดตสถานะ
$sell->update(['sale_status' => 'ยกเลิก']);
```

**สิ่งที่เกิดขึ้นอัตโนมัติ:**
1. ✅ เพิ่ม `batch_pen_allocations.current_quantity`
2. ✅ เพิ่ม `batches.current_quantity`
3. ✅ ตรวจสอบไม่ให้เกินจำนวนเริ่มต้น

---

### สถานการณ์ที่ 4: บันทึกหมูตาย

```php
// ใน PigDeathController@create

use App\Helpers\PigInventoryHelper;

// ลดหมูจากระบบ
$result = PigInventoryHelper::reducePigInventory(
    batchId: $batch->id,
    penId: $penId,
    quantity: $quantity,
    reason: 'death'
);

if (!$result['success']) {
    return redirect()->back()->with('error', $result['message']);
}

// บันทึกการตาย
PigDeath::create([
    'batch_id' => $batch->id,
    'pen_id' => $penId,
    'quantity' => $quantity,
    'death_date' => $deathDate,
    // ... ฟิลด์อื่นๆ
]);
```

---

## ⚠️ ข้อควรระวัง

### 1. **อย่าอัปเดตจำนวนหมูด้วยตัวเอง**

❌ **ผิด:**
```php
// อย่าทำแบบนี้!
$batch = Batch::find(1);
$batch->current_quantity -= 30;
$batch->save();

DB::table('batch_pen_allocations')
    ->where('id', 1)
    ->update(['current_quantity' => DB::raw('current_quantity - 30')]);
```

✅ **ถูก:**
```php
// ใช้ Helper แทน
PigInventoryHelper::reducePigInventory(
    batchId: 1,
    penId: 1,
    quantity: 30,
    reason: 'sale'
);
```

### 2. **เช็ค success ก่อนดำเนินการต่อ**

```php
$result = PigInventoryHelper::reducePigInventory(...);

if (!$result['success']) {
    // จัดการ error
    return redirect()->back()->with('error', $result['message']);
}

// ดำเนินการต่อ...
```

### 3. **ใช้ Transaction ถ้ามีหลายขั้นตอน**

```php
DB::beginTransaction();

try {
    // ลดหมู
    $result = PigInventoryHelper::reducePigInventory(...);
    if (!$result['success']) {
        throw new Exception($result['message']);
    }
    
    // บันทึกการขาย
    PigSell::create([...]);
    
    // บันทึกการชำระเงิน
    Payment::create([...]);
    
    DB::commit();
} catch (Exception $e) {
    DB::rollBack();
    return redirect()->back()->with('error', $e->getMessage());
}
```

---

## 🧪 ตัวอย่าง Response

### Success Response
```php
[
    'success' => true,
    'message' => '✅ ลดจำนวนหมูเรียบร้อย (30 ตัว)',
    'data' => [
        'reason' => 'sale',
        'quantity_reduced' => 30,
        'pen_allocation' => [
            'before' => 100,
            'after' => 70,
            'remaining' => 70
        ],
        'batch' => [
            'before' => 500,
            'after' => 470,
            'remaining' => 470
        ]
    ]
]
```

### Error Response
```php
[
    'success' => false,
    'message' => '❌ หมูในเล้า-คอกไม่เพียงพอ (มีอยู่ 70 ตัว ต้องการ 100 ตัว)',
    'data' => [
        'available' => 70,
        'requested' => 100,
        'shortage' => 30
    ]
]
```

---

## 📝 สรุป

### ✅ ข้อดีของการใช้ Helper:

1. **ข้อมูลสอดคล้องกัน** - อัปเดตทั้ง 2 ตารางพร้อมกัน
2. **ป้องกัน Race Condition** - ใช้ `lockForUpdate()`
3. **ใช้ Transaction** - Rollback อัตโนมัติถ้าผิดพลาด
4. **ตรวจสอบความถูกต้อง** - เช็คจำนวนหมูเพียงพอ
5. **Return ข้อมูลครบถ้วน** - รู้ก่อน-หลังการเปลี่ยนแปลง
6. **Error Message ชัดเจน** - บอกสาเหตุอย่างละเอียด

### 🎯 เมื่อไหร่ควรใช้:

- ✅ ซื้อหมูเข้า (`addPigs`)
- ✅ ขายหมู (`reducePigInventory`)
- ✅ หมูตาย (`reducePigInventory`)
- ✅ คัดหมูทิ้ง (`reducePigInventory`)
- ✅ ยกเลิกการขาย (`increasePigInventory`)
- ✅ ตรวจสอบจำนวนหมู (`getPigsByBatch`, `getPigsByPen`)

---

**วันที่สร้างเอกสาร:** 12 ตุลาคม 2025  
**ผู้สร้าง:** GitHub Copilot  
**เวอร์ชัน:** 1.0
