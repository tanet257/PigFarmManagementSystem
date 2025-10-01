<!DOCTYPE html>
<html lang="th">

<head>
    @include('admin.css')

    <style>
        /* ปรับความกว้าง dropdown ให้กว้างกว่ากล่อง input */
        .choices__list--dropdown {
            /*min-width: 250px;*/
            /* กว้างขึ้น */
            /*max-width: 400px;*/
            /* ถ้าต้องการจำกัด */
            width: auto;
            /* ให้ขยายตาม content */
        }

        /* ปรับ width ของกล่อง select ที่เลือกแล้ว */
        .choices__inner {
            min-width: 500px;
        }


        /* พื้นหลังของ dropdown */
        .choices__list--dropdown,
        .choices__list[aria-expanded] {
            background-color: #2c2540;
            /* ม่วงเข้มตามธีม */
            color: #f0e6ff;
            /* สีตัวอักษร */
        }

        /* พื้นหลังตอน hover */
        .choices__list--dropdown .choices__item--selectable.is-highlighted {
            background-color: #5a4e7c !important;
            /* ม่วงอ่อน */
            color: #ffffff;
        }

        /* ตัวเลือกปกติ */
        .choices__list--dropdown .choices__item--selectable {
            color: #f0e6ff;
        }

        /* กล่องที่เลือกแล้ว */
        .choices__inner {
            background-color: #1e1b29;
            color: #f0e6ff;
            border: 1px solid #5a4e7c;
        }

        /* search input ด้านบนของ dropdown */
        .choices__list--dropdown .choices__input {
            background-color: #2c2540;
            /* พื้นหลังเหมือนกัน */
            color: #f0e6ff;
            /* ตัวอักษรเหมือนกัน */
            border: 1px solid #5a4e7c;
        }

        /* ตอน focus กล่อง search */
        .choices__list--dropdown .choices__input:focus {
            outline: none;
            border-color: #7e6fc1;
            background-color: #2c2540;
            color: #f0e6ff;
        }

        .filter-form {
            position: sticky;
            top: 0;
            /* อยู่บนสุดเวลา scroll */
            z-index: 10;
            /* ต้องสูงกว่า table header ที่ z-index: 5 */
            padding: 10px 0;
        }


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
                sb.style.backgroundColor = "#28a745";
                sb.style.display = "flex";
                sb.classList.add("show");
                setTimeout(() => {
                    sb.classList.remove("show");
                    sb.style.display = "none";
                }, 10500);
            @elseif (session('error'))
                sbMsg.innerText = "{{ session('error') }}";
                sb.style.backgroundColor = "#dc3545";
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
    @include('admin.header')
    @include('admin.sidebar')

    <div class="page-content">
        <div class="container my-5 table-container">

            <h1 class="text-center">การจัดสรรหมู (Batch Pen Allocations)</h1>

            <!-- Toolbar -->
