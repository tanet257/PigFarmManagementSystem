@extends('layouts.admin')

@section('title', 'ดูหมูในฟาร์ม')

@section('content')
    <div class="container my-5" data-page="batch-pen-allocations">
        <div class="card-header">
            <h1 class="text-center">ดูหมูในฟาร์ม</h1>
        </div>
        <div class="py-2"></div>

        {{-- Toolbar --}}
        <div class="card-custom-secondary mb-3">
            <form method="GET" action="{{ route('batch_pen_allocations.index') }}"
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
                                href="{{ route('batch_pen_allocations.index', array_merge(request()->except('selected_date'), [])) }}">วันที่ทั้งหมด</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'today' ? 'active' : '' }}"
                                href="{{ route('batch_pen_allocations.index', array_merge(request()->all(), ['selected_date' => 'today'])) }}">วันนี้</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'this_week' ? 'active' : '' }}"
                                href="{{ route('batch_pen_allocations.index', array_merge(request()->all(), ['selected_date' => 'this_week'])) }}">สัปดาห์นี้</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'this_month' ? 'active' : '' }}"
                                href="{{ route('batch_pen_allocations.index', array_merge(request()->all(), ['selected_date' => 'this_month'])) }}">เดือนนี้</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'this_year' ? 'active' : '' }}"
                                href="{{ route('batch_pen_allocations.index', array_merge(request()->all(), ['selected_date' => 'this_year'])) }}">ปีนี้</a>
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
                                href="{{ route('batch_pen_allocations.index', array_merge(request()->except('farm_id'), [])) }}">ฟาร์มทั้งหมด</a>
                        </li>
                        @foreach ($farms as $farm)
                            <li><a class="dropdown-item {{ request('farm_id') == $farm->id ? 'active' : '' }}"
                                    href="{{ route('batch_pen_allocations.index', array_merge(request()->all(), ['farm_id' => $farm->id])) }}">{{ $farm->farm_name }}</a>
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
                                href="{{ route('batch_pen_allocations.index', array_merge(request()->except('batch_id'), [])) }}">รุ่นทั้งหมด</a>
                        </li>
                        @foreach ($batches as $batch)
                            <li><a class="dropdown-item {{ request('batch_id') == $batch->id ? 'active' : '' }}"
                                    href="{{ route('batch_pen_allocations.index', array_merge(request()->all(), ['batch_id' => $batch->id])) }}">{{ $batch->batch_code }}</a>
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
                                href="{{ route('batch_pen_allocations.index', array_merge(request()->all(), ['sort' => 'name_asc'])) }}">ชื่อ
                                (ก-ฮ)</a></li>
                        <li><a class="dropdown-item {{ request('sort') == 'name_desc' ? 'active' : '' }}"
                                href="{{ route('batch_pen_allocations.index', array_merge(request()->all(), ['sort' => 'name_desc'])) }}">ชื่อ
                                (ฮ-ก)</a></li>
                        <li><a class="dropdown-item {{ request('sort') == 'quantity_asc' ? 'active' : '' }}"
                                href="{{ route('batch_pen_allocations.index', array_merge(request()->all(), ['sort' => 'quantity_asc'])) }}">จำนวนน้อย
                                → มาก</a></li>
                        <li><a class="dropdown-item {{ request('sort') == 'quantity_desc' ? 'active' : '' }}"
                                href="{{ route('batch_pen_allocations.index', array_merge(request()->all(), ['sort' => 'quantity_desc'])) }}">จำนวนมาก
                                → น้อย</a></li>
                    </ul>
                </div>

                <!-- Per Page -->
                @include('components.per-page-dropdown')

                {{-- Export Section --}}
                <div class="ms-auto d-flex gap-2 align-items-center flex-wrap">
                    <div class="d-flex gap-2 align-items-center">
                        <label class="text-nowrap small mb-0" style="min-width: 100px;">
                            <i class="bi bi-calendar-range"></i> ช่วงวันที่:
                        </label>
                        <input type="date" id="exportDateFrom" class="form-control form-control-sm" style="width: 140px;">
                        <span class="text-nowrap small">ถึง</span>
                        <input type="date" id="exportDateTo" class="form-control form-control-sm" style="width: 140px;">
                    </div>
                    <button type="button" class="btn btn-sm btn-success" id="exportCsvBtn">
                        <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
                    </button>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-primary mb-0">
                <thead class="table-header-custom">
                    <tr>
                        <th class="text-center">ฟาร์ม</th>
                        <th class="text-center">รุ่น</th>
                        <th class="text-center">เล้า (Barn)</th>
                        <th class="text-center">ความจุเล้า</th>
                        <th class="text-center">จำนวนที่จัดสรร</th>
                        <th class="text-center">หมูคงเหลือ</th>
                        <th class="text-center">รายละเอียด</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($barnSummaries as $barn)
                        <tr data-row-click="#detailModal{{ $loop->index }}" class="clickable-row">
                            <td class="text-center">{{ $barn['farm_name'] }}</td>
                            <td class="text-center">{{ $barn['batch_code'] }}</td>
                            <td class="text-center">{{ $barn['barn_code'] }}</td>
                            <td class="text-center"><strong>{{ $barn['capacity'] }}</strong></td>
                            <td class="text-center"><strong>{{ $barn['total_allocated'] }}</strong></td>
                            <td class="text-center"><strong
                                    style="color: {{ $barn['total_current_quantity'] > 0 ? '#28a745' : '#dc3545' }}">{{ $barn['total_current_quantity'] }}</strong>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                    data-bs-target="#detailModal{{ $loop->index }}">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-danger">ไม่มีข้อมูล</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


        {{-- Pagination --}}
        <div class="d-flex justify-content-between mt-3">
            <div>
                แสดง {{ $barnSummaries->firstItem() ?? 0 }} ถึง {{ $barnSummaries->lastItem() ?? 0 }} จาก
                {{ $barnSummaries->total() ?? 0 }} แถว
            </div>
            <div>
                {{ $barnSummaries->withQueryString()->links() }}
            </div>
        </div>
    </div>

    {{-- All Modals (Outside Main Container) --}}
    @foreach ($barnSummaries as $barn)
        <!-- Modal View -->
        <div class="modal fade" id="detailModal{{ $loop->index }}" tabindex="-1"
            aria-labelledby="detailModalLabel{{ $loop->index }}" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="detailModalLabel{{ $loop->index }}">
                            <i class="bi bi-building"></i> รายละเอียดเล้า - {{ $barn['barn_code'] }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-secondary table-sm table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center">รหัสรุ่น</th>
                                    <th class="text-center">รหัสคอก</th>
                                    <th class="text-center">จำนวนหมูที่จุได้</th>
                                    <th class="text-center">หมูที่จัดสรร</th>
                                    <th class="text-center">หมูคงเหลือ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($barn['pens'] as $pen)
                                    <tr>
                                        <td class="text-center">
                                            @foreach ($pen['batches'] as $batch_code)
                                                <span class="badge bg-info text-light">{{ $batch_code }}</span>
                                            @endforeach
                                        </td>
                                        <td class="text-center">{{ $pen['pen_code'] }}</td>
                                        <td class="text-center">{{ $pen['capacity'] }}</td>
                                        <td class="text-center">
                                            <strong>{{ $pen['allocated'] }}</strong>
                                        </td>
                                        <td class="text-center">
                                            <strong class="text-success">{{ $pen['current_quantity'] }}</strong>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const farmSelect = document.getElementById('farmSelect');
                const batchSelect = document.getElementById('batchSelect');

                const farmChoices = new Choices(farmSelect, {
                    searchEnabled: false,
                    itemSelectText: '',
                    shouldSort: false
                });
                const batchChoices = new Choices(batchSelect, {
                    searchEnabled: false,
                    itemSelectText: '',
                    shouldSort: false,
                    removeItemButton: true
                });

                farmSelect.addEventListener('change', function() {
                    const farmId = this.value;
                    batchChoices.clearChoices();

                    if (!farmId) return;

                    fetch('/get-batches/' + farmId)
                        .then(res => res.json())
                        .then(data => {
                            batchChoices.setChoices(
                                data.map(batch => ({
                                    value: batch.id,
                                    label: batch.batch_code
                                })),
                                'value', 'label', true
                            );
                        });
                });
            });
        </script>

        {{-- Auto-submit filter form --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const filterForm = document.getElementById('filterForm');
                const filterSelects = filterForm.querySelectorAll('select');

                filterSelects.forEach(function(select) {
                    select.addEventListener('change', function() {
                        filterForm.submit();
                    });
                });

                // เรียกใช้ common table click handler
                setupClickableRows();

                // Export CSV with date filter
                document.getElementById('exportCsvBtn').addEventListener('click', function() {
                    console.log('📥 [Batch Pen Allocation] Exporting CSV');
                    const params = new URLSearchParams(window.location.search);
                    const dateFrom = document.getElementById('exportDateFrom').value;
                    const dateTo = document.getElementById('exportDateTo').value;
                    if (dateFrom) params.set('export_date_from', dateFrom);
                    if (dateTo) params.set('export_date_to', dateTo);
                    const url = `{{ route('batch_pen_allocations.export.csv') }}?${params.toString()}`;
                    window.location.href = url;
                });
            });
        </script>
        <script src="{{ asset('admin/js/common-table-click.js') }}"></script>
    @endpush
@endsection
