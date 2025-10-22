# Soft Delete Show/Hide Toggle - All Records

## 📋 Overview

เพิ่ม checkbox toggle สำหรับแสดง/ซ่อนรายการที่ยกเลิกสำหรับ:
- ✅ Batches (status = 'cancelled')
- ✅ Pig Entry Records (batch status = 'cancelled')
- ✅ Pig Sales (status = 'ยกเลิกการขาย')

## 🔧 Implementation Details

### 1. Batch Toggle ✅

**File**: `app/Http/Controllers/BatchController.php` (Line 72-75)

```php
if (!$request->has('show_cancelled') || !$request->show_cancelled) {
    $query->where('status', '!=', 'cancelled');
}
```

**View**: `resources/views/admin/batches/index.blade.php`

```blade
<div class="form-check ms-2">
    <input class="form-check-input" type="checkbox" id="showCancelledCheckbox" 
        {{ request('show_cancelled') ? 'checked' : '' }}
        onchange="toggleCancelled()">
    <label class="form-check-label">
        <i class="bi bi-eye"></i> แสดงรุ่นที่ยกเลิก
    </label>
</div>
```

---

### 2. Pig Entry Records Toggle ✅

**File**: `app/Http/Controllers/PigEntryController.php` (Line 273-280)

```php
// ✅ Exclude cancelled entries - unless show_cancelled is true
if (!$request->has('show_cancelled') || !$request->show_cancelled) {
    $query->whereHas('batch', function ($q) {
        $q->where('status', '!=', 'cancelled');
    });
}
```

**Logic**: Filters by batch status (pig entry ไม่มี status โดยตัวเองแต่เกี่ยวข้องกับ batch)

**View**: `resources/views/admin/pig_entry_records/index.blade.php`

```blade
<div class="form-check ms-2">
    <input class="form-check-input" type="checkbox" id="showCancelledCheckboxEntry" 
        {{ request('show_cancelled') ? 'checked' : '' }}
        onchange="toggleCancelledEntry()">
    <label class="form-check-label">
        <i class="bi bi-eye"></i> แสดงรุ่นที่ยกเลิก
    </label>
</div>
```

---

### 3. Pig Sales Toggle ✅

**File**: `app/Http/Controllers/PigSaleController.php` (Line 304-307)

```php
// ✅ Exclude cancelled sales - unless show_cancelled is true
if (!$request->has('show_cancelled') || !$request->show_cancelled) {
    $query->where('status', '!=', 'ยกเลิกการขาย');
}
```

**View**: `resources/views/admin/pig_sales/index.blade.php`

```blade
<div class="form-check ms-2">
    <input class="form-check-input" type="checkbox" id="showCancelledCheckboxSale" 
        {{ request('show_cancelled') ? 'checked' : '' }}
        onchange="toggleCancelledSale()">
    <label class="form-check-label">
        <i class="bi bi-eye"></i> แสดงการขายที่ยกเลิก
    </label>
</div>
```

---

## 📊 JavaScript Functions

### Batch Toggle
```javascript
function toggleCancelled() {
    const checkbox = document.getElementById('showCancelledCheckbox');
    const form = document.getElementById('filterForm');
    
    if (checkbox.checked) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'show_cancelled';
        input.value = '1';
        form.appendChild(input);
    } else {
        const input = form.querySelector('input[name="show_cancelled"]');
        if (input) input.remove();
    }
    form.submit();
}
```

### Pig Entry Toggle
```javascript
function toggleCancelledEntry() {
    const checkbox = document.getElementById('showCancelledCheckboxEntry');
    const form = document.getElementById('filterForm');
    
    if (checkbox.checked) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'show_cancelled';
        input.value = '1';
        form.appendChild(input);
    } else {
        const input = form.querySelector('input[name="show_cancelled"]');
        if (input) input.remove();
    }
    form.submit();
}
```

