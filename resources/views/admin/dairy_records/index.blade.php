@extends('layouts.admin')

@section('title', 'บันทึกประจำวัน')

@section('content')
    <div class="container my-5">
        <div class="card-header">
            <h1 class="text-center">บันทึกประจำวัน (Dairy Records)</h1>
        </div>
        <div class="py-2"></div>

        {{-- Toolbar --}}
        <div class="card-custom-secondary mb-3">
            <form method="GET" action="{{ route('dairy_records.index') }}" class="d-flex align-items-center gap-2 flex-wrap">
                <!-- Search -->
                <input type="search" name="search" class="form-control form-control-sm" style="width: 200px;"
                    placeholder="ค้นหา..." value="{{ request('search') }}">

                <!-- Farm Card Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"
                        style="background-color: #1E3E62; color: white; border: none;">
                        <i class="bi bi-building"></i>
                        {{ request('farm_id') ? $farms->find(request('farm_id'))->farm_name ?? 'ฟาร์ม' : 'ฟาร์มทั้งหมด' }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item"
                                href="{{ route('dairy_records.index', array_merge(request()->except('farm_id'), [])) }}">ฟาร์มทั้งหมด</a>
                        </li>
                        @foreach ($farms as $farm)
                            <li><a class="dropdown-item {{ request('farm_id') == $farm->id ? 'active' : '' }}"
                                    href="{{ route('dairy_records.index', array_merge(request()->all(), ['farm_id' => $farm->id])) }}">
                                    {{ $farm->farm_name }}
                                </a></li>
                        @endforeach
                    </ul>
                </div>

                <!-- Batch Card Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"
                        style="background-color: #1E3E62; color: white; border: none;">
                        <i class="bi bi-layers"></i>
                        {{ request('batch_id') ? $batches->find(request('batch_id'))->batch_code ?? 'รุ่น' : 'รุ่นทั้งหมด' }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item"
                                href="{{ route('dairy_records.index', array_merge(request()->except('batch_id'), [])) }}">รุ่นทั้งหมด</a>
                        </li>
                        @foreach ($batches as $batch)
                            <li><a class="dropdown-item {{ request('batch_id') == $batch->id ? 'active' : '' }}"
                                    href="{{ route('dairy_records.index', array_merge(request()->all(), ['batch_id' => $batch->id])) }}">
                                    {{ $batch->batch_code }}
                                </a></li>
                        @endforeach
                    </ul>
                </div>

                <!-- Barn Dropdown (Orange) -->
                <div class="dropdown">
                    <button class="btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"
                        style="background-color: #FF6500; color: white; border: none;">
                        <i class="bi bi-house"></i>
                        {{ request('barn_id') ? $barns->find(request('barn_id'))->barn_code ?? 'เล้า' : 'เล้าทั้งหมด' }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item"
                                href="{{ route('dairy_records.index', array_merge(request()->except('barn_id'), [])) }}">เล้าทั้งหมด</a>
                        </li>
                        @foreach ($barns as $barn)
                            <li><a class="dropdown-item {{ request('barn_id') == $barn->id ? 'active' : '' }}"
                                    href="{{ route('dairy_records.index', array_merge(request()->all(), ['barn_id' => $barn->id])) }}">
                                    {{ $barn->barn_code }}
                                </a></li>
                        @endforeach
                    </ul>
                </div>

                <!-- Type Dropdown (Orange) -->
                <div class="dropdown">
                    <button class="btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"
                        style="background-color: #FF6500; color: white; border: none;">
                        <i class="bi bi-filter"></i>
                        @if (request('type') == 'food')
                            อาหาร
                        @elseif(request('type') == 'treatment')
                            การรักษา
                        @elseif(request('type') == 'death')
                            หมูตาย
                        @else
                            ประเภททั้งหมด
                        @endif
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item"
                                href="{{ route('dairy_records.index', array_merge(request()->except('type'), [])) }}">ประเภททั้งหมด</a>
                        </li>
                        <li><a class="dropdown-item {{ request('type') == 'food' ? 'active' : '' }}"
                                href="{{ route('dairy_records.index', array_merge(request()->all(), ['type' => 'food'])) }}">อาหาร</a>
                        </li>
                        <li><a class="dropdown-item {{ request('type') == 'treatment' ? 'active' : '' }}"
                                href="{{ route('dairy_records.index', array_merge(request()->all(), ['type' => 'treatment'])) }}">การรักษา</a>
                        </li>
                        <li><a class="dropdown-item {{ request('type') == 'death' ? 'active' : '' }}"
                                href="{{ route('dairy_records.index', array_merge(request()->all(), ['type' => 'death'])) }}">หมูตาย</a>
                        </li>
                    </ul>
                </div>

                <!-- Date Filter -->
                <input type="date" name="updated_at" class="form-control form-control-sm" style="width: auto;"
                    value="{{ request('updated_at') }}" onchange="this.form.submit()">

                <!-- Sort Dropdown (Orange) -->
                <div class="dropdown">
                    <button class="btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"
                        style="background-color: #FF6500; color: white; border: none;">
                        <i class="bi bi-sort-down"></i>
                        @if (request('sort_by') == 'created_at')
                            สร้างเมื่อ
                        @elseif(request('sort_by') == 'updated_at')
                            แก้ไขล่าสุด
                        @else
                            วันที่
                        @endif
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item {{ request('sort_by') == 'updated_at' ? 'active' : '' }}"
                                href="{{ route('dairy_records.index', array_merge(request()->all(), ['sort_by' => 'updated_at'])) }}">วันที่</a>
                        </li>
                        <li><a class="dropdown-item {{ request('sort_by') == 'created_at' ? 'active' : '' }}"
                                href="{{ route('dairy_records.index', array_merge(request()->all(), ['sort_by' => 'created_at'])) }}">สร้างเมื่อ</a>
                        </li>
                    </ul>
                </div>

                <!-- Right side buttons -->
                <div class="ms-auto d-flex gap-2">
                    <a class="btn btn-outline-success btn-sm" href="{{ route('dairy_records.export.csv') }}">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i> CSV
                    </a>
                    <a class="btn btn-outline-danger btn-sm" href="{{ route('dairy_records.export.pdf') }}">
                        <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                    </a>
                    <a href="{{ route('dairy_records.record') }}" class="btn btn-success btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> บันทึกใหม่
                    </a>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="card-custom-secondary mt-3">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-header-custom">
                        <tr>
                            <th class="text-center">ลำดับ</th>
                            <th class="text-center">วันที่</th>
                            <th class="text-center">ฟาร์ม</th>
                            <th class="text-center">รุ่น</th>
                            <th class="text-center">เล้า</th>
                            <th class="text-center">ประเภท</th>
                            <th class="text-center">รายละเอียด</th>
                            <th class="text-center">จำนวน</th>
                            <th class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dairyRecords as $index => $record)
                            <tr class="clickable-row" data-bs-toggle="modal"
                                data-bs-target="#viewModal{{ $record->id }}">
                                <td class="text-center">{{ $dairyRecords->firstItem() + $index }}</td>
                                <td class="text-center">{{ \Carbon\Carbon::parse($record->updated_at)->format('d/m/Y') }}
                                </td>
                                <td class="text-center">{{ $record->batch->farm->farm_name ?? '-' }}</td>
                                <td class="text-center">{{ $record->batch->batch_code ?? '-' }}</td>
                                <td class="text-center">{{ $record->batch->pen->barn->barn_code ?? '-' }}</td>
                                <td class="text-center">
                                    @if ($record->type == 'food')
                                        <span class="badge bg-success">อาหาร</span>
                                    @elseif($record->type == 'treatment')
                                        <span class="badge bg-warning">การรักษา</span>
                                    @else
                                        <span class="badge bg-danger">หมูตาย</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ Str::limit($record->details ?? '-', 30) }}</td>
                                <td class="text-center">{{ $record->quantity ?? '-' }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                        data-bs-target="#viewModal{{ $record->id }}">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="{{ route('dairy_records.edit', $record->id) }}"
                                        class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-danger">❌ ไม่มีข้อมูลบันทึกประจำวัน</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-between mt-3">
            <div>
                แสดง {{ $dairyRecords->firstItem() ?? 0 }} ถึง {{ $dairyRecords->lastItem() ?? 0 }} จาก
                {{ $dairyRecords->total() ?? 0 }} แถว
            </div>
            <div>
                {{ $dairyRecords->withQueryString()->links() }}
            </div>
        </div>
    </div>

    {{-- View Modals --}}
    @foreach ($dairyRecords as $record)
        <div class="modal fade" id="viewModal{{ $record->id }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">รายละเอียดบันทึกประจำวัน</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-sm">
                            <tr>
                                <td width="30%"><strong>วันที่:</strong></td>
                                <td>{{ \Carbon\Carbon::parse($record->updated_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>ฟาร์ม:</strong></td>
                                <td>{{ $record->batch->farm->farm_name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>รุ่น:</strong></td>
                                <td>{{ $record->batch->batch_code ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>เล้า:</strong></td>
                                <td>{{ $record->batch->pen->barn->barn_code ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>ประเภท:</strong></td>
                                <td>
                                    @if ($record->type == 'food')
                                        <span class="badge bg-success">อาหาร</span>
                                    @elseif($record->type == 'treatment')
                                        <span class="badge bg-warning">การรักษา</span>
                                    @else
                                        <span class="badge bg-danger">หมูตาย</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>รายละเอียด:</strong></td>
                                <td>{{ $record->details ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>จำนวน:</strong></td>
                                <td>{{ $record->quantity ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <a href="{{ route('dairy_records.edit', $record->id) }}" class="btn btn-warning">
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
        <script src="{{ asset('admin/js/common-dropdowns.js') }}"></script>
    @endpush
@endsection
