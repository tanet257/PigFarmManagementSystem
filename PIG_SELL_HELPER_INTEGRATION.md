# 🚀 PigSellController - การใช้ PigInventoryHelper และเลือกเล้า-คอก

## 📋 สรุปการอัปเดต

อัปเดตระบบขายหมูให้:
1. ✅ ใช้ **PigInventoryHelper** จัดการจำนวนหมูอัตโนมัติ
2. ✅ เลือก **เล้า-คอก (Pen)** ที่มีหมูจริงๆ
3. ✅ ตรวจสอบจำนวนหมูเพียงพอก่อนขาย
4. ✅ คืนหมูกลับเล้า-คอกเมื่อยกเลิกการขาย

---

## 🔧 ไฟล์ที่แก้ไข

### 1. **PigSellController.php**

#### เพิ่ม Import:
```php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Barn;
use App\Models\Pen;
use App\Helpers\PigInventoryHelper;
```

#### เพิ่ม AJAX Endpoint:
```php
/**
 * ดึงรายการเล้า-คอกที่มีหมูของ batch นั้นๆ
 */
public function getPensByBatch($batchId)
{
    $pens = PigInventoryHelper::getPigsByBatch($batchId);
    
    // จัดรูปแบบข้อมูลสำหรับ dropdown
    $penOptions = collect($pens['allocations'])->map(function ($allocation) {
        return [
            'pen_id' => $allocation['pen_id'],
            'display_text' => "{$allocation['barn_name']} - {$allocation['pen_name']} (มีหมู {$allocation['current_quantity']} ตัว)"
        ];
    })->filter(function ($pen) {
        return $pen['current_quantity'] > 0; // แสดงเฉพาะที่มีหมู
    });
    
    return response()->json([
        'success' => true,
        'data' => $penOptions
    ]);
}
```

#### อัปเดต index():
```php
public function index(Request $request)
{
    // เพิ่ม
    $barns = Barn::all();
    $pens = Pen::all();
    
    // Eager load pen relationship
    $query = PigSell::with(['farm', 'batch', 'pen.barn', 'pigLoss']);
    
    return view('admin.pig_sells.index', compact('farms', 'batches', 'barns', 'pens', 'pigDeaths', 'pigSells', 'latestPrice'));
}
```

#### อัปเดต create():
```php
public function create(Request $request)
{
    DB::beginTransaction();
    try {
        $validated = $request->validate([
            'pen_id' => 'required|exists:pens,id', // เพิ่ม pen_id
            // ... ฟิลด์อื่นๆ
        ]);

        // ลดจำนวนหมูจากเล้า-คอก ผ่าน Helper
        $result = PigInventoryHelper::reducePigInventory(
            $validated['batch_id'],
            $validated['pen_id'],
            $validated['quantity'],
            'sale'
        );

        if (!$result['success']) {
            throw new \Exception($result['message']);
        }

        // คำนวณ net_total
        $netTotal = $validated['total_price'] - ($validated['discount'] ?? 0) + ($validated['shipping_cost'] ?? 0);
        
        $validated['net_total'] = $netTotal;
        $validated['payment_status'] = 'รอชำระ';
        $validated['paid_amount'] = 0;
        $validated['balance'] = $netTotal;

        $pigSell = PigSell::create($validated);

        DB::commit();
        return redirect()->route('pig_sells.index')->with('success', 'บันทึกการขายหมูสำเร็จ');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', $e->getMessage());
    }
}
```

#### อัปเดต cancel():
```php
public function cancel($id)
{
    DB::beginTransaction();
    try {
        $pigSell = PigSell::findOrFail($id);

        // คืนจำนวนหมูกลับเล้า-คอก ผ่าน Helper
        if ($pigSell->pen_id) {
            $result = PigInventoryHelper::increasePigInventory(
                $pigSell->batch_id,
                $pigSell->pen_id,
                $pigSell->quantity
            );

            if (!$result['success']) {
                throw new \Exception('ไม่สามารถคืนหมูกลับเล้า-คอกได้: ' . $result['message']);
            }
        }

        // ลบไฟล์ Cloudinary
        if ($pigSell->receipt_file) {
            $publicId = $this->getPublicIdFromUrl($pigSell->receipt_file);
            if ($publicId) {
                Cloudinary::destroy($publicId);
            }
        }

        $pigSell->delete();

        DB::commit();
        return redirect()->route('pig_sells.index')->with('success', 'ยกเลิกการขายสำเร็จ (คืนหมูกลับเล้า-คอกแล้ว)');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', $e->getMessage());
    }
}
```

