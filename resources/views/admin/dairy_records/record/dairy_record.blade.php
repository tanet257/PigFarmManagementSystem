@extends('layouts.admin')

@section('title', 'บันทึกประจำวัน')

@push('styles')
    <style>
        /* ทำให้ dropdown-container เป็น reference point (เพื่อ width:100% ของเมนูอ้างอิงได้) */
        .dropdown {
            position: relative;
        }

        /* บังคับเมนูให้มีความกว้างเท่าปุ่มและไม่ขยายตามเนื้อหา */
        .dropdown .dropdown-menu.w-100 {
            width: 100% !important;
            min-width: 0 !important;
            /* ป้องกัน min-width ของ bootstrap ขยาย */
            max-width: 100% !important;
            box-sizing: border-box;
            left: 0 !important;
        }

        /* ปุ่มที่แสดงผลการเลือก ให้ตัดข้อความยาวเป็น ... */
        .dropdown-toggle {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* รายการในเมนูให้ตัดข้อความยาวเป็น ... */
        .dropdown-item.text-truncate,
        .dropdown-menu .dropdown-item.d-block.text-truncate {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
        }

        /* ถ้าคุณใช้ปุ่มแบบ item-dropdown-btn (ปุ่มแสดงรายการ item) */
        .item-dropdown-btn {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: block;
        }

        /* Card Row */
        .cardTemplateRow {
            background: #a3a3a3;
            /* สีพื้นอ่อน */
            border-radius: 10px;
            padding: 12px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .cardTemplateRow:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        /* Label */
        label {
            display: inline-block;
            font-weight: 600;
            margin-bottom: 6px;
            color: #333333;
        }

        /* Input / Textarea / Select */
        input.form-control,
        select.form-select,
        textarea.form-control {
            border-radius: 8px;
            background: #fafafa;
            border: 1px solid #d1d1d1;
            color: #333333;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.05);
            padding: 8px 12px;
            transition: all 0.2s;
        }

        input.form-control:focus,
        select.form-select:focus,
        textarea.form-control:focus {
            outline: none;
            border-color: #4a90e2;
            background-color: #fafafa;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.15);
        }

        /* Number input hide spin buttons */
        input[type=number]::-webkit-outer-spin-button,
        input[type=number]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type=number] {
            -moz-appearance: textfield;
        }

        /* Dropdown button */
        .dropdown-toggle {
            background-color: #fafafa;
            border: 1px solid #d1d1d1;
            border-radius: 8px;
            color: #333333;
            padding: 8px 12px;
            text-align: left;
            transition: all 0.2s;
        }

        .dropdown-toggle:hover {
            background-color: rgba(0, 0, 0, 0.03);
            /* สีดำจาง 3% เหมือนเงาจางๆ */
        }

        .dropdown-menu {
            border-radius: 8px;
            border: 1px solid #CBCBCB;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .dropdown-item {
            transition: background-color 0.15s;
        }

        .dropdown-item:hover {
            background-color: #CBCBCB;
            color: #333333;
        }

        /* File input */
        input[type="file"].form-control {
            border: 1px solid #d1d1d1;
            border-radius: 8px;
            background-color: #ffffff;
            padding: 8px;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        input[type="file"].form-control::file-selector-button {
            background-color: #4a90e2;
            color: #ffffff;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        input[type="file"].form-control::file-selector-button:hover {
            background-color: #357ab8;
        }
    </style>
@endpush

@section('content')
    <div class="container my-5">
        <div class="card shadow-lg border-0 rounded-3">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">บันทึกประจำวัน (Dairy Record)</h4>
            </div>
            <div class="card-body">
                <div class="card-body position-relative" style="max-height: 80vh; overflow-y: auto;">
                    <form action="{{ route('dairy_records.upload') }}" method="post" enctype="multipart/form-data">
                        @csrf

                        <div class="text-dark pb-2 d-flex justify-content-between align-items-center rounded-3 p-3"
                            style="background: #CBCBCB">
                            <div class="col-md-5">
                                <label>ฟาร์ม</label>
                                <div class="dropdown">
                                    <button class="btn btn-white dropdown-toggle w-100 text-start" type="button"
                                        id="farmDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false">
                                        เลือกฟาร์ม
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
                                <label>รุ่น / Batch</label>
                                <div class="dropdown">
                                    <button class="btn btn-white dropdown-toggle w-100 text-start" type="button"
                                        id="batchDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false">
                                        เลือกรุ่น
                                    </button>
                                    <ul class="dropdown-menu w-100" aria-labelledby="batchDropdownBtn"
                                        id="batchDropdownMenu">
                                        <!-- ตัวเลือกจะ populate หลังจากเลือกฟาร์ม -->
                                    </ul>
                                    <input type="hidden" name="batch_id" id="batchSelect" value="">
                                </div>
                            </div>

                        </div>

                        <!--Feed Section-->
                        <div class="text-white pt-2 pb-2 d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">อาหารสุกรที่ใช้</h4>
                            <div>
                                <button type="button" class="btn btn-danger btn-sm" id="clearFeedUseBtn">ล้างแถว</button>
                                <button type="button" class="btn btn-success btn-sm" id="addFeedUseBtn">เพิ่มแถว</button>
                            </div>
                        </div>
                        <div id="feedUseContainer"></div>
                        <template id="feedUseTemplate">
                            <div class="feed-use-row card shadow-lg border-0 rounded-3 mb-3 p3" data-template
                                style="display:none">
                                <input type="hidden" name="feed_use[0][farm_id]" class="farm-id">
                                <input type="hidden" name="feed_use[0][batch_id]" class="batch-id">
                                <input type="hidden" name="feed_use[0][item_type]" class="item-type" value="feed">

                                <div class="card-body cardTemplateRow">
                                    <div class="row g-2">
                                        <!-- แถว 1: วันที่ + เล้า + อาหาร -->
                                        <div class="col-md-4">
                                            <input type="text" name="feed_use[0][date]" class="form-control date-input"
                                                placeholder="ว/ด/ป ชม.นาที" required>
                                        </div>
                                        <div data-cloned="1">
                                            <div class="col-md-4">
                                                <button
                                                    class="barn-select btn btn-white dropdown-toggle shadow-sm border w-100 text-start"
                                                    type="button" data-bs-toggle="dropdown"
                                                    aria-expanded="false">เลือกเล้า</button>
                                                <ul class="dropdown-menu w-100 barn-dropdown"></ul>
                                                <input type="hidden" class="barn-id" name="feed_use[0][barn_id]"
                                                    value="">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="dropdown">
                                                <button
                                                    class="btn btn-white dropdown-toggle w-100 text-start item-dropdown-btn"
                                                    type="button" id="feedItemDropdownBtn0" data-bs-toggle="dropdown"
                                                    aria-expanded="false">เลือกอาหาร</button>
                                                <ul class="dropdown-menu w-100 item-dropdown-menu"
                                                    aria-labelledby="feedItemDropdownBtn0" id="feedItemDropdownMenu0">
                                                    <!-- ตัวเลือกจะ populate หลังจากเลือก batch -->
                                                </ul>
                                                <input type="hidden" name="feed_use[0][item_code]" class="item-code"
                                                    value="">
                                                <input type="hidden" name="feed_use[0][item_name]" class="item-name"
                                                    value="">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-2 mt-2">
                                        <!-- แถว 2: จำนวน + หมายเหตุ + ปุ่มลบ -->
                                        <div class="col-md-4">
                                            <input type="number" name="feed_use[0][quantity]" class="form-control"
                                                placeholder="จำนวน" required>
                                        </div>
                                        <div class="col-md-7">
                                            <textarea name="feed_use[0][note]" class="form-control" rows="1" placeholder="หมายเหตุ"></textarea>
                                        </div>
                                        <div class="col-md-1 d-flex align-items-end">
                                            <button type="button" class="btn btn-danger remove-row w-100">ลบ</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </template>
                        <!-- END FEED -->

                        <!-- MEDICINE SECTION -->
                        <div class="text-white pt-2 pb-2 d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">ยา/วัคซีนที่ใช้</h4>
                            <div>
                                <button type="button" class="btn btn-danger btn-sm"
                                    id="clearMedicineUseBtn">ล้างแถว</button>
                                <button type="button" class="btn btn-success btn-sm"
                                    id="addMedicineUseBtn">เพิ่มแถว</button>
                            </div>
                        </div>

                        <div id="medicineUseContainer"></div>
                        <template id="medicineUseTemplate">
                            <div class="medicine-use-row card shadow-lg border-0 rounded-3 mb-3 p3" data-template
                                style="display:none">
                                <input type="hidden" name="medicine_use[0][farm_id]" class="farm-id">
                                <input type="hidden" name="medicine_use[0][batch_id]" class="batch-id">
                                <input type="hidden" name="medicine_use[0][item_type]" class="item-type"
                                    value="medicine">
                                <input type="hidden" name="medicine_use[0][barn_pen]" class="barn-pen-json">

                                <div class="card-body cardTemplateRow">
                                    <!-- แถว 1: วันที่ + เล้า + คอก + ยา/วัคซีน -->
                                    <div class="row g-2">
                                        <div class="col-md-3">
                                            <input type="text" name="medicine_use[0][date]"
                                                class="form-control date-input" placeholder="ว/ด/ป ชม.นาที" required>
                                        </div>
                                        <div data-cloned="1">
                                            <div class="col-md-2">
                                                <button
                                                    class="barn-select btn btn-white dropdown-toggle shadow-sm border w-100 text-start"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    เลือกเล้า
                                                </button>
                                                <ul class="dropdown-menu w-100 barn-dropdown"></ul>
                                                <input type="hidden" class="barn-id" name="medicine_use[0][barn_id]"
                                                    value="">
                                            </div>
                                            <div class="col-md-2">
                                                <button
                                                    class="pen-select btn btn-white dropdown-toggle shadow-sm border w-100 text-start"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    เลือกคอก
                                                </button>
                                                <ul class="dropdown-menu w-100 pen-dropdown"></ul>
                                                <input type="hidden" class="barn-pen-json"
                                                    name="medicine_use[0][barn_pen]" value="">
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="dropdown">
                                                <button
                                                    class="btn btn-white dropdown-toggle w-100 text-start item-dropdown-btn"
                                                    type="button" id="medicineItemDropdownBtn0"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    เลือกยา/วัคซีน
                                                </button>
                                                <ul class="dropdown-menu w-100 item-dropdown-menu"
                                                    aria-labelledby="medicineItemDropdownBtn0"
                                                    id="medicineItemDropdownMenu0">
                                                    <!-- ตัวเลือก populate หลังเลือก batch -->
                                                </ul>
                                                <input type="hidden" name="medicine_use[0][item_code]" class="item-code"
                                                    value="">
                                                <input type="hidden" name="medicine_use[0][item_name]" class="item-name"
                                                    value="">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- แถว 2: จำนวน + สถานะ + หมายเหตุ + ปุ่มลบ -->
                                    <div class="row g-2 mt-2">
                                        <div class="col-md-2">
                                            <input type="number" name="medicine_use[0][quantity]" class="form-control"
                                                placeholder="จำนวน" required>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="dropdown">
                                                <button
                                                    class="btn btn-white dropdown-toggle w-100 text-start
                                                        medicine-status-dropdown-btn"
                                                    type="button" id="medicineStatusDropdownBtn0"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    เลือกสถานะ
                                                </button>
                                                <ul class="dropdown-menu w-100 medicine-status-dropdown-menu"
                                                    aria-labelledby="medicineStatusDropdownBtn0">
                                                    <li><a class="dropdown-item" href="#"
                                                            data-value="วางแผนว่าจะให้ยา">วางแผนว่าจะให้ยา</a>
                                                    </li>
                                                    <li><a class="dropdown-item" href="#"
                                                            data-value="กำลังดำเนินการ (กำลังฉีด/กำลังให้ยาอยู่)">กำลังดำเนินการ</a>
                                                    </li>
                                                    <li><a class="dropdown-item" href="#"
                                                            data-value="ให้ยาเสร็จแล้ว">ให้ยาเสร็จแล้ว</a></li>
                                                    <li><a class="dropdown-item" href="#"
                                                            data-value="ยกเลิก">ยกเลิก</a></li>
                                                </ul>
                                                <input type="hidden" name="medicine_use[0][status]" class="status-value"
                                                    value="">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <textarea name="medicine_use[0][note]" class="form-control" rows="1" placeholder="หมายเหตุ"></textarea>
                                        </div>
                                        <div class="col-md-1 d-flex align-items-end">
                                            <button type="button" class="btn btn-danger remove-row w-100">ลบ</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>


                        <!-- END MEDICINE -->

                        <!-- PIGDEATH SECTION -->
                        <div class="text-white pt-2 pb-2 d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">สุกรตาย</h4>
                            <div>
                                <button type="button" class="btn btn-danger btn-sm"
                                    id="clearDeadPigBtn">ล้างแถว</button>
                                <button type="button" class="btn btn-success btn-sm"
                                    id="addDeadPigBtn">เพิ่มแถว</button>
                            </div>
                        </div>
                        <div id="deadPigContainer"></div>
                        <template id="deadPigTemplate">
                            <div class="dead-pig-row card shadow-lg border-0 rounded-3 mb-3 p3" data-template
                                style="display:none">
                                <input type="hidden" name="dead_pig[0][farm_id]" class="farm-id">
                                <input type="hidden" name="dead_pig[0][batch_id]" class="batch-id">
                                <input type="hidden" name="dead_pig[0][barn_pen]" class="barn-pen-json">

                                <div class="card-body cardTemplateRow">
                                    <!-- แถว 1: วันที่ + เล้า + คอก + จำนวน -->
                                    <div class="row g-2">
                                        <div class="col-md-3">
                                            <input type="text" name="dead_pig[0][date]"
                                                class="form-control date-input" placeholder="ว/ด/ป ชม.นาที" required>
                                        </div>
                                        <div data-cloned="1">
                                            <div class="col-md-3">
                                                <button
                                                    class="barn-select btn btn-white dropdown-toggle shadow-sm border w-100 text-start"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    เลือกเล้า
                                                </button>
                                                <ul class="dropdown-menu w-100 barn-dropdown"></ul>
                                                <input type="hidden" class="barn-id" name="dead_pig[0][barn_id]"
                                                    value="">
                                            </div>
                                            <div class="col-md-3">
                                                <button
                                                    class="pen-select btn btn-white dropdown-toggle shadow-sm border w-100 text-start"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    เลือกคอก
                                                </button>
                                                <ul class="dropdown-menu w-100 pen-dropdown"></ul>
                                                <input type="hidden" class="barn-pen-json" name="dead_pig[0][barn_pen]"
                                                    value="">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="dead_pig[0][quantity]" class="form-control"
                                                placeholder="จำนวนสุกรตาย" required>
                                        </div>
                                    </div>

                                    <!-- แถว 2: สาเหตุ + หมายเหตุ + ปุ่มลบ -->
                                    <div class="row g-2 mt-2">
                                        <div class="col-md-5">
                                            <textarea name="dead_pig[0][cause]" class="form-control" rows="1" placeholder="สาเหตุการตาย"></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <textarea name="dead_pig[0][note]" class="form-control" rows="1" placeholder="หมายเหตุ"></textarea>
                                        </div>
                                        <div class="col-md-1 d-flex align-items-end">
                                            <button type="button" class="btn btn-danger remove-row w-100">ลบ</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>


                        <!-- END PIGDEATH -->


                        <!-- ปุ่มติดขวาล่าง -->
                        <div class="position-sticky bottom-0 d-flex justify-content-end" style="z-index:10;">
                            <button type="submit" class="btn btn-primary">บันทึก</button>
                        </div>
                    </form>


                </div>
            </div>
        </div>
    </div>
    </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                // ---------------------- ข้อมูลจาก backend ----------------------
                const batches = @json($batches);
                const barns = @json($barns);
                const pens = @json($pens);
                const storehousesByTypeAndBatch = @json($storehousesByTypeAndBatch);

                const farmSelect = document.getElementById('farmSelect');
                const batchSelect = document.getElementById(
                'batchSelect'); // ---------------------- ฟังก์ชันช่วย ----------------------
                function attachDateInputEvents(root) {
                    (root.querySelectorAll ? root : document).querySelectorAll('.date-input').forEach(input => {
                        if (input._attached) return;
                        input._attached = true;

                        input.addEventListener('focus', () => input.type = 'datetime-local');

                        input.addEventListener('blur', function() {
                            if (!this.value) return;
                            const dt = new Date(this.value);
                            const day = String(dt.getDate()).padStart(2, '0');
                            const month = String(dt.getMonth() + 1).padStart(2, '0');
                            const year = dt.getFullYear();
                            const hours = String(dt.getHours()).padStart(2, '0');
                            const mins = String(dt.getMinutes()).padStart(2, '0');
                            this.type = 'text';
                            this.value = `${day}/${month}/${year} ${hours}:${mins}`;
                        });
                    });
                }

                function attachFarmBatchDropdown() {
                    document.querySelectorAll('#farmDropdownMenu .dropdown-item').forEach(item => {
                        item.addEventListener('click', function(e) {
                            e.preventDefault();
                            const farmId = this.dataset.farmId;
                            farmSelect.value = farmId;
                            document.getElementById('farmDropdownBtn').textContent = this.textContent;

                            populateBatchDropdown(farmId);

                            batchSelect.value = '';
                            document.getElementById('batchDropdownBtn').textContent = 'เลือกรุ่น';

                            // ---------------------- reset barn/pen ของทุก row ----------------------
                            document.querySelectorAll('[data-cloned]').forEach(row => {
                                const hiddenBarn = row.querySelector('.barn-id');
                                if (hiddenBarn) hiddenBarn.value = '';
                                const hiddenPen = row.querySelector('.barn-pen-json');
                                if (hiddenPen) hiddenPen.value = JSON.stringify([]);
                                attachBarnPenDropdowns(row);
                            });
                        });
                    });
                }

                function populateBatchDropdown(farmId) {
                    const batchMenu = document.getElementById('batchDropdownMenu');
                    batchMenu.innerHTML = '';
                    batches.filter(b => b.farm_id == farmId).forEach(b => {
                        const li = document.createElement('li');
                        li.innerHTML =
                            `<a class="dropdown-item" href="#" data-batch-id="${b.id}">${b.batch_code}</a>`;
                        batchMenu.appendChild(li);

                        li.querySelector('a').addEventListener('click', function(e) {
                            e.preventDefault();
                            batchSelect.value = this.dataset.batchId;
                            document.getElementById('batchDropdownBtn').textContent = this.textContent;

                            document.querySelectorAll('[data-cloned]').forEach(row => {
                                updateFarmBatchHiddenInputs(row);
                                populateItemDropdown(row);

                                const hiddenBarn = row.querySelector('.barn-id');
                                if (hiddenBarn) hiddenBarn.value = '';
                                const hiddenPen = row.querySelector('.barn-pen-json');
                                if (hiddenPen) hiddenPen.value = JSON.stringify([]);
                                attachBarnPenDropdowns(row);
                            });
                        });
                    });
                }

                function attachBarnPenDropdowns(root) {
                    (root.querySelectorAll ? root : document).querySelectorAll('[data-cloned]').forEach(
                        rowContainer => {
                            const isFeed = rowContainer.closest('#feedUseContainer') !== null;
                            const isMedicine = rowContainer.closest('#medicineUseContainer') !== null;

                            const barnBtn = rowContainer.querySelector('.barn-select');
                            const barnMenu = rowContainer.querySelector('.barn-dropdown');
                            const hiddenBarn = rowContainer.querySelector('.barn-id');
                            if (!barnBtn || !barnMenu || !hiddenBarn) return;

                            // ---------------------- reset ----------------------
                            barnMenu.innerHTML = '';
                            barnBtn.textContent = 'เลือกเล้า';
                            hiddenBarn.value = '';

                            const selectedFarmId = parseInt(farmSelect.value) || parseInt(rowContainer
                                .querySelector('.farm-id')?.value) || null;
                            const selectedBatchId = parseInt(batchSelect.value) || parseInt(rowContainer
                                .querySelector('.batch-id')?.value) || null;
                            if (!selectedFarmId || !selectedBatchId) {
                                barnBtn.classList.add('disabled');
                                return;
                            } else {
                                barnBtn.classList.remove('disabled');
                            }

                            const filteredBarns = barns.filter(b => b.farm_id === selectedFarmId);

                            filteredBarns.forEach(b => {
                                const li = document.createElement('li');
                                li.innerHTML =
                                    `<a class="dropdown-item" href="#" data-barn="${b.id}">เล้า ${b.barn_code}</a>`;
                                barnMenu.appendChild(li);

                                li.querySelector('a').addEventListener('click', function(e) {
                                    e.preventDefault();
                                    barnBtn.textContent = `เล้า ${b.barn_code}`;
                                    hiddenBarn.value = b.id;

                                    if (!isFeed) {
                                        const penBtn = rowContainer.querySelector('.pen-select');
                                        const penMenu = rowContainer.querySelector('.pen-dropdown');
                                        const hiddenPen = rowContainer.querySelector(
                                            '.barn-pen-json');
                                        if (!penBtn || !penMenu || !hiddenPen) return;

                                        // ---------------------- reset pen ----------------------
                                        penMenu.innerHTML = '';
                                        penBtn.textContent = 'เลือกคอก';
                                        hiddenPen.value = JSON.stringify([]);

                                        pens.filter(p => p.barn_id == b.id).forEach(p => {
                                            const liPen = document.createElement('li');
                                            liPen.innerHTML =
                                                `<a class="dropdown-item" href="#" data-pen="${p.id}">คอก ${p.pen_code}</a>`;
                                            penMenu.appendChild(liPen);

                                            liPen.querySelector('a').addEventListener(
                                                'click',
                                                function(e) {
                                                    e.preventDefault();
                                                    penBtn.textContent =
                                                        `คอก ${p.pen_code}`;

                                                    if (isFeed) {
                                                        // feed_use ยังใช้ JSON array (เหมือนเดิม)
                                                        hiddenPen.value = JSON
                                                            .stringify([{
                                                                barn_id: b.id,
                                                                pen_id: p.id
                                                            }]);
                                                    } else {
                                                        // medicine_use / dead_pig ใช้ scalar เลย
                                                        hiddenPen.value = p.id;
                                                    }
                                                });
                                        });
                                    }
                                });
                            });
                        });
                }

                function updateFarmBatchHiddenInputs(rowContainer) {
                    const farmId = parseInt(farmSelect.value) || '';
                    const batchId = parseInt(batchSelect.value) || '';
                    rowContainer.querySelectorAll('.farm-id').forEach(i => i.value = farmId);
                    rowContainer.querySelectorAll('.batch-id').forEach(i => i.value = batchId);
                }

                function populateItemDropdown(rowContainer) {
                    const batchId = parseInt(batchSelect.value);
                    if (!batchId) return;

                    const isFeed = rowContainer.closest('#feedUseContainer') !== null;
                    const isMedicine = rowContainer.closest('#medicineUseContainer') !== null;

                    const btn = rowContainer.querySelector('.item-dropdown-btn');
                    const menu = rowContainer.querySelector('.item-dropdown-menu');
                    const hiddenCode = rowContainer.querySelector('input.item-code');
                    const hiddenName = rowContainer.querySelector('input.item-name');

                    let hiddenType = rowContainer.querySelector('input.item-type');
                    if (!hiddenType && hiddenCode) {
                        hiddenType = document.createElement('input');
                        hiddenType.type = 'hidden';
                        hiddenType.classList.add('item-type');
                        hiddenType.name = hiddenCode.name.replace('[item_code]', '[item_type]');
                        rowContainer.appendChild(hiddenType);
                    }

                    if (!btn || !menu || !hiddenCode || !hiddenName) return;

                    const type = isFeed ? 'feed' : (isMedicine ? 'medicine' : null);
                    if (!type) return;
                    hiddenType.value = type;

                    menu.innerHTML = '';
                    if (storehousesByTypeAndBatch[type] && storehousesByTypeAndBatch[type][batchId]) {
                        Object.values(storehousesByTypeAndBatch[type][batchId]).forEach(item => {
                            const li = document.createElement('li');
                            li.innerHTML =
                                `<a class="dropdown-item" href="#" data-code="${item.item_code}" data-name="${item.item_name}">${item.item_name}</a>`;
                            menu.appendChild(li);

                            li.querySelector('a').addEventListener('click', function(e) {
                                e.preventDefault();
                                btn.textContent = item.item_name;
                                hiddenCode.value = item.item_code;
                                hiddenName.value = item.item_name;
                                hiddenType.value = type;
                            });
                        });
                    }
                }

                function attachItemNameUpdater(root) {
                    (root.querySelectorAll ? root : document).querySelectorAll('select[name$="[item_code]"]').forEach(
                        sel => {
                            if (sel._itemAttached) return;
                            sel._itemAttached = true;

                            sel.addEventListener('change', function() {
                                const rowContainer = sel.closest('[data-cloned]') || sel.closest(
                                    '.cardTemplateRow');
                                if (!rowContainer) return;
                                const hiddenInput = rowContainer.querySelector('.item-name');
                                if (!hiddenInput) return;

                                const type = sel.classList.contains('feed-item-select') ? 'feed' :
                                    'medicine';
                                const batchId = parseInt(batchSelect.value);
                                const itemData = storehousesByTypeAndBatch[type]?.[batchId]?.[sel.value];
                                hiddenInput.value = itemData ? itemData.item_name : '';
                            });
                        });
                }

                function addRow(containerId, templateId) {
                    const container = document.getElementById(containerId);
                    const template = document.getElementById(templateId);
                    if (!container || !template) return null;

                    const newIndex = container.querySelectorAll('[data-cloned]').length;
                    const newRow = template.content.firstElementChild.cloneNode(true);
                    newRow.style.display = 'block';
                    newRow.removeAttribute('data-template');
                    newRow.setAttribute('data-cloned', '1');

                    newRow.querySelectorAll('input,textarea').forEach(i => {
                        if (i.type !== 'hidden') i.value = '';
                    });
                    newRow.querySelectorAll('select').forEach(s => s.selectedIndex = -1);

                    newRow.querySelectorAll('input[name], select[name], textarea[name]').forEach(el => {
                        if (!el.name) return;
                        el.name = el.name.replace(/\[\d+\]/, `[${newIndex}]`);
                    });

                    // copy farm + batch จาก global
                    newRow.querySelectorAll('.farm-id').forEach(i => i.value = farmSelect.value || '');
                    newRow.querySelectorAll('.batch-id').forEach(i => i.value = batchSelect.value || '');

                    container.appendChild(newRow);

                    attachDateInputEvents(newRow);
                    attachItemNameUpdater(newRow);
                    attachBarnPenDropdowns(newRow);
                    populateItemDropdown(newRow);

                    return newRow;
                }

                function clearRows(containerId) {
                    const container = document.getElementById(containerId);
                    if (!container) return;
                    container.querySelectorAll('[data-cloned]').forEach(c => c.remove());
                }

                // ---------------------- Event remove row ----------------------
                ['feedUseContainer', 'medicineUseContainer', 'deadPigContainer'].forEach(containerId => {
                    const container = document.getElementById(containerId);
                    container?.addEventListener('click', function(e) {
                        if (e.target && e.target.classList.contains('remove-row')) {
                            const row = e.target.closest('[data-cloned]');
                            row?.remove();
                        }
                    });
                });

                // ---------------------- Add / Clear buttons ----------------------
                document.getElementById('addFeedUseBtn')?.addEventListener('click', () => addRow('feedUseContainer',
                    'feedUseTemplate'));
                document.getElementById('clearFeedUseBtn')?.addEventListener('click', () => clearRows(
                    'feedUseContainer'));
                document.getElementById('addMedicineUseBtn')?.addEventListener('click', () => addRow(
                    'medicineUseContainer', 'medicineUseTemplate'));
                document.getElementById('clearMedicineUseBtn')?.addEventListener('click', () => clearRows(
                    'medicineUseContainer'));
                document.getElementById('addDeadPigBtn')?.addEventListener('click', () => addRow('deadPigContainer',
                    'deadPigTemplate'));
                document.getElementById('clearDeadPigBtn')?.addEventListener('click', () => clearRows(
                    'deadPigContainer'));

                // ---------------------- Init ----------------------
                attachDateInputEvents(document);
                attachItemNameUpdater(document);
                attachBarnPenDropdowns(document);
                attachFarmBatchDropdown();
                document.querySelectorAll('[data-cloned]').forEach(row => {
                    updateFarmBatchHiddenInputs(row);
                    populateItemDropdown(row);
                });

            });

            // ---------------------- Dropdown ปุ่มเลือกสถานะ (ยา) ----------------------
            document.addEventListener('click', function(e) {
                const item = e.target.closest('.medicine-status-dropdown-menu .dropdown-item');
                if (!item) return;

                const dropdown = item.closest('.dropdown');
                if (!dropdown) return;

                const button = dropdown.querySelector('.medicine-status-dropdown-btn');
                const hiddenInput = dropdown.querySelector('input.status-value');

                if (button && hiddenInput) {
                    e.preventDefault();
                    const value = item.dataset.value ?? item.textContent.trim();
                    button.textContent = value;
                    hiddenInput.value = value;

                    const rowContainer = dropdown.closest('[data-cloned]');
                    if (rowContainer) {
                        const farmIdInput = rowContainer.querySelector('.farm-id');
                        const batchIdInput = rowContainer.querySelector('.batch-id');

                        if (farmIdInput && batchIdInput) {
                            farmIdInput.value = parseInt(farmSelect.value) || '';
                            batchIdInput.value = parseInt(batchSelect.value) || '';
                        }
                    }
                }
            });
        </script>
    @endpush
@endsection
