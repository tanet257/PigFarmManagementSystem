@extends('layouts.admin')

@section('title', 'บันทึกหมูเข้า')

@section('content')
    <div class="container my-5">
        <div class="card-header">
            <h1 class="text-center">บันทึกหมูเข้า (Pig Entry Records)</h1>
        </div>
        <div class="py-2"></div>

        {{-- Toolbar --}}
        <div class="card-custom-secondary mb-3">
            <form method="GET" action="{{ route('pig_entry_records.index') }}"
                class="d-flex align-items-center gap-2 flex-wrap" id="filterForm">
                <!-- Date Filter (Orange) -->
                <select name="selected_date" id="dateFilter" class="form-select form-select-sm filter-select-orange">
                    <option value="">วันที่ทั้งหมด</option>
                    <option value="today" {{ request('selected_date') == 'today' ? 'selected' : '' }}>วันนี้
                    </option>
                    <option value="this_week" {{ request('selected_date') == 'this_week' ? 'selected' : '' }}>
                        สัปดาห์นี้</option>
                    <option value="this_month" {{ request('selected_date') == 'this_month' ? 'selected' : '' }}>
                        เดือนนี้</option>
                    <option value="this_year" {{ request('selected_date') == 'this_year' ? 'selected' : '' }}>ปีนี้
                    </option>
                </select>

                <!-- Farm Filter (Dark Blue) -->
                <select name="farm_id" id="farmFilter" class="form-select form-select-sm filter-select-orange">
                    <option value="">เลือกฟาร์มก่อน</option>
                    @foreach ($farms as $farm)
                        <option value="{{ $farm->id }}" {{ request('farm_id') == $farm->id ? 'selected' : '' }}>
                            {{ $farm->farm_name }}
                        </option>
                    @endforeach
                </select>

                <!-- Batch Filter (Dark Blue) -->
                <select name="batch_id" id="batchFilter" class="form-select form-select-sm filter-select-orange">
                    <option value="">รุ่นทั้งหมด</option>
                    @if (request('farm_id'))
                        @foreach ($batches as $batch)
                            <option value="{{ $batch->id }}" {{ request('batch_id') == $batch->id ? 'selected' : '' }}>
                                {{ $batch->batch_code }}
                            </option>
                        @endforeach
                    @endif
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

                <div class="ms-auto d-flex gap-2">
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                        data-bs-target="#createModal">
                        <i class="bi bi-plus-circle me-1"></i> เพิ่มหมูเข้า
                    </button>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-primary mb-0">
                <thead class="table-header-custom">
                    <tr>
                        <th class="text-center">
                            <a href="{{ route('pig_entry_records.index', array_merge(request()->all(), ['sort_by' => 'pig_entry_date', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}"
                                class="text-white text-decoration-none d-flex align-items-center justify-content-center gap-1">
                                วันที่
                                @if (request('sort_by') == 'pig_entry_date')
                                    <i class="bi bi-{{ request('sort_order') == 'asc' ? 'sort-up' : 'sort-down' }}"></i>
                                @else
                                    <i class="bi bi-arrow-down-up"></i>
                                @endif
                            </a>
                        </th>
                        <th class="text-center">ฟาร์ม</th>
                        <th class="text-center">รุ่น (Batch)</th>
                        <th class="text-center">
                            <a href="{{ route('pig_entry_records.index', array_merge(request()->all(), ['sort_by' => 'total_pig_amount', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}"
                                class="text-white text-decoration-none d-flex align-items-center justify-content-center gap-1">
                                จำนวนหมู
                                @if (request('sort_by') == 'total_pig_amount')
                                    <i class="bi bi-{{ request('sort_order') == 'asc' ? 'sort-up' : 'sort-down' }}"></i>
                                @else
                                    <i class="bi bi-arrow-down-up"></i>
                                @endif
                            </a>
                        </th>
                        <th class="text-center">
                            <a href="{{ route('pig_entry_records.index', array_merge(request()->all(), ['sort_by' => 'total_pig_weight', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}"
                                class="text-white text-decoration-none d-flex align-items-center justify-content-center gap-1">
                                น้ำหนักรวม
                                @if (request('sort_by') == 'total_pig_weight')
                                    <i class="bi bi-{{ request('sort_order') == 'asc' ? 'sort-up' : 'sort-down' }}"></i>
                                @else
                                    <i class="bi bi-arrow-down-up"></i>
                                @endif
                            </a>
                        </th>
                        <th class="text-center">
                            <a href="{{ route('pig_entry_records.index', array_merge(request()->all(), ['sort_by' => 'total_pig_price', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}"
                                class="text-white text-decoration-none d-flex align-items-center justify-content-center gap-1">
                                ราคาลูกหมู
                                @if (request('sort_by') == 'total_pig_price')
                                    <i class="bi bi-{{ request('sort_order') == 'asc' ? 'sort-up' : 'sort-down' }}"></i>
                                @else
                                    <i class="bi bi-arrow-down-up"></i>
                                @endif
                            </a>
                        </th>
                        <th class="text-center">ค่าน้ำหนักเกิน</th>
                        <th class="text-center">ค่าขนส่ง</th>
                        <th class="text-center">ราคารวม</th>
                        <th class="text-center">โน๊ต</th>
                        <th class="text-center">ใบเสร็จ</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pigEntryRecords as $record)
                        <tr data-row-click="#viewModal{{ $record->id }}" class="clickable-row">
                            <td class="text-center">{{ $record->pig_entry_date }}</td>
                            <td class="text-center">{{ $record->farm->farm_name ?? '-' }}</td>
                            <td class="text-center">{{ $record->batch->batch_code ?? '-' }}</td>
                            <td class="text-center"><strong>{{ $record->total_pig_amount }}</strong></td>
                            <td class="text-center">
                                <strong>{{ number_format($record->total_pig_weight, 2) }}</strong> กก.
                            </td>
                            <td class="text-center">{{ number_format($record->total_pig_price, 2) }} ฿</td>

                            {{-- คำนวณจาก batch->costs --}}
                            <td class="text-center">
                                {{ number_format($record->batch->costs->sum('excess_weight_cost') ?? 0, 2) }}
                                ฿
                            </td>
                            <td class="text-center">
                                {{ number_format($record->batch->costs->sum('transport_cost') ?? 0, 2) }}
                                ฿</td>
                            <td class="text-center">
                                <strong>{{ number_format(
                                    $record->total_pig_price +
                                        ($record->batch->costs->sum('excess_weight_cost') ?? 0) +
                                        ($record->batch->costs->sum('transport_cost') ?? 0),
                                    2,
                                ) }}
                                    ฿</strong>
                            </td>
                            <td class="text-center">{{ $record->note ?? '-' }}</td>
                            {{-- ดึงภาพจาก cloudinary --}}
                            <td class="text-center">
                                @if ($record->latestCost && !empty($record->latestCost->receipt_file))
                                    @php
                                        $file = $record->latestCost->receipt_file;
                                    @endphp

                                    @if (Str::endsWith($file, ['.jpg', '.jpeg', '.png']))
                                        <img src="{{ $file }}" alt="Receipt"
                                            style="max-width:100px; max-height:100px;">
                                    @else
                                        <a href="{{ $file }}" target="_blank">Download</a>
                                    @endif
                                @else
                                    <span class="text-muted">ไม่มีไฟล์</span>
                                @endif
                            </td>

                            <td class="text-center">
                                {{-- View Button --}}
                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                    data-bs-target="#viewModal{{ $record->id }}">
                                    <i class="bi bi-eye"></i>
                                </button>

                                {{-- Delete Button --}}
                                <form action="{{ route('pig_entry_records.delete', $record->id) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger"
                                        onclick="event.stopPropagation(); if(confirm('คุณแน่ใจไหมว่าจะลบรายการนี้?')) { this.form.submit(); }">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="12" class="text-danger">❌ ไม่มีข้อมูล</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between mt-3">
            <div>
                แสดง {{ $pigEntryRecords->firstItem() ?? 0 }} ถึง {{ $pigEntryRecords->lastItem() ?? 0 }} จาก
                {{ $pigEntryRecords->total() ?? 0 }} แถว
            </div>
            <div>
                {{ $pigEntryRecords->withQueryString()->links() }}
            </div>
        </div>
    </div>

    {{-- Modal Create --}}
    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-dark text-light">
                <div class="modal-header">
                    <h5>เพิ่มหมูเข้า</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('pig_entry_records.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">

                        {{-- FARM DROPDOWN BUTTON --}}
                        <div class="mb-3">
                            <label>ฟาร์ม</label>
                            <div class="dropdown">
                                <button
                                    class="btn btn-primary dropdown-toggle w-100 d-flex justify-content-between align-items-center"
                                    type="button" id="createFarmDropdownBtn" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <span>เลือกฟาร์ม</span>
                                </button>
                                <ul class="dropdown-menu w-100" aria-labelledby="createFarmDropdownBtn"
                                    id="createFarmDropdownMenu">
                                    @foreach ($farms as $farm)
                                        <li>
                                            <a class="dropdown-item" href="#" data-farm-id="{{ $farm->id }}">
                                                {{ $farm->farm_name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                                <input type="hidden" name="farm_id" id="createFarmSelect" value="">
                            </div>
                        </div>

                        {{-- BATCH DROPDOWN BUTTON --}}
                        <div class="mb-3">
                            <label>รุ่น (Batch)</label>
                            <div class="dropdown">
                                <button
                                    class="btn btn-primary dropdown-toggle w-100 d-flex justify-content-between align-items-center"
                                    type="button" id="createBatchDropdownBtn" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <span>เลือกรุ่น</span>
                                </button>
                                <ul class="dropdown-menu w-100" aria-labelledby="createBatchDropdownBtn"
                                    id="createBatchDropdownMenu">
                                    <!-- จะ populate เมื่อเลือกฟาร์ม -->
                                </ul>
                                <input type="hidden" name="batch_id" id="createBatchSelect" value="">
                            </div>
                        </div>

                        {{-- BARN CHECKBOXES --}}
                        <div class="mb-3">
                            <label>เล้า (Barn) - สามารถเลือกได้หลายตัว</label>
                            <div class="border rounded p-3"
                                style="background-color: #495057; max-height: 150px; overflow-y: auto;">
                                <div id="createBarnCheckboxContainer">
                                    <!-- จะ populate เมื่อเลือกฟาร์ม -->
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>วันที่เข้า</label>
                            <input type="text" name="pig_entry_date" placeholer="ว/ด/ป ชม. นาที"
                                class="form-control dateWrapper" required>
                        </div>

                        <div class="mb-3">
                            <label>จำนวนหมู</label>
                            <input type="number" name="total_pig_amount" class="form-control" min="1" required>
                        </div>

                        <div class="mb-3">
                            <label>น้ำหนักรวม</label>
                            <input type="number" name="total_pig_weight" class="form-control" min="0"
                                step="0.01" required>
                        </div>

                        <div class="mb-3">
                            <label>ราคาลูกหมูรวม</label>
                            <input type="number" name="total_pig_price" class="form-control" min="0"
                                step="0.01" required>
                        </div>

                        <div class="mb-3">
                            <label>ค่าน้ำหนักส่วนเกิน</label>
                            <input type="number" name="excess_weight_cost" class="form-control" min="0"
                                step="0.01">
                        </div>

                        <div class="mb-3">
                            <label>ค่าขนส่ง</label>
                            <input type="number" name="transport_cost" class="form-control" min="0"
                                step="0.01">
                        </div>

                        <div class="mb-3">
                            <label>โน๊ต</label>
                            <textarea name="note" class="form-control"></textarea>
                        </div>

                        <div class="mb-3">
                            <label>ใบเสร็จ</label>
                            <input type="file" name="receipt_file" class="form-control"
                                accept=".jpg,.jpeg,.png,.pdf">
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">บันทึก</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        </div>
                    </div>
            </div>
        </div>
    </div>
    </div>
    {{-- End Create Modal --}}

    @push('scripts')
        <!--flatpickr-->
        <script>
            // ใช้ class dateWrapper
            document.addEventListener('shown.bs.modal', function(event) {
                event.target.querySelectorAll('.dateWrapper').forEach(el => {
                    if (!el._flatpickr) {
                        flatpickr(el, {
                            enableTime: true,
                            dateFormat: "d/m/Y H:i",
                            maxDate: "today",
                        });
                    }
                });
            });
        </script>

        {{-- JS สำหรับ fetch barns + batches --}}

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const batches = @json($batches);
                const farms = @json($farms);

                // Elements
                const farmDropdownBtn = document.getElementById('createFarmDropdownBtn');
                const farmDropdownMenu = document.getElementById('createFarmDropdownMenu');
                const farmSelect = document.getElementById('createFarmSelect');

                const batchDropdownBtn = document.getElementById('createBatchDropdownBtn');
                const batchDropdownMenu = document.getElementById('createBatchDropdownMenu');
                const batchSelect = document.getElementById('createBatchSelect');

                const barnCheckboxContainer = document.getElementById('createBarnCheckboxContainer');

                // FARM DROPDOWN HANDLER
                farmDropdownMenu.addEventListener('click', function(e) {
                    if (e.target.classList.contains('dropdown-item')) {
                        e.preventDefault();
                        const farmId = e.target.getAttribute('data-farm-id');
                        const farmName = e.target.textContent.trim();

                        farmDropdownBtn.querySelector('span').textContent = farmName;
                        farmSelect.value = farmId;

                        // Reset batch
                        batchDropdownBtn.querySelector('span').textContent = 'เลือกรุ่น';
                        batchSelect.value = '';
                        batchDropdownMenu.innerHTML = '';

                        // Populate batches
                        const farmBatches = batches.filter(b => b.farm_id === parseInt(farmId));
                        farmBatches.forEach(batch => {
                            const li = document.createElement('li');
                            const a = document.createElement('a');
                            a.className = 'dropdown-item';
                            a.href = '#';
                            a.setAttribute('data-batch-id', batch.id);
                            a.textContent = batch.batch_code;
                            li.appendChild(a);
                            batchDropdownMenu.appendChild(li);
                        });

                        // Populate barns checkboxes
                        const farm = farms.find(f => f.id === parseInt(farmId));
                        if (farm && farm.barns) {
                            barnCheckboxContainer.innerHTML = '';

                            // Fetch barn capacity data
                            fetch('/get-barn-capacity/' + farmId)
                                .then(res => res.json())
                                .then(barnData => {
                                    farm.barns.forEach(barn => {
                                        const capacityInfo = barnData.find(b => b.id === barn.id);
                                        const available = capacityInfo ? capacityInfo
                                            .available_capacity : 0;
                                        const isFull = capacityInfo ? capacityInfo.is_full : false;

                                        const div = document.createElement('div');
                                        div.className = 'form-check';

                                        const isDisabled = isFull ? 'disabled' : '';
                                        const statusText = isFull ?
                                            `<span class="text-danger"> ❌ เต็มแล้ว</span>` :
                                            `<span class="text-success"> (เหลือ ${available} ตัว)</span>`;

                                        div.innerHTML = `
                                            <input type="checkbox" class="form-check-input barn-checkbox"
                                                name="barn_id[]" value="${barn.id}" id="createBarn_${barn.id}" ${isDisabled}>
                                            <label class="form-check-label" for="createBarn_${barn.id}">
                                                ${barn.barn_code} ${statusText}
                                            </label>
                                        `;
                                        barnCheckboxContainer.appendChild(div);
                                    });
                                })
                                .catch(err => console.error('Error fetching barn capacity:', err));
                        }
                    }
                });

                // BATCH DROPDOWN HANDLER
                batchDropdownMenu.addEventListener('click', function(e) {
                    if (e.target.classList.contains('dropdown-item')) {
                        e.preventDefault();
                        const batchId = e.target.getAttribute('data-batch-id');
                        const batchCode = e.target.textContent.trim();

                        batchDropdownBtn.querySelector('span').textContent = batchCode;
                        batchSelect.value = batchId;
                    }
                });

                // FORM VALIDATION
                document.querySelector('#createModal form').addEventListener('submit', function(e) {
                    const farmId = farmSelect.value;
                    const batchId = batchSelect.value;
                    const barnCheckboxes = document.querySelectorAll(
                        '#createBarnCheckboxContainer .barn-checkbox:checked');

                    if (!farmId) {
                        e.preventDefault();
                        showSnackbar('กรุณาเลือกฟาร์ม');
                        return false;
                    }
                    if (!batchId) {
                        e.preventDefault();
                        showSnackbar('กรุณาเลือกรุ่น');
                        return false;
                    }
                    if (barnCheckboxes.length === 0) {
                        e.preventDefault();
                        showSnackbar('กรุณาเลือกเล้าอย่างน้อยหนึ่งตัว');
                        return false;
                    }
                });
            });
        </script>



        {{-- Auto-submit filters --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const filterForm = document.getElementById('filterForm');
                const farmFilter = document.getElementById('farmFilter');
                const batchFilter = document.getElementById('batchFilter');
                const allFilters = filterForm.querySelectorAll('select');

                // เมื่อพยายามเลือก batch โดยที่ยังไม่เลือก farm
                batchFilter.addEventListener('mousedown', function(e) {
                    if (!farmFilter.value) {
                        e.preventDefault();
                        showSnackbar(' กรุณาเลือกฟาร์มก่อนเลือกรุ่น');
                        // Focus ไปที่ farm filter
                        farmFilter.focus();
                    }
                });

                batchFilter.addEventListener('focus', function(e) {
                    if (!farmFilter.value) {
                        showSnackbar(' กรุณาเลือกฟาร์มก่อนเลือกรุ่น');
                        // Focus ไปที่ farm filter
                        setTimeout(() => farmFilter.focus(), 100);
                    }
                });

                // เมื่อเลือกฟาร์ม
                farmFilter.addEventListener('change', function() {
                    const farmId = this.value;

                    if (farmId) {
                        // แสดง loading
                        batchFilter.innerHTML = '<option value="">กำลังโหลด...</option>';

                        // โหลด batches จาก API (ใช้ absolute url เพื่อให้ทำงานภายใต้ sub-folder ถ้ามี)
                        fetch('{{ url('get-batches') }}/' + farmId)
                            .then(response => response.json())
                            .then(data => {
                                batchFilter.innerHTML = '<option value="">รุ่นทั้งหมด</option>';
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
                                batchFilter.innerHTML = '<option value="">เกิดข้อผิดพลาด</option>';
                                showSnackbar('เกิดข้อผิดพลาดในการโหลดรุ่น');
                            });
                    } else {
                        // ถ้าเลือก "เลือกฟาร์มก่อน" ให้รีเซ็ต batch filter
                        batchFilter.innerHTML = '<option value="">รุ่นทั้งหมด</option>';
                        // Submit form
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

                // เรียกใช้ common table click handler
                setupClickableRows();
            });
        </script>
        <script src="{{ asset('admin/js/common-table-click.js') }}"></script>
    @endpush
@endsection