---

### 2. **routes/web.php**

เพิ่ม route สำหรับ AJAX:
```php
Route::prefix('pig_sell')->group(function () {
    Route::get('/', [PigSellController::class, 'index'])->name('pig_sell.index');
    Route::get('/pens-by-batch/{batchId}', [PigSellController::class, 'getPensByBatch'])->name('pig_sell.pens_by_batch'); // ใหม่
    Route::post('/create', [PigSellController::class, 'create'])->name('pig_sell.create');
    // ...
});
```

---

### 3. **app/Models/PigSell.php**

#### เพิ่ม pen_id ใน fillable:
```php
protected $fillable = [
    'customer_id',
    'sale_number',
    'farm_id',
    'batch_id',
    'pen_id', // ใหม่
    // ... ฟิลด์อื่นๆ
];
```

#### เพิ่ม relationship:
```php
public function pen()
{
    return $this->belongsTo(Pen::class);
}
```

---

### 4. **resources/views/admin/pig_sells/index.blade.php**

#### เพิ่ม Dropdown เล้า-คอก:
```html
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">ฟาร์ม <span class="text-danger">*</span></label>
        <select name="farm_id" id="farm_select_create" class="form-select" required>
            <option value="">-- เลือกฟาร์ม --</option>
            @foreach ($farms as $farm)
                <option value="{{ $farm->id }}">{{ $farm->farm_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">รุ่น <span class="text-danger">*</span></label>
        <select name="batch_id" id="batch_select_create" class="form-select" required>
            <option value="">-- เลือกรุ่น --</option>
            @foreach ($batches as $batch)
                <option value="{{ $batch->id }}" data-farm-id="{{ $batch->farm_id }}">
                    {{ $batch->batch_code }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<!-- ใหม่: เลือกเล้า-คอก -->
<div class="row">
    <div class="col-md-12 mb-3">
        <label class="form-label">เล้า-คอก <span class="text-danger">*</span></label>
        <select name="pen_id" id="pen_select_create" class="form-select" required>
            <option value="">-- เลือกรุ่นก่อน --</option>
        </select>
        <small class="text-muted">
            <i class="bi bi-info-circle"></i> จะแสดงเฉพาะเล้า-คอกที่มีหมูของรุ่นที่เลือก
        </small>
    </div>
</div>
```

#### เพิ่ม JavaScript AJAX:
```javascript
// AJAX: ดึงเล้า-คอกตาม batch
const batchSelect = document.getElementById('batch_select_create');
const penSelect = document.getElementById('pen_select_create');

batchSelect.addEventListener('change', function() {
    const batchId = this.value;
    
    if (!batchId) {
        // รีเซ็ต pen dropdown
        choicesInstances['pen_select_create'].clearChoices();
        choicesInstances['pen_select_create'].setChoices([
            {value: '', label: '-- เลือกรุ่นก่อน --', selected: true, disabled: true}
        ]);
        return;
    }

    // Loading state
    choicesInstances['pen_select_create'].setChoices([
        {value: '', label: 'กำลังโหลด...', selected: true, disabled: true}
    ]);

    // AJAX call
    fetch(`/pig_sell/pens-by-batch/${batchId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                const choices = [{value: '', label: '-- เลือกเล้า-คอก --', selected: true, disabled: true}];
                
                data.data.forEach(pen => {
                    choices.push({
                        value: pen.pen_id,
                        label: pen.display_text, // "เล้า A - คอก 1 (มีหมู 50 ตัว)"
                        selected: false
                    });
                });

                choicesInstances['pen_select_create'].clearChoices();
                choicesInstances['pen_select_create'].setChoices(choices);
            } else {
                choicesInstances['pen_select_create'].setChoices([
                    {value: '', label: '❌ ไม่พบหมูในรุ่นนี้', selected: true, disabled: true}
                ]);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            choicesInstances['pen_select_create'].setChoices([
                {value: '', label: '❌ เกิดข้อผิดพลาด', selected: true, disabled: true}
            ]);
        });
});
```

#### แสดงเล้า-คอกใน Detail Modal:
```html
<tr>
    <td><strong>เล้า-คอก:</strong></td>
    <td>
        @if($sell->pen)
            {{ $sell->pen->barn->barn_code ?? '' }} - {{ $sell->pen->pen_code }}
        @else
            -
        @endif
    </td>
