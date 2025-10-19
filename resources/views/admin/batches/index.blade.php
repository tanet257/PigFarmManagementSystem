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

                <!-- Batch Filter -->
                <select name="batch_id" id="batchFilter" class="form-select form-select-sm filter-select-orange">
                    <option value="">รุ่นทั้งหมด</option>
                    @foreach ($allBatches as $batch)
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
                        <th class="text-center">วันที่เริ่มต้น</th>
                        <th class="text-center">วันที่สิ้นสุด</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $batch)
                        <tr>
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
                                @else
                                    <span class="badge bg-dark">-</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $batch->note ?? '-' }}</td>
                            <td class="text-center">{{ $batch->start_date ?? '-' }}</td>
                            <td class="text-center">{{ $batch->end_date ?? '-' }}</td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm btn-action" data-bs-toggle="modal"
                                    data-bs-target="#editModal{{ $batch->id }}">แก้ไข</button>

                                <form action="{{ route('batches.delete', $batch->id) }}" method="POST"
                                    style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('คุณแน่ใจไหมว่าจะลบรุ่นนี้?')">Delete</button>
                                </form>

                                {{-- Modal Edit --}}
                                <div class="modal fade" id="editModal{{ $batch->id }}" tabindex="-1"
                                    aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content bg-dark text-light">
                                            <div class="modal-header">
                                                <h5>แก้ไขรุ่นหมู (Batch)</h5>
                                                <button type="button" class="btn-close btn-close-white"
                                                    data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('batches.update', $batch->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label>รหัสรุ่น</label>
                                                        <input type="text" name="batch_code" class="form-control"
                                                            value="{{ $batch->batch_code }}" required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label>น้ำหนักรวม (kg)</label>
                                                        <input type="number" name="total_pig_weight"
                                                            class="form-control" value="{{ $batch->total_pig_weight }}"
                                                            required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label>จำนวนรวม</label>
                                                        <input type="number" name="total_pig_amount"
                                                            class="form-control" value="{{ $batch->total_pig_amount }}"
                                                            required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label>ราคารวม (บาท)</label>
                                                        <input type="number" name="total_pig_price" class="form-control"
                                                            value="{{ $batch->total_pig_price }}" required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label>สถานะ</label>
                                                        <select name="status" class="form-select" required>
                                                            <option value="กำลังเลี้ยง"
                                                                {{ $batch->status == 'กำลังเลี้ยง' ? 'selected' : '' }}>
                                                                กำลังเลี้ยง</option>
                                                            <option value="เสร็จสิ้น"
                                                                {{ $batch->status == 'เสร็จสิ้น' ? 'selected' : '' }}>
                                                                เสร็จสิ้น</option>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label>โน๊ต</label>
                                                        <textarea name="note" class="form-control">{{ $batch->note }}</textarea>
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-primary">บันทึก</button>
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">ยกเลิก</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                {{-- End Modal Edit --}}
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
                                <label class="form-label">ฟาร์ม</label>
                                <select name="farm_id" class="form-select" required>
                                    <option value="">เลือกฟาร์ม</option>
                                    @foreach ($farms as $farm)
                                        <option value="{{ $farm->id }}">{{ $farm->farm_name }}</option>
                                    @endforeach
                                </select>
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
            @foreach ($batches as $batch)
                flatpickr("#end_date_{{ $batch->id }}", {
                    enableTime: true,
                    dateFormat: "d/m/Y H:i",
                    maxDate: "today",
                    time_24hr: true,
                });
            @endforeach
        </script>
    @endpush
@endsection
