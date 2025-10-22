# Batch Soft Delete - Show/Hide Cancelled Feature

## 📋 Overview

ระบบ batch soft delete ตอนนี้รองรับการแสดง/ซ่อน cancelled batches ผ่าน checkbox ในหน้า index

## ✨ Features

### Default Behavior
- ❌ Cancelled batches ถูก **exclude** จากรายการเริ่มต้น
- ✅ สามารถใช้ checkbox เพื่อแสดง cancelled batches ได้

### Checkbox: "แสดงรุ่นที่ยกเลิก"
```blade
<!-- Show Cancelled Batches Checkbox -->
<div class="form-check ms-2">
    <input class="form-check-input" type="checkbox" id="showCancelledCheckbox" 
        {{ request('show_cancelled') ? 'checked' : '' }}
        onchange="toggleCancelled()">
    <label class="form-check-label" for="showCancelledCheckbox">
        <i class="bi bi-eye"></i> แสดงรุ่นที่ยกเลิก
    </label>
</div>
```

## 🔧 Implementation Details

### 1. BatchController::indexBatch()

**File**: `app/Http/Controllers/BatchController.php` (Line 72-75)

```php
// ✅ Exclude cancelled batches (soft delete) - unless show_cancelled is true
if (!$request->has('show_cancelled') || !$request->show_cancelled) {
    $query->where('status', '!=', 'cancelled');
}
```

**Logic**:
- Default (no parameter): Exclude cancelled batches ❌
- `?show_cancelled=1`: Include cancelled batches ✅

### 2. View: Checkbox & JavaScript

**File**: `resources/views/admin/batches/index.blade.php`

#### Checkbox HTML
- Position: In filter toolbar (next to "Per Page" dropdown)
- Checked state: Based on `request('show_cancelled')`
- Function: Calls `toggleCancelled()` on change

#### JavaScript Function
```javascript
function toggleCancelled() {
    const checkbox = document.getElementById('showCancelledCheckbox');
    const form = document.getElementById('filterForm');
    
    if (checkbox.checked) {
        // Add show_cancelled parameter
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'show_cancelled';
        input.value = '1';
        form.appendChild(input);
    } else {
        // Remove show_cancelled parameter
        const input = form.querySelector('input[name="show_cancelled"]');
        if (input) {
            input.remove();
        }
    }
    form.submit();
}
```

## 📊 Workflow

### Show Cancelled Batches

```
User click checkbox "แสดงรุ่นที่ยกเลิก"
    ↓
toggleCancelled() is called
    ↓
Add hidden input: <input name="show_cancelled" value="1">
    ↓
Form submit: GET /batches?show_cancelled=1
    ↓
BatchController::indexBatch() - condition skipped
    ↓
Query returns ALL batches (including cancelled)
    ↓
Display list with cancelled batches visible
    ↓
Cancelled batches show with "ยกเลิกแล้ว" badge (red)
```

### Hide Cancelled Batches

```
User uncheck checkbox "แสดงรุ่นที่ยกเลิก"
    ↓
toggleCancelled() is called
    ↓
Remove hidden input
    ↓
Form submit: GET /batches (no show_cancelled param)
    ↓
BatchController::indexBatch() - condition applies
    ↓
Query excludes cancelled: where('status', '!=', 'cancelled')
    ↓
Display list without cancelled batches
```

## 🎯 Key Points

1. **Default Safe Behavior**: Cancelled batches hidden by default ✅
2. **Optional Visibility**: User can choose to view cancelled batches
3. **Clean UI**: Checkbox positioned in filter bar
4. **State Persistence**: Checkbox state maintained in URL parameter
5. **Visual Indication**: Cancelled batches marked with red "ยกเลิกแล้ว" badge

## 🧪 Test Checklist

- [ ] ✅ Cancelled batches hidden by default
- [ ] ✅ Checkbox appears in filter toolbar
- [ ] ✅ Check checkbox → shows cancelled batches
- [ ] ✅ Uncheck checkbox → hides cancelled batches
- [ ] ✅ Cancelled batches show red "ยกเลิกแล้ว" badge
- [ ] ✅ Other filters work with checkbox (farm, date, sort)
- [ ] ✅ URL parameter persists: `?show_cancelled=1`

## 📋 Related Files Modified

| File | Changes |
|------|---------|
| `app/Http/Controllers/BatchController.php` | Added condition for `show_cancelled` parameter |
| `resources/views/admin/batches/index.blade.php` | Added checkbox + JavaScript function |

## ✅ Validation

```
✅ BatchController.php - No syntax errors
✅ View file - Checkbox and JavaScript added
✅ Cache cleared - Changes applied
✅ Ready for testing
```

---

**Status**: ✅ **IMPLEMENTATION COMPLETE**

Now users can choose whether to show or hide cancelled batches! 🎉
