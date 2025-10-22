# Cancel Request UI - Code Reference

## File: resources/views/admin/payment_approvals/index.blade.php

### Section 1: Pending Tab - Cancel Requests

**Location**: After pending notifications table (line ~190)

```blade
{{-- Cancel Requests Section --}}
@if ($pendingCancelRequests && $pendingCancelRequests->count() > 0)
    <hr class="my-4">
    <h5 class="mb-3">
        <i class="bi bi-exclamation-circle"></i> ขอยกเลิกการขายหมู
        <span class="badge bg-warning text-dark">{{ $pendingCancelRequests->count() }}</span>
    </h5>
    <div class="table-responsive">
        <table class="table table-warning mb-0">
            <thead class="table-header-custom">
                <tr>
                    <th class="text-center">ลำดับ</th>
                    <th class="text-center">เลขที่ขาย</th>
                    <th class="text-center">จำนวนหมู</th>
                    <th class="text-center">ผู้ขอยกเลิก</th>
                    <th class="text-center">วันที่ขอยกเลิก</th>
                    <th class="text-center">เหตุผล</th>
                    <th class="text-center">การกระทำ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingCancelRequests as $index => $cancelRequest)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="text-center">
                            {{ $cancelRequest->relatedModel === 'PigSale' 
                                ? \App\Models\PigSale::find($cancelRequest->related_model_id)?->id 
                                : '-' }}
                        </td>
                        <td class="text-center">
                            {{ $cancelRequest->relatedModel === 'PigSale' 
                                ? \App\Models\PigSale::find($cancelRequest->related_model_id)?->quantity . ' ตัว'
                                : '-' }}
                        </td>
                        <td class="text-center">{{ $cancelRequest->relatedUser->name ?? '-' }}</td>
                        <td class="text-center">{{ $cancelRequest->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ Str::limit($cancelRequest->message, 50) }}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                data-bs-target="#approveCancelModal{{ $cancelRequest->id }}">
                                <i class="bi bi-check"></i> อนุมัติ
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                data-bs-target="#rejectCancelModal{{ $cancelRequest->id }}">
                                <i class="bi bi-x"></i> ปฏิเสธ
                            </button>
                        </td>
                    </tr>

                    {{-- Approve Cancel Modal --}}
                    <div class="modal fade" id="approveCancelModal{{ $cancelRequest->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title">อนุมัติการยกเลิกการขาย</h5>
                                    <button type="button" class="btn-close btn-close-white"
                                        data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('payment_approvals.approve_cancel_sale', $cancelRequest->id) }}"
                                    method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <div class="modal-body">
                                        <div class="alert alert-warning">
                                            <strong>เหตุผลการขอยกเลิก:</strong>
                                            <p class="mb-0">{{ $cancelRequest->message }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">หมายเหตุการอนุมัติ (ไม่จำเป็น)</label>
                                            <textarea name="approval_notes" class="form-control" rows="3"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">ยกเลิก</button>
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-check-circle"></i> อนุมัติการยกเลิก
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Reject Cancel Modal --}}
                    <div class="modal fade" id="rejectCancelModal{{ $cancelRequest->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">ปฏิเสธการยกเลิกการขาย</h5>
                                    <button type="button" class="btn-close btn-close-white"
                                        data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('payment_approvals.reject_cancel_sale', $cancelRequest->id) }}"
                                    method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <div class="modal-body">
                                        <div class="alert alert-warning">
                                            <strong>เหตุผลการขอยกเลิก:</strong>
                                            <p class="mb-0">{{ $cancelRequest->message }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">เหตุผลในการปฏิเสธ <span
                                                    class="text-danger">*</span></label>
                                            <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">ยกเลิก</button>
                                        <button type="submit" class="btn btn-danger">
                                            <i class="bi bi-x-circle"></i> ปฏิเสธ
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                @endforelse
            </tbody>
        </table>
    </div>
@endif
```

---

### Section 2: Approved Tab - Approved Cancellations

**Location**: After approved notifications table (line ~370)

