<!DOCTYPE html>
<html>
<head>
    @include('admin.css')
    <style>
        label {
            display:inline-block;
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
            -moz-appearance: textfield; /* Firefox */
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
                        <h4 class="mb-0">บันทึกสุกรรับเข้า (Pig Entry Record)</h4>
                    </div>
                    <div class="card-body">
                        <link rel="stylesheet"
                    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
                    
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
                </style>

                @if (session('success'))
                    <div id="snackbar" class="snackbar" style="background-color:#28a745">
                        {{ session('success') }}
                        <button onclick="closeSnackbar()">✖</button>
                    </div>
                @endif

                @if (session('error'))
                    <div id="snackbar" class="snackbar" style="background-color:#dc3545">
                        <span id="snackbarMessage">{{ session('error') }}</span>
                        <button id="copyBtn" onclick="copySnackbar()"><i class="bi bi-copy"></i></button>
                        <button onclick="closeSnackbar()">✖</button>
                    </div>
                @endif

                <script>
                    window.onload = function() {
                        let sb = document.getElementById("snackbar");
                        if (sb) {
                            sb.classList.add("show");
                            setTimeout(function() {
                                if (sb) sb.classList.remove("show");
                            }, 10500);
                        }
                    };

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
                        if (sb) {
                            sb.classList.remove("show");
                        }
                    }
                </script>

                        <form action="{{ url('upload_pig_entry_record') }}" method="post" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label>ฟาร์ม</label>
                                <select name="farm_id" id="farmSelect" class="form-select" required>
                                    <option value="">-- เลือกฟาร์ม --</option>
                                    @foreach($farms as $farm)
                                        <option value="{{ $farm->id }}">{{ $farm->farm_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label>รุ่น / Batch</label>
                                    <select name="batch_id" class="form-select" id="batchSelect" required>
                                        <option value="">-- เลือกรุ่น --</option>
                                        <!-- จะถูกเติมโดย JS ตามฟาร์มที่เลือก -->
                                    </select>
                            </div>

                            <div class="mb-3">
                                <label>วันและเวลาที่รับเข้า</label>
                                <input type="datetime-local" name="pig_entry_date" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label>จำนวนหมูทั้งหมดที่รับเข้า</label>
                                <input type="number" name="total_pig_amount" class="form-control no-scroll" min="1" required>
                            </div>

                            <div class="mb-3">
                                <label>น้ำหนักหมูรวม</label>
                                <input type="number" step="0.01" name="total_pig_weight" class="form-control no-scroll" required>
                            </div>

                            <div class="mb-3">
                                <label>น้ำหนักส่วนเกิน</label>
                                <input type="number" step="0.01" name="excess_weight" class="form-control no-scroll">
                            </div>

                            <div class="mb-3">
                                <label>ค่าน้ำหนักส่วนเกิน</label>
                                <input type="number" step="0.01" name="excess_weight_cost" class="form-control no-scroll">
                            </div>

                            <div class="mb-3">
                                <label>ราคารวม(บาท)</label>
                                <input type="number" step="0.01" name="total_pig_price" class="form-control no-scroll" required>
                            </div>

                            <div class="mb-3">
                                <label>ค่าขนส่ง</label>
                                <input type="number" step="0.01" name="transport_cost" class="form-control no-scroll">
                            </div>

                            <div class="mb-3">
                                <label>อัปโหลดใบเสร็จ</label>
                                <input type="file" name="receipt_file" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label>หมายเหตุ</label>
                                <textarea name="note" rows="4" class="form-control"></textarea>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">บันทึกข้อมูล</button>
                            </div>
                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        const batches = @json($batches); // ส่งมาจาก controller
        const farmSelect = document.getElementById('farmSelect');
        const batchSelect = document.getElementById('batchSelect');

        farmSelect.addEventListener('change', function() {
        const farmId = parseInt(this.value);
        batchSelect.innerHTML = '<option value="">-- เลือกรุ่น --</option>';
        batches.filter(b => b.farm_id === farmId)
               .forEach(b => {
                   const option = document.createElement('option');
                   option.value = b.id;
                   option.text = b.batch_code;
                   batchSelect.appendChild(option);
               });
        });

    </script>

    @include('admin.js')
</body>
</html>
