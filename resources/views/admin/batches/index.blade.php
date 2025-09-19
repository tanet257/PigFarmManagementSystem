{{-- resources/views/batches/index.blade.php --}}
<!DOCTYPE html>
<html lang="th">

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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

        /* snackbar */
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

    <script>
        window.onload = function() {
            const sb = document.getElementById("snackbar");
            const sbMsg = document.getElementById("snackbarMessage");

            @if (session('success'))
                sbMsg.innerText = "{{ session('success') }}";
                sb.style.backgroundColor = "#28a745"; // เขียว
                sb.style.display = "flex";
                sb.classList.add("show");
                setTimeout(() => {
                    sb.classList.remove("show");
                    sb.style.display = "none";
                }, 10500);
            @elseif (session('error'))
                sbMsg.innerText = "{{ session('error') }}";
                sb.style.backgroundColor = "#dc3545"; // แดง
                sb.style.display = "flex";
                sb.classList.add("show");
                setTimeout(() => {
                    sb.classList.remove("show");
                    sb.style.display = "none";
                }, 10500);
            @endif
        };

        function showSnackbar(message, bgColor = "#dc3545") {
            const sb = document.getElementById("snackbar");
            const sbMsg = document.getElementById("snackbarMessage");
            sbMsg.innerText = message;
            sb.style.backgroundColor = bgColor;
            sb.style.display = "flex";
            sb.classList.add("show");
            setTimeout(() => {
                sb.classList.remove("show");
                sb.style.display = "none";
            }, 5000);
        }

        function copySnackbar() {
            let text = document.getElementById("snackbarMessage").innerText;
            navigator.clipboard.writeText(text).then(() => {
                let btn = document.getElementById("copyBtn");
                btn.innerHTML = '<i class="bi bi-check2"></i> Copied';
                btn.disabled = true;
                setTimeout(() => {
                    btn.innerHTML = '<i class="bi bi-copy"></i>';
                    btn.disabled = false;
                }, 2000);
            });
        }

        function closeSnackbar() {
            let sb = document.getElementById("snackbar");
            sb.classList.remove("show");
            sb.style.display = "none";
        }
    </script>
</head>

