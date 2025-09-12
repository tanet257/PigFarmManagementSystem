<!DOCTYPE html>
<html lang="th">
<head>
    @include('admin.css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

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
            box-shadow: 0 4px 20px rgba(0,0,0,0.4);
            background-color: #2c2540;
            padding: 15px;
        }

        td, th {
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
    </style>
</head>
<body>
    @include('admin.header')
    @include('admin.sidebar')

    <div class="page-content">
        <div class="container my-5 table-container">

            <!-- Title -->
            <h1 class="text-center">🐷 ข้อมูลรุ่นหมู (Batches)</h1>

            <!-- Toolbar -->
            <div class="toolbar">
                <!-- Left: Search -->
                <div class="left-tools">
                    <form method="GET" action="{{ url()->current() }}" class="d-flex">
                        <input type="search" name="search" class="form-control form-control-sm me-2"
                               placeholder="ค้นหา..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-sm btn-outline-light">ค้นหา</button>
                    </form>
                </div>

                <!-- Right: Filter, Sort, Export -->
                <div class="right-tools">
                    <form method="GET" action="{{ url()->current() }}" class="d-flex">
                        <!-- Filter by Farm -->
                        <select name="farm_id" class="form-select form-select-sm me-2">
                            <option value="">เลือกฟาร์มทั้งหมด</option>
                            @foreach($farms as $farm)
                                <option value="{{ $farm->id }}" {{ request('farm_id') == $farm->id ? 'selected' : '' }}>
                                    {{ $farm->farm_name }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Sort by -->
                        <select name="sort_by" class="form-select form-select-sm me-2">
                            <option value="">เรียงลำดับ...</option>
                            <option value="start_date" {{ request('sort_by') == 'start_date' ? 'selected' : '' }}>วันที่เริ่มต้น</option>
                            <option value="end_date" {{ request('sort_by') == 'end_date' ? 'selected' : '' }}>วันที่สิ้นสุด</option>
                            <option value="total_pig_amount" {{ request('sort_by') == 'total_pig_amount' ? 'selected' : '' }}>จำนวนสุกรรวม</option>
                            <option value="total_pig_price" {{ request('sort_by') == 'total_pig_price' ? 'selected' : '' }}>ราคารวมสุกร</option>
                        </select>

                        <!-- Sort order -->
                        <select name="sort_order" class="form-select form-select-sm me-2">
                            <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>น้อย → มาก</option>
                            <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>มาก → น้อย</option>
                        </select>

                        <button type="submit" class="btn btn-sm btn-primary me-2">Apply</button>
                    </form>

                    <!-- Export buttons -->
                    <a href="{{ route('batches.export.csv') }}" class="btn btn-sm btn-outline-success">Export CSV</a>
                    <a href="{{ route('batches.export.pdf') }}" class="btn btn-primary">Export PDF</a>
                </div>
            </div>

            <!-- Card + Table -->
            <div class="card-custom">
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle text-center">
                        <thead>
                            <tr>
                                <th>ชื่อฟาร์ม</th>
                                <th>รหัสเล้า</th>
                                <th>จำนวนเล้า</th>
                                <th>จำนวนสุกร</th>
                                <th>จำนวนคอก</th>
                                <th>รหัสคอก</th>
                                <th>รหัสรุ่น</th>
                                <th>น้ำหนักรวม</th>
                                <th>จำนวนรวม</th>
                                <th>ราคารวม</th>
                                <th>สถานะ</th>
                                <th>หมายเหตุ</th>
                                <th>วันที่เริ่มต้น</th>
                                <th>วันที่สิ้นสุด</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($batches as $batch)
                            <tr>
                                <td>{{ $batch->farm->farm_name ?? '-' }}</td>
                                <td>{{ $batch->barn->barn_code ?? '-' }}</td>
                                <td>{{ $batch->farm->barn_capacity ?? '-' }}</td>
                                <td>{{ $batch->barn->pig_capacity ?? '-' }}</td>
                                <td>{{ $batch->barn->pen_capacity ?? '-' }}</td>
                                <td>{{ $batch->pen->pen_code ?? '-' }}</td>
                                <td>{{ $batch->batch_code }}</td>
                                <td>{{ number_format($batch->total_pig_weight, 2) }}</td>
                                <td>{{ $batch->total_pig_amount }}</td>
                                <td>{{ number_format($batch->total_pig_price, 2) }}</td>

                                <td>
                                    @if($batch->status == 'กำลังเลี้ยง')
                                        <span class="badge bg-purple">กำลังเลี้ยง</span>
                                    @elseif($batch->status == 'เสร็จสิ้น')
                                        <span class="badge bg-secondary">เสร็จสิ้น</span>
                                    @else
                                        <span class="badge bg-dark">-</span>
                                    @endif
                                </td>

                                <td>{{ $batch->note ?? '-' }}</td>
                                <td>{{ $batch->start_date ?? '-' }}</td>
                                <td>{{ $batch->end_date ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('batches.edit', $batch->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('batches.delete', $batch->id )}}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('คุณแน่ใจมั้ยว่าจะลบรุ่นนี้?')">Delete
                                        </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="14" class="text-danger">❌ ไม่มีข้อมูล Batch</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    @include('admin.js')
</body>
</html>
