<!DOCTYPE html>
<html lang="th">

<head>
    @include('admin.css')
</head>

<body>
    @include('admin.header')
    @include('admin.sidebar')

    <div class="page-content">
        <div class="container my-5">
            <div class="card-header">
                <h1 class="text-center">รายงานความเคลื่อนไหวของสต็อก (Inventory Movement)</h1>
            </div>
            <div class="py-2"></div>

            {{-- Toolbar --}}
            <div class="card-custom-secondary mb-3">
                <form method="GET" action="{{ route('inventory_movements.index') }}"
                    class="d-flex align-items-center gap-2 flex-wrap" id="filterForm">
                    <!-- Farm Filter (Dark Blue) -->
                    <select name="farm_id" class="form-select form-select-sm filter-select-blue">
                        <option value="">ฟาร์มทั้งหมด</option>
                        @foreach ($farms as $farm)
                            <option value="{{ $farm->id }}" {{ request('farm_id') == $farm->id ? 'selected' : '' }}>
                                {{ $farm->farm_name }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Batch Filter (Dark Blue) -->
                    <select name="batch_id" class="form-select form-select-sm filter-select-blue">
                        <option value="">รุ่นทั้งหมด</option>
                        @foreach ($batches as $batch)
                            <option value="{{ $batch->id }}"
                                {{ request('batch_id') == $batch->id ? 'selected' : '' }}>
                                {{ $batch->batch_code }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Sort By -->
                    <select name="sort_by" class="form-select form-select-sm filter-select-blue">
                        <option value="">เรียงลำดับ...</option>
                        <option value="date" {{ request('sort_by') == 'date' ? 'selected' : '' }}>วันที่</option>
                        <option value="quantity" {{ request('sort_by') == 'quantity' ? 'selected' : '' }}>จำนวน</option>
                        <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>
                            วันที่บันทึก</option>
                    </select>

                    <!-- Sort Order -->
                    <select name="sort_order" class="form-select form-select-sm filter-select-blue">
                        <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>น้อย → มาก
                        </option>
                        <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>มาก → น้อย
                        </option>
                    </select>

                    <!-- Per Page -->
                    <select name="per_page" class="form-select form-select-sm filter-select-blue">
                        @foreach ([10, 25, 50, 100] as $n)
                            <option value="{{ $n }}" {{ request('per_page', 10) == $n ? 'selected' : '' }}>
                                {{ $n }} แถว
                            </option>
                        @endforeach
                    </select>

                    <div class="ms-auto d-flex gap-2">
                        <a class="btn btn-outline-success btn-sm" href="{{ route('inventory_movements.export.csv') }}">
                            <i class="bi bi-file-earmark-spreadsheet me-1"></i> CSV
                        </a>
                        <a class="btn btn-outline-danger btn-sm" href="{{ route('inventory_movements.export.pdf') }}">
                            <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                        </a>
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="card-custom-secondary mt-3">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-header-custom">
                            <tr>
                                <th class="text-center">วันที่</th>
                                <th class="text-center">ชื่อฟาร์ม</th>
                                <th class="text-center">รหัสรุ่น</th>
                                <th class="text-center">ประเภทสินค้า</th>
                                <th class="text-center">รหัสสินค้า</th>
                                <th class="text-center">ชื่อสินค้า</th>
                                <th class="text-center">ประเภทการเปลี่ยนแปลง</th>
                                <th class="text-center">จำนวน</th>
                                <th class="text-center">โน้ต</th>
                                <th class="text-center">บันทึกเมื่อ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movements as $movement)
                                <tr>
                                    <td>{{ $movement->date }}</td>
                                    <td>{{ $movement->storehouse->farm->farm_name ?? '-' }}</td>
                                    <td>{{ $movement->batch->batch_code ?? '-' }}</td>
                                    <td>{{ $movement->storehouse->item_type ?? '- ' }}</td>
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

    {{-- Auto-submit filters --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterForm = document.getElementById('filterForm');
            const filters = filterForm.querySelectorAll('select');

            filters.forEach(filter => {
                filter.addEventListener('change', function() {
                    filterForm.submit();
                });
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @include('admin.js')
</body>

</html>
