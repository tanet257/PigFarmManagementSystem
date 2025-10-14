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

            <!-- Toolbar Filter -->
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

                <div class="d-flex gap-2">
                    <a href="{{ route('dairy_records.export.csv') }}" class="btn btn-sm btn-outline-success">Export
                        CSV</a>
                    <a href="{{ route('dairy_records.export.pdf') }}" class="btn btn-sm btn-primary">Export PDF</a>
                </div>
            </div>

            <!-- Table -->
            <div class="card-custom">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>ฟาร์ม</th>
                                <th>รุ่น</th>
                                <th>เล้า/คอก</th>
                                <th>วันที่</th>
                                <th>โน๊ต</th>
                                <th>ประเภท</th>
                                <th>รายละเอียด</th>
                                <th>จำนวน</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
    @forelse($dairyRecords as $index => $record)
        @php
            $items = collect();
            $record->dairy_storehouse_uses->each(fn($use) => $items->push([
                'type' => $use->storehouse?->item_type == 'feed' ? 'อาหาร' : ($use->storehouse?->item_type == 'medicine' ? 'การรักษา' : '-'),
                'detail' => 'รหัส: '.$use->storehouse?->item_code.', หน่วย: '.$use->storehouse?->unit,
                'quantity' => $use->quantity,
                'edit_id' => $use->id,
                'delete_route' => route('dairy_storehouse_uses.destroy', $use->id),
                'modal_target' => 'editUseModal'.$use->id,
            ]));
            $record->batch_treatments->each(fn($bt) => $items->push([
                'type' => 'การรักษา',
                'detail' => 'ยา: '.$bt->medicine_code.', หน่วย: '.$bt->unit.', สถานะ: '.$bt->status,
                'quantity' => $bt->quantity,
                'edit_id' => $bt->id,
                'delete_route' => route('batch_treatments.destroy', $bt->id),
                'modal_target' => 'editMedicineModal'.$bt->id,
            ]));
            $record->pig_deaths->each(fn($pd) => $items->push([
                'type' => 'หมูตาย',
                'detail' => 'จำนวน: '.$pd->quantity.', สาเหตุ: '.($pd->cause ?? '-'),
                'quantity' => $pd->quantity,
                'edit_id' => $pd->id,
                'delete_route' => route('pig_deaths.destroy', $pd->id),
                'modal_target' => 'editDeathModal'.$pd->id,
            ]));
        @endphp

        @foreach($items as $item)
            <tr>
                <td>{{ $dairyRecords->firstItem() + $index }}</td>
                <td>{{ $record->batch?->farm?->farm_name ?? '-' }}</td>
                <td>{{ $record->batch?->batch_code ?? '-' }}</td>
                <td>
                    @php
                        $barn_codes = $record->dairy_storehouse_uses->pluck('barn.barn_code')
                                        ->merge($record->batch_treatments->pluck('barn.barn_code'))
                                        ->merge($record->pig_deaths->pluck('barn.barn_code'))
                                        ->filter()->unique()->values()->join('/');
                    @endphp
                    {{ $barn_codes }}
                </td>
                <td>{{ $record->updated_at }}</td>
                <td>{{ $record->note ?? '-' }}</td>
                <td>{{ $item['type'] }}</td>
                <td>{{ $item['detail'] }}</td>
                <td>{{ $item['quantity'] }}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                        data-bs-target="#{{ $item['modal_target'] }}">Edit</button>
                    <form action="{{ $item['delete_route'] }}" method="POST" style="display:inline-block;"
                        onsubmit="return confirm('คุณแน่ใจจะลบหรือไม่?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        @endforeach
    @empty
        <tr>
            <td colspan="10" class="text-center">ไม่มีข้อมูล</td>
        </tr>
    @endforelse
