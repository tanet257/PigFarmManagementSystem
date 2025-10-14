<!DOCTYPE html>
<html lang="th">

<head>
    @include('admin.css')

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
