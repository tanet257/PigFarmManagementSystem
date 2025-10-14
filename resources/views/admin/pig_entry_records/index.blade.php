<!DOCTYPE html>
<html lang="th">

<head>
    @include('admin.css')
</head>

<body>
    <div id="snackbar" class="snackbar">
        <span id="snackbarMessage"></span>
        <button onclick="copySnackbar()" id="copyBtn"><i class="bi bi-copy"></i></button>
        <button onclick="closeSnackbar()">✕</button>
    </div>

    @include('admin.header')
    @include('admin.sidebar')

    <div class="page-content">
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
                    <select name="selected_date" id="dateFilter"
                        class="form-select form-select-sm filter-select-orange">
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
                    <select name="farm_id" id="farmFilter" class="form-select form-select-sm filter-select-blue">
                        <option value="">ฟาร์มทั้งหมด</option>
                        @foreach ($farms as $farm)
                            <option value="{{ $farm->id }}" {{ request('farm_id') == $farm->id ? 'selected' : '' }}>
                                {{ $farm->farm_name }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Batch Filter (Dark Blue) -->
                    <select name="batch_id" id="batchFilter" class="form-select form-select-sm filter-select-blue">
                        <option value="">รุ่นทั้งหมด</option>
                        @foreach ($batches as $batch)
                            <option value="{{ $batch->id }}"
                                {{ request('batch_id') == $batch->id ? 'selected' : '' }}>
                                {{ $batch->batch_code }}
                            </option>
                        @endforeach
                    </select>

                    <div class="ms-auto d-flex gap-2">
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                            data-bs-target="#createModal">
                            <i class="bi bi-plus-circle me-1"></i> เพิ่มหมูเข้า
                        </button>
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="card-custom-secondary mt-3">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-header-custom">
                            <tr>
                                <th class="text-center">วันที่</th>
                                <th class="text-center">ฟาร์ม</th>
                                <th class="text-center">รุ่น (Batch)</th>
                                <th class="text-center">จำนวนหมู</th>
                                <th class="text-center">น้ำหนักรวม</th>
                                <th class="text-center">ราคาลูกหมู</th>
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
                                <tr>
                                    <td>{{ $record->pig_entry_date }}</td>
                                    <td>{{ $record->farm->farm_name ?? '-' }}</td>
                                    <td>{{ $record->batch->batch_code ?? '-' }}</td>
                                    <td>{{ $record->total_pig_amount }}</td>
                                    <td>{{ $record->total_pig_weight }}</td>
                                    <td>{{ number_format($record->total_pig_price, 2) }}</td>

                                    {{-- คำนวณจาก batch->costs --}}
                                    <td>{{ number_format($record->batch->costs->where('cost_type', 'excess_weight')->sum('total_price') ?? 0, 2) }}
                                    </td>
                                    <td>{{ number_format($record->batch->costs->sum('transport_cost') ?? 0, 2) }}</td>
                                    </td>
                                    <td>
                                        {{ number_format(
                                            ($record->total_pig_price ?? 0) +
                                                ($record->excess_weight_cost ?? 0) +
                                                ($record->batch->costs->sum('transport_cost') ?? 0),
                                            2,
                                        ) }}
                                    </td>
                                    <td>{{ $record->note ?? '-' }}</td>
                                    {{-- ดึงภาพจาก cloudinary --}}
                                    <td>
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
                                        <form action="{{ route('pig_entry_records.delete', $record->id) }}"
                                            method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-action btn-danger"
                                                onclick="return confirm('คุณแน่ใจไหมว่าจะลบรายการนี้?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>

                                {{-- Modal Edit --}}
                                <div class="modal fade" id="editModal{{ $record->id }}" tabindex="-1"
                                    aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content bg-dark text-light">
                                            <div class="modal-header">
                                                <h5>แก้ไขข้อมูลหมูเข้า</h5>
                                                <button type="button" class="btn-close"
                                                    data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('pig_entry_records.update', $record->id) }}"
                                                method="POST" enctype="multipart/form-data">
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
                                                        <input type="number" name="total_pig_amount"
                                                            class="form-control"
                                                            value="{{ $record->total_pig_amount }}" required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label>น้ำหนักรวม (กก.)</label>
                                                        <input type="number" step="0.01" name="total_pig_weight"
                                                            class="form-control"
                                                            value="{{ $record->total_pig_weight }}" required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label>ราคารวม (บาท)</label>
                                                        <input type="number" step="0.01" name="total_pig_price"
                                                            class="form-control"
                                                            value="{{ $record->total_pig_price }}" required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label>ค่าน้ำหนักส่วนเกิน</label>
                                                        <input type="number" step="0.01"
                                                            name="excess_weight_cost" class="form-control"
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
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            @endphp ?> ?> ?> ?> ?> ?> ?> ?>
                                                            <small class="text-muted">ไฟล์ปัจจุบัน:</small>
                                                            @if (Str::endsWith($file, ['.jpg', '.jpeg', '.png']))
                                                                <div><img src="{{ $file }}" alt="Receipt"
                                                                        style="max-width:100px;"></div>
                                                            @else
                                                                <div><a href="{{ $file }}"
                                                                        target="_blank">Download</div>
                                                            @endif

                                                            {{-- hidden input กันเคสไม่ได้ติ๊ก checkbox --}}
                                                            <input type="hidden" name="delete_receipt_file"
                                                                value="0">

                                                            <div class="form-check mt-1">
                                                                <input type="checkbox" name="delete_receipt_file"
                                                                    value="1" class="form-check-input"
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
                            <input type="number" name="total_pig_amount" class="form-control" min="1"
                                required>
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
                </form>
            </div>
        </div>
    </div>
    {{-- End Create Modal --}}

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
                batchChoice.clearChoices();
                barnChoice.clearChoices();

                if (!farmId) return;

                fetch('/get-batches/' + farmId)
                    .then(res => res.json())
                    .then(data => {
                        batchChoice.setChoices(
                            data.map(batch => ({
                                value: batch.id,
                                label: batch.batch_code
                            })), 'value', 'label', true
                        );
                    });

                fetch('/get-available-barns/' + farmId)
                    .then(res => res.json())
                    .then(data => {
                        barnChoice.setChoices(
                            data.map(barn => ({
                                value: barn.id,
                                label: barn.barn_code + ' (เหลือ ' + barn.remaining + ' ตัว)'
                            })),
                            'value', 'label', true
                        );
                    });
            });
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

        /* // Edit modal
         if ( /*___directives_script_0___*/
        ) {
            document.addEventListener('shown.bs.modal', function(event) {
                if (event.target.id === 'editModal{{ $record->id }}') {
                    const farm = document.getElementById('editFarmSelect{{ $record->id }}');
                    const batch = document.getElementById('editBatchSelect{{ $record->id }}');
                    const barn = document.getElementById('editBarnSelect{{ $record->id }}');
                    if (!farm.choicesInstance) initFarmBatchBarnChoices(farm, batch, barn);
                }
            });
        @endforeach */
    </script>

    {{-- Auto-submit filters --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterForm = document.getElementById('filterForm');
            const filters = filterForm.querySelectorAll('select');

            filters.forEach(filter => {
                filter.addEventListener('change', function() {
                    filterForm.submit();
                });
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @include('admin.js')
</body>

</html>
