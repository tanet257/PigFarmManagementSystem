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
                <select name="farm_id" id="farmFilter" class="form-select form-select-sm filter-select-orange">
                    <option value="">ฟาร์มทั้งหมด</option>
                    @foreach ($farms as $farm)
                        <option value="{{ $farm->id }}" {{ request('farm_id') == $farm->id ? 'selected' : '' }}>
                            {{ $farm->farm_name }}
                        </option>
                    @endforeach
                </select>

                <!-- Batch Filter (Dark Blue) -->
                <select name="batch_id" id="batchFilter" class="form-select form-select-sm filter-select-orange">
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
                <select name="per_page" class="form-select form-select-sm filter-select-orange">
                    @foreach ([10, 25, 50, 100] as $n)
                        <option value="{{ $n }}" {{ request('per_page', 10) == $n ? 'selected' : '' }}>
                            {{ $n }} แถว</option>
                    @endforeach
                </select>

                <div class="ms-auto d-flex gap-2">
                    <a class="btn btn-success btn-sm" href="{{ route('inventory_movements.export.csv') }}">
                        <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
                    </a>
                    <a class="btn btn-danger btn-sm" href="{{ route('inventory_movements.export.pdf') }}">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
                    </a>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-primary mb-0">
                <thead class="table-header-custom">
                    <tr>
                        <th class="text-center">วันที่</th>
                        <th class="text-center">ชื่อฟาร์ม</th>
                        <th class="text-center">รหัสรุ่น</th>
                        <th class="text-center">ประเภทสินค้า</th>
                        <th class="text-center">รหัสสินค้า</th>
                        <th class="text-center">ชื่อสินค้า</th>
                        <th class="text-center">ประเภทการเปลี่ยนแปลง</th>
                        <th class="text-center">จำนวน</th>
                        <th class="text-center">โน้ต</th>
                        <th class="text-center">บันทึกเมื่อ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                        <tr>
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
                            <td class="text-center"><strong>{{ number_format($movement->quantity, 2) }}</strong></td>
                            <td class="text-center">{{ $movement->note ?? '-' }}</td>
                            <td class="text-center">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-danger">❌ ไม่มีข้อมูลความเคลื่อนไหว</td>
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

    @push('scripts')
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
        </script>
    @endpush
@endsection
