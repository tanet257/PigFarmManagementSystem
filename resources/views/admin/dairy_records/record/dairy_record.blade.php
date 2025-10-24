@extends('layouts.admin')

@section('title', 'บันทึกประจำวัน')

@push('styles')
@endpush

@section('content')

    <div class="container my-5">
        <div class="card shadow-lg border-0 rounded-3">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">บันทึกประจำวัน (Dairy Record)</h4>
            </div>
            <div class="card-body">

                <form action="{{ route('dairy_records.upload') }}" method="post" enctype="multipart/form-data">
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

                    <!--Feed Section-->
                    <div card class="card card-custom-secondary">
                        <div class="text-white pt-2 pb-2 d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">อาหารสุกรที่ใช้</h4>
                            <div>
                                <button type="button" class="btn btn-danger btn-sm" id="clearFeedUseBtn">ล้างแถว</button>
                                <button type="button" class="btn btn-success btn-sm" id="addFeedUseBtn">เพิ่มแถว</button>
                            </div>
                        </div>
                        <div id="feedUseContainer"></div>
                        <template id="feedUseTemplate">
                            <div class="feed-use-row shadow-lg border-0 rounded-3 mb-3 p3" data-template
                                style="display:none">
                                <input type="hidden" name="feed_use[0][farm_id]" class="farm-id">
                                <input type="hidden" name="feed_use[0][batch_id]" class="batch-id">
                                <input type="hidden" name="feed_use[0][item_type]" class="item-type" value="feed">

                                <div class=" card-custom-tertiary cardTemplateRow">
                                    <div class="row g-2" data-cloned="1">
                                        <!-- แถว 1: วันที่ + เล้า + อาหาร -->
                                        <div class="col-md-4">
                                            <input type="text" name="feed_use[0][date]" class="form-control date-input"
                                                placeholder="ว/ด/ป ชม.นาที" required>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="dropdown">
                                                <button
                                                    class="barn-select btn btn-primary dropdown-toggle shadow-sm border w-100 d-flex justify-content-between align-items-center"
                                                    type="button" data-bs-toggle="dropdown"
                                                    aria-expanded="false"><span>เลือกเล้า</span></button>
                                                <ul class="dropdown-menu w-100 barn-dropdown"></ul>
                                                <input type="hidden" class="barn-id" name="feed_use[0][barn_id]"
                                                    value="">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="dropdown">
                                                <button
                                                    class="btn btn-primary dropdown-toggle w-100 d-flex justify-content-between align-items-center item-dropdown-btn"
                                                    type="button" id="feedItemDropdownBtn0" data-bs-toggle="dropdown"
                                                    aria-expanded="false"><span>เลือกอาหาร</span></button>
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
                    </div>
                    <!-- END FEED -->

                    <!-- MEDICINE SECTION -->
                    <div card class="card card-custom-secondary">
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
                            <div class="medicine-use-row shadow-lg border-0 rounded-3 mb-3 p3" data-template
                                style="display:none">
                                <input type="hidden" name="medicine_use[0][farm_id]" class="farm-id">
                                <input type="hidden" name="medicine_use[0][batch_id]" class="batch-id">
                                <input type="hidden" name="medicine_use[0][item_type]" class="item-type"
                                    value="medicine">
                                <input type="hidden" name="medicine_use[0][barn_pen]" class="barn-pen-json">

                                <div class="card-custom-tertiary cardTemplateRow">
                                    <!-- แถว 1: วันที่ + เล้า + คอก + ยา/วัคซีน -->
                                    <div class="row g-2" data-cloned="1">
                                        <div class="col-md-3">
                                            <input type="text" name="medicine_use[0][date]"
                                                class="form-control date-input" placeholder="ว/ด/ป ชม.นาที" required>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="dropdown">
                                                <button
                                                    class="barn-select btn btn-primary dropdown-toggle shadow-sm border w-100 d-flex justify-content-between align-items-center"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <span>เลือกเล้า</span>
                                                </button>
                                                <ul class="dropdown-menu w-100 barn-dropdown"></ul>
                                                <input type="hidden" class="barn-id" name="medicine_use[0][barn_id]"
                                                    value="">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="dropdown">
                                                <button
                                                    class="pen-select btn btn-primary dropdown-toggle shadow-sm border w-100 d-flex justify-content-between align-items-center"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <span>เลือกคอก</span>
                                                </button>
                                                <ul class="dropdown-menu w-100 pen-dropdown"></ul>
                                                <input type="hidden" class="barn-pen-json"
                                                    name="medicine_use[0][barn_pen]" value="">
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="dropdown">
                                                <button
                                                    class="btn btn-primary dropdown-toggle w-100 d-flex justify-content-between align-items-center item-dropdown-btn"
                                                    type="button" id="medicineItemDropdownBtn0"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    <span>เลือกยา/วัคซีน</span>
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
                                                    class="btn btn-primary dropdown-toggle w-100 d-flex justify-content-between align-items-center
                                                        medicine-status-dropdown-btn"
                                                    type="button" id="medicineStatusDropdownBtn0"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    <span>เลือกสถานะ</span>
                                                </button>
                                                <ul class="dropdown-menu w-100 medicine-status-dropdown-menu"
                                                    aria-labelledby="medicineStatusDropdownBtn0">
                                                    <li><a class="dropdown-item" href="#"
                                                            data-value="วางแผนว่าจะให้ยา">วางแผนว่าจะให้ยา</a>
                                                    </li>
                                                    <li><a class="dropdown-item" href="#"
                                                            data-value="กำลังดำเนินการ (กำลังฉีด/กำลังให้ยาอยู่)">กำลังดำเนินการ</a>
                                                    </li>
                                                    <li><a class="dropdown-item" href#"
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
                    </div>

                    <!-- END MEDICINE -->

                    <!-- PIGDEATH SECTION -->
                    <div card class="card card-custom-secondary">
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
                            <div class="dead-pig-row shadow-lg border-0 rounded-3 mb-3 p3" data-template
                                style="display:none">
                                <input type="hidden" name="dead_pig[0][farm_id]" class="farm-id">
                                <input type="hidden" name="dead_pig[0][batch_id]" class="batch-id">
                                <input type="hidden" name="dead_pig[0][barn_pen]" class="barn-pen-json">

                                <div class="card-custom-tertiary cardTemplateRow">
                                    <!-- แถว 1: วันที่ + เล้า + คอก + จำนวน -->
                                    <div class="row g-2" data-cloned="1">
                                        <div class="col-md-3">
                                            <input type="text" name="dead_pig[0][date]"
                                                class="form-control date-input" placeholder="ว/ด/ป ชม.นาที" required>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="dropdown">
                                                <button
                                                    class="barn-select btn btn-primary dropdown-toggle shadow-sm border w-100 d-flex justify-content-between align-items-center"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <span>เลือกเล้า</span>
                                                </button>
                                                <ul class="dropdown-menu w-100 barn-dropdown"></ul>
                                                <input type="hidden" class="barn-id" name="dead_pig[0][barn_id]"
                                                    value="">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="dropdown">
                                                <button
                                                    class="pen-select btn btn-primary dropdown-toggle shadow-sm border w-100 d-flex justify-content-between align-items-center"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <span>เลือกคอก</span>
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
                    </div>

                    <!-- END PIGDEATH -->


                    <!-- ปุ่มติดขวาล่าง -->
                    <div class="position-sticky bottom-0 d-flex justify-content-end" style="z-index:10;">
                        <button type="submit" class="btn btn-primary">บันทึก</button>
                    </div>
                </form>



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
                const batchSelect = document.getElementById('batchSelect');
                const batchDropdownBtn = document.getElementById('batchDropdownBtn');

                // ---------------------- Validation for Batch dropdown ----------------------
                document.addEventListener('click', function(e) {
                    if (e.target.closest('#batchDropdownBtn')) {
                        const farmId = farmSelect.value;
                        if (!farmId) {
                            e.preventDefault();
                            e.stopPropagation();
                            showSnackbar('กรุณาเลือกฟาร์มก่อน');
                            return false;
                        }
                    }

                    // Validation for Barn dropdown
                    if (e.target.closest('.barn-select')) {
                        const farmId = farmSelect.value;
                        const batchId = batchSelect.value;
                        if (!farmId) {
                            e.preventDefault();
                            e.stopPropagation();
                            showSnackbar('กรุณาเลือกฟาร์มก่อน');
                            return false;
                        }
                        if (!batchId) {
                            e.preventDefault();
                            e.stopPropagation();
                            showSnackbar('กรุณาเลือกรุ่นก่อน');
                            return false;
                        }
                    }

                    // Validation for Pen dropdown
                    if (e.target.closest('.pen-select')) {
                        const row = e.target.closest('[data-cloned]');
                        if (row) {
                            const barnId = row.querySelector('.barn-id')?.value;
                            if (!barnId) {
                                e.preventDefault();
                                e.stopPropagation();
                                showSnackbar('กรุณาเลือกเล้าก่อน');
                                return false;
                            }
                        }
                    }

                    // Validation for Item dropdown
                    if (e.target.closest('.item-dropdown-btn')) {
                        const farmId = farmSelect.value;
                        const batchId = batchSelect.value;
                        if (!farmId) {
                            e.preventDefault();
                            e.stopPropagation();
                            showSnackbar('กรุณาเลือกฟาร์มก่อน');
                            return false;
                        }
                        if (!batchId) {
                            e.preventDefault();
                            e.stopPropagation();
                            showSnackbar('กรุณาเลือกรุ่นก่อน');
                            return false;
                        }
                    }

                    // Validation for Status dropdown (medicine)
                    if (e.target.closest('.medicine-status-dropdown-btn')) {
                        const farmId = farmSelect.value;
                        const batchId = batchSelect.value;
                        if (!farmId) {
                            e.preventDefault();
                            e.stopPropagation();
                            showSnackbar('กรุณาเลือกฟาร์มก่อน');
                            return false;
                        }
                        if (!batchId) {
                            e.preventDefault();
                            e.stopPropagation();
                            showSnackbar('กรุณาเลือกรุ่นก่อน');
                            return false;
                        }
                    }
                }, true);

                // ---------------------- ฟังก์ชันช่วย ----------------------
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
                            document.getElementById('farmDropdownBtn').querySelector('span')
                                .textContent = this.textContent;

                            // Reset Batch dropdown
                            batchDropdownBtn.querySelector('span').textContent = 'เลือกรุ่น';
                            batchSelect.value = '';

                            // Populate batch options
                            populateBatchDropdown(farmId);

                            // ---------------------- Reset all rows: barn, pen, item dropdowns ----------------------
                            document.querySelectorAll('[data-cloned]').forEach(row => {
                                // Reset Barn dropdown
                                const barnBtn = row.querySelector('.barn-select');
                                if (barnBtn) {
                                    barnBtn.querySelector('span').textContent = 'เลือกเล้า';
                                }
                                const barnId = row.querySelector('.barn-id');
                                if (barnId) barnId.value = '';

                                // Reset Pen dropdown
                                const penBtn = row.querySelector('.pen-select');
                                if (penBtn) {
                                    penBtn.querySelector('span').textContent = 'เลือกคอก';
                                }
                                const penJson = row.querySelector('.barn-pen-json');
                                if (penJson) penJson.value = '';

                                // Reset Item dropdown
                                const itemBtn = row.querySelector('.item-dropdown-btn');
                                if (itemBtn) {
                                    const typeHidden = row.querySelector('.item-type');
                                    const type = typeHidden?.value;
                                    if (type === 'feed') {
                                        itemBtn.querySelector('span').textContent = '-- เลือกชื่อประเภทอาหารหมู --';
                                    } else if (type === 'medicine') {
                                        itemBtn.querySelector('span').textContent = '-- เลือกชื่อยา/วัคซีน --';
                                    } else {
                                        itemBtn.querySelector('span').textContent = '-- เลือกสินค้า --';
                                    }
                                }
                                const itemCode = row.querySelector('.item-code');
                                if (itemCode) itemCode.value = '';
                                const itemName = row.querySelector('.item-name');
                                if (itemName) itemName.value = '';

                                // Reset Status dropdown (for medicine)
                                const statusBtn = row.querySelector('.medicine-status-dropdown-btn');
                                if (statusBtn) {
                                    statusBtn.querySelector('span').textContent = 'เลือกสถานะ';
                                }
                                const statusValue = row.querySelector('.status-value');
                                if (statusValue) statusValue.value = '';
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

                                // Reset item selections
                                const itemBtn = row.querySelector('.item-dropdown-btn');
                                if (itemBtn) {
                                    const typeHidden = row.querySelector('.item-type');
                                    const type = typeHidden?.value;
                                    if (type === 'feed') {
                                        itemBtn.querySelector('span').textContent = '-- เลือกชื่อประเภทอาหารหมู --';
                                    } else if (type === 'medicine') {
                                        itemBtn.querySelector('span').textContent = '-- เลือกชื่อยา/วัคซีน --';
                                    } else {
                                        itemBtn.querySelector('span').textContent = '-- เลือกสินค้า --';
                                    }
                                }
                                const itemCode = row.querySelector('.item-code');
                                if (itemCode) itemCode.value = '';
                                const itemName = row.querySelector('.item-name');
                                if (itemName) itemName.value = '';

                                // Reset barn/pen selections
                                const barnBtn = row.querySelector('.barn-select');
                                if (barnBtn) {
                                    barnBtn.querySelector('span').textContent = 'เลือกเล้า';
                                }
                                const barnId = row.querySelector('.barn-id');
                                if (barnId) barnId.value = '';

                                const penBtn = row.querySelector('.pen-select');
                                if (penBtn) {
                                    penBtn.querySelector('span').textContent = 'เลือกคอก';
                                }
                                const penJson = row.querySelector('.barn-pen-json');
                                if (penJson) penJson.value = '';

                                // Reset status for medicine
                                const statusBtn = row.querySelector('.medicine-status-dropdown-btn');
                                if (statusBtn) {
                                    statusBtn.querySelector('span').textContent = 'เลือกสถานะ';
                                }
                                const statusValue = row.querySelector('.status-value');
                                if (statusValue) statusValue.value = '';

                                populateItemDropdown(row);
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
                                return;
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

                function populateItemDropdown(row) {
                    const typeHidden = row.querySelector('.item-type');
                    const type = typeHidden?.value;
                    const batchId = parseInt(batchSelect.value);
                    const itemDropdownMenu = row.querySelector('.item-dropdown-menu');
                    const itemDropdownBtn = row.querySelector('.item-dropdown-btn');
                    if (!itemDropdownMenu || !itemDropdownBtn) return;

                    // Clear dropdown menu
                    itemDropdownMenu.innerHTML = '';

                    // Determine placeholder text based on item type
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
                        const seen = new Set();
                        Object.values(storehousesByTypeAndBatch[type][batchId]).forEach(item => {
                            // Skip if already added (prevent duplicates)
                            if (seen.has(item.item_code)) return;
                            seen.add(item.item_code);

                            const li = document.createElement('li');
                            const a = document.createElement('a');
                            a.className = 'dropdown-item';
                            a.href = '#';
                            a.setAttribute('data-item-code', item.item_code);
                            a.setAttribute('data-item-name', item.item_name);
                            a.textContent = item.item_name;
                            li.appendChild(a);
                            itemDropdownMenu.appendChild(li);

                            a.addEventListener('click', function(e) {
                                e.preventDefault();
                                const codeHidden = row.querySelector('.item-code');
                                const nameHidden = row.querySelector('.item-name');
                                if (codeHidden) codeHidden.value = item.item_code;
                                if (nameHidden) nameHidden.value = item.item_name;
                                itemDropdownBtn.textContent = item.item_name;
                            });
                        });
                    }

                    // Clear hidden inputs
                    const codeHidden = row.querySelector('.item-code');
                    const nameHidden = row.querySelector('.item-name');
                    if (codeHidden) codeHidden.value = '';
                    if (nameHidden) nameHidden.value = '';
                }

                function attachItemNameUpdater(root) {
                    (root.querySelectorAll ? root : document).querySelectorAll('input.item-code').forEach(
                        codeInput => {
                            if (codeInput._itemAttached) return;
                            codeInput._itemAttached = true;

                            codeInput.addEventListener('change', function() {
                                const rowContainer = codeInput.closest('[data-cloned]') || codeInput.closest(
                                    '.cardTemplateRow');
                                if (!rowContainer) return;

                                const typeHidden = rowContainer.querySelector('.item-type');
                                const type = typeHidden?.value;
                                const batchId = parseInt(batchSelect.value);
                                const nameHidden = rowContainer.querySelector('input.item-name');

                                if (nameHidden && type && batchId && storehousesByTypeAndBatch[type]?.[batchId]?.[codeInput.value]) {
                                    const itemData = storehousesByTypeAndBatch[type][batchId][codeInput.value];
                                    nameHidden.value = itemData.item_name;
                                } else if (nameHidden) {
                                    nameHidden.value = '';
                                }
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

                    // Set item-type based on container
                    const typeInput = newRow.querySelector('.item-type');
                    if (typeInput && containerId === 'feedUseContainer') {
                        typeInput.value = 'feed';
                    } else if (typeInput && containerId === 'medicineUseContainer') {
                        typeInput.value = 'medicine';
                    }

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
