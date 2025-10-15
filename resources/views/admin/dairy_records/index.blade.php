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

                <!-- Farm Card Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"
                        id="farmDropdownBtn">
                        <i class="bi bi-building"></i>
                        {{ request('farm_id') ? $farms->find(request('farm_id'))->farm_name ?? 'ฟาร์ม' : 'ฟาร์มทั้งหมด' }}
                    </button>
                    <ul class="dropdown-menu" id="farmDropdownMenu">
                        <li><a class="dropdown-item farm-link" data-farm-id=""
                                href="{{ route('dairy_records.index', array_merge(request()->except(['farm_id', 'batch_id']), [])) }}">ฟาร์มทั้งหมด</a>
                        </li>
                        @foreach ($farms as $farm)
                            <li><a class="dropdown-item farm-link {{ request('farm_id') == $farm->id ? 'active' : '' }}"
                                    data-farm-id="{{ $farm->id }}"
                                    href="{{ route('dairy_records.index', array_merge(request()->except('batch_id'), ['farm_id' => $farm->id])) }}">
                                    {{ $farm->farm_name }}
                                </a></li>
                        @endforeach
                    </ul>
                </div>

                <!-- Batch Card Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"
                        id="batchDropdownBtn">
                        <i class="bi bi-layers"></i>
                        {{ request('batch_id') ? $batches->find(request('batch_id'))->batch_code ?? 'รุ่น' : 'รุ่นทั้งหมด' }}
                    </button>
                    <ul class="dropdown-menu" id="batchDropdownMenu">
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
                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"
                        id="barnDropdownBtn">
                        <i class="bi bi-house"></i>
                        {{ request('barn_id') ? $barns->find(request('barn_id'))->barn_code ?? 'เล้า' : 'เล้าทั้งหมด' }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item"
                                href="{{ route('dairy_records.index', array_merge(request()->except('barn_id'), [])) }}">เล้าทั้งหมด</a>
                        </li>
                        @foreach ($barns as $barn)
                            <li><a class=" dropdown-item {{ request('barn_id') == $barn->id ? 'active' : '' }}"
                                    href="{{ route('dairy_records.index', array_merge(request()->all(), ['barn_id' => $barn->id])) }}">
                                    {{ $barn->barn_code }}
                                </a></li>
                        @endforeach
                    </ul>
                </div>

                <!-- Type Dropdown (Orange) -->
                <div class="dropdown">
                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"
                        id="typeDropdownBtn">
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

                <!-- Sort Dropdown (Orange) -->
                <div class="dropdown">
                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
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
                                href="{{ route('dairy_records.index', array_merge(request()->all(), ['sort' => 'name_asc'])) }}">ชื่อ
                                (ก-ฮ)</a></li>
                        <li><a class="dropdown-item {{ request('sort') == 'name_desc' ? 'active' : '' }}"
                                href="{{ route('dairy_records.index', array_merge(request()->all(), ['sort' => 'name_desc'])) }}">ชื่อ
                                (ฮ-ก)</a></li>
                        <li><a class="dropdown-item {{ request('sort') == 'quantity_asc' ? 'active' : '' }}"
                                href="{{ route('dairy_records.index', array_merge(request()->all(), ['sort' => 'quantity_asc'])) }}">จำนวนน้อย
                                → มาก</a></li>
                        <li><a class="dropdown-item {{ request('sort') == 'quantity_desc' ? 'active' : '' }}"
                                href="{{ route('dairy_records.index', array_merge(request()->all(), ['sort' => 'quantity_desc'])) }}">จำนวนมาก
                                → น้อย</a></li>
                    </ul>
                </div>

                <!-- Per Page -->
                <select name="per_page" class="per-page form-select form-select-sm w-20 filter-select-orange">
                    @foreach ([10, 25, 50, 100] as $n)
                        <option value="{{ $n }}" {{ request('per_page', 10) == $n ? 'selected' : '' }}>
                            {{ $n }} แถว</option>
                    @endforeach
                </select>

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
        <div class="table-responsive">
            <table class="table table-primary mb-0">
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
                        <tr class="clickable-row" data-bs-toggle="modal" data-bs-target="#viewModal{{ $record->id }}">
                            <td class="text-center">{{ $dairyRecords->firstItem() + $index }}</td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($record->updated_at)->format('d/m/Y') }}</td>
                            <td class="text-center">{{ $record->batch->farm->farm_name ?? '-' }}</td>
                            <td class="text-center">{{ $record->batch->batch_code ?? '-' }}</td>
                            <td class="text-center">{{ $record->display_barn }}</td>
                            <td class="text-center">
                                @php
                                    $typeBadge = '-';
                                    if (request('type')) {
                                        if (request('type') == 'food') {
                                            $typeBadge = '<span class="badge bg-success">อาหาร</span>';
                                        } elseif (request('type') == 'treatment') {
                                            $typeBadge = '<span class="badge bg-warning">การรักษา</span>';
                                        } elseif (request('type') == 'death') {
                                            $typeBadge = '<span class="badge bg-danger">หมูตาย</span>';
                                        }
                                    } else {
                                        if ($record->dairy_storehouse_uses->count()) {
                                            $typeBadge = '<span class="badge bg-success">อาหาร</span>';
                                        } elseif ($record->batch_treatments->count()) {
                                            $typeBadge = '<span class="badge bg-warning">การรักษา</span>';
                                        } elseif ($record->pig_deaths->count()) {
                                            $typeBadge = '<span class="badge bg-danger">หมูตาย</span>';
                                        }
                                    }
                                @endphp
                                {!! $typeBadge !!}
                            </td>
                            <td class="text-center">{{ Str::limit($record->display_details ?? '-', 30) }}</td>
                            <td class="text-center">{{ $record->display_quantity }}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                    data-bs-target="#viewModal{{ $record->id }}">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <a href="{{ route('dairy_records.edit', $record->id) }}" class="btn btn-sm btn-warning">
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
                        <table class="table table-secondary table-sm">
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
                                <td>{{ $record->display_barn }}</td>
                            </tr>
                            <tr>
                                <td><strong>ประเภท:</strong></td>
                                <td>
                                    @php
                                        $typeBadge = '-';
                                        if (request('type')) {
                                            if (request('type') == 'food') {
                                                $typeBadge = '<span class="badge bg-success">อาหาร</span>';
                                            } elseif (request('type') == 'treatment') {
                                                $typeBadge = '<span class="badge bg-warning">การรักษา</span>';
                                            } elseif (request('type') == 'death') {
                                                $typeBadge = '<span class="badge bg-danger">หมูตาย</span>';
                                            }
                                        } else {
                                            if ($record->dairy_storehouse_uses->count()) {
                                                $typeBadge = '<span class="badge bg-success">อาหาร</span>';
                                            } elseif ($record->batch_treatments->count()) {
                                                $typeBadge = '<span class="badge bg-warning">การรักษา</span>';
                                            } elseif ($record->pig_deaths->count()) {
                                                $typeBadge = '<span class="badge bg-danger">หมูตาย</span>';
                                            }
                                        }
                                    @endphp
                                    {!! $typeBadge !!}
                                </td>
                            </tr>
                            <tr>
                                <td><strong>รายละเอียด:</strong></td>
                                <td>{{ $record->display_details }}</td>
                            </tr>
                            <tr>
                                <td><strong>จำนวน:</strong></td>
                                <td>{{ $record->display_quantity }}</td>
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

    @push('scripts')
        <script src="{{ asset('admin/js/common-dropdowns.js') }}"></script>

        {{-- Farm to Batch Filter Script --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const farmLinks = document.querySelectorAll('.farm-link');
                const batchDropdownMenu = document.getElementById('batchDropdownMenu');
                const batchDropdownBtn = document.getElementById('batchDropdownBtn');

                farmLinks.forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();

                        const farmId = this.getAttribute('data-farm-id');
                        const targetUrl = this.getAttribute('href');

                        if (farmId) {
                            // โหลด batches จาก API
                            fetch('/get-batches/' + farmId)
                                .then(response => response.json())
                                .then(data => {
                                    // อัพเดท batch dropdown
                                    updateBatchDropdown(data, targetUrl);
                                    // Redirect
                                    window.location.href = targetUrl;
                                })
                                .catch(error => {
                                    console.error('Error loading batches:', error);
                                    // Redirect ถึงแม้เกิด error
                                    window.location.href = targetUrl;
                                });
                        } else {
                            // ถ้าเลือก "ฟาร์มทั้งหมด" redirect ทันที
                            window.location.href = targetUrl;
                        }
                    });
                });

                function updateBatchDropdown(batches, currentUrl) {
                    // สร้าง URL object เพื่อจัดการ query parameters
                    const url = new URL(currentUrl, window.location.origin);
                    const params = new URLSearchParams(url.search);

                    // ลบ batch_id ออกจาก params
                    params.delete('batch_id');

                    // สร้าง base URL สำหรับ batch links
                    const baseUrl = url.pathname + '?' + params.toString();

                    // สร้าง HTML ใหม่
                    let html = `<li><a class="dropdown-item" href="${baseUrl}">รุ่นทั้งหมด</a></li>`;

                    batches.forEach(batch => {
                        const batchParams = new URLSearchParams(params);
                        batchParams.set('batch_id', batch.id);
                        const batchUrl = url.pathname + '?' + batchParams.toString();
                        html += `<li><a class="dropdown-item" href="${batchUrl}">${batch.batch_code}</a></li>`;
                    });

                    batchDropdownMenu.innerHTML = html;

                    // อัพเดทปุ่ม dropdown
                    batchDropdownBtn.innerHTML = '<i class="bi bi-layers"></i> รุ่นทั้งหมด';
                }
            });
        </script>
    @endpush
@endsection
