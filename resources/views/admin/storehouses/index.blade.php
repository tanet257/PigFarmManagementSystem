@extends('layouts.admin')

@section('title', 'จัดการคลังสินค้า')

@section('content')
    <div class="container my-5">
        <div class="card-header">
            <h1 class="text-center">จัดการคลังสินค้า (Storehouses)</h1>
        </div>
        <div class="py-2"></div>

        {{-- แจ้งเตือนสินค้าใกล้หมด --}}
        @php
            $lowStockItems = $storehouses->filter(function ($item) {
                return ($item->stock ?? 0) > 0 && ($item->stock ?? 0) < ($item->min_quantity ?? 0);
            });
            $outOfStockItems = $storehouses->filter(function ($item) {
                return ($item->stock ?? 0) <= 0;
            });
        @endphp

        @if ($lowStockItems->count() > 0)
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>เตือน!</strong> มีสินค้าใกล้หมด {{ $lowStockItems->count() }} รายการ:
                <ul class="mb-0 mt-2">
                    @foreach ($lowStockItems->take(5) as $item)
                        <li>
                            <strong>{{ $item->item_name }}</strong>
                            - เหลือ {{ $item->stock }} {{ $item->unit }}
                            (ขั้นต่ำ {{ $item->min_quantity ?? 0 }})
                        </li>
                    @endforeach
                    @if ($lowStockItems->count() > 5)
                        <li class="text-muted">และอีก {{ $lowStockItems->count() - 5 }} รายการ...</li>
                    @endif
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($outOfStockItems->count() > 0)
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-x-circle-fill me-2"></i>
                <strong>สินค้าหมด!</strong> มีสินค้าหมดสต็อก {{ $outOfStockItems->count() }} รายการ:
                <ul class="mb-0 mt-2">
                    @foreach ($outOfStockItems->take(5) as $item)
                        <li><strong>{{ $item->item_name }}</strong> ({{ $item->item_code }})</li>
                    @endforeach
                    @if ($outOfStockItems->count() > 5)
                        <li class="text-muted">และอีก {{ $outOfStockItems->count() - 5 }} รายการ...</li>
                    @endif
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Toolbar --}}
        <div class="card-custom-secondary mb-3">
            <form method="GET" action="{{ route('storehouse_records.index') }}"
                class="d-flex align-items-center gap-2 flex-wrap">

                <!-- Farm Card Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-building"></i>
                        {{ request('farm_id') ? $farms->find(request('farm_id'))->farm_name ?? 'ฟาร์ม' : 'ฟาร์มทั้งหมด' }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item"
                                href="{{ route('storehouse_records.index', array_merge(request()->except('farm_id'), [])) }}">ฟาร์มทั้งหมด</a>
                        </li>
                        @foreach ($farms as $farm)
                            <li><a class="dropdown-item {{ request('farm_id') == $farm->id ? 'active' : '' }}"
                                    href="{{ route('storehouse_records.index', array_merge(request()->all(), ['farm_id' => $farm->id])) }}">
                                    {{ $farm->farm_name }}
                                </a></li>
                        @endforeach
                    </ul>
                </div>

                <!-- Item Type Dropdown (Orange) -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-tag"></i>
                        @if (request('item_type') == 'อาหาร')
                            อาหาร
                        @elseif(request('item_type') == 'ยา')
                            ยา
                        @elseif(request('item_type') == 'วัคซีน')
                            วัคซีน
                        @elseif(request('item_type') == 'อุปกรณ์')
                            อุปกรณ์
                        @else
                            ประเภททั้งหมด
                        @endif
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item"
                                href="{{ route('storehouse_records.index', array_merge(request()->except('item_type'), [])) }}">ประเภททั้งหมด</a>
                        </li>
                        <li><a class="dropdown-item {{ request('item_type') == 'อาหาร' ? 'active' : '' }}"
                                href="{{ route('storehouse_records.index', array_merge(request()->all(), ['item_type' => 'อาหาร'])) }}">อาหาร</a>
                        </li>
                        <li><a class="dropdown-item {{ request('item_type') == 'ยา' ? 'active' : '' }}"
                                href="{{ route('storehouse_records.index', array_merge(request()->all(), ['item_type' => 'ยา'])) }}">ยา</a>
                        </li>
                        <li><a class="dropdown-item {{ request('item_type') == 'วัคซีน' ? 'active' : '' }}"
                                href="{{ route('storehouse_records.index', array_merge(request()->all(), ['item_type' => 'วัคซีน'])) }}">วัคซีน</a>
                        </li>
                        <li><a class="dropdown-item {{ request('item_type') == 'อุปกรณ์' ? 'active' : '' }}"
                                href="{{ route('storehouse_records.index', array_merge(request()->all(), ['item_type' => 'อุปกรณ์'])) }}">อุปกรณ์</a>
                        </li>
                    </ul>
                </div>

                <!-- Stock Status Dropdown (Orange) -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-box-seam"></i>
                        @if (request('stock_status') == 'in_stock')
                            มีสินค้า
                        @elseif(request('stock_status') == 'low_stock')
                            สินค้าใกล้หมด
                        @elseif(request('stock_status') == 'out_of_stock')
                            สินค้าหมด
                        @else
                            สถานะทั้งหมด
                        @endif
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item"
                                href="{{ route('storehouse_records.index', array_merge(request()->except('stock_status'), [])) }}">สถานะทั้งหมด</a>
                        </li>
                        <li><a class="dropdown-item {{ request('stock_status') == 'in_stock' ? 'active' : '' }}"
                                href="{{ route('storehouse_records.index', array_merge(request()->all(), ['stock_status' => 'in_stock'])) }}">มีสินค้า</a>
                        </li>
                        <li><a class="dropdown-item {{ request('stock_status') == 'low_stock' ? 'active' : '' }}"
                                href="{{ route('storehouse_records.index', array_merge(request()->all(), ['stock_status' => 'low_stock'])) }}">สินค้าใกล้หมด</a>
                        </li>
                        <li><a class="dropdown-item {{ request('stock_status') == 'out_of_stock' ? 'active' : '' }}"
                                href="{{ route('storehouse_records.index', array_merge(request()->all(), ['stock_status' => 'out_of_stock'])) }}">สินค้าหมด</a>
                        </li>
                    </ul>
                </div>

                <!-- Sort Dropdown (Orange) -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-sort-down"></i>
                        @if (request('sort') == 'name_asc')
                            ชื่อ (ก-ฮ)
                        @elseif(request('sort') == 'name_desc')
                            ชื่อ (ฮ-ก)
                        @elseif(request('sort') == 'quantity_asc')
                            จำนวนน้อย
                        @elseif(request('sort') == 'quantity_desc')
                            จำนวนมาก
                        @else
                            เรียงตาม
                        @endif
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item {{ request('sort') == 'name_asc' ? 'active' : '' }}"
                                href="{{ route('storehouse_records.index', array_merge(request()->all(), ['sort' => 'name_asc'])) }}">ชื่อ
                                (ก-ฮ)</a></li>
                        <li><a class="dropdown-item {{ request('sort') == 'name_desc' ? 'active' : '' }}"
                                href="{{ route('storehouse_records.index', array_merge(request()->all(), ['sort' => 'name_desc'])) }}">ชื่อ
                                (ฮ-ก)</a></li>
                        <li><a class="dropdown-item {{ request('sort') == 'quantity_asc' ? 'active' : '' }}"
                                href="{{ route('storehouse_records.index', array_merge(request()->all(), ['sort' => 'quantity_asc'])) }}">จำนวนน้อย
                                → มาก</a></li>
                        <li><a class="dropdown-item {{ request('sort') == 'quantity_desc' ? 'active' : '' }}"
                                href="{{ route('storehouse_records.index', array_merge(request()->all(), ['sort' => 'quantity_desc'])) }}">จำนวนมาก
                                → น้อย</a></li>
                    </ul>
                </div>

                <!-- Per Page -->
                @include('components.per-page-dropdown')

                <!-- Right side buttons -->
                <div class="ms-auto d-flex gap-2">
                    <div class="btn-group">
                        <button type="button" class="btn btn-success btn-sm " data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="bi bi-plus-circle me-1"></i> เพิ่มสินค้า
                        </button>
                        <ul class="dropdown-menu dropdown-menu-xl">
                            <li>
                                <a class="dropdown-item" href="{{ route('storehouse_records.record') }}">
                                    <i class="bi bi-journal-text me-1"></i> อัปเดทสต็อกสินค้า
                                </a>
                            </li>
                            <li>
                                <button class="dropdown-item" type="button" data-bs-toggle="modal"
                                    data-bs-target="#createModal">
                                    <i class="bi bi-plus-circle me-1"></i> เพิ่มสินค้าใหม่
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </form>
        </div>

        {{-- Export Section --}}
        <div class="card-custom-secondary mb-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-download me-2 text-primary"></i>
                    <strong>ส่วนการส่งออก</strong>
                </div>
                <!-- Custom Date Range Filter for Export -->
                <div class="ms-auto d-flex gap-2 align-items-center">
                    <label class="text-nowrap small mb-0" style="min-width: 100px;">
                        <i class="bi bi-calendar-range"></i> ช่วงวันที่:
                    </label>
                    <input type="date" id="exportDateFrom" class="form-control form-control-sm" style="width: 140px;">
                    <span class="text-nowrap small">ถึง</span>
                    <input type="date" id="exportDateTo" class="form-control form-control-sm" style="width: 140px;">
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success btn-sm" id="exportCsvBtn">
                        <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
                    </button>
                </div>
            </div>
        </div>

        {{-- Table --}}

        <div class=" table-responsive">
            <table class=" table-primary table mb-0">
                <thead class="table-header-custom">
                    <tr>
                        <th class="text-center">รหัส</th>
                        <th class="text-center">ชื่อสินค้า</th>
                        <th class="text-center">ประเภท</th>
                        <th class="text-center">ฟาร์ม</th>
                        <th class="text-center">จำนวน</th>
                        <th class="text-center">หน่วย</th>
                        @if ($storehouses->some(function($s) { return $s->base_unit; }))
                            <th class="text-center">ปริมาณ/หน่วย</th>
                            <th class="text-center">หน่วยพื้นฐาน</th>
                        @endif
                        <th class="text-center">สถานะ</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($storehouses as $item)
                        <tr data-row-click="#viewModal{{ $item->id }}" class="clickable-row">

                            <td class="text-center">
                                <strong>{{ $item->item_code ?? 'ST-' . str_pad($item->id, 4, '0', STR_PAD_LEFT) }}</strong>
                            </td>
                            <td>{{ $item->item_name }}</td>
                            <td class="text-center">
                                @if ($item->item_type == 'อาหาร')
                                    <span class="badge bg-success">อาหาร</span>
                                @elseif($item->item_type == 'ยา')
                                    <span class="badge bg-warning">ยา</span>
                                @elseif($item->item_type == 'วัคซีน')
                                    <span class="badge bg-info">วัคซีน</span>
                                @else
                                    <span class="badge bg-secondary">{{ $item->item_type }}</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $item->farm->farm_name ?? '-' }}</td>
                            <td class="text-center"><strong>{{ $item->stock ?? 0 }}</strong></td>
                            <td class="text-center">{{ $item->unit }}</td>
                            @if ($storehouses->some(function($s) { return $s->base_unit; }))
                                <td class="text-center">
                                    @if ($item->quantity_per_unit)
                                        <small>{{ $item->quantity_per_unit }} {{ $item->base_unit ?? '-' }}/{{ $item->unit }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($item->base_unit)
                                        <small>{{ $item->base_unit }}</small>
                                        @if ($item->conversion_rate)
                                            <br><small class="text-muted">(× {{ $item->conversion_rate }})</small>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            @endif
                            <td class="text-center">
                                @if (($item->stock ?? 0) <= 0)
                                    <span class="badge bg-danger">หมด</span>
                                @elseif(($item->stock ?? 0) < ($item->min_quantity ?? 0))
                                    <span class="badge bg-warning">ใกล้หมด</span>
                                @else
                                    <span class="badge bg-success">พร้อมใช้</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-info"
                                    onclick="event.stopPropagation(); new bootstrap.Modal(document.getElementById('viewModal{{ $item->id }}')).show();">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                    data-bs-target="#editModal{{ $item->id }}" onclick="event.stopPropagation()">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <form action="{{ route('storehouse_records.delete', $item->id) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger"
                                        onclick="event.stopPropagation(); if(confirm('ต้องการลบสินค้านี้หรือไม่?')) { this.form.submit(); }">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-danger">ไม่มีข้อมูลสินค้าในคลัง</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


        {{-- Pagination --}}
        <div class="d-flex justify-content-between mt-3">
            <div>
                แสดง {{ $storehouses->firstItem() ?? 0 }} ถึง {{ $storehouses->lastItem() ?? 0 }} จาก
                {{ $storehouses->total() ?? 0 }} รายการ
            </div>
            <div>
                {{ $storehouses->withQueryString()->links() }}
            </div>
        </div>
    </div>

    {{-- View Modals --}}
    @foreach ($storehouses as $item)
        <div class="modal fade" id="viewModal{{ $item->id }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-box"></i> รายละเอียดสินค้า - {{ $item->item_name }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="bi bi-info-circle"></i> ข้อมูลทั่วไป
                                </h6>
                                <table class="table table-secondary table-sm table-hover">
                                    <tr>
                                        <td width="40%"><strong>รหัสสินค้า:</strong></td>
                                        <td><code
                                                class="text-info">{{ $item->item_code ?? 'ST-' . str_pad($item->id, 4, '0', STR_PAD_LEFT) }}</code>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>ชื่อสินค้า:</strong></td>
                                        <td>{{ $item->item_name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>ประเภท:</strong></td>
                                        <td>{{ $item->item_type }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>ฟาร์ม:</strong></td>
                                        <td>{{ $item->farm->farm_name ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="bi bi-graph-up"></i> สต็อก
                                </h6>
                                <table class="table table-secondary table-sm table-hover">
                                    <tr>
                                        <td width="40%"><strong>จำนวน:</strong></td>
                                        <td><strong class="text-success">{{ $item->stock }}
                                                {{ $item->unit }}</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>จำนวนขั้นต่ำ:</strong></td>
                                        <td>{{ $item->min_quantity ?? 0 }} {{ $item->unit }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>วันหมดอายุ:</strong></td>
                                        <td>
                                            @if ($item->expire_date)
                                                {{ \Carbon\Carbon::parse($item->expire_date)->format('d/m/Y') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                @if ($item->base_unit && $item->quantity_per_unit)
                                    <hr>
                                    <h6 class="text-info mb-3">
                                        <i class="bi bi-arrow-left-right"></i> การแปลงหน่วย
                                    </h6>
                                    <table class="table table-light table-sm table-hover">
                                        <tr class="bg-light">
                                            <td width="40%"><strong>1 {{ $item->unit }}:</strong></td>
                                            <td>{{ $item->quantity_per_unit }} {{ $item->base_unit }}</td>
                                        </tr>
                                        @if ($item->conversion_rate)
                                            <tr class="bg-light">
                                                <td><strong>อัตราแปลง:</strong></td>
                                                <td>{{ $item->conversion_rate }} ×</td>
                                            </tr>
                                        @endif
                                    </table>
                                @endif
                            </div>
                        </div>
                        @if ($item->note)
                            <hr>
                            <h6 class="text-primary mb-2">
                                <i class="bi bi-chat-left-text"></i> หมายเหตุ
                            </h6>
                            <div class="bg-light p-3 rounded">
                                <p class="mb-0">{{ $item->note }}</p>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> ปิด
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Create Modal --}}
    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('storehouse_records.create') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">เพิ่มสินค้าใหม่</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">รหัสสินค้า</label>
                                <input type="text" class="form-control" name="item_code" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ชื่อสินค้า</label>
                                <input type="text" class="form-control" name="item_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ประเภท <span class="text-danger">*</span></label>
                                <div class="dropdown">
                                    <button
                                        class="btn btn-primary dropdown-toggle w-100 d-flex justify-content-between align-items-center"
                                        type="button" id="storeCreateItemTypeDropdownBtn" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <span>-- เลือกประเภท --</span>

                                    </button>
                                    <ul class="dropdown-menu w-100" role="listbox">
                                        <li><a class="dropdown-item" href="#" data-item-type="อาหาร"
                                                onclick="updateStoreItemType(event)">อาหาร</a></li>
                                        <li><a class="dropdown-item" href="#" data-item-type="ยา"
                                                onclick="updateStoreItemType(event)">ยา</a></li>
                                        <li><a class="dropdown-item" href="#" data-item-type="วัคซีน"
                                                onclick="updateStoreItemType(event)">วัคซีน</a></li>
                                        <li><a class="dropdown-item" href="#" data-item-type="อื่นๆ"
                                                onclick="updateStoreItemType(event)">อื่นๆ</a></li>
                                    </ul>
                                    <input type="hidden" name="item_type" id="storeCreateItemType" value=""
                                        required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ฟาร์ม <span class="text-danger">*</span></label>
                                <div class="dropdown">
                                    <button
                                        class="btn btn-primary dropdown-toggle w-100 d-flex justify-content-between align-items-center"
                                        type="button" id="storeCreateFarmDropdownBtn" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <span>-- เลือกฟาร์ม --</span>
                                    </button>
                                    <ul class="dropdown-menu w-100" id="storeCreateFarmDropdownMenu">
                                        @foreach ($farms as $farm)
                                            <li>
                                                <a class="dropdown-item" href="#"
                                                    data-farm-id="{{ $farm->id }}">
                                                    {{ $farm->farm_name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <input type="hidden" name="farm_id" id="storeCreateFarmSelect" value=""
                                        required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">หน่วย</label>
                                <input type="text" class="form-control" name="unit" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">จำนวนขั้นต่ำ (เตือนเมื่อสต็อกต่ำกว่านี้)</label>
                                <input type="number" class="form-control" name="min_quantity" min="0"
                                    step="0.01">
                                <small class="text-muted">ระบบจะแจ้งเตือนเมื่อสต็อกต่ำกว่าจำนวนนี้</small>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">หมายเหตุ</label>
                                <textarea class="form-control" name="note" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> บันทึก
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Modals --}}
    @foreach ($storehouses as $item)
        <div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="{{ route('storehouse_records.update', $item->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">แก้ไขสินค้า - {{ $item->item_name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">รหัสสินค้า </label>
                                    <input type="text" class="form-control" value="{{ $item->item_code }}" disabled
                                        readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">ชื่อสินค้า</label>
                                    <input type="text" class="form-control" name="item_name"
                                        value="{{ $item->item_name }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">ประเภท </label>
                                    <input type="text" class="form-control" value="{{ $item->item_type }}" disabled
                                        readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">ฟาร์ม </label>
                                    <input type="text" class="form-control"
                                        value="{{ $farms->find($item->farm_id)->farm_name ?? '' }}" disabled readonly>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">หน่วย</label>
                                    <input type="text" class="form-control" name="unit"
                                        value="{{ $item->unit }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">จำนวนขั้นต่ำ (เตือนเมื่อสต็อกต่ำกว่านี้)</label>
                                    <input type="number" class="form-control" name="min_quantity"
                                        value="{{ $item->min_quantity ?? 0 }}" min="0" step="0.01">
                                    <small class="text-muted">ระบบจะแจ้งเตือนเมื่อสต็อกต่ำกว่าจำนวนนี้</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">สถานะ</label>
                                    <div class="dropdown">
                                        <button
                                            class="btn btn-primary dropdown-toggle w-100 d-flex justify-content-between align-items-center"
                                            type="button" id="storeEditStatusDropdownBtn{{ $item->id }}"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <span>{{ $item->status == 'available' ? 'พร้อมใช้' : 'หมด' }}</span>

                                        </button>
                                        <ul class="dropdown-menu w-100" role="listbox">
                                            <li><a class="dropdown-item" href="#" data-status="available"
                                                    onclick="updateStoreEditStatus(event, {{ $item->id }})">พร้อมใช้</a>
                                            </li>
                                            <li><a class="dropdown-item" href="#" data-status="unavailable"
                                                    onclick="updateStoreEditStatus(event, {{ $item->id }})">หมด</a>
                                            </li>
                                        </ul>
                                        <input type="hidden" name="status" id="storeEditStatus{{ $item->id }}"
                                            value="{{ $item->status }}">
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">หมายเหตุ</label>
                                    <textarea class="form-control" name="note" rows="3">{{ $item->note }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> บันทึก
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    @push('scripts')
        <script>
            // Farm Dropdown Handler for Storehouse Create Modal
            document.addEventListener('DOMContentLoaded', function() {
                const farmDropdownMenu = document.getElementById('storeCreateFarmDropdownMenu');
                const farmDropdownBtn = document.getElementById('storeCreateFarmDropdownBtn');
                const farmSelect = document.getElementById('storeCreateFarmSelect');

                if (farmDropdownMenu) {
                    farmDropdownMenu.addEventListener('click', function(e) {
                        if (e.target.classList.contains('dropdown-item')) {
                            e.preventDefault();
                            const farmId = e.target.getAttribute('data-farm-id');
                            const farmName = e.target.textContent.trim();

                            // Update button text and hidden input
                            farmDropdownBtn.querySelector('span').textContent = farmName;
                            farmSelect.value = farmId;
                        }
                    });
                }
            });

            // Update item_type for Create Modal
            function updateStoreItemType(event) {
                event.preventDefault();
                const itemType = event.target.getAttribute('data-item-type');
                const itemTypeText = event.target.textContent.trim();

                document.getElementById('storeCreateItemTypeDropdownBtn')
                    .querySelector('span').textContent = itemTypeText;
                document.getElementById('storeCreateItemType').value = itemType;
            }

            // Update status for Edit Modal
            function updateStoreEditStatus(event, itemId) {
                event.preventDefault();
                const status = event.target.getAttribute('data-status');
                const statusText = event.target.textContent.trim();

                document.getElementById('storeEditStatusDropdownBtn' + itemId)
                    .querySelector('span').textContent = statusText;
                document.getElementById('storeEditStatus' + itemId).value = status;
            }

            // Export CSV
            document.getElementById('exportCsvBtn').addEventListener('click', function() {
                console.log('📥 [Storehouses] Exporting CSV');
                const params = new URLSearchParams(window.location.search);
                const dateFrom = document.getElementById('exportDateFrom').value;
                const dateTo = document.getElementById('exportDateTo').value;
                if (dateFrom) params.set('export_date_from', dateFrom);
                if (dateTo) params.set('export_date_to', dateTo);
                const url = `{{ route('storehouse_records.export.csv') }}?${params.toString()}`;
                window.location.href = url;
            });


        </script>
        <script src="{{ asset('admin/js/common-table-click.js') }}"></script>
    @endpush
@endsection
