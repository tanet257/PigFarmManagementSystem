@extends('layouts.admin')

@section('title', 'บันทึกสินค้าเข้าคลัง')

@section('content')
    {{-- SnackBar --}}
    <div id="snackbar" class="snackbar">
        <span id="snackbarMessage"></span>
        <button onclick="copySnackbar()" id="copyBtn"><i class="bi bi-copy"></i></button>
        <button onclick="closeSnackbar()">✕</button>
    </div>

    <div class="container my-5">
        <div class="card shadow-lg border-0 rounded-3">
            <div class="card-header text-white">
                <h4 class="mb-0">บันทึกสินค้าเข้าคลัง (Store House Record)</h4>
            </div>
            <div class=" card-body">
                <form action="{{ route('store_house_record.upload') }}" method="post" enctype="multipart/form-data">
                    @csrf

                    <div class="card card-custom-secondary">
                        <div class="text-dark pb-2 d-flex justify-content-between align-items-center rounded-3 p-3">

                            <div class="col-md-5">
                                <div class="dropdown">
                                    <button
                                        class="btn btn-primary dropdown-toggle w-100 d-flex justify-content-between align-items-center"
                                        type="button" id="farmDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span>เลือกฟาร์ม</span>
                                    </button>
                                    <ul class="dropdown-menu w-100" aria-labelledby="farmDropdownBtn" id="farmDropdownMenu">
                                        @foreach ($farms as $farm)
                                            <li>
                                                <a class="dropdown-item" href="#"
                                                    data-farm-id="{{ $farm->id }}">{{ $farm->farm_name }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <input type="hidden" name="farm_id" id="farmSelect" value="">
                                </div>
                            </div>

                            <div class="col-md-5">
                                <div class="dropdown">
                                    <button
                                        class="btn btn-primary dropdown-toggle w-100 d-flex justify-content-between align-items-center"
                                        type="button" id="batchDropdownBtn" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <span>เลือกรุ่น</span>
                                    </button>
                                    <ul class="dropdown-menu w-100" aria-labelledby="batchDropdownBtn"
                                        id="batchDropdownMenu">
                                        <!-- ตัวเลือกจะ populate หลังจากเลือกฟาร์ม -->
                                    </ul>
                                    <input type="hidden" name="batch_id" id="batchSelect" value="">
                                </div>
                            </div>

                        </div>
                    </div>

                    <div card class="card card-custom-secondary">
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
                            <div class="feed-row shadow-lg border-0 rounded-3 mb-3 p3" data-template style="display:none">
                                <input type="hidden" name="feed[0][farm_id]" class="farm-id" value="">
                                <input type="hidden" name="feed[0][batch_id]" class="batch-id" value="">

                                <div class="card-custom-tertiary cardTemplateRow">
                                    <div class="row g-3">
                                        <!-- แถวบน: วันที่ + ประเภท + ชื่อสินค้า -->
                                        <div class="col-md-4">
                                            <input type="text" name="feed[0][date]" class="form-control date-input"
                                                placeholder="ว/ด/ป ชม.นาที" required>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="dropdown">
                                                <button
                                                    class="btn btn-primary dropdown-toggle w-100 d-flex justify-content-between align-items-center item-type-dropdown-btn"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false"><span>--
                                                        เลือกประเภท --</span></button>
                                                <ul class="dropdown-menu w-100 item-type-dropdown-menu">
                                                    <li><a class="dropdown-item" href="#"
                                                            data-value="feed">ค่าอาหาร</a></li>
                                                </ul>
                                                <input type="hidden" name="feed[0][item_type]" class="item-type-hidden"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="dropdown">
                                                <button
                                                    class="btn btn-primary dropdown-toggle w-100 d-flex justify-content-between align-items-center item-dropdown-btn"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false"><span>--
                                                        เลือกชื่อประเภทอาหารหมู --</span></button>
                                                <ul class="dropdown-menu w-100 item-dropdown-menu">
                                                    <!-- ตัวเลือกจะ populate หลังจากเลือก batch และ item type -->
                                                </ul>
                                                <input type="hidden" name="feed[0][item_code]" class="item-code-hidden"
                                                    required>
                                                <input type="hidden" name="feed[0][item_name]" class="item-name-hidden">
                                            </div>
                                        </div>

                                        <!-- แถวกลาง: จำนวน + ราคาต่อชิ้น + หน่วย + ค่าขนส่ง -->
                                        <div class="col-md-3">
                                            <input type="number" name="feed[0][stock]" class="form-control"
                                                placeholder="จำนวน" required>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="feed[0][price_per_unit]" class="form-control"
                                                placeholder="ราคาต่อชิ้น">
                                        </div>
                                        <div class="col-md-3">
                                            <div class="dropdown">
                                                <button
                                                    class="btn btn-primary dropdown-toggle w-100 d-flex justify-content-between align-items-center unit-dropdown-btn"
                                                    type="button" data-bs-toggle="dropdown"
                                                    aria-expanded="false"><span>- เลือกหน่วย -</span></button>
                                                <ul class="dropdown-menu w-100 unit-dropdown-menu">
                                                    <!-- ตัวเลือกจะ populate ตาม item type -->
                                                </ul>
                                                <input type="hidden" name="feed[0][unit]" class="unit-hidden">
                                            </div>
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
                    </div>
                    <!-- END FEED -->

                    <!-- MEDICINE SECTION -->
                    <div card class="card card-custom-secondary">
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
                            <div class="medicine-row shadow-lg border-0 rounded-3 mb-3 p3" data-template
                                style="display:none">
                                <input type="hidden" name="medicine[0][farm_id]" class="farm-id" value="">
                                <input type="hidden" name="medicine[0][batch_id]" class="batch-id" value="">

                                <div class="card-custom-tertiary cardTemplateRow">
                                    <div class="row g-3">
                                        <!-- แถวบน: วันที่ + ประเภท + ชื่อสินค้า -->
                                        <div class="col-md-4">
                                            <input type="text" name="medicine[0][date]"
                                                class="form-control date-input" placeholder="ว/ด/ป ชม.นาที" required>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="dropdown">
                                                <button
                                                    class="btn btn-primary dropdown-toggle w-100 d-flex justify-content-between align-items-center item-type-dropdown-btn"
                                                    type="button" data-bs-toggle="dropdown"
                                                    aria-expanded="false"><span>-- เลือกประเภท --</span></button>
                                                <ul class="dropdown-menu w-100 item-type-dropdown-menu">
                                                    <li><a class="dropdown-item" href="#"
                                                            data-value="medicine">ค่ายา/วัคซีน</a></li>
                                                </ul>
                                                <input type="hidden" name="medicine[0][item_type]"
                                                    class="item-type-hidden" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="dropdown">
                                                <button
                                                    class="btn btn-primary dropdown-toggle w-100 d-flex justify-content-between align-items-center item-dropdown-btn"
                                                    type="button" data-bs-toggle="dropdown"
                                                    aria-expanded="false"><span>-- เลือกชื่อยา/วัคซีน --</span></button>
                                                <ul class="dropdown-menu w-100 item-dropdown-menu">
                                                    <!-- ตัวเลือกจะ populate หลังจากเลือก batch และ item type -->
                                                </ul>
                                                <input type="hidden" name="medicine[0][item_code]"
                                                    class="item-code-hidden" required>
                                                <input type="hidden" name="medicine[0][item_name]"
                                                    class="item-name-hidden">
                                            </div>
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
                                            <div class="dropdown">
                                                <button
                                                    class="btn btn-primary dropdown-toggle w-100 d-flex justify-content-between align-items-center unit-dropdown-btn"
                                                    type="button" data-bs-toggle="dropdown"
                                                    aria-expanded="false"><span>- เลือกหน่วย -</span></button>
                                                <ul class="dropdown-menu w-100 unit-dropdown-menu">
                                                    <!-- ตัวเลือกจะ populate ตาม item type -->
                                                </ul>
                                                <input type="hidden" name="medicine[0][unit]" class="unit-hidden">
                                            </div>
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
                    </div>
                    <!-- END MEDICINE -->

                    <!-- MONTHLY SECTION -->
                    <div card class="card card-custom-secondary">
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
                            <div class="monthly-row shadow-lg border-0 rounded-3 mb-3 p3" data-template
                                style="display:none">
                                <input type="hidden" name="monthly[0][farm_id]" class="farm-id" value="">
                                <input type="hidden" name="monthly[0][batch_id]" class="batch-id" value="">

                                <div class="card-custom-tertiary cardTemplateRow">
                                    <div class="row g-3">
                                        <!-- แถวบน: เดือน/ปี + ประเภทค่าใช้จ่าย -->
                                        <div class="col-md-4">
                                            <input type="text" name="monthly[0][date]"
                                                class="form-control monthly-date-input" placeholder="เดือน/ปี" required>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="dropdown">
                                                <button
                                                    class="btn btn-primary dropdown-toggle w-100 d-flex justify-content-between align-items-center"
                                                    type="button" data-bs-toggle="dropdown"
                                                    aria-expanded="false"><span>-- เลือกประเภทค่าใช้จ่าย --</span></button>
                                                <ul class="dropdown-menu w-100">
                                                    <li><a class="dropdown-item monthly-type-item" href="#"
                                                            data-value="monthly">ค่าใช้จ่ายประจำเดือน</a></li>
                                                </ul>
                                                <input type="hidden" name="monthly[0][item_type]"
                                                    class="monthly-type-hidden" required>
                                            </div>
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
                    </div>
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

                // Farm and Batch Dropdown Elements
                const farmDropdownBtn = document.getElementById('farmDropdownBtn');
                const farmDropdownMenu = document.getElementById('farmDropdownMenu');
                const farmSelect = document.getElementById('farmSelect'); // hidden input

                const batchDropdownBtn = document.getElementById('batchDropdownBtn');
                const batchDropdownMenu = document.getElementById('batchDropdownMenu');
                const batchSelect = document.getElementById('batchSelect'); // hidden input

                // ---------------------
                // FARM DROPDOWN HANDLER
                // ---------------------
                farmDropdownMenu.addEventListener('click', function(e) {
                    if (e.target.classList.contains('dropdown-item')) {
                        e.preventDefault();
                        const farmId = e.target.getAttribute('data-farm-id');
                        const farmName = e.target.textContent;

                        // Update button text
                        farmDropdownBtn.querySelector('span').textContent = farmName;

                        // Update hidden input
                        farmSelect.value = farmId;

                        // Reset batch selection when farm changes
                        batchDropdownBtn.querySelector('span').textContent = 'เลือกรุ่น';
                        batchSelect.value = '';

                        // Populate batch dropdown
                        populateBatchDropdown(parseInt(farmId));

                        // Clear all item dropdowns in existing rows
                        document.querySelectorAll('.feed-row, .medicine-row, .monthly-row').forEach(row => {
                            // Reset item type
                            const itemTypeBtn = row.querySelector('.item-type-dropdown-btn');
                            const itemTypeHidden = row.querySelector('.item-type-hidden');
                            if (itemTypeBtn) itemTypeBtn.querySelector('span').textContent =
                                '-- เลือกประเภท --';
                            if (itemTypeHidden) itemTypeHidden.value = '';

                            // Reset item
                            const itemBtn = row.querySelector('.item-dropdown-btn');
                            const itemCodeHidden = row.querySelector('.item-code-hidden');
                            const itemNameHidden = row.querySelector('.item-name-hidden');
                            if (itemBtn) {
                                if (row.classList.contains('feed-row')) {
                                    itemBtn.querySelector('span').textContent =
                                        '-- เลือกชื่อประเภทอาหารหมู --';
                                } else if (row.classList.contains('medicine-row')) {
                                    itemBtn.querySelector('span').textContent =
                                        '-- เลือกชื่อยา/วัคซีน --';
                                }
                            }
                            if (itemCodeHidden) itemCodeHidden.value = '';
                            if (itemNameHidden) itemNameHidden.value = '';

                            // Reset unit
                            const unitBtn = row.querySelector('.unit-dropdown-btn');
                            const unitHidden = row.querySelector('.unit-hidden');
                            if (unitBtn) unitBtn.querySelector('span').textContent = '- เลือกหน่วย -';
                            if (unitHidden) unitHidden.value = '';
                        });

                        // Update hidden inputs in all rows
                        ['feedRowsContainer', 'medicineRowsContainer', 'monthlyRowsContainer'].forEach(
                            updateHiddenInputs);
                    }
                });

                // ---------------------
                // BATCH DROPDOWN HANDLER
                // ---------------------
                batchDropdownMenu.addEventListener('click', function(e) {
                    if (e.target.classList.contains('dropdown-item')) {
                        e.preventDefault();
                        const batchId = e.target.getAttribute('data-batch-id');
                        const batchCode = e.target.textContent;

                        // Update button text
                        batchDropdownBtn.querySelector('span').textContent = batchCode;

                        // Update hidden input
                        batchSelect.value = batchId;

                        // Update hidden inputs and refresh item options
                        ['feedRowsContainer', 'medicineRowsContainer', 'monthlyRowsContainer'].forEach(
                            updateHiddenInputs);
                        document.querySelectorAll('.feed-row, .medicine-row, .monthly-row').forEach(
                            updateRowOptions);
                    }
                });

                // ---------------------
                // POPULATE BATCH DROPDOWN
                // ---------------------
                function populateBatchDropdown(farmId) {
                    batchDropdownMenu.innerHTML = '';
                    batchDropdownBtn.querySelector('span').textContent = 'เลือกรุ่น';
                    batchSelect.value = '';

                    const filteredBatches = batches.filter(b => b.farm_id === farmId);
                    filteredBatches.forEach(batch => {
                        const li = document.createElement('li');
                        const a = document.createElement('a');
                        a.className = 'dropdown-item';
                        a.href = '#';
                        a.setAttribute('data-batch-id', batch.id);
                        a.textContent = batch.batch_code;
                        li.appendChild(a);
                        batchDropdownMenu.appendChild(li);
                    });
                }

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
                    const typeHidden = row.querySelector('.item-type-hidden');
                    const type = typeHidden?.value;
                    const batchId = parseInt(batchSelect.value);
                    const itemDropdownMenu = row.querySelector('.item-dropdown-menu');
                    const itemDropdownBtn = row.querySelector('.item-dropdown-btn');
                    if (!itemDropdownMenu || !itemDropdownBtn) return;

                    // Clear dropdown menu
                    itemDropdownMenu.innerHTML = '';

                    // Determine placeholder text based on row type or item type value
                    let placeholderText;
                    if (type === 'feed' || row.classList.contains('feed-row')) {
                        placeholderText = '-- เลือกชื่อประเภทอาหารหมู --';
                    } else if (type === 'medicine' || row.classList.contains('medicine-row')) {
                        placeholderText = '-- เลือกชื่อยา/วัคซีน --';
                    } else {
                        placeholderText = '-- เลือกสินค้า --';
                    }
                    itemDropdownBtn.querySelector('span').textContent = placeholderText;

                    if (type && batchId && storehousesByTypeAndBatch[type]?.[batchId]) {
                        Object.values(storehousesByTypeAndBatch[type][batchId]).forEach(item => {
                            const li = document.createElement('li');
                            const a = document.createElement('a');
                            a.className = 'dropdown-item';
                            a.href = '#';
                            a.setAttribute('data-item-code', item.item_code);
                            a.setAttribute('data-item-name', item.item_name);
                            a.textContent = item.item_name;
                            li.appendChild(a);
                            itemDropdownMenu.appendChild(li);
                        });
                    }

                    // Clear hidden inputs
                    const codeHidden = row.querySelector('.item-code-hidden');
                    const nameHidden = row.querySelector('.item-name-hidden');
                    if (codeHidden) codeHidden.value = '';
                    if (nameHidden) nameHidden.value = '';
                }

                function updateUnitOptions(row) {
                    const typeHidden = row.querySelector('.item-type-hidden');
                    const type = typeHidden?.value;
                    const unitDropdownMenu = row.querySelector('.unit-dropdown-menu');
                    const unitDropdownBtn = row.querySelector('.unit-dropdown-btn');
                    if (!unitDropdownMenu || !unitDropdownBtn) return;

                    let units = [];
                    if (row.classList.contains('monthly-row')) units = ['บาท'];
                    else units = unitsByType[type] || [];

                    // Clear dropdown menu
                    unitDropdownMenu.innerHTML = '';
                    unitDropdownBtn.querySelector('span').textContent = '- เลือกหน่วย -';

                    units.forEach(u => {
                        const li = document.createElement('li');
                        const a = document.createElement('a');
                        a.className = 'dropdown-item';
                        a.href = '#';
                        a.setAttribute('data-value', u);
                        a.textContent = u;
                        li.appendChild(a);
                        unitDropdownMenu.appendChild(li);
                    });

                    // Clear hidden input
                    const unitHidden = row.querySelector('.unit-hidden');
                    if (unitHidden) unitHidden.value = '';
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
                // ITEM DROPDOWN CLICK HANDLER
                // ---------------------
                document.addEventListener('click', function(e) {
                    // Handle batch dropdown button clicks - validate farm selection
                    if (e.target.closest('#batchDropdownBtn')) {
                        const farmId = farmSelect.value;
                        if (!farmId) {
                            e.preventDefault();
                            e.stopPropagation();
                            showSnackbar('กรุณาเลือกฟาร์มก่อน', '#dc3545');
                            return false;
                        }
                    }

                    // Handle item type dropdown button clicks - validate farm and batch selection
                    if (e.target.closest('.item-type-dropdown-btn')) {
                        const farmId = farmSelect.value;
                        const batchId = batchSelect.value;

                        if (!farmId) {
                            e.preventDefault();
                            e.stopPropagation();
                            showSnackbar('กรุณาเลือกฟาร์มก่อน', '#dc3545');
                            return false;
                        }
                        if (!batchId) {
                            e.preventDefault();
                            e.stopPropagation();
                            showSnackbar('กรุณาเลือกรุ่นก่อน', '#dc3545');
                            return false;
                        }
                    }

                    // Handle item dropdown button clicks - validate prerequisites
                    if (e.target.closest('.item-dropdown-btn')) {
                        const row = e.target.closest('.feed-row, .medicine-row');
                        if (!row) return;

                        const farmId = farmSelect.value;
                        const batchId = batchSelect.value;
                        const typeHidden = row.querySelector('.item-type-hidden');
                        const itemType = typeHidden?.value;

                        if (!farmId) {
                            e.preventDefault();
                            e.stopPropagation();
                            showSnackbar('กรุณาเลือกฟาร์มก่อน', '#dc3545');
                            return false;
                        }
                        if (!batchId) {
                            e.preventDefault();
                            e.stopPropagation();
                            showSnackbar('กรุณาเลือกรุ่นก่อน', '#dc3545');
                            return false;
                        }
                        if (!itemType) {
                            e.preventDefault();
                            e.stopPropagation();
                            showSnackbar('กรุณาเลือกประเภทก่อน', '#dc3545');
                            return false;
                        }
                    }

                    // Handle unit dropdown button clicks - validate prerequisites
                    if (e.target.closest('.unit-dropdown-btn')) {
                        const row = e.target.closest('.feed-row, .medicine-row, .monthly-row');
                        if (!row) return;

                        if (!row.classList.contains('monthly-row')) {
                            const typeHidden = row.querySelector('.item-type-hidden');
                            const itemType = typeHidden?.value;

                            if (!itemType) {
                                e.preventDefault();
                                e.stopPropagation();
                                showSnackbar('กรุณาเลือกประเภทก่อน', '#dc3545');
                                return false;
                            }
                        }
                    }

                    // Handle item dropdown clicks
                    if (e.target.classList.contains('dropdown-item') && e.target.closest(
                            '.item-dropdown-menu')) {
                        e.preventDefault();
                        const row = e.target.closest('.feed-row, .medicine-row, .monthly-row');
                        if (!row) return;

                        const itemCode = e.target.getAttribute('data-item-code');
                        const itemName = e.target.getAttribute('data-item-name');
                        const itemDropdownBtn = row.querySelector('.item-dropdown-btn');
                        const codeHidden = row.querySelector('.item-code-hidden');
                        const nameHidden = row.querySelector('.item-name-hidden');

                        if (itemDropdownBtn) itemDropdownBtn.querySelector('span').textContent = itemName;
                        if (codeHidden) codeHidden.value = itemCode;
                        if (nameHidden) nameHidden.value = itemName;
                    }

                    // Handle item type dropdown clicks
                    if (e.target.classList.contains('dropdown-item') && e.target.closest(
                            '.item-type-dropdown-menu')) {
                        e.preventDefault();
                        const row = e.target.closest('.feed-row, .medicine-row');
                        if (!row) return;

                        const value = e.target.getAttribute('data-value');
                        const text = e.target.textContent;
                        const btn = row.querySelector('.item-type-dropdown-btn');
                        const hidden = row.querySelector('.item-type-hidden');

                        if (btn) btn.querySelector('span').textContent = text;
                        if (hidden) hidden.value = value;

                        // Update item and unit dropdowns
                        updateRowOptions(row);
                    }

                    // Handle unit dropdown clicks
                    if (e.target.classList.contains('dropdown-item') && e.target.closest(
                            '.unit-dropdown-menu')) {
                        e.preventDefault();
                        const row = e.target.closest('.feed-row, .medicine-row, .monthly-row');
                        if (!row) return;

                        const value = e.target.getAttribute('data-value');
                        const btn = row.querySelector('.unit-dropdown-btn');
                        const hidden = row.querySelector('.unit-hidden');

                        if (btn) btn.querySelector('span').textContent = value;
                        if (hidden) hidden.value = value;
                    }

                    // Handle monthly type dropdown clicks
                    if (e.target.classList.contains('monthly-type-item')) {
                        e.preventDefault();
                        const row = e.target.closest('.monthly-row');
                        if (!row) return;

                        const value = e.target.getAttribute('data-value');
                        const text = e.target.textContent;
                        const btn = e.target.closest('.dropdown').querySelector('button');
                        const hidden = row.querySelector('.monthly-type-hidden');

                        if (btn) btn.querySelector('span').textContent = text;
                        if (hidden) hidden.value = value;
                    }
                });

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
