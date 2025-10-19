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
                            - เหลือ {{ number_format($item->stock, 2) }} {{ $item->unit }}
                            (ขั้นต่ำ {{ number_format($item->min_quantity ?? 0, 2) }})
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

                <!-- Category Dropdown (Orange) -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-tag"></i>
                        @if (request('category') == 'อาหาร')
                            อาหาร
                        @elseif(request('category') == 'ยา')
                            ยา
                        @elseif(request('category') == 'วัคซีน')
                            วัคซีน
                        @elseif(request('category') == 'อุปกรณ์')
                            อุปกรณ์
                        @else
                            ประเภททั้งหมด
                        @endif
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item"
                                href="{{ route('storehouse_records.index', array_merge(request()->except('category'), [])) }}">ประเภททั้งหมด</a>
                        </li>
                        <li><a class="dropdown-item {{ request('category') == 'อาหาร' ? 'active' : '' }}"
                                href="{{ route('storehouse_records.index', array_merge(request()->all(), ['category' => 'อาหาร'])) }}">อาหาร</a>
                        </li>
                        <li><a class="dropdown-item {{ request('category') == 'ยา' ? 'active' : '' }}"
                                href="{{ route('storehouse_records.index', array_merge(request()->all(), ['category' => 'ยา'])) }}">ยา</a>
                        </li>
                        <li><a class="dropdown-item {{ request('category') == 'วัคซีน' ? 'active' : '' }}"
                                href="{{ route('storehouse_records.index', array_merge(request()->all(), ['category' => 'วัคซีน'])) }}">วัคซีน</a>
                        </li>
                        <li><a class="dropdown-item {{ request('category') == 'อุปกรณ์' ? 'active' : '' }}"
                                href="{{ route('storehouse_records.index', array_merge(request()->all(), ['category' => 'อุปกรณ์'])) }}">อุปกรณ์</a>
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
                    <a class="btn btn-outline-success btn-sm" href="{{ route('storehouse_records.export.csv') }}">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i> CSV
                    </a>
                    <a class="btn btn-outline-danger btn-sm" href="{{ route('storehouse_records.export.pdf') }}">
                        <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                    </a>
                    <div class="btn-group">
                        <button type="button" class="btn btn-success btn-sm " data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="bi bi-plus-circle me-1"></i> เพิ่มสินค้า
                        </button>
                        <ul class="dropdown-menu dropdown-menu-xl">
                            <li>
                                <a class="dropdown-item" href="{{ route('store_house_record.record') }}">
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
                        <th class="text-center">สถานะ</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($storehouses as $item)
                        <tr class="clickable-row" data-bs-toggle="modal" data-bs-target="#viewModal{{ $item->id }}">
                            <td class="text-center">
                                <strong>{{ $item->item_code ?? 'ST-' . str_pad($item->id, 4, '0', STR_PAD_LEFT) }}</strong>
                            </td>
                            <td>{{ $item->item_name }}</td>
                            <td class="text-center">
                                @if ($item->category == 'อาหาร')
                                    <span class="badge bg-success">อาหาร</span>
                                @elseif($item->category == 'ยา')
                                    <span class="badge bg-warning">ยา</span>
                                @elseif($item->category == 'วัคซีน')
                                    <span class="badge bg-info">วัคซีน</span>
                                @else
                                    <span class="badge bg-secondary">{{ $item->category }}</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $item->farm->farm_name ?? '-' }}</td>
                            <td class="text-center"><strong>{{ number_format($item->stock ?? 0, 2) }}</strong></td>
                            <td class="text-center">{{ $item->unit }}</td>
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
                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                    data-bs-target="#viewModal{{ $item->id }}">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                    data-bs-target="#editModal{{ $item->id }}">
                                    <i class="bi bi-pencil-square"></i>

                                    <form action="{{ route('storehouse_records.delete', $item->id) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('ต้องการลบสินค้านี้หรือไม่?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-danger">❌ ไม่มีข้อมูลสินค้าในคลัง</td>
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
                    <div class="modal-header">
                        <h5 class="modal-title">รายละเอียดสินค้า - {{ $item->item_name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-secondary table-sm">
                                    <tr>
                                        <td width="40%"><strong>รหัสสินค้า:</strong></td>
                                        <td>{{ $item->item_code ?? 'ST-' . str_pad($item->id, 4, '0', STR_PAD_LEFT) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>ชื่อสินค้า:</strong></td>
                                        <td>{{ $item->item_name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>ประเภท:</strong></td>
                                        <td>{{ $item->category }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>ฟาร์ม:</strong></td>
                                        <td>{{ $item->farm->farm_name ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-secondary table-sm">
                                    <tr>
                                        <td width="40%"><strong>จำนวน:</strong></td>
                                        <td><strong>{{ number_format($item->stock, 2) }} {{ $item->unit }}</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>จำนวนขั้นต่ำ:</strong></td>
                                        <td>{{ number_format($item->min_quantity ?? 0, 2) }} {{ $item->unit }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>วันหมดอายุ:</strong></td>
                                        <td>
                                            @if ($item->expire_date)
                                                {{ \Carbon\Carbon::parse($item->expire_date)->format('d/m/Y') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>หมายเหตุ:</strong></td>
                                        <td>{{ $item->note ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-warning" data-bs-dismiss="modal" data-bs-toggle="modal"
                            data-bs-target="#editModal{{ $item->id }}">
                            <i class="bi bi-pencil-square"></i> แก้ไข
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
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
                                <label class="form-label">ประเภท</label>
                                <select class="form-select" name="item_type" required>
                                    <option value="">เลือกประเภท</option>
                                    <option value="อาหาร">อาหาร</option>
                                    <option value="ยา">ยา</option>
                                    <option value="วัคซีน">วัคซีน</option>
                                    <option value="อื่นๆ">อื่นๆ</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ฟาร์ม</label>
                                <select class="form-select" name="farm_id" required>
                                    <option value="">เลือกฟาร์ม</option>
                                    @foreach ($farms as $farm)
                                        <option value="{{ $farm->id }}">{{ $farm->farm_name }}</option>
                                    @endforeach
                                </select>
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
                                    <label class="form-label">รหัสสินค้า</label>
                                    <input type="text" class="form-control" name="item_code"
                                        value="{{ $item->item_code }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">ชื่อสินค้า</label>
                                    <input type="text" class="form-control" name="item_name"
                                        value="{{ $item->item_name }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">ประเภท</label>
                                    <select class="form-select" name="item_type" required>
                                        <option value="อาหาร" {{ $item->item_type == 'อาหาร' ? 'selected' : '' }}>อาหาร
                                        </option>
                                        <option value="ยา" {{ $item->item_type == 'ยา' ? 'selected' : '' }}>ยา
                                        </option>
                                        <option value="วัคซีน" {{ $item->item_type == 'วัคซีน' ? 'selected' : '' }}>วัคซีน
                                        </option>
                                        <option value="อื่นๆ" {{ $item->item_type == 'อื่นๆ' ? 'selected' : '' }}>อื่นๆ
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">ฟาร์ม</label>
                                    <select class="form-select" name="farm_id" required>
                                        <option value="">เลือกฟาร์ม</option>
                                        @foreach ($farms as $farm)
                                            <option value="{{ $farm->id }}"
                                                {{ $item->farm_id == $farm->id ? 'selected' : '' }}>
                                                {{ $farm->farm_name }}
                                            </option>
                                        @endforeach
                                    </select>
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
                                    <select class="form-select" name="status">
                                        <option value="available" {{ $item->status == 'available' ? 'selected' : '' }}>
                                            พร้อมใช้</option>
                                        <option value="unavailable"
                                            {{ $item->status == 'unavailable' ? 'selected' : '' }}>หมด</option>
                                    </select>
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
        <script src="{{ asset('admin/js/common-dropdowns.js') }}"></script>
    @endpush
@endsection
