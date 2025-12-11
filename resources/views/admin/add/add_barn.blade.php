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
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-header bg-primary text-white">
                        <h4 class="text-center">เพิ่มเล้าหมู</h4>
                    </div>
                    <div class="card-body">

                        <link rel="stylesheet"
                            href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

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