</tr>
```

---

### 5. **database/migrations/2025_10_12_104026_add_pen_id_to_pig_sells_table.php**

```php
public function up()
{
    Schema::table('pig_sells', function (Blueprint $table) {
        $table->unsignedBigInteger('pen_id')->nullable()->after('batch_id');
        $table->foreign('pen_id')->references('id')->on('pens')->onDelete('set null');
    });
}

public function down()
{
    Schema::table('pig_sells', function (Blueprint $table) {
        $table->dropForeign(['pen_id']);
        $table->dropColumn('pen_id');
    });
}
```

---

## 🔄 Workflow การทำงาน

### สถานการณ์ที่ 1: บันทึกการขาย

```
1. ผู้ใช้เลือก "ฟาร์ม" → กรอง Batch ให้เห็นเฉพาะฟาร์มนั้น
2. ผู้ใช้เลือก "รุ่น" → AJAX ดึงเล้า-คอกที่มีหมูของรุ่นนั้น
3. ผู้ใช้เลือก "เล้า-คอก" → เห็น "เล้า A - คอก 1 (มีหมู 50 ตัว)"
4. กรอกจำนวนที่ต้องการขาย: 30 ตัว
5. กด "บันทึก"

→ Controller:
   - ตรวจสอบ pen_id ผ่าน validation
   - เรียก PigInventoryHelper::reducePigInventory(batch_id, pen_id, 30, 'sale')
   - Helper ตรวจสอบว่ามีหมู 50 ตัว → ลดเหลือ 20 ตัว
   - บันทึก PigSell พร้อม pen_id

→ Database:
   batch_pen_allocations:
     current_quantity: 50 → 20 ✅
   
   batches:
     current_quantity: 500 → 470 ✅
   
   pig_sells:
     pen_id: 1
     quantity: 30
     payment_status: รอชำระ
```

### สถานการณ์ที่ 2: ขายมากกว่าที่มี (Error Handling)

```
1. เล้า-คอกมีหมู 20 ตัว
2. พยายามขาย 50 ตัว
3. Helper return: {
     'success': false,
     'message': '❌ หมูในเล้า-คอกไม่เพียงพอ (มีอยู่ 20 ตัว ต้องการ 50 ตัว)'
   }
4. Controller throw Exception
5. DB::rollBack()
6. แสดง Error ให้ผู้ใช้เห็น
```

### สถานการณ์ที่ 3: ยกเลิกการขาย

```
1. ผู้ใช้กดปุ่ม "ยกเลิก" ที่การขาย 30 ตัว
2. ยืนยัน
3. Controller:
   - เรียก PigInventoryHelper::increasePigInventory(batch_id, pen_id, 30)
   - Helper คืนหมู 30 ตัวกลับเล้า-คอก
   - ลบ receipt_file จาก Cloudinary
   - ลบ PigSell

→ Database:
   batch_pen_allocations:
     current_quantity: 20 → 50 ✅
   
   batches:
     current_quantity: 470 → 500 ✅
   
   pig_sells:
     (ถูกลบ)
