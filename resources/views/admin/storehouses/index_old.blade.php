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
                        <select name="farm_id" class="form-select form-select-sm me-2">
                            <option value="">เลือกฟาร์มทั้งหมด</option>
                            @foreach ($farms as $farm)
                                <option value="{{ $farm->id }}"
                                    {{ request('farm_id') == $farm->id ? 'selected' : '' }}>
                                    {{ $farm->farm_name }}
                                </option>
                            @endforeach
                        </select>

                        <select name="sort_by" class="form-select form-select-sm me-2">
                            <option value="">เรียงลำดับ...</option>
                            <option value="date" {{ request('sort_by') == 'date' ? 'selected' : '' }}>
                                วันที่ซื้อสินค้าเข้าคลัง</option>
                            <option value="updated_at" {{ request('sort_by') == 'updated_at' ? 'selected' : '' }}>
                                วันที่แก้ไขล่าสุด</option>
                            <option value="stock" {{ request('sort_by') == 'stock' ? 'selected' : '' }}>จำนวนสต็อก
                            </option>
                            <option value="total_price" {{ request('sort_by') == 'total_price' ? 'selected' : '' }}>
                                ราคารวม</option>
                        </select>

                        <select name="sort_order" class="form-select form-select-sm me-2">
                            <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>น้อย → มาก
                            </option>
                            <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>มาก → น้อย
                            </option>
                        </select>

                        <select name="per_page" class="form-select form-select-sm me-2">
                            @foreach ([10, 25, 50, 100] as $n)
                                <option value="{{ $n }}"
                                    {{ request('per_page', 10) == $n ? 'selected' : '' }}>
                                    {{ $n }} แถว
                                </option>
                            @endforeach
                        </select>

                        <button type="submit" class="btn btn-sm btn-action btn-primary me-2">Apply</button>
                    </form>

                    <a href="{{ route('storehouses.export.csv') }}" class="btn btn-sm btn-outline-success">Export
                        CSV</a>
                    <a href="{{ route('storehouses.export.pdf') }}" class="btn btn-primary">Export PDF</a>

                    <button class="btn btn-success" data-bs-toggle="modal"
                        data-bs-target="#createModal">เพิ่มสินค้าเข้าคลัง
                    </button>
                </div>
            </div>

            {{-- Table --}}
            <div class="card-custom">
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle text-center">
                        <thead>
                            <tr>
                                <th>วันที่เพิ่มเข้าคลัง</th>
                                <th>ชื่อฟาร์ม</th>

                                <th>ประเภทรายการ</th>
                                <th>รหัสรายการ</th>
                                <th>ชื่อรายการ</th>
                                <th>จำนวนสต็อก</th>
                                <th>ราคาต่อรายการ</th>
                                <th>ค่าส่ง</th>
                                <th>ราคารวม</th>
                                <th>หน่วย</th>
                                <th>สถานะ</th>
                                <th>สลิป</th>
                                <th>โน๊ต</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($storehouses as $storehouse)
                                <tr>
                                    <td>{{ $storehouse->latestCost->date ?? '-' }}</td>
                                    <td>{{ $storehouse->farm->farm_name ?? '-' }}</td>

                                    <td>{{ $storehouse->item_type }}</td>
                                    <td>{{ $storehouse->item_code }}</td>
                                    <td>{{ $storehouse->item_name }}</td>
                                    <td>{{ number_format($storehouse->stock, 2) }}</td>
                                    <td>{{ number_format($storehouse->latestCost->price_per_unit ?? 0, 2) }}</td>
                                    <td>{{ number_format($storehouse->latestCost->transport_cost ?? 0, 2) }}</td>
                                    <td>{{ number_format($storehouse->latestCost->total_price ?? 0, 2) }}</td>
                                    <td>{{ $storehouse->unit }}</td>
                                    <td>
                                        @if ($storehouse->status == 'available')
                                            <span class="badge bg-purple">available</span>
                                        @elseif($storehouse->status == 'unavailable')
                                            <span class="badge bg-secondary">unavailable</span>
                                        @else
                                            <span class="badge bg-dark">-</span>
                                        @endif
                                    </td>
                                    {{-- ดึงภาพจาก cloudinary --}}
                                    <td>
                                        @if ($storehouse->latestCost && !empty($storehouse->latestCost->receipt_file))
                                            @php
                                                $file = $storehouse->latestCost->receipt_file;
                                            @endphp

                                            @if (Str::endsWith($file, ['.jpg', '.jpeg', '.png']))
                                                <img src="{{ $file }}" alt="Receipt"
                                                    style="max-width:100px; max-height:100px;">
                                            @else
                                                <a href="{{ $file }}" target="_blank">Download</a>
                                            @endif
                                        @else
                                            <span class="text-muted">ไม่มีไฟล์</span>
                                        @endif
                                    </td>




                                    <td>{{ $storehouse->note ?? '-' }}</td>
                                    <td>
                                        {{-- Edit Button --}}
                                        <button class="btn btn-warning btn-sm btn-action" data-bs-toggle="modal"
                                            data-bs-target="#editModal{{ $storehouse->id }}">
                                            แก้ไข
                                        </button>
                                        {{-- Delete Button --}}
                                        <form action="{{ route('storehouses.delete', $storehouse->id) }}"
                                            method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-action btn-danger"
                                                onclick="return confirm('คุณแน่ใจไหมว่าจะลบรายการนี้?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>

                                {{-- Modal Edit --}}
                                <div class="modal fade" id="editModal{{ $storehouse->id }}" tabindex="-1"
                                    aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content bg-dark text-light">
                                            <div class="modal-header">
                                                <h5>แก้ไขสินค้า</h5>
                                                <button type="button" class="btn-close"
                                                    data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('storehouses.update', $storehouse->id) }}"
                                                method="POST" enctype="multipart/form-data">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label>ฟาร์ม</label>
                                                        <select name="farm_id" class="form-select" required>
                                                            @foreach ($farms as $farm)
                                                                <option value="{{ $farm->id }}"
                                                                    {{ $storehouse->farm_id == $farm->id ? 'selected' : '' }}>
                                                                    {{ $farm->farm_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label>ชื่อรายการ</label>
                                                        <input type="text" name="item_name" class="form-control"
                                                            value="{{ $storehouse->item_name }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label>รหัสรายการ</label>
                                                        <input type="text" name="item_code" class="form-control"
                                                            value="{{ $storehouse->item_code }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label>หน่วย</label>
                                                        <input type="text" name="unit" class="form-control"
                                                            value="{{ $storehouse->unit }}">
                                                    </div>

                                                    <div class="mb-3">
                                                        <label>โน๊ต</label>
                                                        <textarea name="note" class="form-control">{{ $storehouse->note }}</textarea>
                                                    </div>

                                                    <div class="mb-3"> <label>แนบไฟล์ใบเสร็จ (ถ้ามี)</label> <input
                                                            type="file" name="receipt_file" class="form-control">
                                                        {{-- delete file --}}
                                                        @if ($storehouse->latestCost && $storehouse->latestCost->receipt_file)
                                                            @php$file = $storehouse->latestCost->receipt_file;
                                                                                                                        @endphp ?>
                                                            <small class="text-muted">ไฟล์ปัจจุบัน:</small>
                                                            @if (Str::endsWith($file, ['.jpg', '.jpeg', '.png']))
                                                                <div><img src="{{ $file }}" alt="Receipt"
                                                                        style="max-width:100px;"></div>
                                                            @else
                                                                <div><a href="{{ $file }}"
                                                                        target="_blank">Download</div>
                                                            @endif

                                                            {{-- hidden input กันเคสไม่ได้ติ๊ก checkbox --}}
                                                            <input type="hidden" name="delete_receipt_file"
                                                                value="0">

                                                            <div class="form-check mt-1">
                                                                <input type="checkbox" name="delete_receipt_file"
                                                                    value="1" class="form-check-input"
                                                                    id="deleteReceipt{{ $storehouse->id }}">
                                                                <label class="form-check-label"
                                                                    for="deleteReceipt{{ $storehouse->id }}">
                                                                    ลบไฟล์ปัจจุบัน
                                                                </label>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-primary">บันทึก</button>
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">ยกเลิก</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                {{-- Modal Edit Form --}}

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
                        แสดง {{ $storehouses->firstItem() ?? 0 }} ถึง {{ $storehouses->lastItem() ?? 0 }} จาก
                        {{ $storehouses->total() ?? 0 }} แถว
                    </div>
                    <div>
                        {{ $storehouses->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Create --}}
    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-dark text-light">
                <div class="modal-header">
                    <h5>เพิ่มสินค้าใหม่เข้าคลัง</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('storehouses.create') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>ฟาร์ม</label>
                            <select name="farm_id" class="form-select">
                                @foreach ($farms as $farm)
                                    <option value="{{ $farm->id }}">{{ $farm->farm_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>ประเภทรายการ</label>
                            <select name="item_type" class="form-select" required>
                                <option value="">-- ประเภทรายการ --</option>
                                <option value="feed">อาหาร</option>
                                <option value="medicine">ยา</option>
                                <option value="vaccine">วัคซีน</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>ชื่อรายการ</label>
                            <input type="text" name="item_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>รหัสรายการ</label>
                            <input type="text" name="item_code" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>หน่วย</label>
                            <input type="text" name="unit" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>โน๊ต</label>
                            <textarea name="note" class="form-control"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">บันทึก</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- End Modal Create --}}

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @include('admin.js')
</body>

</html>
