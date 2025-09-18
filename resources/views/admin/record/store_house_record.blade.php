<!DOCTYPE html>
<html>

<head>
    @include('admin.css')
    <style>
        label {
            display: inline-block;
            font-weight: bold;
            margin-bottom: 6px;
        }

        /* ปิด scroll/arrow ของ number input */
        input[type=number]::-webkit-outer-spin-button,
        input[type=number]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type=number] {
            -moz-appearance: textfield;
            /* Firefox */
        }

        /* เพิ่ม class no-scroll เฉพาะ input number ก็ได้ */
        .no-scroll::-webkit-outer-spin-button,
        .no-scroll::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .no-scroll {
            -moz-appearance: textfield;
        }
    </style>

</head>

<body>
    @include('admin.header')
    @include('admin.sidebar')

    <div class="page-content">
        <div class="page-header">
            <div class="container-fluid">
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">บันทึกสินค้าเข้าคลัง (Store House Record)</h4>
                    </div>
                    <div class="card-body">
                        <!-- Bootstrap Icon -->
                        <link rel="stylesheet"
                            href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

                        <!-- DateSelect Plugin -->
                        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
                        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

                        <!-- MonthSelect Plugin -->
                        <link rel="stylesheet"
                            href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
                        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

                        <style>
                            .snackbar {
                                visibility: hidden;
                                min-width: 250px;
                                margin-left: -125px;
                                background-color: #333;
                                color: #fff;
                                text-align: center;
                                border-radius: 8px;
                                padding: 16px;
                                position: fixed;
                                z-index: 9999;
                                right: 20px;
                                bottom: 30px;
                                font-size: 16px;
                                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                            }

                            .snackbar.show {
                                visibility: visible;
                                animation: fadein 0.5s, fadeout 0.5s 10s;
                            }

                            .snackbar button {
                                background: none;
                                border: none;
                                color: #fff;
                                font-weight: bold;
                                margin-left: 10px;
                                cursor: pointer;
                            }

                            @keyframes fadein {
                                from {
                                    bottom: 0;
                                    opacity: 0;
                                }

                                to {
                                    bottom: 30px;
                                    opacity: 1;
                                }
                            }

                            @keyframes fadeout {
                                from {
                                    bottom: 30px;
                                    opacity: 1;
                                }

                                to {
                                    bottom: 0;
                                    opacity: 0;
                                }
                            }

                            input.form-control,
                            select.form-select {
                                border-radius: 0.75rem;
                                /* rounded-3 ของ Bootstrap */
                                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 1px 3px rgba(0, 0, 0, 0.08);
                                transition: transform 0.2s, box-shadow 0.2s;
                                background: #f5f5f5
                            }


                            input[type="file"].form-control {
                                border: 1px solid #6b6b6b;
                                border-radius: 10px;
                                background-color: #f8f9fa;
                                padding: 6px;
                            }

                            input[type="file"].form-control::file-selector-button {
                                background-color: #717171;
                                color: white;
                                border: none;
                                padding: 8px 12px;
                                border-radius: 6px;
                                cursor: pointer;
                                transition: background-color 0.2s;
                            }

                            input[type="file"].form-control::file-selector-button:hover {
                                background-color: #0b5ed7;
                            }
                        </style>

                        <div id="snackbar" class="snackbar" style="display:none">
                            <span id="snackbarMessage"></span>
                            <button id="copyBtn" onclick="copySnackbar()">
                                <i class="bi bi-copy"></i></button>
                            <button onclick="closeSnackbar()">✖</button>
                        </div>

                        <script>
                            window.onload = function() {
                                const sb = document.getElementById("snackbar");
                                const sbMsg = document.getElementById("snackbarMessage");

                                @if (session('success'))
                                    sbMsg.innerText = "{{ session('success') }}";
                                    sb.style.backgroundColor = "#28a745"; //เขียว
                                    sb.style.display = "flex";
                                    sb.classList.add("show");
                                    setTimeout(() => {
                                        sb.classList.remove("show");
                                        sb.style.display = "none"
                                    }, 10500);
                                @elseif (session('error'))
                                    sbMsg.innerText = "{{ session('error') }}";
                                    sb.style.backgroundColor = "#dc3545"; //แดง
                                    sb.style.display = "flex";
                                    sb.classList.add("show");
                                    setTimeout(() => {
                                        sb.classList.remove("show");
                                        sb.style.display = "none"
                                    }, 10500);
                                @endif
                            };

                            function showSnackbar(message, bgColor = "#dc3545") {
                                const sb = document.getElementById("snackbar");
                                const sbMsg = document.getElementById("snackbarMessage");
                                sbMsg.innerText = message;
                                sb.style.backgroundColor = bgColor;
                                sb.style.display = "flex";
                                sb.classList.add("show");
                                setTimeout(() => {
                                    sb.classList.remove("show");
                                    sb.style.display = "none";
                                }, 5000);
                            }


                            function copySnackbar() {
                                let text = document.getElementById("snackbarMessage").innerText;
                                navigator.clipboard.writeText(text).then(() => {
                                    let btn = document.getElementById("copyBtn");
                                    btn.innerHTML = '<i class="bi bi-check2"></i> Copied';
                                    btn.disabled = true; // ป้องกันกดซ้ำ
                                    setTimeout(() => {
                                        btn.innerHTML = '<i class="bi bi-copy"></i> Copy';
                                        btn.disabled = false;
                                    }, 2000); // 2 วิแล้วกลับมาเหมือนเดิม
                                });
                            }

                            function closeSnackbar() {
                                let sb = document.getElementById("snackbar");
                                sb.classList.remove("show");
                                sb.style.display = "none";
                            }
                        </script>

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
                                    <!-- จะถูกเติมโดย JS ตามฟาร์มที่เลือก -->
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


                        <!-- feedRowsContainer การให้อาหารสำหรับหลาย row -->
                        <div id="feedRowsContainer">
                            <div class="feed-row card shadow-lg border-0 rounded-3 mb-3 p3" style="background: #CBCBCB">
                                <form action="{{ url('upload_store_house_record') }}" method="post"
                                    enctype="multipart/form-data">
                                    @csrf

                                    <input type="hidden" name="farm_id" class="farm-id" value="">
                                    <input type="hidden" name="batch_id" class="batch-id" value="">
                                    <div class="card-body">
                                        <div class="row mb-3 text-darkbg">

                                            <div class="col-md-2 p-3 position-relative" id="dateWrapper">
                                                <input type="text" name="date" placeholder="ว/ด/ป ชม.นาที"
                                                    class="form-control no-scroll" style="background:#f5f5f5"
                                                    title="วัน/เดือน/ปี ชม. นาที" data-input required>
                                            </div>

                                            <div class="col-md-2 p-3">
                                                <select name="item_type"
                                                    class="item-type-select form-select text-truncate w-100 " required>
                                                    <option value="">-- เลือกประเภท --</option>
                                                    <option value="feed"> ค่าอาหาร </option>
                                                </select>
                                            </div>

                                            <div class="col-md-2 p-3">
                                                <select name="item_code" id="itemNameSelect" class="form-select"
                                                    title="เลือกชื่อประเภทอาหารหมู" required>
                                                    <option value="">-- เลือกชื่อประเภทอาหารหมู --</option>
                                                    @foreach ($storehouses as $storehouse)
                                                        <option value="{{ $storehouse->item_code }}"
                                                            data-name="{{ $storehouse->item_name }}">
                                                            {{ $storehouse->item_name }}
                                                        </option>
                                                    @endforeach

                                                </select>

                                                <input type="hidden" name="item_name" class="item-name-hidden">
                                            </div>


                                            <div class="col-md-2 p-3">
                                                <input placeholder="จำนวน" type="number" name="stock"
                                                    class="form-control no-scroll" title="จำนวน" required>
                                            </div>

                                            <div class="col-md-2 p-3">
                                                <input placeholder="ราคาต่อชิ้น" type="number" name="price_per_unit"
                                                    class="form-control no-scroll" title="ราคาต่อชิ้น">
                                            </div>

                                            <div class="col-md-2 p-3">
                                                <select name="unit" class="unit-select form-select">
                                                    <option value="">- เลือกหน่วย -</option>
                                                </select>
                                            </div>

                                            <div class="col-md-2 p-3">
                                                <input placeholder="ค่าขนส่ง" type="number" name="transport_cost"
                                                    class="form-control no-scroll" title="ค่าขนส่ง (บาท)">
                                            </div>

                                            <div class="col-md-2 p-3">
                                                <input type="file" name="receipt_file" class="form-control"
                                                    title="เลือกสลิปการชำระเงิน">
                                            </div>

                                            <div class="col-md-4 p-3">
                                                <textarea placeholder="หมายเหตุ" name="note" rows="4" class="form-control text-darkbg"
                                                    style="background: #f5f5f5"></textarea>
                                            </div>

                                            <!-- ปุ่มชิดขวาล่าง -->
                                            <div
                                                class="card-footer d-flex justify-content-end gap-2 bg-transparent border-0">
                                                <button type="button"
                                                    class="btn btn-danger remove-row">ลบแถว</button>
                                                <button type="submit" class="btn btn-primary">บันทึก</button>
                                            </div>

                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!-- จบ feedRowsContainer -->

                        <!-- Header + Add Row Delete Row ของ ยา/วัคซีนสุกรขาเข้า -->
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

                        <!-- Container ยา/วัคซีนสุกรสำหรับหลาย row -->
                        <div id="medicineRowsContainer">
                            <div class="medicine-row card shadow-lg border-0 rounded-3 mb-3 p3"
                                style="background: #CBCBCB">
                                <form action="{{ url('upload_store_house_record') }}" method="post"
                                    enctype="multipart/form-data">
                                    @csrf

                                    <input type="hidden" name="farm_id" class="farm-id" value="">
                                    <input type="hidden" name="batch_id" class="batch-id" value="">
                                    <div class="card-body">
                                        <div class="row mb-3 text-darkbg">

                                            <div class="col-md-2 p-3 position-relative" id="dateWrapper">
                                                <input type="text" name="date" placeholder="ว/ด/ป ชม.นาที"
                                                    class="form-control no-scroll" style="background: #f5f5f5"
                                                    title="วัน/เดือน/ปี ชม. นาที" data-input required>
                                            </div>



                                            <div class="col-md-2 p-3">
                                                <select name="item_type"
                                                    class="item-type-select form-select text-truncate w-100" required>
                                                    <option value="">-- เลือกประเภท --</option>
                                                    <option value="medicine"> ยา/วัคซีน </option>
                                                </select>
                                            </div>

                                            <div class="col-md-2 p-3">
                                                <select name="item_code" id="itemNameSelect" class="form-select"
                                                    title="เลือกชื่อยา" required>
                                                    <option value="">-- เลือกชื่อยา --</option>
                                                    @foreach ($storehouses as $storehouse)
                                                        <option value="{{ $storehouse->item_code }}">
                                                            {{ $storehouse->item_name }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <input type="hidden" name="item_name" class="item-name-hidden">
                                            </div>

                                            <div class="col-md-2 p-3">
                                                <input placeholder="จำนวน" type="number" name="stock"
                                                    class="form-control no-scroll" title="จำนวน" required>
                                            </div>

                                            <div class="col-md-2 p-3">
                                                <input placeholder="ราคาต่อชิ้น" type="number" name="price_per_unit"
                                                    class="form-control no-scroll" title="ราคาต่อชิ้น">
                                            </div>

                                            <div class="col-md-2 p-3">
                                                <select name="unit" class="unit-select form-select">
                                                    <option value="">- เลือกหน่วย -</option>
                                                </select>
                                            </div>

                                            <div class="col-md-2 p-3">
                                                <input placeholder="ค่าขนส่ง" type="number" name="transport_cost"
                                                    class="form-control no-scroll" title="ค่าขนส่ง (บาท)">
                                            </div>

                                            <div class="col-md-2 p-3">
                                                <input type="file" name="receipt_file" class="form-control"
                                                    title="เลือกสลิปการชำระเงิน">
                                            </div>

                                            <div class="col-md-4 p-3">
                                                <textarea placeholder="หมายเหตุ" name="note" rows="4" class="form-control text-darkbg"
                                                    style="background: #f5f5f5"></textarea>
                                            </div>
                                        </div>

                                        <!-- ปุ่มชิดขวาล่าง -->
                                        <div
                                            class="card-footer d-flex justify-content-end gap-2 bg-transparent border-0">
                                            <button type="button" class="btn btn-danger remove-row">ลบแถว</button>
                                            <button type="submit" class="btn btn-primary">บันทึก</button>
                                        </div>

                                    </div>

                                </form>
                            </div>
                        </div>
                        <!-- จบ medicineRowsContainer -->

                        <!-- Header + Add Row Delete Row ของ ต้นทุนรายเดือน -->
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

                        <div id="monthlyRowsContainer">

                            <div class="monthly-row card shadow-lg border-0 rounded-3 mb-3 p-3"
                                style="background: #CBCBCB;">
                                <form action="{{ url('upload_store_house_record') }}" method="post"
                                    enctype="multipart/form-data">
                                    @csrf

                                    <input type="hidden" name="farm_id" class="farm-id" value="">
                                    <input type="hidden" name="batch_id" class="batch-id" value="">

                                    <div class="card-body">

                                        <div class="row mb-3 text-darkbg">

                                            <!-- เดือน/ปี -->
                                            <div class="col-md-2 p-3">
                                                <input type="text" name="date" placeholder="ด/ป"
                                                    id="monthWrapper" class="form-control no-scroll"
                                                    style="background: #f5f5f5" title="เดือน/ปี" required>
                                            </div>

                                            <!-- ประเภทค่าใช้จ่าย -->
                                            <div class="col-md-2 p-3">
                                                <select name="item_type"
                                                    class="item-type-select form-select text-truncate w-100" required>
                                                    <option value="">-- เลือกประเภท --</option>
                                                    <option value="wage">ค่าแรงงาน</option>
                                                    <option value="electric_bill">ค่าไฟ</option>
                                                    <option value="water_bill">ค่าน้ำ</option>
                                                </select>
                                            </div>

                                            <!-- ราคาต่อชิ้น/จำนวนเงิน -->
                                            <div class="col-md-2 p-3">
                                                <input type="number" name="price_per_unit" placeholder="ราคาต่อชิ้น"
                                                    class="form-control no-scroll" title="ราคาต่อชิ้น">
                                            </div>

                                            <!-- หน่วย -->
                                            <div class="col-md-2 p-3">
                                                <select name="unit" class="unit-select form-select">
                                                    <option value="">- เลือกหน่วย -</option>
                                                </select>
                                            </div>

                                            <!-- สลิป -->
                                            <div class="col-md-2 p-3">
                                                <input type="file" name="receipt_file" class="form-control"
                                                    title="เลือกสลิปการชำระเงิน">
                                            </div>

                                            <!-- หมายเหตุ -->
                                            <div class="col-md-4 p-3">
                                                <textarea name="note" rows="4" placeholder="หมายเหตุ" class="form-control text-darkbg"
                                                    style="background: #f5f5f5"></textarea>
                                            </div>

                                        </div> <!-- จบ row -->

                                    </div> <!-- จบ card-body -->

                                    <!-- ปุ่มชิดขวาล่าง -->
                                    <div class="card-footer d-flex justify-content-end gap-2 bg-transparent border-0">
                                        <button type="button" class="btn btn-danger remove-row">ลบแถว</button>
                                        <button type="submit" class="btn btn-primary">บันทึก</button>
                                    </div>

                                </form>
                            </div> <!-- จบ monthly-row card -->

                        </div>
                        <!-- จบ monthlyRowsContainer -->


                    </div>
                </div>

            </div>
        </div>
    </div>

    <!--flatpickr-->
    <script>
        // ใช้ dateWrapper
        flatpickr("#dateWrapper", {
            enableTime: true,
            dateFormat: "d/m/Y H:i",
            maxDate: "today",
            time_24hr: true,
            wrap: true
        });
        // ใช้ monthWrapper
        flatpickr("#monthWrapper", {
            plugins: [
                new monthSelectPlugin({
                    shorthand: true,
                    dateFormat: "m/Y",
                    altFormat: "F Y"
                })
            ],
            //wrap: true
        });
    </script>

    <!-- ทำให้ item_name อัปเดทค่าตาม form select ของ item_code เสมอ -->
    <script>
        document.addEventListener('change', function(e) {
            if (e.target.matches('select[name="item_code"]')) {
                const selectedOption = e.target.selectedOptions[0];
                const itemNameInput = e.target.closest('form').querySelector('.item-name-hidden');
                if (selectedOption && itemNameInput) {
                    itemNameInput.value = selectedOption.dataset.name || '';
                }
            }
        });

        // Init ตอนโหลดเพื่อกรณีมีค่า default
        document.querySelectorAll('select[name="item_code"]').forEach(sel => {
            sel.dispatchEvent(new Event('change'));
        });
    </script>

    <!-- ส่ง option batch_code ไปให้ batchSelect-->
    <script>
        const farmSelect = document.getElementById('farmSelect');
        const batchSelect = document.getElementById('batchSelect');
        const batches = @json($batches); // ส่งจาก controller

        // อัปเดต hidden inputs ของทุกฟอร์มใน container
        function updateHiddenInputsForAll(containerId) {
            document.querySelectorAll(`#${containerId} .farm-id`).forEach(i => i.value = farmSelect.value || '');
            document.querySelectorAll(`#${containerId} .batch-id`).forEach(i => i.value = batchSelect.value || '');
        }

        // เมื่อเปลี่ยนฟาร์ม → กรอง batch
        farmSelect.addEventListener('change', function() {
            const farmId = parseInt(this.value);
            batchSelect.innerHTML = '<option value="">-- เลือกรุ่น --</option>';
            batches.filter(b => b.farm_id === farmId).forEach(b => {
                const option = document.createElement('option');
                option.value = b.id;
                option.text = b.batch_code;
                batchSelect.appendChild(option);
            });

            updateHiddenInputsForAll('feedRowsContainer');
            updateHiddenInputsForAll('medicineRowsContainer');
            updateHiddenInputsForAll('monthlyRowsContainer');
        });

        // เมื่อเปลี่ยน batch → update hidden
        batchSelect.addEventListener('change', function() {
            updateHiddenInputsForAll('feedRowsContainer');
            updateHiddenInputsForAll('medicineRowsContainer');
            updateHiddenInputsForAll('monthlyRowsContainer');
        });

        // init ตอนโหลดครั้งแรก
        document.addEventListener('DOMContentLoaded', function() {
            updateHiddenInputsForAll('feedRowsContainer');
            updateHiddenInputsForAll('medicineRowsContainer');
            updateHiddenInputsForAll('monthlyRowsContainer');
        });
    </script>

    <!-- ส่ง option unit ไปให้ unitSelect-->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const unitsByType = @json($unitsByType);

            // Event delegation สำหรับทุกแถว
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('item-type-select')) {
                    const row = e.target.closest('.feed-row, .medicine-row, .monthly-row');
                    const unitSelect = row.querySelector('.unit-select');
                    const type = e.target.value;
                    const units = unitsByType[type] || [];

                    unitSelect.innerHTML = '<option value="">- เลือกหน่วย -</option>';
                    units.forEach(u => {
                        const option = document.createElement('option');
                        option.value = u;
                        option.textContent = u;
                        unitSelect.appendChild(option);
                    });
                }
            });

            // Init units ของแถวแรกทุก container
            document.querySelectorAll('.feed-row, .medicine-row, .monthly-row').forEach(row => {
                const itemTypeSelect = row.querySelector('.item-type-select');
                if (itemTypeSelect) {
                    itemTypeSelect.dispatchEvent(new Event('change'));
                }
            });
        });
    </script>


    <!-- ส่วนเพิ่มแถวใหม่ (clone row) -->
    <script>
        function addRow(containerId) {
            try {
                let container = document.getElementById(containerId);
                if (!container) throw new Error("ไม่พบ container: " + containerId);

                let firstRow = container.querySelector('.feed-row, .medicine-row, .monthly-row');
                if (!firstRow) throw new Error("ไม่พบ row");

                let newRow = firstRow.cloneNode(true);

                // ล้างค่า input (เว้น hidden farm/batch)
                newRow.querySelectorAll('input').forEach(input => {
                    if (input.type !== 'hidden') input.value = '';
                });
                newRow.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
                newRow.querySelectorAll('textarea').forEach(txt => txt.value = '');

                // อัปเดต hidden farm/batch ให้ตรงกับ select
                const farmHidden = newRow.querySelector('.farm-id');
                const batchHidden = newRow.querySelector('.batch-id');
                if (farmHidden) farmHidden.value = farmSelect.value || '';
                if (batchHidden) batchHidden.value = batchSelect.value || '';

                container.appendChild(newRow);
            } catch (err) {
                alert(err.message);
            }
        }

        // ปุ่มเพิ่มแถว
        document.getElementById('addFeedRowBtn').addEventListener('click', () => addRow('feedRowsContainer'));
        document.getElementById('addMedicineRowBtn').addEventListener('click', () => addRow('medicineRowsContainer'));
        document.getElementById('addMonthlyRowBtn').addEventListener('click', () => addRow('monthlyRowsContainer'));
    </script>

    <!-- ส่วนลบแถวเดียว -->
    <script>
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-row')) {
                const row = e.target.closest('.feed-row, .medicine-row');
                if (row) {
                    const container = row.parentElement;
                    if (container.querySelectorAll('.feed-row, .medicine-row, .monthly-row').length > 1) {
                        row.remove();
                    } else {
                        alert("ต้องมีอย่างน้อย 1 แถว");
                    }
                }
            }
        });
    </script>

    <!-- ส่วนล้างแถว -->
    <script>
        function clearRows(containerId) {
            const container = document.getElementById(containerId);
            if (!container) return;

            const allRows = Array.from(container.children)
                .filter(c => c.classList.contains('feed-row') ||
                    c.classList.contains('medicine-row') ||
                    c.classList.contains('monthly-row'));
            if (allRows.length === 0) return;

            const firstRow = allRows[0];

            // ลบแถวอื่นออกหมด
            allRows.forEach((row, i) => {
                if (i > 0) row.remove();
            });

            // เคลียร์ค่าในแถวแรก (เว้น hidden)
            firstRow.querySelectorAll('input').forEach(input => {
                if (input.type !== 'hidden') input.value = '';
            });
            firstRow.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
            firstRow.querySelectorAll('textarea').forEach(txt => txt.value = '');
        }

        // ปุ่มล้าง
        document.getElementById('clearAddFeedRowBtn').addEventListener('click', () => clearRows(
            'feedRowsContainer'));
        document.getElementById('clearAddMedicineRowBtn').addEventListener('click', () => clearRows(
            'medicineRowsContainer'));
        document.getElementById('clearAddMonthlyRowBtn').addEventListener('click', () => clearRows(
            'monthlyRowsContainer'));
    </script>


    @include('admin.js')
</body>

</html>