<div class="toolbar d-flex justify-content-between align-items-center mb-3">
    <!-- Left tools -->
    <div class="left-tools d-flex align-items-center gap-2">
        <form method="GET" action="{{ route('batch_pen_allocations.index') }}"
            class="d-flex align-items-center gap-2">
            <input type="search" name="search" class="form-control form-control-sm w-auto"
                placeholder="ค้นหา..." value="{{ request('search') }}">

            <select name="farm_id" class="form-select form-select-sm w-auto">
                <option value="">ฟาร์มทั้งหมด</option>
                @foreach ($farms as $farm)
                    <option value="{{ $farm->id }}" {{ request('farm_id') == $farm->id ? 'selected' : '' }}>
                        {{ $farm->farm_name }}
                    </option>
                @endforeach
            </select>

            <select name="batch_id" class="form-select form-select-sm w-auto">
                <option value="">รุ่นทั้งหมด</option>
                @foreach ($batches as $batch)
                    <option value="{{ $batch->id }}" {{ request('batch_id') == $batch->id ? 'selected' : '' }}>
                        {{ $batch->batch_code }}
                    </option>
                @endforeach
            </select>

            <select name="sort_by" class="form-select form-select-sm w-auto">
                <option value="">เรียง...</option>
                <option value="barn_code" {{ request('sort_by') == 'barn_code' ? 'selected' : '' }}>เล้า</option>
                <option value="capacity" {{ request('sort_by') == 'capacity' ? 'selected' : '' }}>ความจุเล้า</option>
                <option value="total_allocated"
                    {{ request('sort_by') == 'total_allocated' ? 'selected' : '' }}>จำนวนที่จัดสรรแล้ว</option>
            </select>

            <select name="sort_order" class="form-select form-select-sm w-auto">
                <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>⬆️ น้อย → มาก</option>
                <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>⬇️ มาก → น้อย</option>
            </select>

            <select name="per_page" class="form-select form-select-sm w-auto">
                @foreach ([10, 25, 50, 100] as $n)
                    <option value="{{ $n }}" {{ request('per_page', 10) == $n ? 'selected' : '' }}>
                        {{ $n }} แถว
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-sm btn-primary">Apply</button>
        </form>
    </div>

    <!-- Right tools -->
    <div class="right-tools d-flex align-items-center gap-2">
        <a href="{{ route('batch_pen_allocations.export.csv', request()->all()) }}"
            class="btn btn-sm btn-outline-success">CSV</a>
        <a href="{{ route('batch_pen_allocations.export.pdf', request()->all()) }}"
            class="btn btn-sm btn-outline-danger">PDF</a>
    </div>
</div>





            <div class="card-custom">


                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle text-center">
                        <thead>
                            <tr>
                                <th>ฟาร์ม</th>
                                <th>รุ่น</th>
                                <th>เล้า (Barn)</th>
                                <th>ความจุเล้า</th>
                                <th>จำนวนที่จัดสรรแล้ว</th>
                                <th>รายละเอียดคอก (Pens)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($barnSummaries as $barn)
                                <tr>
                                    <td>{{ $barn['farm_name'] }}</td>
                                    <td>{{ $barn['batch_code'] }}</td>
                                    <td>{{ $barn['barn_code'] }}</td>
                                    <td>{{ $barn['capacity'] }}</td>
                                    <td>{{ $barn['total_allocated'] }}</td>
                                    <td>
                                        <table class="table table-sm table-bordered table-dark text-center mb-0">
                                            <thead>
                                                <tr style="background-color:#3a3361;">
                                                    <th>Pen Code</th>
                                                    <th>Capacity</th>
                                                    <th>Allocated</th>
                                                    <th>Batches</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($barn['pens'] as $pen)
                                                    <tr>
                                                        <td>{{ $pen['pen_code'] }}</td>
                                                        <td>{{ $pen['capacity'] }}</td>
                                                        <td>{{ $pen['allocated'] }}</td>
                                                        <td>
                                                            @foreach ($pen['batches'] as $batch_code)
                                                                <span class="badge-batch">{{ $batch_code }}</span>
                                                            @endforeach
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-danger">❌ ไม่มีข้อมูล</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-between mt-3">
                    <div>
                        แสดง {{ $barnSummaries->firstItem() ?? 0 }} ถึง {{ $barnSummaries->lastItem() ?? 0 }} จาก
                        {{ $barnSummaries->total() ?? 0 }} แถว
                    </div>
                    <div>
                        {{ $barnSummaries->withQueryString()->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const farmSelect = document.getElementById('farmSelect');
            const batchSelect = document.getElementById('batchSelect');

            const farmChoices = new Choices(farmSelect, {
                searchEnabled: false,
                itemSelectText: '',
                shouldSort: false
            });
            const batchChoices = new Choices(batchSelect, {
                searchEnabled: false,
                itemSelectText: '',
                shouldSort: false,
                removeItemButton: true
            });

            farmSelect.addEventListener('change', function() {
                const farmId = this.value;
                batchChoices.clearChoices();

                if (!farmId) return;

                fetch('/get-batches/' + farmId)
                    .then(res => res.json())
                    .then(data => {
                        batchChoices.setChoices(
                            data.map(batch => ({
                                value: batch.id,
                                label: batch.batch_code
                            })),
                            'value', 'label', true
                        );
                    });
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @include('admin.js')
</body>

</html>
