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

            <!-- Title -->
            <h1 class="text-center">รายงานความเคลื่อนไหวของสต็อก (Inventory Movement)</h1>

            <!-- Toolbar -->
            <div class="toolbar">
                <div class="left-tools">
                    <form method="GET" action="{{ route('inventory_movements.index') }}" class="d-flex">
                        <input type="search" name="search" class="form-control form-control-sm me-2"
                               placeholder="ค้นหา..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-sm btn-outline-light">ค้นหา</button>
                    </form>
                </div>

                <div class="right-tools">
                    <form method="GET" action="{{ route('inventory_movements.index') }}" class="d-flex">
                        <select name="farm_id" class="form-select form-select-sm me-2">
                            <option value="">เลือกฟาร์มทั้งหมด</option>
                            @foreach ($farms as $farm)
                                <option value="{{ $farm->id }}"
                                    {{ request('farm_id') == $farm->id ? 'selected' : '' }}>
                                    {{ $farm->farm_name }}
                                </option>
                            @endforeach
                        </select>

                        <select name="batch_id" class="form-select form-select-sm me-2">
                            <option value="">เลือกรุ่นทั้งหมด</option>
                            @foreach ($batches as $batch)
                                <option value="{{ $batch->id }}"
                                    {{ request('batch_id') == $batch->id ? 'selected' : '' }}>
                                    {{ $batch->batch_code }}
                                </option>
                            @endforeach
                        </select>

                        <select name="sort_by" class="form-select form-select-sm me-2">
                            <option value="">เรียงลำดับ...</option>
                            <option value="date" {{ request('sort_by') == 'date' ? 'selected' : '' }}>วันที่</option>
                            <option value="quantity" {{ request('sort_by') == 'quantity' ? 'selected' : '' }}>จำนวน</option>
                            <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>วันที่บันทึก</option>
                        </select>

                        <select name="sort_order" class="form-select form-select-sm me-2">
                            <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>น้อย → มาก</option>
                            <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>มาก → น้อย</option>
                        </select>

                        <select name="per_page" class="form-select form-select-sm me-2">
                            @foreach ([10, 25, 50, 100] as $n)
                                <option value="{{ $n }}" {{ request('per_page', 10) == $n ? 'selected' : '' }}>
                                    {{ $n }} แถว
                                </option>
                            @endforeach
                        </select>

                        <button type="submit" class="btn btn-sm btn-action btn-primary me-2">Apply</button>
                    </form>

                    <a href="{{ route('inventory_movements.export.csv') }}" class="btn btn-sm btn-outline-success">Export CSV</a>
                    <a href="{{ route('inventory_movements.export.pdf') }}" class="btn btn-primary">Export PDF</a>
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
                                <th>ประเภทสินค้า</th>
                                <th>รหัสสินค้า</th>
                                <th>ชื่อสินค้า</th>
                                <th>ประเภทการเปลี่ยนแปลง</th>
                                <th>จำนวน</th>
                                <th>โน้ต</th>
                                <th>บันทึกเมื่อ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movements as $movement)
                                <tr>
                                    <td>{{ $movement->date }}</td>
                                    <td>{{ $movement->storehouse->farm->farm_name ?? '-' }}</td>
                                    <td>{{ $movement->batch->batch_code ?? '-' }}</td>
                                    <td>{{ $movement->storehouse->item_type ?? '- '}}</td>
                                    <td>{{ $movement->storehouse->item_code ?? '-' }}</td>
                                    <td>{{ $movement->storehouse->item_name ?? '-' }}</td>
                                    <td>
                                        @if ($movement->change_type == 'in')
                                            <span class="badge bg-purple">เข้า</span>
                                        @elseif($movement->change_type == 'out')
                                            <span class="badge bg-secondary">ออก</span>
                                        @else
                                            <span class="badge bg-dark">-</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($movement->quantity, 2) }}</td>
                                    <td>{{ $movement->note ?? '-' }}</td>
                                    <td>{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-danger">❌ ไม่มีข้อมูลความเคลื่อนไหว</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-between mt-3">
                    <div>
                        แสดง {{ $movements->firstItem() ?? 0 }} ถึง {{ $movements->lastItem() ?? 0 }} จาก
                        {{ $movements->total() ?? 0 }} แถว
                    </div>
                    <div>
                        {{ $movements->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @include('admin.js')
</body>
</html>
