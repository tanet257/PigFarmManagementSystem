@extends('layouts.admin')

@section('title', 'จัดการคลังสินค้า')

@section('content')
    <div class="container my-5">
        <div class="card-header">
            <h1 class="text-center">จัดการคลังสินค้า (Storehouses)</h1>
        </div>
        <div class="py-2"></div>

        {{-- Toolbar --}}
        <div class="card-custom-secondary mb-3">
            <form method="GET" action="{{ route('storehouses.index') }}" class="d-flex align-items-center gap-2 flex-wrap">
                <!-- Search -->
                <input type="search" name="search" class="form-control form-control-sm" style="width: 200px;"
                    placeholder="ค้นหาสินค้า..." value="{{ request('search') }}">

                <!-- Farm Card Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"
                        style="background-color: #1E3E62; color: white; border: none;">
                        <i class="bi bi-building"></i>
                        {{ request('farm_id') ? $farms->find(request('farm_id'))->farm_name ?? 'ฟาร์ม' : 'ฟาร์มทั้งหมด' }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item"
                                href="{{ route('storehouses.index', array_merge(request()->except('farm_id'), [])) }}">ฟาร์มทั้งหมด</a>
                        </li>
                        @foreach ($farms as $farm)
                            <li><a class="dropdown-item {{ request('farm_id') == $farm->id ? 'active' : '' }}"
                                    href="{{ route('storehouses.index', array_merge(request()->all(), ['farm_id' => $farm->id])) }}">
                                    {{ $farm->farm_name }}
                                </a></li>
                        @endforeach
                    </ul>
                </div>

                <!-- Category Dropdown (Orange) -->
                <div class="dropdown">
                    <button class="btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"
                        style="background-color: #FF6500; color: white; border: none;">
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
                                href="{{ route('storehouses.index', array_merge(request()->except('category'), [])) }}">ประเภททั้งหมด</a>
                        </li>
                        <li><a class="dropdown-item {{ request('category') == 'อาหาร' ? 'active' : '' }}"
                                href="{{ route('storehouses.index', array_merge(request()->all(), ['category' => 'อาหาร'])) }}">อาหาร</a>
                        </li>
                        <li><a class="dropdown-item {{ request('category') == 'ยา' ? 'active' : '' }}"
                                href="{{ route('storehouses.index', array_merge(request()->all(), ['category' => 'ยา'])) }}">ยา</a>
                        </li>
                        <li><a class="dropdown-item {{ request('category') == 'วัคซีน' ? 'active' : '' }}"
                                href="{{ route('storehouses.index', array_merge(request()->all(), ['category' => 'วัคซีน'])) }}">วัคซีน</a>
                        </li>
                        <li><a class="dropdown-item {{ request('category') == 'อุปกรณ์' ? 'active' : '' }}"
                                href="{{ route('storehouses.index', array_merge(request()->all(), ['category' => 'อุปกรณ์'])) }}">อุปกรณ์</a>
                        </li>
                    </ul>
                </div>

                <!-- Stock Status Dropdown (Orange) -->
                <div class="dropdown">
                    <button class="btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"
                        style="background-color: #FF6500; color: white; border: none;">
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
                                href="{{ route('storehouses.index', array_merge(request()->except('stock_status'), [])) }}">สถานะทั้งหมด</a>
                        </li>
                        <li><a class="dropdown-item {{ request('stock_status') == 'in_stock' ? 'active' : '' }}"
                                href="{{ route('storehouses.index', array_merge(request()->all(), ['stock_status' => 'in_stock'])) }}">มีสินค้า</a>
                        </li>
                        <li><a class="dropdown-item {{ request('stock_status') == 'low_stock' ? 'active' : '' }}"
                                href="{{ route('storehouses.index', array_merge(request()->all(), ['stock_status' => 'low_stock'])) }}">สินค้าใกล้หมด</a>
                        </li>
                        <li><a class="dropdown-item {{ request('stock_status') == 'out_of_stock' ? 'active' : '' }}"
                                href="{{ route('storehouses.index', array_merge(request()->all(), ['stock_status' => 'out_of_stock'])) }}">สินค้าหมด</a>
                        </li>
                    </ul>
                </div>

                <!-- Sort Dropdown (Orange) -->
                <div class="dropdown">
                    <button class="btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"
                        style="background-color: #FF6500; color: white; border: none;">
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
                                href="{{ route('storehouses.index', array_merge(request()->all(), ['sort' => 'name_asc'])) }}">ชื่อ
                                (ก-ฮ)</a></li>
                        <li><a class="dropdown-item {{ request('sort') == 'name_desc' ? 'active' : '' }}"
                                href="{{ route('storehouses.index', array_merge(request()->all(), ['sort' => 'name_desc'])) }}">ชื่อ
                                (ฮ-ก)</a></li>
                        <li><a class="dropdown-item {{ request('sort') == 'quantity_asc' ? 'active' : '' }}"
                                href="{{ route('storehouses.index', array_merge(request()->all(), ['sort' => 'quantity_asc'])) }}">จำนวนน้อย
                                → มาก</a></li>
                        <li><a class="dropdown-item {{ request('sort') == 'quantity_desc' ? 'active' : '' }}"
                                href="{{ route('storehouses.index', array_merge(request()->all(), ['sort' => 'quantity_desc'])) }}">จำนวนมาก
                                → น้อย</a></li>
                    </ul>
                </div>

                <!-- Right side buttons -->
                <div class="ms-auto d-flex gap-2">
                    <a class="btn btn-outline-success btn-sm" href="{{ route('storehouses.export.csv') }}">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i> CSV
                    </a>
                    <a class="btn btn-outline-danger btn-sm" href="{{ route('storehouses.export.pdf') }}">
                        <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                    </a>
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                        data-bs-target="#createModal">
                        <i class="bi bi-plus-circle me-1"></i> เพิ่มสินค้า
                    </button>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="card-custom-secondary mt-3">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-header-custom">
                        <tr>
                            <th class="text-center">รหัส</th>
                            <th class="text-center">ชื่อสินค้า</th>
                            <th class="text-center">ประเภท</th>
                            <th class="text-center">ฟาร์ม</th>
                            <th class="text-center">จำนวน</th>
                            <th class="text-center">หน่วย</th>
                            <th class="text-center">สถานะ</th>
                            <th class="text-center">วันหมดอายุ</th>
                            <th class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($storehouses as $item)
                            <tr class="clickable-row" data-bs-toggle="modal"
                                data-bs-target="#viewModal{{ $item->id }}">
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
                                <td class="text-center"><strong>{{ number_format($item->quantity, 2) }}</strong></td>
                                <td class="text-center">{{ $item->unit }}</td>
                                <td class="text-center">
                                    @if ($item->quantity <= 0)
                                        <span class="badge bg-danger">หมด</span>
                                    @elseif($item->quantity < $item->min_quantity)
                                        <span class="badge bg-warning">ใกล้หมด</span>
                                    @else
                                        <span class="badge bg-success">พร้อมใช้</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($item->expire_date)
                                        {{ \Carbon\Carbon::parse($item->expire_date)->format('d/m/Y') }}
                                        @if (\Carbon\Carbon::parse($item->expire_date)->isPast())
                                            <small class="text-danger d-block">หมดอายุแล้ว!</small>
                                        @elseif(\Carbon\Carbon::parse($item->expire_date)->diffInDays() < 30)
                                            <small class="text-warning d-block">ใกล้หมดอายุ</small>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                        data-bs-target="#viewModal{{ $item->id }}">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="{{ route('storehouses.edit', $item->id) }}" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('storehouses.delete', $item->id) }}" method="POST"
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
                                <table class="table table-sm">
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
                                <table class="table table-sm">
                                    <tr>
                                        <td width="40%"><strong>จำนวน:</strong></td>
                                        <td><strong>{{ number_format($item->quantity, 2) }} {{ $item->unit }}</strong>
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
                        <a href="{{ route('storehouses.edit', $item->id) }}" class="btn btn-warning">
                            <i class="bi bi-pencil-square"></i> แก้ไข
                        </a>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <style>
        .clickable-row {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .clickable-row:hover {
            background-color: #FFF5E6 !important;
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(255, 91, 34, 0.15);
        }

        .clickable-row:active {
            transform: translateY(0);
        }

        .clickable-row td:last-child {
            pointer-events: none;
        }

        .clickable-row td:last-child>* {
            pointer-events: auto;
        }

        .dropdown-menu .dropdown-item.active {
            background-color: #FF6500;
            color: white;
        }

        .dropdown-menu {
            z-index: 1050 !important;
        }
    </style>

    @push('scripts')
        <script src="{{ asset('admin/js/pages/storehouses.js') }}"></script>
    @endpush
@endsection
