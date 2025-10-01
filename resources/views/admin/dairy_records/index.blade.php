<!DOCTYPE html>
<html lang="th">

<head>
    @include('admin.css')
    <style>
        body {
            background-color: #1e1b29;
            color: #f0e6ff;
        }

        h1 {
            margin-bottom: 10px;
            font-weight: bold;
        }

        .table-container {
            margin: 20px auto;
            max-width: 95%;
        }

        .table thead th {
            background-color: #5a4e7c;
            color: #fff;
            position: sticky;
            top: 0;
            z-index: 5;
        }

        .table tbody tr:hover {
            background-color: #3a3361;
        }

        .badge-purple {
            background-color: #7e6fc1;
        }

        .card-custom {
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
            background-color: #2c2540;
            padding: 15px;
        }

        td,
        th {
            vertical-align: middle !important;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .toolbar .left-tools {
            flex: 1;
        }

        .toolbar .right-tools {
            display: flex;
            gap: 10px;
        }

        .toolbar .form-select-sm,
        .toolbar .btn-sm,
        .toolbar input[type="search"] {
            font-size: 0.85rem;
            padding: 0.35rem 0.5rem;
        }

        input[type="search"] {
            border-radius: 20px;
            padding-left: 12px;
            border: 1px solid #5a4e7c;
            background: #1e1b29;
            color: #f0e6ff;
        }

        .btn-action {
            min-width: 90px;
            text-align: center;
        }
    </style>
</head>

<body>
    @include('admin.header')
    @include('admin.sidebar')

    <div class="page-content">
        <div class="container my-5 table-container">
            <h1 class="text-center">บันทึกประจำวัน (Dairy Records)</h1>

            <div class="toolbar">
                <form method="GET" action="{{ route('dairy_records.index') }}" class="d-flex flex-wrap gap-2">

                    <input type="search" name="search" class="form-control form-control-sm" placeholder="ค้นหา..."
                        value="{{ request('search') }}">

                    <select name="farm_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">ฟาร์มทั้งหมด</option>
                        @foreach ($farms as $farm)
                            <option value="{{ $farm->id }}" {{ request('farm_id') == $farm->id ? 'selected' : '' }}>
                                {{ $farm->farm_name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="batch_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">รุ่นทั้งหมด</option>
                        @foreach ($batches as $batch)
                            <option value="{{ $batch->id }}"
                                {{ request('batch_id') == $batch->id ? 'selected' : '' }}>
                                {{ $batch->batch_code }}
                            </option>
                        @endforeach
                    </select>

                    <select name="barn_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">เล้าทั้งหมด</option>
                        @foreach ($barns as $barn)
                            <option value="{{ $barn->id }}" {{ request('barn_id') == $barn->id ? 'selected' : '' }}>
                                {{ $barn->barn_code }}
                            </option>
                        @endforeach
                    </select>

                    <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">ประเภททั้งหมด</option>
                        <option value="food" {{ request('type') == 'food' ? 'selected' : '' }}>อาหาร</option>
                        <option value="treatment" {{ request('type') == 'treatment' ? 'selected' : '' }}>การรักษา
                        </option>
                        <option value="death" {{ request('type') == 'death' ? 'selected' : '' }}>หมูตาย</option>
                    </select>

                    <input type="date" name="updated_at" class="form-control form-control-sm"
                        value="{{ request('updated_at') }}" onchange="this.form.submit()">

                    <select name="sort_by" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="updated_at" {{ request('sort_by') == 'updated_at' ? 'selected' : '' }}>วันที่
                        </option>
                        <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>
                            สร้างเมื่อ</option>
                        <option value="updated_at" {{ request('sort_by') == 'updated_at' ? 'selected' : '' }}>
                            แก้ไขล่าสุด</option>
                    </select>

                    <select name="sort_order" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>น้อย → มาก
                        </option>
                        <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>มาก → น้อย
                        </option>
                    </select>

                    <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 แถว</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 แถว</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 แถว</option>
                    </select>

                    <button type="submit" class="btn btn-sm btn-outline-light">ค้นหา</button>
                </form>
            </div>

            <div class="card-custom">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>ฟาร์ม</th>
                                <th>รุ่น</th>
                                <th>เล้า</th>
                                <th>วันที่</th>
                                <th>โน๊ต</th>
                                <th>ประเภท</th>
                                <th>รายละเอียด</th>
                                <th>จำนวน</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dairyRecords as $index => $record)
                                {{-- ถ้ามี feed (อาหาร) --}}
                                @foreach ($record->dairy_storehouse_uses as $use)
                                    <tr>
                                        <td>{{ $dairyRecords->firstItem() + $index }}</td>
                                        <td>{{ $record->batch?->farm?->farm_name ?? '-' }}</td>
                                        <td>{{ $record->batch?->batch_code ?? '-' }}</td>
                                        <td>{{ $use->barn?->barn_code ?? '-' }}</td>
                                        <td>{{ $record->updated_at }}</td>
                                        <td>{{ $record->note ?? '-' }}</td>
                                        <td>อาหาร</td>
                                        <td>รหัส: {{ $use->storehouse?->item_code }}, หน่วย:
                                            {{ $use->storehouse?->unit }}</td>
                                        <td>{{ $use->quantity }}</td>
                                    </tr>
                                @endforeach

                                {{-- ถ้ามีการรักษา --}}
                                @foreach ($record->batch_treatments as $bt)
                                    <tr>
                                        <td>{{ $dairyRecords->firstItem() + $index }}</td>
                                        <td>{{ $record->batch?->farm?->farm_name ?? '-' }}</td>
                                        <td>{{ $record->batch?->batch_code ?? '-' }}</td>
                                        <td>{{ $record->barn?->barn_code ?? '-' }}/{{ $bt->pen?->pen_code ?? '-' }}
                                        </td>
                                        <td>{{ $record->updated_at }}</td>
                                        <td>{{ $record->note ?? '-' }}</td>
                                        <td>การรักษา</td>
                                        <td>
                                            ยา: {{ $bt->medicine_code }},
                                            หน่วย: {{ $bt->unit }},
                                            สถานะ: {{ $bt->status }}
                                        </td>
                                        <td>{{ $bt->quantity }}</td>
                                    </tr>
                                @endforeach

                                {{-- ถ้ามีหมูตาย --}}
                                @foreach ($record->pig_deaths as $pd)
                                    <tr>
                                        <td>{{ $dairyRecords->firstItem() + $index }}</td>
                                        <td>{{ $record->batch?->farm?->farm_name ?? '-' }}</td>
                                        <td>{{ $record->batch?->batch_code ?? '-' }}</td>
                                        <td>{{ $record->barn?->barn_code ?? '-' }}/{{ $pd->pen?->pen_code ?? '-' }}
                                        </td>
                                        <td>{{ $record->updated_at }}</td>
                                        <td>{{ $record->note ?? '-' }}</td>
                                        <td>หมูตาย</td>
                                        <td>คอก: {{ $pd->pen?->pen_code ?? '-' }}</td>
                                        <td>{{ $pd->quantity }}</td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">ไม่มีข้อมูล</td>
                                </tr>
                            @endforelse
                        </tbody>



                    </table>

                    <div class="mt-3">
                        {{ $dairyRecords->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        @include('admin.js')
</body>

</html>
