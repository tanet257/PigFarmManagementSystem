<!DOCTYPE html>
<html>

<head>
    @include('admin.css')

</head>

<body>

    @include('admin.header')
    @include('admin.sidebar')

    <div class="page-content">
        <div class="page-header">
            <div class="container-fluid">

                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">เพิ่มคอก (Add Pen)</h4>
                    </div>
                    <div class="card-body">
                        <link rel="stylesheet"
                            href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

                        <form action="{{ url('upload_pen') }}" method="post" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label for="barn_code" class="form-label">เลือกเล้า</label>
                                <select name="barn_id" class="form-select" required>
                                    <option value="">-- เลือกเล้า --</option>
                                    @foreach ($barns as $barn)
                                        <option value="{{ $barn->id }}">
                                            {{ $barn->barn_code ?? 'เล้า ' . $barn->id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="pen_code" class="form-label">รหัสคอก</label>
                                <input type="text" class="form-control" id="pen_code" name="pen_code"
                                    value="{{ old('pen_code') }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="pig_capacity" class="form-label">จำนวนหมูสูงสุดต่อคอก</label>
                                <input type="number" class="form-control" id="pig_capacity" name="pig_capacity"
                                    value="{{ old('pig_capacity') }}" required min="0">
                            </div>

                            <div class="mb-3">
                                <select name="สถานะ" class="form-select" required>
                                    <option value="">-- เลือกสถานะ --</option>
                                    <option value="กำลังใช้งาน">กำลังใช้งาน</option>
                                    <option value="ไม่ได้ใช้งาน">ไม่ได้ใช้งาน</option>
                                    <option value="ใช้กักหมูป่วย">ใช้กักหมูป่วย</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="note" class="form-label">หมายเหตุ</label>
                                <textarea class="form-control" id="note" name="note">{{ old('note') }}</textarea>
                            </div>

                            <button type="submit" value="Add Pen" class="btn btn-primary">บันทึก</button>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @include('admin.js')
</body>

</html>
