{{-- resources/views/batches/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'ข้อมูลรุ่นหมู')

@section('content')
    <div class="container my-5">
        <div class="card-header">
            <h1 class="text-center">ข้อมูลรุ่นหมู (Batches)</h1>
        </div>
        <div class="py-2"></div>

        {{-- Toolbar --}}
        <div class="card-custom-secondary mb-3">
            <form method="GET" action="{{ route('batches.index') }}" class="d-flex align-items-center gap-2 flex-wrap"
                id="filterForm">

                <!-- Date Filter (Orange) -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="dateFilterBtn"
                        data-bs-toggle="dropdown">
                        <i class="bi bi-calendar-event"></i>
                        @if (request('selected_date') == 'today')
                            วันนี้
                        @elseif(request('selected_date') == 'this_week')
                            สัปดาห์นี้
                        @elseif(request('selected_date') == 'this_month')
                            เดือนนี้
                        @elseif(request('selected_date') == 'this_year')
                            ปีนี้
                        @else
                            วันที่ทั้งหมด
                        @endif
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item {{ request('selected_date') == '' ? 'active' : '' }}"
                                href="{{ route('batches.index', array_merge(request()->except('selected_date'), [])) }}">วันที่ทั้งหมด</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'today' ? 'active' : '' }}"
                                href="{{ route('batches.index', array_merge(request()->all(), ['selected_date' => 'today'])) }}">วันนี้</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'this_week' ? 'active' : '' }}"
                                href="{{ route('batches.index', array_merge(request()->all(), ['selected_date' => 'this_week'])) }}">สัปดาห์นี้</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'this_month' ? 'active' : '' }}"
                                href="{{ route('batches.index', array_merge(request()->all(), ['selected_date' => 'this_month'])) }}">เดือนนี้</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'this_year' ? 'active' : '' }}"
                                href="{{ route('batches.index', array_merge(request()->all(), ['selected_date' => 'this_year'])) }}">ปีนี้</a>
                        </li>
                    </ul>
                </div>

                <!-- Farm Filter (Dark Blue) -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="farmFilterBtn"
                        data-bs-toggle="dropdown">
                        <i class="bi bi-building"></i>
                        {{ request('farm_id') ? $farms->find(request('farm_id'))->farm_name ?? 'ฟาร์ม' : 'ฟาร์มทั้งหมด' }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item {{ request('farm_id') == '' ? 'active' : '' }}"
                                href="{{ route('batches.index', array_merge(request()->except('farm_id'), [])) }}">ฟาร์มทั้งหมด</a>
                        </li>
                        @foreach ($farms as $farm)
                            <li><a class="dropdown-item {{ request('farm_id') == $farm->id ? 'active' : '' }}"
                                    href="{{ route('batches.index', array_merge(request()->all(), ['farm_id' => $farm->id])) }}">{{ $farm->farm_name }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Batch Filter -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="batchFilterBtn"
                        data-bs-toggle="dropdown">
                        <i class="bi bi-diagram-3"></i>
                        {{ request('batch_id') ? $allBatches->find(request('batch_id'))->batch_code ?? 'รุ่น' : 'รุ่นทั้งหมด' }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item {{ request('batch_id') == '' ? 'active' : '' }}"
                                href="{{ route('batches.index', array_merge(request()->except('batch_id'), [])) }}">รุ่นทั้งหมด</a>
                        </li>
                        @foreach ($allBatches as $batch)
                            <li><a class="dropdown-item {{ request('batch_id') == $batch->id ? 'active' : '' }}"
                                    href="{{ route('batches.index', array_merge(request()->all(), ['batch_id' => $batch->id])) }}">{{ $batch->batch_code }}</a>
                            </li>
                        @endforeach
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
                                href="{{ route('batches.index', array_merge(request()->all(), ['sort' => 'name_asc'])) }}">ชื่อ
                                (ก-ฮ)</a></li>
                        <li><a class="dropdown-item {{ request('sort') == 'name_desc' ? 'active' : '' }}"
                                href="{{ route('batches.index', array_merge(request()->all(), ['sort' => 'name_desc'])) }}">ชื่อ
                                (ฮ-ก)</a></li>
                        <li><a class="dropdown-item {{ request('sort') == 'quantity_asc' ? 'active' : '' }}"
                                href="{{ route('batches.index', array_merge(request()->all(), ['sort' => 'quantity_asc'])) }}">จำนวนน้อย
                                → มาก</a></li>
                        <li><a class="dropdown-item {{ request('sort') == 'quantity_desc' ? 'active' : '' }}"
                                href="{{ route('batches.index', array_merge(request()->all(), ['sort' => 'quantity_desc'])) }}">จำนวนมาก
                                → น้อย</a></li>
                    </ul>
                </div>

                <!-- Per Page -->
                @include('components.per-page-dropdown')

                <!-- Show Cancelled Batches Checkbox -->
                <div class="form-check ms-2">
                    <input class="form-check-input" type="checkbox" id="showCancelledCheckbox"
                        {{ request('show_cancelled') ? 'checked' : '' }}
                        onchange="toggleCancelled()">
                    <label class="form-check-label" for="showCancelledCheckbox">
                        <i class="bi bi-eye"></i> แสดงรุ่นที่ยกเลิก
                    </label>
                </div>

                <div class="ms-auto d-flex gap-2">
                    <a href="{{ route('batches.export.csv') }}" class="btn btn-sm btn-success">
                        <i class="bi bi-file-earmark-excel"></i> Export CSV
                    </a>
                    <a href="{{ route('batches.export.pdf') }}" class="btn btn-sm btn-danger">
                        <i class="bi bi-file-earmark-pdf"></i> Export PDF
                    </a>
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                        data-bs-target="#createModal">
                        <i class="bi bi-plus-circle"></i> เพิ่มรุ่นใหม่
                    </button>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-primary mb-0">
                <thead class="table-header-custom">
                    <tr>
                        <th class="text-center">ชื่อฟาร์ม</th>
                        <th class="text-center">รหัสรุ่น</th>
                        <th class="text-center">จำนวนเล้า</th>
                        <th class="text-center">จำนวนคอก</th>
                        <th class="text-center">น้ำหนักหมูรวม</th>
                        <th class="text-center">น้ำหนักเฉลี่ย/ตัว</th>
                        <th class="text-center">จำนวนหมูรวม</th>
                        <th class="text-center">หมูคงเหลือ</th>
                        <th class="text-center">ราคารวม</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-center">หมายเหตุ</th>
                        <th class="text-center">
                            <a href="{{ route('batches.index', array_merge(request()->all(), ['sort_by' => 'start_date', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}"
                                class="text-white text-decoration-none d-flex align-items-center justify-content-center gap-1">
                                วันที่เริ่มต้น
                                @if (request('sort_by') == 'start_date')
                                    <i class="bi bi-{{ request('sort_order') == 'asc' ? 'sort-up' : 'sort-down' }}"></i>
                                @else
                                    <i class="bi bi-arrow-down-up"></i>
                                @endif
                            </a>
                        </th>
                        <th class="text-center">
                            <a href="{{ route('batches.index', array_merge(request()->all(), ['sort_by' => 'end_date', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}"
                                class="text-white text-decoration-none d-flex align-items-center justify-content-center gap-1">
                                วันที่สิ้นสุด
                                @if (request('sort_by') == 'end_date')
                                    <i class="bi bi-{{ request('sort_order') == 'asc' ? 'sort-up' : 'sort-down' }}"></i>
                                @else
                                    <i class="bi bi-arrow-down-up"></i>
                                @endif
                            </a>
                        </th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $batch)
                        <tr class="clickable-row" data-row-click="#viewModal{{ $batch->id }}">
                            <td class="text-center">{{ $batch->farm->farm_name ?? '-' }}</td>
                            <td class="text-center">{{ $batch->batch_code }}</td>

                            {{-- เลือก barn แรกของ farm --}}
                            <td class="text-center">{{ $batch->farm->barns->count() ?? '-' }}</td>

                            {{-- เลือก pen แรกของ barn --}}
                            <td class="text-center">{{ $batch->farm->barns->first()->pens->count() ?? '-' }}</td>

                            <td class="text-center">
                                <strong>{{ number_format($batch->pig_entry_records->sum('total_pig_weight') ?? 0, 2) }}</strong>
                                กก.
                            </td>
                            <td class="text-center">
                                @php
                                    $totalWeight = $batch->pig_entry_records->sum('total_pig_weight') ?? 0;
                                    $totalAmount = $batch->pig_entry_records->sum('total_pig_amount') ?? 0;
                                    $avgWeight = $totalAmount > 0 ? $totalWeight / $totalAmount : 0;
                                @endphp
                                {{ number_format($avgWeight, 2) }} กก.
                            </td>
                            <td class="text-center">
                                <strong>{{ number_format($batch->pig_entry_records->sum('total_pig_amount') ?? 0) }}</strong>
                            </td>
                            <td class="text-center">
                                <strong>{{ number_format($batch->current_quantity ?? $batch->total_pig_amount) }}</strong>
                            </td>
                            <td class="text-center">
                                <strong>{{ number_format(
                                    $batch->costs->sum('total_price') + $batch->costs->sum('excess_weight_cost') + $batch->costs->sum('transport_cost'),
                                    2,
                                ) }}
                                    ฿</strong>
                            </td>
                            <td class="text-center">
                                @if ($batch->status == 'กำลังเลี้ยง')
                                    <span class="badge bg-success">กำลังเลี้ยง</span>
                                @elseif($batch->status == 'เสร็จสิ้น')
                                    <span class="badge bg-secondary">เสร็จสิ้น</span>
                                @elseif($batch->status == 'cancelled')
                                    <span class="badge bg-danger">ยกเลิก</span>
                                @else
                                    <span class="badge bg-dark">-</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $batch->note ?? '-' }}</td>
                            <td class="text-center">{{ $batch->start_date ?? '-' }}</td>
                            <td class="text-center">{{ $batch->end_date ?? '-' }}</td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-info"
                                    onclick="event.stopPropagation(); new bootstrap.Modal(document.getElementById('viewModal{{ $batch->id }}')).show();">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @if ($batch->status != 'เสร็จสิ้น' && $batch->status != 'cancelled')
                                    <button class="btn btn-warning btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#editModal{{ $batch->id }}"
                                        onclick="event.stopPropagation()">แก้ไข</button>
                                @endif

                                {{-- Soft Delete: Update Status to 'cancelled' --}}
                                @if ($batch->status != 'cancelled')
                                    <form action="{{ route('batches.delete', $batch->id) }}" method="POST"
                                        style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('คุณแน่ใจไหมว่าจะยกเลิกรุ่นนี้?\n(สถานะจะถูกเปลี่ยนเป็น cancelled)')">
                                            <i class="bi bi-trash"></i> ยกเลิก
                                        </button>
                                    </form>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="bi bi-x-circle"></i> ยกเลิกแล้ว
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="text-danger">❌ ไม่มีข้อมูล Batch</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-between mt-3">
            <div>
                แสดง {{ $batches->firstItem() ?? 0 }} ถึง {{ $batches->lastItem() ?? 0 }} จาก
                {{ $batches->total() ?? 0 }} แถว
            </div>
            <div>
                {{ $batches->withQueryString()->links() }}
            </div>
        </div>
    </div>

    </div>
    </div>

    {{-- Modals (Outside table, Inside loop) --}}
    @foreach ($batches as $batch)
        {{-- Modal View --}}
        <div class="modal fade" id="viewModal{{ $batch->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-layers"></i> รายละเอียดรุ่นหมู - {{ $batch->batch_code }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="bi bi-info-circle"></i> ข้อมูลทั่วไป
                                </h6>
                                <table class="table table-secondary table-sm">
                                    <tr>
                                        <td width="35%"><strong>ฟาร์ม:</strong></td>
                                        <td>{{ $batch->farm->farm_name ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>รหัสรุ่น:</strong></td>
                                        <td><code class="text-info">{{ $batch->batch_code }}</code></td>
                                    </tr>
                                    <tr>
                                        <td><strong>จำนวนเล้า:</strong></td>
                                        <td>{{ $batch->farm->barns->count() ?? '-' }} เล้า</td>
                                    </tr>
                                    <tr>
                                        <td><strong>จำนวนคอก:</strong></td>
                                        <td>{{ $batch->farm->barns->first()->pens->count() ?? '-' }} คอก</td>
                                    </tr>
                                    <tr>
                                        <td><strong>สถานะ:</strong></td>
                                        <td>
                                            @if ($batch->status == 'กำลังเลี้ยง')
                                                <span class="badge bg-success">
                                                    <i class="bi bi-hourglass-split"></i> {{ $batch->status }}
                                                </span>
                                            @elseif($batch->status == 'เสร็จสิ้น')
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-check-circle"></i> {{ $batch->status }}
                                                </span>
                                            @elseif($batch->status == 'cancelled')
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-x-circle"></i> {{ $batch->status }}
                                                </span>
                                            @else
                                                <span class="badge bg-dark">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="bi bi-graph-up"></i> ข้อมูลการเลี้ยง
                                </h6>
                                <table class="table table-secondary table-sm">
                                    <tr>
                                        <td width="35%"><strong>น้ำหนักรวม:</strong></td>
                                        <td>
                                            <strong class="text-success">
                                                {{ number_format($batch->pig_entry_records->sum('total_pig_weight') ?? 0, 2) }}
                                                กก.
                                            </strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>วันเริ่มต้น:</strong></td>
                                        <td>{{ $batch->start_date ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>วันสิ้นสุด:</strong></td>
                                        <td>{{ $batch->end_date ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        @if ($batch->note)
                            <hr>
                            <h6 class="text-primary mb-2">
                                <i class="bi bi-chat-left-text"></i> หมายเหตุ
                            </h6>
                            <div class="bg-light p-3 rounded">
                                <p class="mb-0">{{ $batch->note }}</p>
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

        {{-- Modal Edit --}}
        <div class="modal fade" id="editModal{{ $batch->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content bg-dark text-light">
                    <div class="modal-header">
                        <h5>แก้ไขรุ่นหมู (Batch)</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('batches.update', $batch->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="mb-3">
                                <label>สถานะ <span class="text-danger">*</span></label>
                                <div class="dropdown">
                                    <button
                                        class="btn btn-primary dropdown-toggle w-100 d-flex justify-content-between align-items-center"
                                        type="button" id="statusDropdownBtn{{ $batch->id }}"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <span>{{ $batch->status }}</span>
                                    </button>
                                    <ul class="dropdown-menu w-100">
                                        <li>
                                            <a class="dropdown-item" href="#" data-status="กำลังเลี้ยง"
                                                onclick="updateStatusDropdown(event, {{ $batch->id }})">
                                                กำลังเลี้ยง
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" data-status="เสร็จสิ้น"
                                                onclick="updateStatusDropdown(event, {{ $batch->id }})">
                                                เสร็จสิ้น
                                            </a>
                                        </li>
                                    </ul>
                                    <input type="hidden" name="status" id="status{{ $batch->id }}"
                                        value="{{ $batch->status }}" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>โน๊ต</label>
                                <textarea name="note" class="form-control">{{ $batch->note }}</textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">บันทึก</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Modal Create --}}
    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('batches.create') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">เพิ่มรุ่นใหม่</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ฟาร์ม <span class="text-danger">*</span></label>
                                <div class="dropdown">
                                    <button
                                        class="btn btn-primary dropdown-toggle w-100 d-flex justify-content-between align-items-center"
                                        type="button" id="farmDropdownBtn" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <span>-- เลือกฟาร์ม --</span>
                                    </button>
                                    <ul class="dropdown-menu w-100" id="farmDropdownMenu">
                                        @foreach ($farms as $farm)
                                            <li>
                                                <a class="dropdown-item" href="#"
                                                    data-farm-id="{{ $farm->id }}">
                                                    {{ $farm->farm_name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <input type="hidden" name="farm_id" id="farmSelect" value="" required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">รหัสรุ่น</label>
                                <input type="text" name="batch_code" class="form-control" required>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">หมายเหตุ</label>
                                <textarea name="note" class="form-control" rows="3"></textarea>
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

    @push('scripts')
        {{-- Status Dropdown Handler for Edit Modal --}}
        <script>
            function updateStatusDropdown(event, batchId) {
                event.preventDefault();
                const status = event.target.getAttribute('data-status');
                const statusText = event.target.textContent.trim();

                // Update button text and hidden input
                document.getElementById('statusDropdownBtn' + batchId).querySelector('span').textContent = statusText;
                document.getElementById('status' + batchId).value = status;
            }
        </script>

        {{-- Farm Dropdown Handler for Create Modal --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const farmDropdownMenu = document.getElementById('farmDropdownMenu');
                const farmDropdownBtn = document.getElementById('farmDropdownBtn');
                const farmSelect = document.getElementById('farmSelect');

                // Handle farm dropdown clicks
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
            });
        </script>

        {{-- Auto-submit filter form --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const filterForm = document.getElementById('filterForm');
                const farmFilter = document.getElementById('farmFilter');
                const batchFilter = document.getElementById('batchFilter');
                const allFilters = filterForm.querySelectorAll('select');

                // เมื่อเลือกฟาร์ม
                farmFilter.addEventListener('change', function() {
                    const farmId = this.value;

                    // รีเซ็ต batch filter
                    batchFilter.innerHTML = '<option value="">รุ่นทั้งหมด</option>';

                    if (farmId) {
                        // โหลด batches จาก API
                        fetch('/get-batches/' + farmId)
                            .then(response => response.json())
                            .then(data => {
                                data.forEach(batch => {
                                    const option = document.createElement('option');
                                    option.value = batch.id;
                                    option.textContent = batch.batch_code;
                                    batchFilter.appendChild(option);
                                });
                                // Submit form หลังจากโหลด batches เสร็จ
                                filterForm.submit();
                            })
                            .catch(error => {
                                console.error('Error loading batches:', error);
                                // Submit form แม้เกิด error
                                filterForm.submit();
                            });
                    } else {
                        // ถ้าเลือก "ฟาร์มทั้งหมด" ให้ submit ทันที
                        filterForm.submit();
                    }
                });

                // Auto-submit สำหรับ filters อื่นๆ (ไม่รวม farm)
                allFilters.forEach(filter => {
                    if (filter.id !== 'farmFilter') {
                        filter.addEventListener('change', function() {
                            filterForm.submit();
                        });
                    }
                });
            });
        </script>

        {{-- Flatpickr Script --}}
        <script>
            // Toggle Show Cancelled Batches
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
        </script>

        {{-- Flatpickr Script --}}
        <script>
            @foreach ($batches as $batch)
                flatpickr("#end_date_{{ $batch->id }}", {
                    enableTime: true,
                    dateFormat: "d/m/Y H:i",
                    maxDate: "today",
                    time_24hr: true,
                });
            @endforeach
        </script>

        {{-- Include Clickable Row Script --}}
        <script src="{{ asset('admin/js/common-table-click.js') }}"></script>
    @endpush
@endsection
