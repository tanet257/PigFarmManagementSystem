@extends('layouts.admin')

@section('title', 'รายงานความเคลื่อนไหวของสต็อก')

@section('content')
    <div class="container my-5">
        <div class="card-header">
            <h1 class="text-center">รายงานความเคลื่อนไหวของสต็อก (Inventory Movement)</h1>
        </div>
        <div class="py-2"></div>

        {{-- Toolbar --}}
        <div class="card-custom-secondary mb-3">
            <form method="GET" action="{{ route('inventory_movements.index') }}"
                class="d-flex align-items-center gap-2 flex-wrap" id="filterForm">

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
                                href="{{ route('inventory_movements.index', array_merge(request()->except('selected_date'), [])) }}">วันที่ทั้งหมด</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'today' ? 'active' : '' }}"
                                href="{{ route('inventory_movements.index', array_merge(request()->all(), ['selected_date' => 'today'])) }}">วันนี้</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'this_week' ? 'active' : '' }}"
                                href="{{ route('inventory_movements.index', array_merge(request()->all(), ['selected_date' => 'this_week'])) }}">สัปดาห์นี้</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'this_month' ? 'active' : '' }}"
                                href="{{ route('inventory_movements.index', array_merge(request()->all(), ['selected_date' => 'this_month'])) }}">เดือนนี้</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'this_year' ? 'active' : '' }}"
                                href="{{ route('inventory_movements.index', array_merge(request()->all(), ['selected_date' => 'this_year'])) }}">ปีนี้</a>
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
                                href="{{ route('inventory_movements.index', array_merge(request()->except('farm_id'), [])) }}">ฟาร์มทั้งหมด</a>
                        </li>
                        @foreach ($farms as $farm)
                            <li><a class="dropdown-item {{ request('farm_id') == $farm->id ? 'active' : '' }}"
                                    href="{{ route('inventory_movements.index', array_merge(request()->all(), ['farm_id' => $farm->id])) }}">{{ $farm->farm_name }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Batch Filter (Dark Blue) -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="batchFilterBtn"
                        data-bs-toggle="dropdown">
                        <i class="bi bi-diagram-3"></i>
                        {{ request('batch_id') ? $batches->find(request('batch_id'))->batch_code ?? 'รุ่น' : 'รุ่นทั้งหมด' }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item {{ request('batch_id') == '' ? 'active' : '' }}"
                                href="{{ route('inventory_movements.index', array_merge(request()->except('batch_id'), [])) }}">รุ่นทั้งหมด</a>
                        </li>
                        @foreach ($batches as $batch)
                            <li><a class="dropdown-item {{ request('batch_id') == $batch->id ? 'active' : '' }}"
                                    href="{{ route('inventory_movements.index', array_merge(request()->all(), ['batch_id' => $batch->id])) }}">{{ $batch->batch_code }}</a>
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
                                href="{{ route('inventory_movements.index', array_merge(request()->all(), ['sort' => 'name_asc'])) }}">ชื่อ
                                (ก-ฮ)</a></li>
                        <li><a class="dropdown-item {{ request('sort') == 'name_desc' ? 'active' : '' }}"
                                href="{{ route('inventory_movements.index', array_merge(request()->all(), ['sort' => 'name_desc'])) }}">ชื่อ
                                (ฮ-ก)</a></li>
                        <li><a class="dropdown-item {{ request('sort') == 'quantity_asc' ? 'active' : '' }}"
                                href="{{ route('inventory_movements.index', array_merge(request()->all(), ['sort' => 'quantity_asc'])) }}">จำนวนน้อย
                                → มาก</a></li>
                        <li><a class="dropdown-item {{ request('sort') == 'quantity_desc' ? 'active' : '' }}"
                                href="{{ route('inventory_movements.index', array_merge(request()->all(), ['sort' => 'quantity_desc'])) }}">จำนวนมาก
                                → น้อย</a></li>
                    </ul>
                </div>

                <!-- Per Page -->
                @include('components.per-page-dropdown')

                <!-- Show Cancelled Batches Checkbox -->
                <div class="form-check ms-2">
                    <input class="form-check-input" type="checkbox" id="showCancelledCheckboxInventory"
                        {{ request('show_cancelled') ? 'checked' : '' }}
                        onchange="toggleCancelledInventory()">
                    <label class="form-check-label" for="showCancelledCheckboxInventory" title="แสดงรายการที่ยกเลิก">
                        <i class="bi bi-eye"></i>
                    </label>
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
                    <button type="button" class="btn btn-danger btn-sm" id="exportPdfBtn">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
                    </button>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-primary mb-0">
                <thead class="table-header-custom">
                    <tr>
                        <th class="text-center">
                            <a href="{{ route('inventory_movements.index', array_merge(request()->all(), ['sort_by' => 'date', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}"
                                class="text-white text-decoration-none d-flex align-items-center justify-content-center gap-1">
                                วันที่
                                @if (request('sort_by') == 'date')
                                    <i class="bi bi-{{ request('sort_order') == 'asc' ? 'sort-up' : 'sort-down' }}"></i>
                                @else
                                    <i class="bi bi-arrow-down-up"></i>
                                @endif
                            </a>
                        </th>
                        <th class="text-center">ชื่อฟาร์ม</th>
                        <th class="text-center">รหัสรุ่น</th>
                        <th class="text-center">ประเภทสินค้า</th>
                        <th class="text-center">รหัสสินค้า</th>
                        <th class="text-center">ชื่อสินค้า</th>
                        <th class="text-center">ประเภทการเปลี่ยนแปลง</th>
                        <th class="text-center">จำนวน</th>
                        <th class="text-center">หน่วย</th>
                        <th class="text-center">โน้ต</th>
                        <th class="text-center">ใบเสร็จ</th>
                        <th class="text-center">บันทึกเมื่อ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                        <tr class="clickable-row" data-row-click="#viewModal{{ $movement->id }}">
                            <td class="text-center">{{ $movement->date }}</td>
                            <td class="text-center">{{ $movement->storehouse->farm->farm_name ?? '-' }}</td>
                            <td class="text-center">{{ $movement->batch->batch_code ?? '-' }}</td>
                            <td class="text-center">{{ $movement->storehouse->item_type ?? '- ' }}</td>
                            <td class="text-center">{{ $movement->storehouse->item_code ?? '-' }}</td>
                            <td class="text-center">{{ $movement->storehouse->item_name ?? '-' }}</td>
                            <td class="text-center">
                                @if ($movement->change_type == 'in')
                                    <span class="badge bg-success">เข้า</span>
                                @elseif($movement->change_type == 'out')
                                    <span class="badge bg-danger">ออก</span>
                                @else
                                    <span class="badge bg-dark">-</span>
                                @endif
                            </td>
                            <td class="text-center"><strong>{{ $movement->quantity }}</strong></td>
                            <td class="text-center">
                                @php
                                    // ถ้าเป็นยา (medicine) ที่มี base_unit ให้แสดงแบบ ml ด้วย
                                    $storehouse = $movement->storehouse;
                                    $displayUnit = $movement->quantity_unit ?? $storehouse->unit ?? '-';

                                    // ถ้ามี base_unit (ยา/วัคซีน) ให้แสดง "100 ml (1 ขวด)"
                                    if ($storehouse && $storehouse->base_unit && $storehouse->quantity_per_unit) {
                                        $baseQuantity = $movement->quantity * $storehouse->quantity_per_unit * ($storehouse->conversion_rate ?? 1);
                                        $displayUnit = "{$baseQuantity} {$storehouse->base_unit} ({$movement->quantity} {$storehouse->unit})";
                                    }
                                @endphp
                                {{ $displayUnit }}
                            </td>
                            <td class="text-center">{{ $movement->note ?? '-' }}</td>
                            <td class="text-center">
                                @if ($movement->cost && !empty($movement->cost->receipt_file))
                                    @php
                                        $file = (string) $movement->cost->receipt_file;
                                    @endphp

                                    @if (is_string($file) && Str::endsWith($file, ['.jpg', '.jpeg', '.png']))
                                        <a href="{{ $file }}" target="_blank">
                                            <img src="{{ $file }}" alt="Receipt"
                                                style="max-width:100px; max-height:100px; cursor: pointer; border-radius: 4px; object-fit: cover; transition: transform 0.2s;"
                                                onmouseover="this.style.transform='scale(1.05)'"
                                                onmouseout="this.style.transform='scale(1)'"
                                                title="คลิกเพื่อดูภาพในแท็บใหม่">
                                        </a>
                                    @else
                                        <a href="{{ $file }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-download"></i> ดาวน์โหลด
                                        </a>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center text-danger">ไม่มีข้อมูลความเคลื่อนไหว</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-between mt-3">
            <div>
                แสดง {{ $movements->firstItem() ?? 0 }} ถึง {{ $movements->lastItem() ?? 0 }} จาก
                {{ $movements->total() ?? 0 }} แถว
            </div>
            <div>
                {{ $movements->withQueryString()->links() }}
            </div>
        </div>
    </div>
    </div>

    {{-- View Modal --}}
    @foreach ($movements as $movement)
        <div class="modal fade" id="viewModal{{ $movement->id }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-box-seam"></i> รายละเอียดความเคลื่อนไหวของสต็อก
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
                                        <td width="40%"><strong>วันที่:</strong></td>
                                        <td>{{ \Carbon\Carbon::parse($movement->date)->format('d/m/Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>ฟาร์ม:</strong></td>
                                        <td>{{ $movement->storehouse->farm->farm_name ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>รุ่น:</strong></td>
                                        <td>{{ $movement->batch->batch_code ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>ประเภทการเปลี่ยน:</strong></td>
                                        <td>
                                            @if ($movement->change_type == 'in')
                                                <span class="badge bg-success">
                                                    <i class="bi bi-arrow-up-right"></i> เข้า
                                                </span>
                                            @elseif($movement->change_type == 'out')
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-arrow-down-left"></i> ออก
                                                </span>
                                            @else
                                                <span class="badge bg-dark">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>บันทึกเมื่อ:</strong></td>
                                        <td>
                                            <small>
                                                <i class="bi bi-calendar-event"></i>
                                                {{ $movement->created_at->format('d/m/Y H:i') }}
                                            </small>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="bi bi-box"></i> ข้อมูลสินค้า
                                </h6>
                                <table class="table table-secondary table-sm table-hover">
                                    <tr>
                                        <td width="40%"><strong>ประเภท:</strong></td>
                                        <td>{{ $movement->storehouse->item_type ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>รหัสสินค้า:</strong></td>
                                        <td>
                                            <code class="text-info">{{ $movement->storehouse->item_code ?? '-' }}</code>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>ชื่อสินค้า:</strong></td>
                                        <td>{{ $movement->storehouse->item_name ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>จำนวน:</strong></td>
                                        <td>
                                            <strong class="text-success">
                                                {{ number_format($movement->quantity, 2) }}
                                                {{ $movement->quantity_unit ?? $movement->storehouse->unit ?? 'หน่วย' }}
                                            </strong>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        @if ($movement->note)
                            <hr>
                            <h6 class="text-primary mb-2 ">
                                <i class="bi bi-chat-left-text"></i> หมายเหตุ
                            </h6>
                            <div class="bg-light p-3 rounded">
                                <p class="mb-0">{{ $movement->note }}</p>
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

    @push('scripts')
        <!-- Toggle Show Cancelled Batches -->
        <script>
            function toggleCancelledInventory() {
                const checkbox = document.getElementById('showCancelledCheckboxInventory');
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

        {{-- Auto-submit filters --}}
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

            // Export CSV
            document.getElementById('exportCsvBtn').addEventListener('click', function() {
                console.log('📥 [Inventory Movements] Exporting CSV');
                const params = new URLSearchParams(window.location.search);
                const dateFrom = document.getElementById('exportDateFrom').value;
                const dateTo = document.getElementById('exportDateTo').value;
                if (dateFrom) params.set('export_date_from', dateFrom);
                if (dateTo) params.set('export_date_to', dateTo);
                const url = `{{ route('inventory_movements.export.csv') }}?${params.toString()}`;
                window.location.href = url;
            });

            // Export PDF
            document.getElementById('exportPdfBtn').addEventListener('click', function() {
                console.log('📥 [Inventory Movements] Exporting PDF');
                const params = new URLSearchParams(window.location.search);
                const dateFrom = document.getElementById('exportDateFrom').value;
                const dateTo = document.getElementById('exportDateTo').value;
                if (dateFrom) params.set('export_date_from', dateFrom);
                if (dateTo) params.set('export_date_to', dateTo);
                const url = `{{ route('inventory_movements.export.pdf') }}?${params.toString()}`;
                window.location.href = url;
            });
        </script>
        <script src="{{ asset('admin/js/common-table-click.js') }}"></script>
    @endpush
@endsection
