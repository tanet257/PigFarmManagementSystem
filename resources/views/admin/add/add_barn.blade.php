<!DOCTYPE html>
<html>

<head>
    @include('admin.css')

    <style>
        label {
            display: inline-block;
            width: 200px;
            color: white;
        }

        .div_deg {
            padding: 10px
        }
    </style>

</head>

<body>

    @include('admin.header')

    @include('admin.sidebar')


    <div class="page-content">
        <div class="page-header">
            <div class="container-fluid">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">เพิ่มเล้าหมู (Add Barn)</h4>
                </div>

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



                <form action="{{ url('upload_barn') }}" method="post" enctype="multipart/form-data">

                    @csrf
                    <div class="mb-3">
                        <label>ฟาร์ม</label>
                        <select name="farm_id" id="farmSelect" class="form-select" required>
                            <option value="">-- เลือกฟาร์ม --</option>
                            @foreach ($farms as $farm)
                                <option value="{{ $farm->id }}">{{ $farm->farm_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="barn_code" class="form-label">รหัสเล้า</label>
                        <input type="text" class="form-control" id="barn_code" name="barn_code"
                            value="{{ old('barn_code') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="pig_capacity" class="form-label">จำนวนหมูสูงสุดต่อเล้า</label>
                        <input type="number" class="form-control" id="pig_capacity" name="pig_capacity"
                            value="{{ old('pig_capacity') }}" required min="0">
                    </div>

                    <div class="mb-3">
                        <label for="pen_capacity" class="form-label">จำนวนคอกสูงสุด</label>
                        <input type="number" class="form-control" id="pen_capacity" name="pen_capacity"
                            value="{{ old('pen_capacity') }}" required min="0">
                    </div>

                    <div class="mb-3">
                        <label for="note" class="form-label">หมายเหตุ</label>
                        <textarea class="form-control" id="note" name="note">{{ old('note') }}</textarea>
                    </div>

                    <button type="submit" value="Add Barn" class="btn btn-primary">บันทึก</button>
                </form>

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