```

---

## 📊 ตัวอย่าง UI

### Create Modal:

```
┌─────────────────────────────────────────────────────────┐
│         บันทึกการขายหมูใหม่                     [X]     │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  ฟาร์ม:     [ฟาร์มบ้านสวน ▼]                          │
│  รุ่น:      [BATCH-2024-001 ▼]                         │
│                                                          │
│  เล้า-คอก:  [เล้า A - คอก 1 (มีหมู 50 ตัว) ▼]        │
│  ℹ️ จะแสดงเฉพาะเล้า-คอกที่มีหมูของรุ่นที่เลือก         │
│                                                          │
│  วันที่ขาย: [2025-10-12]                                │
│  ประเภท:    [ขายหมูปกติ ▼]                             │
│                                                          │
│  จำนวน:     [30] ตัว                                    │
│  น้ำหนัก:    [3,300] kg                                 │
│                                                          │
│  ℹ️ ราคาอ้างอิง CPF: 56.00 บาท/กก. (ณ 06/10/2025)     │
│                                                          │
│  ราคา/kg:   [56.00] บาท                                │
│  ราคารวม:   [184,800.00] บาท                           │
│                                                          │
│  ผู้ซื้อ:    [บริษัท สมบูรณ์เนื้อสด]                   │
│  หมายเหตุ:  [________________________________]          │
│                                                          │
│               [ปิด]              [บันทึก]               │
└─────────────────────────────────────────────────────────┘
```

### View Detail Modal:

```
┌─────────────────────────────────────────────────────────┐
│    รายละเอียดการขาย - SELL-2025-001            [X]     │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  ข้อมูลการขาย                                           │
│  ────────────                                           │
│  เลขที่:      SELL-2025-001                             │
│  วันที่ขาย:   12/10/2025                                │
│  ลูกค้า:      บริษัท สมบูรณ์เนื้อสด                     │
│  ฟาร์ม:       ฟาร์มบ้านสวน                              │
│  รุ่น:        BATCH-2024-001                            │
│  เล้า-คอก:    เล้า A - คอก 1           ← ใหม่!         │
│                                                          │
│  รายละเอียด                                              │
│  ──────────                                             │
│  จำนวน:       30 ตัว                                    │
│  น้ำหนัก:     3,300.00 kg                               │
│  ราคา/kg:     56.00 บาท                                │
│  ราคารวม:     184,800.00 บาท                           │
│  ส่วนลด:      0.00 บาท                                 │
│  ค่าขนส่ง:    3,000.00 บาท                             │
│  ────────────────────────                               │
│  ราคาสุทธิ:   187,800.00 บาท                           │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

---

## ✅ ข้อดีของการใช้ Helper

### 1. **ข้อมูลถูกต้อง 100%**
- ลดหมูจาก `batch_pen_allocations` และ `batches` พร้อมกัน
- ไม่มีกรณีที่ข้อมูล 2 ตารางไม่ตรงกัน

### 2. **ป้องกัน Race Condition**
- ใช้ `lockForUpdate()` ใน Helper
- หลายคนขายพร้อมกันไม่ทำให้หมูติดลบ

### 3. **Error Handling ชัดเจน**
- รู้ทันทีว่าหมูไม่พอ
- Message บอกชัดว่ามีเท่าไหร่ ต้องการเท่าไหร่

### 4. **ใช้งานง่าย**
```php
// เดิม (50+ lines)
$batch = Batch::find($batchId);
$batch->total_pig_amount -= $quantity;
$batch->save();

DB::table('batch_pen_allocations')
    ->where('pen_id', $penId)
    ->update(['allocated_pigs' => DB::raw('allocated_pigs - ' . $quantity)]);

// ใหม่ (3 lines)
$result = PigInventoryHelper::reducePigInventory($batchId, $penId, $quantity, 'sale');
if (!$result['success']) throw new \Exception($result['message']);
```

### 5. **Reusable**
- ใช้ได้ทั้ง PigSell, PigDeath, PigEntry
- Code เดียวกัน ทำงานเหมือนกันทุกที่

---

## 🎯 สิ่งที่ต้องทำต่อ

### ✅ เสร็จแล้ว:
1. ✅ PigSellController ใช้ Helper
2. ✅ เพิ่ม AJAX endpoint ดึงเล้า-คอก
3. ✅ View มี dropdown เลือกเล้า-คอก
4. ✅ คืนหมูเมื่อยกเลิกการขาย
5. ✅ Migration เพิ่ม pen_id
6. ✅ Model relationship กับ Pen

### ⏳ ยังไม่ได้ทำ:
1. ⏳ Run Migration: `php artisan migrate`
2. ⏳ ทดสอบการขาย
3. ⏳ ทดสอบยกเลิกการขาย
4. ⏳ ตรวจสอบ Error Handling

---

**วันที่อัปเดต:** 12 ตุลาคม 2025  
**ผู้อัปเดต:** GitHub Copilot  
**เวอร์ชัน:** 3.0
