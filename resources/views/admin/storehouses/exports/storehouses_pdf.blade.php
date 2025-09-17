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
            <h1 class="text-center">จัดการคลัง (storehouses)</h1>

            <!-- Toolbar -->
            <div class="toolbar">
                <div class="left-tools">
                    <form method="GET" action="{{ route('storehouses.index') }}" class="d-flex">
                        <input type="search" name="search" class="form-control form-control-sm me-2"
                               placeholder="ค้นหา..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-sm btn-outline-light">ค้นหา</button>
                    </form>
                </div>

                <div class="right-tools">
                    <form method="GET" action="{{ route('storehouses.index') }}" class="d-flex">
                        {{-- Filter by Farm --}}
                        <select name="farm_id" class="form-select form-select-sm me-2">
                            <option value="">เลือกฟาร์มทั้งหมด</option>
                            @foreach($farms as $farm)
                                <option value="{{ $farm->id }}" {{ request('farm_id') == $farm->id ? 'selected' : '' }}>
                                    {{ $farm->farm_name }}
                                </option>
                            @endforeach
                        </select>

                        {{-- Sort by --}}
                        <select name="sort_by" class="form-select form-select-sm me-2">
                            <option value="">เรียงลำดับ...</option>
                            <option value="date" {{ request('sort_by')=='date' ? 'selected':'' }}>วันที่ซื้อสินค้าเข้าคลัง</option>
                            <option value="updated_at" {{ request('sort_by')=='updated_at' ? 'selected':'' }}>วันที่แก้ไขล่าสุด</option>
                            <option value="stock" {{ request('sort_by')=='stock' ? 'selected':'' }}>จำนวนสต็อก</option>
                            <option value="total_price" {{ request('sort_by')=='total_price' ? 'selected':'' }}>ราคารวม</option>
                        </select>

                        {{-- Sort order --}}
                        <select name="sort_order" class="form-select form-select-sm me-2">
                            <option value="asc" {{ request('sort_order')=='asc' ? 'selected':'' }}>น้อย → มาก</option>
                            <option value="desc" {{ request('sort_order')=='desc' ? 'selected':'' }}>มาก → น้อย</option>
                        </select>

                        {{-- Rows per page --}}
                        <select name="per_page" class="form-select form-select-sm me-2">
                            @foreach([10, 25, 50, 100] as $n)
                                <option value="{{ $n }}" {{ request('per_page', 10)==$n ? 'selected':'' }}>{{ $n }} แถวต่อหน้า</option>
                            @endforeach
                        </select>

                        <button type="submit" class="btn btn-sm btn-primary me-2">Apply</button>
                    </form>

                    {{-- Export --}}
                    <a href="{{ route('storehouses.export.csv') }}" class="btn btn-sm btn-outline-success">Export CSV</a>
                    <a href="{{ route('storehouses.export.pdf') }}" class="btn btn-primary">Export PDF</a>
                </div>
            </div>

            {{-- Table --}}
            <div class="card-custom">
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle text-center">
                        <thead>
                            <tr>
                                <th>วันที่</th>
                                <th>ชื่อฟาร์ม</th>
                                <th>รหัสรุ่น</th>
                                <th>ประเภทรายการ</th>
                                <th>รหัสรายการ</th>
                                <th>ชื่อรายการ</th>
                                <th>จำนวนสต็อก</th>
                                <th>ราคาต่อรายการ</th>
                                <th>ค่าส่ง</th>
                                <th>ราคารวม</th>
                                <th>หน่วย</th>
                                <th>สถานะสต็อก</th>
                                <th>สลิป</th>
                                <th>โน๊ต</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($storehouses as $storehouse)
                            <tr>
                                <td>{{ $storehouse->latestCost->date ?? '-' }}</td> <!--มาจาก costs-->
                                <td>{{ $storehouse->farm->farm_name ?? '-' }}</td>
                                <td>{{ $storehouse->batch_code }}</td>
                                <td>{{ $storehouse->item_type }}</td>
                                <td>{{ $storehouse->item_code }}</td>
                                <td>{{ $storehouse->item_name }}</td>
                                <td>{{ number_format($storehouse->stock, 2) }}</td>
                                <td>{{ number_format($storehouse->price_per_unit, 2) }}</td>
                                <td>{{ number_format($storehouse->latestCost->transport_cost ?? 0, 2) }}</td> <!--มาจาก costs-->
                                <td>{{ number_format($storehouse->latestCost->total_price ?? 0, 2) }}</td> <!--มาจาก costs-->
                                <td>{{ $storehouse->unit }}</td>
                                <td>
                                    @if($storehouse->status=='available')
                                        <span class="badge bg-purple">available</span>
                                    @elseif($storehouse->status=='unavailable')
                                        <span class="badge bg-secondary">unavailable</span>
                                    @else
                                        <span class="badge bg-dark">-</span>
                                    @endif
                                </td>
                                <td>{{ $storehouse->latestCost->receipt_file ?? '-' }}</td> <!--มาจาก costs-->
                                <td>{{ $storehouse->note ?? '-' }}</td>

                                <td>
                                    <a href="{{ route('storehouses.edit', $storehouse->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('storehouses.delete', $storehouse->id) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('คุณแน่ใจไหมว่าจะลบรายการนี้?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="15" class="text-danger">❌ ไม่มีข้อมูล storehouse</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-between mt-3">
                    <div>
                        แสดง {{ $storehouses->firstItem() ?? 0 }} ถึง {{ $storehouses->lastItem() ?? 0 }} จาก {{ $storehouses->total() ?? 0 }} แถว
                    </div>
                    <div>
                        {{ $storehouses->withQueryString()->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>

    @include('admin.js')
</body>
</html>
