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
                        <tr class="clickable-row" data-bs-toggle="modal" data-bs-target="#viewModal{{ $record->id }}">
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
                                {{ number_format($record->batch->costs->where('cost_type', 'excess_weight')->sum('total_price') ?? 0, 2) }}
                                ฿
                            </td>
                            <td class="text-center">
                                {{ number_format($record->batch->costs->sum('transport_cost') ?? 0, 2) }} ฿</td>
                            <td class="text-center">
                                <strong>{{ number_format(
                                    ($record->total_pig_price ?? 0) +
                                        ($record->excess_weight_cost ?? 0) +
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


                            <td>
                                {{-- Edit Button --}}
                                <button class="btn btn-warning btn-sm btn-action" data-bs-toggle="modal"
                                    data-bs-target="#editModal{{ $record->id }}">
                                    แก้ไข
                                </button>
                                {{-- Delete Button --}}
                                <form action="{{ route('pig_entry_records.delete', $record->id) }}" method="POST"
                                    style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-action btn-danger"
                                        onclick="return confirm('คุณแน่ใจไหมว่าจะลบรายการนี้?')">Delete</button>
                                </form>
                            </td>
                        </tr>

                        {{-- Modal Edit --}}
                        <div class="modal fade" id="editModal{{ $record->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content bg-dark text-light">
                                    <div class="modal-header">
                                        <h5>แก้ไขข้อมูลหมูเข้า</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('pig_entry_records.update', $record->id) }}" method="POST"
                                        enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body">

                                            <div class="mb-3">
                                                <label>ฟาร์ม</label>
                                                <select id="editFarmSelect{{ $record->id }}"
                                                    class="farmSelect form-select" name="farm_id" required>
                                                    <option value="">-- เลือกฟาร์ม --</option>
                                                    @foreach ($farms as $farm)
                                                        <option value="{{ $farm->id }}"
                                                            {{ $record->farm_id == $farm->id ? 'selected' : '' }}>
                                                            {{ $farm->farm_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label>รุ่น (Batch)</label>
                                                <select id="editBatchSelect{{ $record->id }}"
                                                    class="batchSelect form-select" name="batch_id" required>
                                                    <option value="{{ $record->batch_id }}" selected>
                                                        {{ $record->batch->batch_code ?? '-' }}</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label>วันที่หมูเข้า</label>
                                                <input type="text" name="pig_entry_date"
                                                    class="form-control dateWrapper"
                                                    value="{{ $record->pig_entry_date }}" required>
                                            </div>

                                            <div class="mb-3">
                                                <label>จำนวนหมูเข้า (ตัว)</label>
                                                <input type="number" name="total_pig_amount" class="form-control"
                                                    value="{{ $record->total_pig_amount }}" required>
                                            </div>

                                            <div class="mb-3">
                                                <label>น้ำหนักรวม (กก.)</label>
                                                <input type="number" step="0.01" name="total_pig_weight"
                                                    class="form-control" value="{{ $record->total_pig_weight }}"
                                                    required>
                                            </div>

                                            <div class="mb-3">
                                                <label>ราคารวม (บาท)</label>
                                                <input type="number" step="0.01" name="total_pig_price"
                                                    class="form-control" value="{{ $record->total_pig_price }}" required>
                                            </div>

                                            <div class="mb-3">
                                                <label>ค่าน้ำหนักส่วนเกิน</label>
                                                <input type="number" step="0.01" name="excess_weight_cost"
                                                    class="form-control"
                                                    value="{{ $record->batch->costs->where('cost_type', 'excess_weight')->sum('total_price') ?? 0 }}">
                                            </div>

                                            <div class="mb-3">
                                                <label>ค่าขนส่ง</label>
                                                <input type="number" step="0.01" name="transport_cost"
                                                    class="form-control"
                                                    value="{{ $record->batch->costs->sum('transport_cost') ?? 0 }}">
                                            </div>

                                            <div class="mb-3">
                                                <label>โน๊ต</label>
                                                <textarea name="note" class="form-control">{{ $record->note }}</textarea>
                                            </div>

                                            <div class="mb-3"> <label>แนบไฟล์ใบเสร็จ (ถ้ามี)</label> <input
                                                    type="file" name="receipt_file" class="form-control">
                                                {{-- delete file --}}
                                                @if ($record->latestCost && $record->latestCost->receipt_file)
                                                    @php$file = $record->latestCost->receipt_file;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    @endphp ?> ?> ?> ?> ?> ?> ?> ?> ?> ?> ?> ?> ?> ?> ?> ?> ?> ?> ?>
                                                    ?>
                                                    <small class="text-muted">ไฟล์ปัจจุบัน:</small>
                                                    @if (Str::endsWith($file, ['.jpg', '.jpeg', '.png']))
                                                        <div><img src="{{ $file }}" alt="Receipt"
                                                                style="max-width:100px;"></div>
                                                    @else
                                                        <div><a href="{{ $file }}" target="_blank">Download</div>
                                                    @endif

                                                    {{-- hidden input กันเคสไม่ได้ติ๊ก checkbox --}}
                                                    <input type="hidden" name="delete_receipt_file" value="0">

                                                    <div class="form-check mt-1">
                                                        <input type="checkbox" name="delete_receipt_file" value="1"
                                                            class="form-check-input"
                                                            id="deleteReceipt{{ $record->id }}">
                                                        <label class="form-check-label"
                                                            for="deleteReceipt{{ $record->id }}">
                                                            ลบไฟล์ปัจจุบัน
                                                        </label>
                                                    </div>
                                                @endif
                                            </div>

                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary">บันทึก</button>
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">ยกเลิก</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>


                        {{-- End Modal Edit --}}

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
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('pig_entry_records.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">

                        <div class="mb-3">
                            <label>ฟาร์ม</label>
                            <select name="farm_id" id="createFarmSelect" class="farmSelect form-select" required>
                                <option value="">-- เลือกฟาร์ม --</option>
                                @foreach ($farms as $farm)
                                    <option value="{{ $farm->id }}">{{ $farm->farm_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>รุ่น (Batch)</label>
                            <select name="batch_id" id="createBatchSelect" class="batchSelect form-select" required>
                                <option value="">-- เลือกรุ่น --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>เล้า (Barn)</label>
                            <select name="barn_id[]" id="createBarnSelect" class="barnSelect form-select" multiple
                                required>
                                <option value="">-- เลือกเล้า --</option>
                            </select>
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
            function initFarmBatchBarnChoices(farmSelect, batchSelect, barnSelect) {
                const farmChoice = new Choices(farmSelect, {
                    searchEnabled: false,
                    itemSelectText: '',
                    shouldSort: false
                });
                const batchChoice = new Choices(batchSelect, {
                    searchEnabled: false,
                    itemSelectText: '',
                    shouldSort: false,
                    removeItemButton: true,
                    placeholderValue: '-- เลือกรุ่น --'
                });
                const barnChoice = new Choices(barnSelect, {
                    searchEnabled: false,
                    itemSelectText: '',
                    shouldSort: false,
                    removeItemButton: true,
                    placeholderValue: '-- เลือกเล้า --'
                });
                farmSelect.addEventListener('change', function() {
                    const farmId = this.value;

                    // Immediately reset batch and barn to placeholder so stale values aren't shown
                    batchChoice.clearChoices();
                    batchChoice.setChoices([{
                        value: '',
                        label: '-- เลือกรุ่น --',
                        selected: true,
                        disabled: true
                    }]);

                    barnChoice.clearChoices();
                    barnChoice.setChoices([{
                        value: '',
                        label: '-- เลือกเล้า --',
                        selected: true,
                        disabled: true
                    }]);

                    // If farm cleared, nothing more to do
                    if (!farmId) return;

                    // Show loading states
                    batchChoice.clearChoices();
                    batchChoice.setChoices([{
                        value: '',
                        label: 'กำลังโหลด...',
                        selected: true,
                        disabled: true
                    }]);

                    barnChoice.clearChoices();
                    barnChoice.setChoices([{
                        value: '',
                        label: 'กำลังโหลดเล้า...',
                        selected: true,
                        disabled: true
                    }]);

                    // Fetch batches with improved error handling
                    fetch('{{ url('get-batches') }}/' + farmId)
                        .then(res => {
                            if (!res.ok) {
                                // Try to read body for extra debug info
                                return res.text().then(body => {
                                    const err = new Error('Failed to load batches: HTTP ' + res.status +
                                        ' - ' + body);
                                    err.status = res.status;
                                    throw err;
                                });
                            }
                            return res.json();
                        })
                        .then(data => {
                            const choices = [{
                                value: '',
                                label: '-- เลือกรุ่น --',
                                selected: true,
                                disabled: true
                            }];
                            if (Array.isArray(data) && data.length > 0) {
                                data.forEach(batch => choices.push({
                                    value: batch.id,
                                    label: batch.batch_code
                                }));
                            } else if (Array.isArray(data) && data.length === 0) {
                                // no batches for this farm
                                choices.push({
                                    value: '',
                                    label: '❌ ไม่พบรุ่นสำหรับฟาร์มนี้',
                                    disabled: true
                                });
                            }
                            batchChoice.clearChoices();
                            batchChoice.setChoices(choices, 'value', 'label', true);
                            try {
                                batchChoice.setChoiceByValue('');
                            } catch (e) {}
                        })
                        .catch(err => {
                            console.error('Error loading batches:', err);
                            batchChoice.clearChoices();
                            batchChoice.setChoices([{
                                value: '',
                                label: '❌ ไม่สามารถโหลดรุ่น',
                                selected: true,
                                disabled: true
                            }]);
                        });

                    // Fetch barns with improved error handling (use absolute url)
                    fetch('{{ url('get-available-barns') }}/' + farmId)
                        .then(res => {
                            if (!res.ok) {
                                return res.text().then(body => {
                                    const err = new Error('Failed to load barns: HTTP ' + res.status +
                                        ' - ' + body);
                                    err.status = res.status;
                                    throw err;
                                });
                            }
                            return res.json();
                        })
                        .then(data => {
                            const choices = [{
                                value: '',
                                label: '-- เลือกเล้า --',
                                selected: true,
                                disabled: true
                            }];
                            if (Array.isArray(data) && data.length > 0) {
                                data.forEach(barn => choices.push({
                                    value: barn.id,
                                    label: barn.barn_code + ' (เหลือ ' + (barn.remaining ?? 0) + ' ตัว)'
                                }));
                            } else if (Array.isArray(data) && data.length === 0) {
                                choices.push({
                                    value: '',
                                    label: '❌ ไม่พบเล้าที่มีที่ว่างในฟาร์มนี้',
                                    disabled: true
                                });
                            }
                            barnChoice.clearChoices();
                            barnChoice.setChoices(choices, 'value', 'label', true);
                            try {
                                barnChoice.setChoiceByValue('');
                            } catch (e) {}
                        })
                        .catch(err => {
                            console.error('Error loading barns:', err);
                            barnChoice.clearChoices();
                            barnChoice.setChoices([{
                                value: '',
                                label: '❌ ไม่สามารถโหลดเล้า',
                                selected: true,
                                disabled: true
                            }]);
                        });
                });

                // mark this farm select as initialized so we don't double-init
                try {
                    farmSelect.choicesInstance = true;
                } catch (e) {
                    // ignore
                }
            }

            // Create modal
            document.addEventListener('shown.bs.modal', function(event) {
                if (event.target.id === 'createModal') {
                    const farm = document.getElementById('createFarmSelect');
                    const batch = document.getElementById('createBatchSelect');
                    const barn = document.getElementById('createBarnSelect');
                    if (!farm.choicesInstance) initFarmBatchBarnChoices(farm, batch, barn);
                }
            });

            // Edit modals (for each record)
            @foreach ($pigEntryRecords as $record)
                document.addEventListener('shown.bs.modal', function(event) {
                    if (event.target.id === 'editModal{{ $record->id }}') {
                        const farm = document.getElementById('editFarmSelect{{ $record->id }}');
                        const batch = document.getElementById('editBatchSelect{{ $record->id }}');
                        const barn = document.getElementById('editBarnSelect{{ $record->id }}');
                        if (!farm.choicesInstance) initFarmBatchBarnChoices(farm, batch, barn);
                    }
                });
            @endforeach
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
            });
        </script>
    @endpush
@endsection