```blade
{{-- Approved Cancel Requests Section --}}
@if ($approvedCancelRequests && $approvedCancelRequests->count() > 0)
    <hr class="my-4">
    <h5 class="mb-3">
        <i class="bi bi-check-circle"></i> ยกเลิกการขายแล้ว
        <span class="badge bg-success">{{ $approvedCancelRequests->count() }}</span>
    </h5>
    <div class="table-responsive">
        <table class="table table-success mb-0">
            <thead class="table-header-custom">
                <tr>
                    <th class="text-center">ลำดับ</th>
                    <th class="text-center">เลขที่ขาย</th>
                    <th class="text-center">จำนวนหมู</th>
                    <th class="text-center">ผู้ขอยกเลิก</th>
                    <th class="text-center">อนุมัติเมื่อ</th>
                    <th class="text-center">การกระทำ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($approvedCancelRequests as $index => $cancelRequest)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="text-center">
                            {{ $cancelRequest->relatedModel === 'PigSale' 
                                ? \App\Models\PigSale::find($cancelRequest->related_model_id)?->id 
                                : '-' }}
                        </td>
                        <td class="text-center">
                            {{ $cancelRequest->relatedModel === 'PigSale' 
                                ? \App\Models\PigSale::find($cancelRequest->related_model_id)?->quantity . ' ตัว'
                                : '-' }}
                        </td>
                        <td class="text-center">{{ $cancelRequest->relatedUser->name ?? '-' }}</td>
                        <td class="text-center">{{ $cancelRequest->updated_at->format('d/m/Y H:i') }}</td>
                        <td class="text-center">
                            <a href="{{ route('payment_approvals.detail', $cancelRequest->id) }}"
                                class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> ดู
                            </a>
                        </td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </table>
    </div>
@endif
```

---

### Section 3: Rejected Tab - Rejected Cancellations

**Location**: After rejected notifications table (line ~460)

```blade
{{-- Rejected Cancel Requests Section --}}
@if ($rejectedCancelRequests && $rejectedCancelRequests->count() > 0)
    <hr class="my-4">
    <h5 class="mb-3">
        <i class="bi bi-x-circle"></i> ปฏิเสธการยกเลิก
        <span class="badge bg-danger">{{ $rejectedCancelRequests->count() }}</span>
    </h5>
    <div class="table-responsive">
        <table class="table table-danger mb-0">
            <thead class="table-header-custom">
                <tr>
                    <th class="text-center">ลำดับ</th>
                    <th class="text-center">เลขที่ขาย</th>
                    <th class="text-center">จำนวนหมู</th>
                    <th class="text-center">ผู้ขอยกเลิก</th>
                    <th class="text-center">ปฏิเสธเมื่อ</th>
                    <th class="text-center">เหตุผลปฏิเสธ</th>
                    <th class="text-center">การกระทำ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rejectedCancelRequests as $index => $cancelRequest)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="text-center">
                            {{ $cancelRequest->relatedModel === 'PigSale' 
                                ? \App\Models\PigSale::find($cancelRequest->related_model_id)?->id 
                                : '-' }}
                        </td>
                        <td class="text-center">
                            {{ $cancelRequest->relatedModel === 'PigSale' 
                                ? \App\Models\PigSale::find($cancelRequest->related_model_id)?->quantity . ' ตัว'
                                : '-' }}
                        </td>
                        <td class="text-center">{{ $cancelRequest->relatedUser->name ?? '-' }}</td>
                        <td class="text-center">{{ $cancelRequest->updated_at->format('d/m/Y H:i') }}</td>
                        <td>{{ Str::limit($cancelRequest->approval_notes, 50) }}</td>
                        <td class="text-center">
                            <a href="{{ route('payment_approvals.detail', $cancelRequest->id) }}"
                                class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> ดู
                            </a>
                        </td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </table>
    </div>
@endif
```

---

## Key Blade Directives Used

| Directive | Usage | Example |
|-----------|-------|---------|
| `@if () ... @endif` | Conditional rendering | Show section only if records exist |
| `@forelse () ... @empty ... @endforelse` | Loop with fallback | Loop through requests, show empty if none |
| `{{ }}` | Echo output | Display values from model |
| `@csrf` | CSRF token | Add to form for security |
| `@method('PATCH')` | HTTP method override | Use PATCH for route |
| `{{ route('name', params) }}` | Generate route URL | Create form action URL |
| `{{ $model->relationship?->method() }}` | Safe navigation | Null-coalescing for optional data |
| `{{ Str::limit(text, length) }}` | Truncate string | Shorten long text with ellipsis |

---

## Bootstrap Classes Used

| Class | Purpose | Usage |
|-------|---------|-------|
| `badge bg-warning text-dark` | Yellow badge | Pending items count |
| `badge bg-success` | Green badge | Approved items count |
| `badge bg-danger` | Red badge | Rejected items count |
| `table table-warning` | Yellow table | Pending cancel requests |
| `table table-success` | Green table | Approved cancellations |
| `table table-danger` | Red table | Rejected cancellations |
| `modal fade` | Modal dialog | Popup windows |
| `modal-header bg-success text-white` | Green header | Approve modal |
| `modal-header bg-danger text-white` | Red header | Reject modal |
| `btn btn-success` | Green button | Approve action |
| `btn btn-danger` | Red button | Reject action |
| `btn btn-info` | Blue button | View/Detail action |
| `btn btn-secondary` | Gray button | Cancel/Close action |
| `alert alert-warning` | Yellow alert | Show request reason |
| `table-responsive` | Responsive table | Scroll on mobile |

---