<body>

    <div id="snackbar" class="snackbar">
        <span id="snackbarMessage"></span>
        <button onclick="copySnackbar()" id="copyBtn"><i class="bi bi-copy"></i></button>
        <button onclick="closeSnackbar()">✕</button>
    </div>

    @include('admin.header')
    @include('admin.sidebar')

    <!-- DateSelect Plugin -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <div class="page-content">
        <div class="container my-5 table-container">

            <h1 class="text-center">ข้อมูลรุ่นหมู (Batches)</h1>

            {{-- Toolbar --}}
            <div class="toolbar">
                <div class="left-tools">
                    <form method="GET" action="{{ route('batches.index') }}" class="d-flex">
                        <input type="search" name="search" class="form-control form-control-sm me-2"
                            placeholder="ค้นหา..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-sm btn-outline-light">ค้นหา</button>
                    </form>
                </div>

                <div class="right-tools">
                    <form method="GET" action="{{ route('batches.index') }}" class="d-flex">
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
                            <option value="start_date" {{ request('sort_by') == 'start_date' ? 'selected' : '' }}>
                                วันที่เริ่มต้น</option>
                            <option value="end_date" {{ request('sort_by') == 'end_date' ? 'selected' : '' }}>
                                วันที่สิ้นสุด</option>
                            <option value="total_pig_amount"
                                {{ request('sort_by') == 'total_pig_amount' ? 'selected' : '' }}>จำนวนสุกรรวม</option>
                            <option value="total_pig_price"
                                {{ request('sort_by') == 'total_pig_price' ? 'selected' : '' }}>ราคารวมสุกร</option>
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
                                    {{ $n }} แถวต่อหน้า</option>
                            @endforeach
                        </select>

                        <button type="submit" class="btn btn-sm btn-primary me-2">Apply</button>
                    </form>

                    <a href="{{ route('batches.export.csv') }}" class="btn btn-sm btn-outline-success">Export CSV</a>
                    <a href="{{ route('batches.export.pdf') }}" class="btn btn-primary">Export PDF</a>

                    <button class="btn btn-success" data-bs-toggle="modal"
                        data-bs-target="#createModal">เพิ่มรุ่นใหม่</button>
                </div>
            </div>

            {{-- Table --}}
            <div class="card-custom">
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle text-center">
                        <thead>
                            <tr>
                                <th>ชื่อฟาร์ม</th>
                                <th>รหัสรุ่น</th>
                                <th>จำนวนเล้า</th>
                                <th>จำนวนสุกร</th>
                                <th>จำนวนคอก</th>
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
                                    <td>{{ $batch->batch_code }}</td>

                                    {{-- เลือก barn แรกของ farm --}}
                                    <td>{{ $batch->farm->barns->count() ?? '-' }}</td>

                                    {{-- เลือก pen แรกของ barn --}}
                                    <td>{{ $batch->farm->barns->first()->pig_capacity ?? '-' }}</td>
                                    <td>{{ $batch->farm->barns->first()->pens->count() ?? '-' }}</td>

                                    <td>{{ number_format($batch->total_pig_weight ?? 0, 2) }}</td>
                                    <td>{{ number_format($batch->total_pig_amount ?? 0) }}</td>
                                    <td>{{ number_format($batch->total_pig_price ?? 0, 2) }}</td>
                                    <td>
                                        @if ($batch->status == 'กำลังเลี้ยง')
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
                                        <button class="btn btn-warning btn-sm btn-action" data-bs-toggle="modal"
                                            data-bs-target="#editModal{{ $batch->id }}">แก้ไข</button>

                                        <form action="{{ route('batches.delete', $batch->id) }}" method="POST"
                                            style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('คุณแน่ใจไหมว่าจะลบรุ่นนี้?')">Delete</button>
                                        </form>

                                        {{-- Modal Edit --}}
                                        <div class="modal fade" id="editModal{{ $batch->id }}" tabindex="-1"
                                            aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content bg-dark text-light">
                                                    <div class="modal-header">
                                                        <h5>แก้ไขรุ่นหมู (Batch)</h5>
                                                        <button type="button" class="btn-close btn-close-white"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('batches.update', $batch->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label>รหัสรุ่น</label>
                                                                <input type="text" name="batch_code"
                                                                    class="form-control"
                                                                    value="{{ $batch->batch_code }}" required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label>น้ำหนักรวม (kg)</label>
                                                                <input type="number"
                                                                    name="total_pig_weight" class="form-control"
                                                                    value="{{ $batch->total_pig_weight }}" required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label>จำนวนรวม</label>
                                                                <input type="number" name="total_pig_amount"
                                                                    class="form-control"
                                                                    value="{{ $batch->total_pig_amount }}" required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label>ราคารวม (บาท)</label>
                                                                <input type="number"
                                                                    name="total_pig_price" class="form-control"
                                                                    value="{{ $batch->total_pig_price }}" required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label>สถานะ</label>
                                                                <select name="status" class="form-select" required>
                                                                    <option value="กำลังเลี้ยง"
                                                                        {{ $batch->status == 'กำลังเลี้ยง' ? 'selected' : '' }}>
                                                                        กำลังเลี้ยง</option>
                                                                    <option value="เสร็จสิ้น"
                                                                        {{ $batch->status == 'เสร็จสิ้น' ? 'selected' : '' }}>
                                                                        เสร็จสิ้น</option>
                                                                </select>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label>โน๊ต</label>
                                                                <textarea name="note" class="form-control">{{ $batch->note }}</textarea>
                                                            </div>

                                                            <div class="modal-footer">
                                                                <button type="submit"
                                                                    class="btn btn-primary">บันทึก</button>
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">ยกเลิก</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- End Modal Edit --}}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="15" class="text-danger">❌ ไม่มีข้อมูล Batch</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-between mt-3">
                    <div>
                        แสดง {{ $batches->firstItem() ?? 0 }} ถึง {{ $batches->lastItem() ?? 0 }} จาก
                        {{ $batches->total() ?? 0 }} แถว
                    </div>
                    <div>
                        {{ $batches->withQueryString()->links() }}
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
                    <h5>เพิ่มรุ่นใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('batches.create') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label>ฟาร์ม</label>
                        <select name="farm_id" class="form-select">
                            @foreach ($farms as $farm)
                                <option value="{{ $farm->id }}">{{ $farm->farm_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>รหัสรุ่น</label>
                        <input type="text" name="batch_code" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>โน๊ต</label>
                        <textarea name="note" class="form-control"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">บันทึก</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Flatpickr Script --}}
    <script>
        @foreach ($batches as $batch)
            flatpickr("#end_date_{{ $batch->id }}", {
                enableTime: true,
                dateFormat: "d/m/Y H:i",
                maxDate: "today",
                time_24hr: true,
            });
        @endforeach
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @include('admin.js')
</body>

</html>
