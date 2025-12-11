<!DOCTYPE html>
<html>

<head>
    @include('admin.css')
    <style>
        label {
            display: inline-block;
            width: 200px;
            font-weight: bold;
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
                        <h4 class="text-center">เพิ่มฟาร์ม</h4>
                    </div>
                    <div class="card-body">
                        <link rel="stylesheet"
                            href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

                        <form action="{{ url('upload_farm') }}" method="post" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label for="farm_name" class="form-label">ชื่อฟาร์ม</label>
                                <input type="text" class="form-control" id="farm_name" name="farm_name"
                                    value="{{ old('farm_name') }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="barn_capacity" class="form-label">จำนวนเล้าในฟาร์ม</label>
                                <input type="number" class="form-control" id="barn_capacity" name="barn_capacity"
                                    value="{{ old('barn_capacity') }}" required>
                            </div>

                            <button type="submit" value="Add Farm" class="btn btn-primary">บันทึก</button>


                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @include('admin.js')
</body>

</html>