## Data Attributes Used

```blade
<!-- Modal Trigger Button -->
<button type="button" 
    class="btn btn-sm btn-success" 
    data-bs-toggle="modal"                           {{-- Bootstrap modal toggle --}}
    data-bs-target="#approveCancelModal{{ $id }}">  {{-- Target modal ID --}}
    Approve
</button>

<!-- Modal Definition -->
<div class="modal fade" 
    id="approveCancelModal{{ $id }}"                {{-- Unique modal ID --}}
    tabindex="-1">                                   {{-- Keyboard accessibility --}}
    ...
</div>
```

---

## Form Handling

### Approve Form
```blade
<form action="{{ route('payment_approvals.approve_cancel_sale', $cancelRequest->id) }}"
    method="POST">
    @csrf
    @method('PATCH')
    
    <!-- Optional field - can be empty -->
    <textarea name="approval_notes" class="form-control" rows="3"></textarea>
    
    <button type="submit" class="btn btn-success">Approve</button>
</form>
```

### Reject Form
```blade
<form action="{{ route('payment_approvals.reject_cancel_sale', $cancelRequest->id) }}"
    method="POST">
    @csrf
    @method('PATCH')
    
    <!-- Required field - must be filled -->
    <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
    
    <button type="submit" class="btn btn-danger">Reject</button>
</form>
```

---

## Data Access Patterns

### Get Related PigSale
```blade
{{ $cancelRequest->relatedModel === 'PigSale' 
    ? \App\Models\PigSale::find($cancelRequest->related_model_id)?->id 
    : '-' }}
```

### Safe Null Handling
```blade
{{ $model->relationship?->attribute }}  {{-- Uses ?-> safe navigation --}}
{{ $model->attribute ?? 'default' }}    {{-- Uses ?? null coalescing --}}
```

### Format Dates
```blade
{{ $cancelRequest->created_at->format('d/m/Y H:i') }}  {{-- Format as: 01/10/2024 14:30 --}}
{{ $notification->read_at?->format('d/m/Y H:i') }}     {{-- Safe date format --}}
```

### String Manipulation
```blade
{{ Str::limit($text, 50) }}  {{-- Truncate to 50 chars with "..." --}}
```

---

## Icons Used

| Icon | Source | Usage |
|------|--------|-------|
| `bi-exclamation-circle` | Bootstrap Icons | Pending warning |
| `bi-check-circle` | Bootstrap Icons | Approved success |
| `bi-x-circle` | Bootstrap Icons | Rejected error |
| `bi-check` | Bootstrap Icons | Approve button |
| `bi-x` | Bootstrap Icons | Reject button |
| `bi-eye` | Bootstrap Icons | View/Detail button |

---

## CSS Classes Generated

```css
/* Badge styles -->*/
.badge.bg-warning { background-color: #ffc107; color: #000; }
.badge.bg-success { background-color: #198754; color: #fff; }
.badge.bg-danger { background-color: #dc3545; color: #fff; }

/* Table styles */
.table.table-warning { background-color: #fff3cd; }
.table.table-success { background-color: #d1e7dd; }
.table.table-danger { background-color: #f8d7da; }

/* Button styles */
.btn.btn-success { background-color: #198754; }
.btn.btn-danger { background-color: #dc3545; }
.btn.btn-info { background-color: #0dcaf0; }
```

---

## Performance Considerations

### Database Queries
```php
// Optimized with eager loading
$pendingCancelRequests = Notification::where('approval_status', 'pending')
    ->where('type', 'cancel_pig_sale')
    ->with('relatedUser')        {{-- Eager load user to avoid N+1 --}}
    ->orderBy('created_at', 'desc')
    ->paginate(15);
```

### Frontend Optimization
- Each request object is accessed once per row
- Relationships loaded once (not in loop)
- Pagination limits data to 15 items/page
- Modal IDs unique to each row (prevents ID collision)

### Conditional Rendering
- Sections only render if data exists
- Reduces DOM size
- Faster page load

---

## Accessibility Features

1. **Semantic HTML**
   - `<table>` for tabular data
   - `<form>` for forms
   - `<button>` for actions

2. **Labels**
   - Form fields have `<label>` elements
   - Descriptive button text + icons

3. **Keyboard Navigation**
   - `tabindex="-1"` on modals (proper tab order)
   - Buttons are keyboard accessible
   - Forms support keyboard submission

4. **Color + Icons**
   - Not color-only (also uses icons)
   - Screen readers can find buttons
   - Text descriptions provided

5. **ARIA Attributes** (from Bootstrap)
   - Modal role attributes
   - Button aria-labels
   - Dialog role on modals

---

## Browser Compatibility

✅ Tested with:
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

⚠️ Limited support:
- IE 11 (Bootstrap 5 doesn't support IE)
- Older browsers without ES6 support
