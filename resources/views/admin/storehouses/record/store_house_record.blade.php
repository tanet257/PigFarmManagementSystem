@extends('layouts.admin')

@section('title', 'บันทึกสินค้าเข้าคลัง')

@section('content')
    <div class="container my-5">
        <div class="card shadow-lg border-0 rounded-3">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">บันทึกสินค้าเข้าคลัง (Store House Record)</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('store_house_record.upload') }}" method="post" enctype="multipart/form-data">
                    @csrf

                    <div class="text-dark pb-2 d-flex justify-content-between align-items-center rounded-3 p-3"
                        style="background: #CBCBCB">
                        <div class="col-md-5">
                            <label>ฟาร์ม</label>
                            <select name="farm_id" id="farmSelect" class="form-select" title="เลือกฟาร์ม" required>
                                <option value="">-- เลือกฟาร์ม --</option>
                                @foreach ($farms as $farm)
                                    <option value="{{ $farm->id }}">{{ $farm->farm_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label>รุ่น / Batch</label>
                            <select name="batch_id" class="form-select" id="batchSelect" title="เลือกรุ่น" required>
                                <option value="">-- เลือกรุ่น --</option>
                            </select>
                        </div>
                    </div>

                    <div class="text-white pt-2 pb-2 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">อาหารสุกรขาเข้า</h4>
                        <div>
                            <button type="button" class="btn btn-danger btn-sm" id="clearAddFeedRowBtn"
                                data-bs-toggle="tooltip" title="ล้างแถวที่ถูกเพิ่ม">
                                <i class="bi bi-dash-lg"></i> ล้างแถว
                            </button>
                            <button type="button" class="btn btn-success btn-sm" id="addFeedRowBtn"
                                data-bs-toggle="tooltip" title="เพิ่มแถว">
                                <i class="bi bi-plus-lg"></i> เพิ่มแถว
                            </button>
                        </div>
                    </div>

                    <!-- FEED ROWS -->
                    <div id="feedRowsContainer"></div>
                    <template id="feedRowTemplate">
                        <div class="feed-row  card shadow-lg border-0 rounded-3 mb-3 p3" data-template style="display:none">
                            <input type="hidden" name="feed[0][farm_id]" class="farm-id" value="">
                            <input type="hidden" name="feed[0][batch_id]" class="batch-id" value="">

                            <div class="card-body cardTemplateRow">
                                <div class="row g-3">
                                    <!-- แถวบน: วันที่ + ประเภท + ชื่อสินค้า -->
                                    <div class="col-md-4">
                                        <input type="text" name="feed[0][date]" class="form-control date-input"
                                            placeholder="ว/ด/ป ชม.นาที" required>
                                    </div>
                                    <div class="col-md-4">
                                        <select name="feed[0][item_type]" class="form-select item-type-select" required>
                                            <option value="">-- เลือกประเภท --</option>
                                            <option value="feed">ค่าอาหาร</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <select name="feed[0][item_code]" class="form-select item-code-select" required>
                                            <option value="">-- เลือกชื่อประเภทอาหารหมู --</option>
                                            @foreach ($storehouses as $storehouse)
                                                <option value="{{ $storehouse->item_code }}"
                                                    data-name="{{ $storehouse->item_name }}">
                                                    {{ $storehouse->item_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="feed[0][item_name]" class="item-name-hidden">
                                    </div>

                                    <!-- แถวกลาง: จำนวน + ราคาต่อชิ้น + หน่วย + ค่าขนส่ง -->
                                    <div class="col-md-3">
                                        <input type="number" name="feed[0][stock]" class="form-control" placeholder="จำนวน"
                                            required>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" name="feed[0][price_per_unit]" class="form-control"
                                            placeholder="ราคาต่อชิ้น">
                                    </div>
                                    <div class="col-md-3">
                                        <select name="feed[0][unit]" class="form-select unit-select">
                                            <option value="">- เลือกหน่วย -</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" name="feed[0][transport_cost]" class="form-control"
                                            placeholder="ค่าขนส่ง">
                                    </div>

                                    <!-- แถวล่าง: ใบเสร็จ + หมายเหตุ + ปุ่มลบ -->
                                    <div class="col-md-5">
                                        <input type="file" name="feed[0][receipt_file]" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <textarea name="feed[0][note]" rows="2" class="form-control" placeholder="หมายเหตุ"></textarea>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger remove-row">ลบแถว</button>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </template>
                    <!-- END FEED -->

                    <!-- MEDICINE SECTION -->
                    <div class="text-white pt-2 pb-2 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">ยา/วัคซีนสุกรขาเข้า</h4>
                        <div>
                            <button type="button" class="btn btn-danger btn-sm" id="clearAddMedicineRowBtn"
                                data-bs-toggle="tooltip" title="ล้างแถวที่ถูกเพิ่ม">
                                <i class="bi bi-dash-lg"></i> ล้างแถว
                            </button>
                            <button type="button" class="btn btn-success btn-sm" id="addMedicineRowBtn"
                                data-bs-toggle="tooltip" title="เพิ่มแถว">
                                <i class="bi bi-plus-lg"></i> เพิ่มแถว
                            </button>
                        </div>
                    </div>

                    <!-- MEDICINE ROWS -->
                    <div id="medicineRowsContainer"></div>
                    <template id="medicineRowTemplate">
                        <div class="medicine-row card shadow-lg border-0 rounded-3 mb-3 p3" data-template
                            style="display:none">
                            <input type="hidden" name="medicine[0][farm_id]" class="farm-id" value="">
                            <input type="hidden" name="medicine[0][batch_id]" class="batch-id" value="">

                            <div class="card-body cardTemplateRow">
                                <div class="row g-3">
                                    <!-- แถวบน: วันที่ + ประเภท + ชื่อสินค้า -->
                                    <div class="col-md-4">
                                        <input type="text" name="medicine[0][date]" class="form-control date-input"
                                            placeholder="ว/ด/ป ชม.นาที" required>
                                    </div>
                                    <div class="col-md-4">
                                        <select name="medicine[0][item_type]" class="form-select item-type-select"
                                            required>
                                            <option value="">-- เลือกประเภท --</option>
                                            <option value="medicine">ค่ายา/วัคซีน</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <select name="medicine[0][item_code]" class="form-select item-code-select"
                                            required>
                                            <option value="">-- เลือกชื่อยา/วัคซีน --</option>
                                            @foreach ($storehouses as $storehouse)
                                                <option value="{{ $storehouse->item_code }}"
                                                    data-name="{{ $storehouse->item_name }}">
                                                    {{ $storehouse->item_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="medicine[0][item_name]" class="item-name-hidden">
                                    </div>

                                    <!-- แถวกลาง: จำนวน + ราคาต่อหน่วย + หน่วย -->
                                    <div class="col-md-4">
                                        <input type="number" name="medicine[0][stock]" class="form-control"
                                            placeholder="จำนวน" required>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="number" name="medicine[0][price_per_unit]" class="form-control"
                                            placeholder="ราคาต่อหน่วย">
                                    </div>
                                    <div class="col-md-4">
                                        <select name="medicine[0][unit]" class="form-select unit-select">
                                            <option value="">- เลือกหน่วย -</option>
                                        </select>
                                    </div>

                                    <!-- แถวล่าง: ใบเสร็จ + หมายเหตุ + ปุ่มลบ -->
                                    <div class="col-md-5">
                                        <input type="file" name="medicine[0][receipt_file]" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <textarea name="medicine[0][note]" rows="2" class="form-control" placeholder="หมายเหตุ"></textarea>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger remove-row">ลบแถว</button>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </template>
                    <!-- END MEDICINE -->

                    <!-- MONTHLY SECTION -->
                    <div class="text-white pt-2 pb-2 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">ต้นทุนรายเดือน(ค่าแรง/ค่าน้ำค่าไฟ)</h4>
                        <div>
                            <button type="button" class="btn btn-danger btn-sm" id="clearAddMonthlyRowBtn"
                                data-bs-toggle="tooltip" title="ล้างแถวที่ถูกเพิ่ม">
                                <i class="bi bi-dash-lg"></i> ล้างแถว
                            </button>
                            <button type="button" class="btn btn-success btn-sm" id="addMonthlyRowBtn"
                                data-bs-toggle="tooltip" title="เพิ่มแถว">
                                <i class="bi bi-plus-lg"></i> เพิ่มแถว
                            </button>
                        </div>
                    </div>

                    <!-- MONTHLY ROWS -->
                    <div id="monthlyRowsContainer"></div>
                    <template id="monthlyRowTemplate">
                        <div class="monthly-row card shadow-lg border-0 rounded-3 mb-3 p3" data-template
                            style="display:none">
                            <input type="hidden" name="monthly[0][farm_id]" class="farm-id" value="">
                            <input type="hidden" name="monthly[0][batch_id]" class="batch-id" value="">

                            <div class="card-body cardTemplateRow">
                                <div class="row g-3">
                                    <!-- แถวบน: เดือน/ปี + ประเภทค่าใช้จ่าย -->
                                    <div class="col-md-4">
                                        <input type="text" name="monthly[0][date]"
                                            class="form-control monthly-date-input" placeholder="เดือน/ปี"
                                            style="background:#f5f5f5" required>
                                    </div>
                                    <div class="col-md-5">
                                        <select name="monthly[0][item_type]" class="form-select" required>
                                            <option value="">-- เลือกประเภทค่าใช้จ่าย --</option>
                                            <option value="monthly">ค่าใช้จ่ายประจำเดือน</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" name="monthly[0][price]" class="form-control"
                                            placeholder="จำนวนเงิน" required>
                                    </div>

                                    <!-- แถวล่าง: ใบเสร็จ + หมายเหตุ + ปุ่มลบ -->
                                    <div class="col-md-5">
                                        <input type="file" name="monthly[0][receipt_file]" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <textarea name="monthly[0][note]" rows="2" class="form-control" placeholder="หมายเหตุ"></textarea>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger remove-row">ลบแถว</button>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </template>
                    <!-- END MONTHLY -->
                    <!-- ปุ่มติดขวาล่าง -->
                    <div class="position-sticky bottom-0 d-flex justify-content-end" style="z-index:10;">
                        <button type="submit" class="btn btn-primary">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const batches = @json($batches);
                const storehousesByTypeAndBatch = @json($storehousesByTypeAndBatch);
                const unitsByType = @json($unitsByType->map(fn($c) => $c->values())->toArray());
                const farmSelect = document.getElementById('farmSelect');
                const batchSelect = document.getElementById('batchSelect');

                // ---------------------
                // DATE INPUT CUSTOM
                // ---------------------
                function attachDateInputEvents(root) {
                    root.querySelectorAll('.date-input').forEach(input => {
                        if (input._attached) return;
                        input._attached = true;

                        input.addEventListener('focus', function() {
                            this.type = 'datetime-local';
                        });
                        input.addEventListener('blur', function() {
                            if (this.value) {
                                const dt = new Date(this.value);
                                const day = String(dt.getDate()).padStart(2, '0');
                                const month = String(dt.getMonth() + 1).padStart(2, '0');
                                const year = dt.getFullYear();
                                const hours = String(dt.getHours()).padStart(2, '0');
                                const mins = String(dt.getMinutes()).padStart(2, '0');
                                this.type = 'text';
                                this.value = `${day}/${month}/${year} ${hours}:${mins}`;
                            }
                        });
                    });
                }

                // ---------------------
                // MONTH INPUT CUSTOM
                // ---------------------
                function attachMonthlyInputEvents(root) {
                    root.querySelectorAll('.monthly-date-input').forEach(input => {
                        if (input._attached) return;
                        input._attached = true;

                        input.addEventListener('focus', function() {
                            const val = this.dataset.submitValue || '';
                            this.type = 'month';
                            if (val) this.value = val;
                        });

                        input.addEventListener('blur', function() {
                            if (this.value) {
                                const [year, month] = this.value.split('-');
                                if (year && month) {
                                    this.dataset.submitValue = this.value;
                                    this.type = 'text';
                                    this.value = `${month}/${year}`;
                                }
                            }
                        });
                    });
                }

                // ---------------------
                // HIDDEN INPUT UPDATE
                // ---------------------
                function updateHiddenInputs(containerId) {
                    const farmId = farmSelect.value || '';
                    const batchId = batchSelect.value || '';
                    document.querySelectorAll(`#${containerId} .farm-id`).forEach(i => i.value = farmId);
                    document.querySelectorAll(`#${containerId} .batch-id`).forEach(i => i.value = batchId);
                }

                // ---------------------
                // ITEM CODE / UNIT
                // ---------------------
                function updateItemCodeOptions(row) {
                    const type = row.querySelector('.item-type-select')?.value;
                    const batchId = parseInt(batchSelect.value);
                    const itemCodeSelect = row.querySelector('.item-code-select');
                    if (!itemCodeSelect) return;

                    itemCodeSelect.innerHTML = '<option value="">-- เลือกชื่อสินค้า --</option>';

                    if (type && batchId && storehousesByTypeAndBatch[type]?.[batchId]) {
                        Object.values(storehousesByTypeAndBatch[type][batchId]).forEach(item => {
                            const opt = document.createElement('option');
                            opt.value = item.item_code;
                            opt.textContent = item.item_name;
                            opt.dataset.name = item.item_name;
                            itemCodeSelect.appendChild(opt);
                        });
                    }

                    const hidden = row.querySelector('.item-name-hidden');
                    if (hidden) hidden.value = '';
                }

                function updateUnitOptions(row) {
                    const type = row.querySelector('.item-type-select')?.value;
                    const unitSelect = row.querySelector('.unit-select');
                    if (!unitSelect) return;

                    let units = [];
                    if (row.classList.contains('monthly-row')) units = ['บาท'];
                    else units = unitsByType[type] || [];

                    unitSelect.innerHTML = '<option value="">- เลือกหน่วย -</option>';
                    units.forEach(u => {
                        const o = document.createElement('option');
                        o.value = u;
                        o.textContent = u;
                        unitSelect.appendChild(o);
                    });
                }

                function updateRowOptions(row) {
                    updateItemCodeOptions(row);
                    updateUnitOptions(row);
                }

                // ---------------------
                // FARM / BATCH CHANGE
                // ---------------------
                farmSelect.addEventListener('change', function() {
                    const farmId = parseInt(this.value);
                    batchSelect.innerHTML = '<option value="">-- เลือกรุ่น --</option>';
                    batches.filter(b => b.farm_id === farmId).forEach(b => {
                        const opt = document.createElement('option');
                        opt.value = b.id;
                        opt.textContent = b.batch_code;
                        batchSelect.appendChild(opt);
                    });
                    ['feedRowsContainer', 'medicineRowsContainer', 'monthlyRowsContainer'].forEach(
                        updateHiddenInputs);
                });

                batchSelect.addEventListener('change', function() {
                    ['feedRowsContainer', 'medicineRowsContainer', 'monthlyRowsContainer'].forEach(
                        updateHiddenInputs);
                    document.querySelectorAll('.feed-row, .medicine-row, .monthly-row').forEach(
                        updateRowOptions);
                });

                // ---------------------
                // ADD ROW
                // ---------------------
                function addRow(containerId, prefix) {
                    const container = document.getElementById(containerId);
                    const template = document.getElementById(`${prefix}RowTemplate`);
                    if (!template) return;

                    const rows = container.querySelectorAll(`.${prefix}-row:not([data-template])`);
                    const newIndex = rows.length;

                    const newRow = template.content.firstElementChild.cloneNode(true);
                    newRow.style.display = 'block';
                    newRow.removeAttribute('data-template');

                    // reset input
                    newRow.querySelectorAll('input, textarea').forEach(i => {
                        if (i.type !== 'hidden' && i.type !== 'file') i.value = '';
                        if (i.type === 'file') i.value = null;
                    });
                    newRow.querySelectorAll('select').forEach(s => s.selectedIndex = 0);

                    // update name index
                    newRow.querySelectorAll('input, select, textarea').forEach(el => {
                        if (el.name) el.name = el.name.replace(/\[\d+\]/, `[${newIndex}]`);
                    });

                    container.appendChild(newRow);

                    attachDateInputEvents(newRow);
                    attachMonthlyInputEvents(newRow);
                    updateRowOptions(newRow);
                    updateHiddenInputs(containerId);
                }

                // ---------------------
                // BUTTONS EVENT
                // ---------------------
                document.getElementById('addFeedRowBtn')?.addEventListener('click', () => addRow('feedRowsContainer',
                    'feed'));
                document.getElementById('addMedicineRowBtn')?.addEventListener('click', () => addRow(
                    'medicineRowsContainer', 'medicine'));
                document.getElementById('addMonthlyRowBtn')?.addEventListener('click', () => addRow(
                    'monthlyRowsContainer', 'monthly'));

                function clearRows(containerId) {
                    const container = document.getElementById(containerId);
                    const rows = Array.from(container.children).filter(c =>
                        c.classList.contains('feed-row') ||
                        c.classList.contains('medicine-row') ||
                        c.classList.contains('monthly-row')
                    );

                    // ลบทุก row เลย
                    rows.forEach(r => r.remove());

                    // อัปเดต hidden inputs หลังลบ row
                    updateHiddenInputs(containerId);
                }

                document.getElementById('clearAddFeedRowBtn')?.addEventListener('click', () => clearRows(
                    'feedRowsContainer'));
                document.getElementById('clearAddMedicineRowBtn')?.addEventListener('click', () => clearRows(
                    'medicineRowsContainer'));
                document.getElementById('clearAddMonthlyRowBtn')?.addEventListener('click', () => clearRows(
                    'monthlyRowsContainer'));

                // ---------------------
                // REMOVE ROW
                // ---------------------
                document.addEventListener('click', function(e) {
                    if (e.target.classList.contains('remove-row')) {
                        const row = e.target.closest('.feed-row, .medicine-row, .monthly-row');
                        if (row) row.remove();
                    }
                });

                // ---------------------
                // ITEM_NAME HIDDEN UPDATE
                // ---------------------
                document.addEventListener('change', function(e) {
                    const row = e.target.closest('.feed-row, .medicine-row, .monthly-row');
                    if (!row) return;
                    if (e.target.classList.contains('item-type-select')) updateRowOptions(row);
                    if (e.target.classList.contains('item-code-select')) {
                        const hidden = row.querySelector('.item-name-hidden');
                        if (hidden) hidden.value = e.target.selectedOptions[0]?.dataset.name || '';
                    }
                });

                // ---------------------
                // INITIALIZE EXISTING ROWS
                // ---------------------
                attachDateInputEvents(document);
                attachMonthlyInputEvents(document);
                ['feedRowsContainer', 'medicineRowsContainer', 'monthlyRowsContainer'].forEach(updateHiddenInputs);
                document.querySelectorAll('.feed-row, .medicine-row, .monthly-row').forEach(updateRowOptions);
            });
        </script>
    @endpush
@endsection
