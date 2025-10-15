@extends('layouts.admin')

@section('title', 'การจัดสรรหมู')

@section('content')
    <div class="container my-5">
        <div class="card-header">
            <h1 class="text-center">การจัดสรรหมู (Batch Pen Allocations)</h1>
        </div>
        <div class="py-2"></div>

        {{-- Toolbar --}}
        <div class="card-custom-secondary mb-3">
            <form method="GET" action="{{ route('batch_pen_allocations.index') }}"
                class="d-flex align-items-center gap-2 flex-wrap" id="filterForm">

                <!-- Date Filter (Orange) -->
                <select name="selected_date" id="dateFilter" class="form-select form-select-sm filter-select-orange">
                    <option value="">วันที่ทั้งหมด</option>
                    <option value="today" {{ request('selected_date') == 'today' ? 'selected' : '' }}>วันนี้</option>
                    <option value="this_week" {{ request('selected_date') == 'this_week' ? 'selected' : '' }}>สัปดาห์นี้
                    </option>
                    <option value="this_month" {{ request('selected_date') == 'this_month' ? 'selected' : '' }}>เดือนนี้
                    </option>
                    <option value="this_year" {{ request('selected_date') == 'this_year' ? 'selected' : '' }}>ปีนี้</option>
                </select>

                <!-- Farm Filter (Dark Blue) -->
                <select name="farm_id" class="form-select form-select-sm filter-select-orange">
                    <option value="">ฟาร์มทั้งหมด</option>
                    @foreach ($farms as $farm)
                        <option value="{{ $farm->id }}" {{ request('farm_id') == $farm->id ? 'selected' : '' }}>
                            {{ $farm->farm_name }}
                        </option>
                    @endforeach
                </select>

                <!-- Batch Filter (Dark Blue) -->
                <select name="batch_id" class="form-select form-select-sm filter-select-orange">
                    <option value="">รุ่นทั้งหมด</option>
                    @foreach ($batches as $batch)
                        <option value="{{ $batch->id }}" {{ request('batch_id') == $batch->id ? 'selected' : '' }}>
                            {{ $batch->batch_code }}
                        </option>
                    @endforeach
                </select>

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
                <select name="per_page" class="form-select form-select-sm filter-select-orange">
                    @foreach ([10, 25, 50, 100] as $n)
                        <option value="{{ $n }}" {{ request('per_page', 10) == $n ? 'selected' : '' }}>
                            {{ $n }} แถว</option>
                    @endforeach
                </select>

                <div class="ms-auto d-flex gap-2">
                    <a href="{{ route('batch_pen_allocations.export.csv', request()->all()) }}"
                        class="btn btn-sm btn-success">
                        <i class="bi bi-file-earmark-excel"></i> Export CSV
                    </a>
                    <a href="{{ route('batch_pen_allocations.export.pdf', request()->all()) }}"
                        class="btn btn-sm btn-danger">
                        <i class="bi bi-file-earmark-pdf"></i> Export PDF
                    </a>
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
                        <th class="text-center">รายละเอียดคอก (Pens)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($barnSummaries as $barn)
                        <tr>
                            <td class="text-center">{{ $barn['farm_name'] }}</td>
                            <td class="text-center">{{ $barn['batch_code'] }}</td>
                            <td class="text-center">{{ $barn['barn_code'] }}</td>
                            <td class="text-center"><strong>{{ $barn['capacity'] }}</strong></td>
                            <td class="text-center"><strong>{{ $barn['total_allocated'] }}</strong></td>
                            <td class="text-center">
                                <table class="table table-sm table-bordered mb-0" style="background-color: #fff;">
                                    <thead>
                                        <tr style="background: linear-gradient(135deg, #FF9130, #FECDA6);">
                                            <th class="text-center">Pen Code</th>
                                            <th class="text-center">Capacity</th>
                                            <th class="text-center">Allocated</th>
                                            <th class="text-center">Batches</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($barn['pens'] as $pen)
                                            <tr>
                                                <td class="text-center">{{ $pen['pen_code'] }}</td>
                                                <td class="text-center">{{ $pen['capacity'] }}</td>
                                                <td class="text-center"><strong>{{ $pen['allocated'] }}</strong></td>
                                                <td class="text-center">
                                                    @foreach ($pen['batches'] as $batch_code)
                                                        <span class="badge bg-info text-dark">{{ $batch_code }}</span>
                                                    @endforeach
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-danger">❌ ไม่มีข้อมูล</td>
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
    </div>
    </div>

    </div>
    </div>

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
            });
        </script>
    @endpush
@endsection