</tbody>

                    </table>

                    <div class="mt-3">
                        {{ $dairyRecords->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>

            <!-- Modals (เหมือนของคุณ, ไม่แก้) -->
            @foreach ($dairyRecords as $record)
                {{-- Feed Edit Modal --}}
                @foreach ($record->dairy_storehouse_uses as $use)
                    <div class="modal fade" id="editUseModal{{ $use->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <form action="{{ route('dairy_records.update_feed', [$record->id, $use->id, 'feed']) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="modal-content bg-dark text-light">
                                    <div class="modal-header">
                                        <h5 class="modal-title">แก้ไขข้อมูลอาหาร</h5>
                                        <button type="button" class="btn-close btn-close-white"
                                            data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label>จำนวน</label>
                                            <input type="number" name="quantity" min="0"
                                                class="form-control" value="{{ $use->quantity }}">
                                        </div>
                                        <div class="mb-3">
                                            <label>โน๊ต</label>
                                            <input type="text" name="note" class="form-control"
                                                value="{{ $use->note }}">
                                        </div>
                                        <div class="mb-3">
                                            <label>วันที่</label>
                                            <input type="datetime-local" name="date" class="form-control"
                                                value="{{ old('date', \Carbon\Carbon::parse($use->date ?? ($record->date ?? now()))->format('Y-m-d\TH:i')) }}">

                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-success">บันทึก</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach

                {{-- Treatment Edit Modal --}}
                @foreach ($record->batch_treatments as $bt)
                    <div class="modal fade" id="editMedicineModal{{ $bt->id }}" tabindex="-1"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <form action="{{ route('dairy_records.update_medicine', [$record->id, $bt->id, 'medicine']) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="modal-content bg-dark text-light">
                                    <div class="modal-header">
                                        <h5 class="modal-title">แก้ไขข้อมูลยา</h5>
                                        <button type="button" class="btn-close btn-close-white"
                                            data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label>จำนวน</label>
                                            <input type="number" name="quantity" class="form-control"
                                                value="{{ $bt->quantity }}">
                                        </div>
                                        <div class="mb-3">
                                            <label>สถานะ</label>
                                            <input type="text" name="status" class="form-control"
                                                value="{{ $bt->status }}">
                                        </div>
                                        <div class="mb-3">
                                            <label>โน้ต</label>
                                            <input type="text" name="note" class="form-control"
                                                value="{{ $bt->note }}">
                                        </div>
                                        <div class="mb-3">
                                            <label>วันที่</label>
                                            <input type="date" name="date" class="form-control"
                                                value="{{ \Carbon\Carbon::parse($bt->date)->format('Y-m-d') }}">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-success">บันทึก</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach

                {{-- Pig Death Edit Modal --}}
                @foreach ($record->pig_deaths as $pd)
                    <div class="modal fade" id="editDeathModal{{ $pd->id }}" tabindex="-1"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <form method="POST" action="{{ route('pig_deaths.update', $pd->id) }}">
                                @csrf @method('PUT')
                                <input type="hidden" name="batch_id" value="{{ $pd->batch_id }}">
                                <input type="hidden" name="dairy_record_id" value="{{ $pd->dairy_record_id }}">
                                <div class="modal-content bg-dark text-light">
                                    <div class="modal-header">
                                        <h5 class="modal-title">แก้ไขข้อมูลหมูตาย</h5>
                                        <button type="button" class="btn-close btn-close-white"
                                            data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label>จำนวน</label>
                                            <input type="number" name="quantity" min="0"
                                                class="form-control" value="{{ $pd->quantity }}">
                                        </div>
                                        <div class="mb-3">
                                            <label>สาเหตุ</label>
                                            <input type="text" name="cause" class="form-control"
                                                value="{{ $pd->cause }}">
                                        </div>
                                        <div class="mb-3">
                                            <label>โน๊ต</label>
                                            <input type="text" name="note" class="form-control"
                                                value="{{ $pd->note }}">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-success">บันทึก</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        @include('admin.js')
</body>

</html>