### Pig Sales Toggle
```javascript
function toggleCancelledSale() {
    const checkbox = document.getElementById('showCancelledCheckboxSale');
    const form = document.getElementById('filterForm');
    
    if (checkbox.checked) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'show_cancelled';
        input.value = '1';
        form.appendChild(input);
    } else {
        const input = form.querySelector('input[name="show_cancelled"]');
        if (input) input.remove();
    }
    form.submit();
}
```

---

## 📋 Status Values Used

| Module | Cancelled Status | Column |
|--------|-----------------|--------|
| Batch | `'cancelled'` | `status` |
| Pig Entry | `'cancelled'` (batch) | Related to batch status |
| Pig Sale | `'ยกเลิกการขาย'` | `status` |

---

## 🎯 Workflow

### Default Behavior
```
Page Load
    ↓
☐ Checkbox unchecked (default)
    ↓
Query excludes cancelled items
    ↓
List shows only active records
```

### Show Cancelled
```
User click: ☑ Checkbox
    ↓
toggleCancelled/Entry/Sale() called
    ↓
Add hidden input: show_cancelled=1
    ↓
Form submit: ?show_cancelled=1
    ↓
Query includes cancelled items
    ↓
List shows all records
```

---

## 🧪 Test Checklist

- [ ] ✅ Batch checkbox works (toggle cancelled batches)
- [ ] ✅ Pig Entry checkbox works (toggle cancelled batches in entry list)
- [ ] ✅ Pig Sale checkbox works (toggle cancelled sales)
- [ ] ✅ URL parameter persists: `?show_cancelled=1`
- [ ] ✅ Checkbox state maintains across filter changes
- [ ] ✅ Visual indicator shows for cancelled items
- [ ] ✅ Delete/Cancel buttons hidden for cancelled items

---

## ✅ Implementation Status

| Component | Status | File |
|-----------|--------|------|
| BatchController | ✅ Updated | `app/Http/Controllers/BatchController.php` |
| Batch View | ✅ Updated | `resources/views/admin/batches/index.blade.php` |
| PigEntryController | ✅ Updated | `app/Http/Controllers/PigEntryController.php` |
| PigEntry View | ✅ Updated | `resources/views/admin/pig_entry_records/index.blade.php` |
| PigSaleController | ✅ Updated | `app/Http/Controllers/PigSaleController.php` |
| PigSale View | ✅ Updated | `resources/views/admin/pig_sales/index.blade.php` |

---

## 🎨 UI/UX Improvements

### Delete Button → Cancelled Badge

When an item is cancelled, the delete/cancel button is replaced with a visual badge:

```blade
@if ($record->batch && $record->batch->status === 'cancelled')
    <span class="badge bg-danger">
        <i class="bi bi-x-circle"></i> ยกเลิก
    </span>
@else
    {{-- Show delete button --}}
@endif
```

**Applied to**:
- ✅ Pig Entry Records: Shows badge when batch status = 'cancelled'
- ✅ Pig Sales: Shows badge when payment_status = 'ยกเลิกการขาย'
- ✅ Batches: Already had cancel button replacement logic

**Benefits**:
- Visual indicator that item is cancelled (red badge)
- Prevents accidental clicks on delete button
- Clear status communication to users

---

## ✨ Summary

✅ **All records now have show/hide toggle for cancelled items**

- Batches: Can toggle to show/hide `status = 'cancelled'`
- Pig Entry: Can toggle to show/hide entries from cancelled batches
- Pig Sales: Can toggle to show/hide `status = 'ยกเลิกการขาย'`

✅ **Delete buttons replaced with badges when cancelled**

- Pig Entry Records: Badge shows when batch is cancelled
- Pig Sales: Badge shows when sale is cancelled
- Batches: Logic already in place

Default behavior: **Cancelled items hidden** (safest for production)  
Optional: **Users can reveal cancelled items** if needed for auditing/review

---

**Status**: ✅ **IMPLEMENTATION COMPLETE**

All controllers validated, cache cleared, ready for testing! 🚀
