@extends('layouts.admin')

@section('title', 'บันทึกหมูเข้า')

@section('content')
    <div class="container my-5">


        <div class="card-header">
            <h1 class="text-center">บันทึกหมูเข้า (Pig Entry Records)</h1>
        </div>
        <div class="py-2"></div>

        {{-- Toolbar --}}
        <div class="card-custom-secondary mb-3">
            <form method="GET" action="{{ route('pig_entry_records.index') }}"
                class="d-flex align-items-center gap-2 flex-wrap" id="filterForm">
                <!-- Date Filter (Orange) -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="dateFilterBtn"
                        data-bs-toggle="dropdown">
                        <i class="bi bi-calendar-event"></i>
                        @if (request('selected_date') == 'today')
                            วันนี้
                        @elseif(request('selected_date') == 'this_week')
                            สัปดาห์นี้
                        @elseif(request('selected_date') == 'this_month')
                            เดือนนี้
                        @elseif(request('selected_date') == 'this_year')
                            ปีนี้
                        @else
                            วันที่ทั้งหมด
                        @endif
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item {{ request('selected_date') == '' ? 'active' : '' }}"
                                href="{{ route('pig_entry_records.index', array_merge(request()->except('selected_date'), [])) }}">วันที่ทั้งหมด</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'today' ? 'active' : '' }}"
                                href="{{ route('pig_entry_records.index', array_merge(request()->all(), ['selected_date' => 'today'])) }}">วันนี้</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'this_week' ? 'active' : '' }}"
                                href="{{ route('pig_entry_records.index', array_merge(request()->all(), ['selected_date' => 'this_week'])) }}">สัปดาห์นี้</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'this_month' ? 'active' : '' }}"
                                href="{{ route('pig_entry_records.index', array_merge(request()->all(), ['selected_date' => 'this_month'])) }}">เดือนนี้</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'this_year' ? 'active' : '' }}"
                                href="{{ route('pig_entry_records.index', array_merge(request()->all(), ['selected_date' => 'this_year'])) }}">ปีนี้</a>
                        </li>
                    </ul>
                </div>

                <!-- Farm Filter (Dark Blue) -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="farmFilterBtn"
                        data-bs-toggle="dropdown">
                        <i class="bi bi-building"></i>
                        {{ request('farm_id') ? $farms->find(request('farm_id'))->farm_name ?? 'ฟาร์ม' : 'เลือกฟาร์มก่อน' }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item {{ request('farm_id') == '' ? 'active' : '' }}"
                                href="{{ route('pig_entry_records.index', array_merge(request()->except('farm_id'), [])) }}">เลือกฟาร์มก่อน</a>
                        </li>
                        @foreach ($farms as $farm)
                            <li><a class="dropdown-item {{ request('farm_id') == $farm->id ? 'active' : '' }}"
                                    href="{{ route('pig_entry_records.index', array_merge(request()->all(), ['farm_id' => $farm->id])) }}">{{ $farm->farm_name }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Batch Filter (Dark Blue) -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="batchFilterBtn"
                        data-bs-toggle="dropdown">
                        <i class="bi bi-diagram-3"></i>
                        {{ request('batch_id') ? $batches->find(request('batch_id'))->batch_code ?? 'รุ่น' : 'รุ่นทั้งหมด' }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item {{ request('batch_id') == '' ? 'active' : '' }}"
                                href="{{ route('pig_entry_records.index', array_merge(request()->except('batch_id'), [])) }}">รุ่นทั้งหมด</a>
                        </li>
                        @if (request('farm_id'))
                            @foreach ($batches as $batch)
                                <li><a class="dropdown-item {{ request('batch_id') == $batch->id ? 'active' : '' }}"
                                        href="{{ route('pig_entry_records.index', array_merge(request()->all(), ['batch_id' => $batch->id])) }}">{{ $batch->batch_code }}</a>
                                </li>
                            @endforeach
                        @else
                            <li><a class="dropdown-item" href="#">(กรุณาเลือกฟาร์มก่อน)</a></li>
                        @endif
                    </ul>
                </div>

                <!-- Sort Dropdown (Orange) -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-sort-down"></i>
                        @if (request('sort') == 'name_asc')
                            ชื่อ (ก-ฮ)
                        @elseif(request('sort') == 'name_desc')
                            ชื่อ (ฮ-ก)
                        @elseif(request('sort') == 'quantity_asc')
                            จำนวนน้อย
                        @elseif(request('sort') == 'quantity_desc')
                            จำนวนมาก
                        @else
                            เรียงตาม
                        @endif
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item {{ request('sort') == 'name_asc' ? 'active' : '' }}"
                                href="{{ route('storehouse_records.index', array_merge(request()->all(), ['sort' => 'name_asc'])) }}">ชื่อ
                                (ก-ฮ)</a></li>
                        <li><a class="dropdown-item {{ request('sort') == 'name_desc' ? 'active' : '' }}"
                                href="{{ route('storehouse_records.index', array_merge(request()->all(), ['sort' => 'name_desc'])) }}">ชื่อ
                                (ฮ-ก)</a></li>
                        <li><a class="dropdown-item {{ request('sort') == 'quantity_asc' ? 'active' : '' }}"
                                href="{{ route('storehouse_records.index', array_merge(request()->all(), ['sort' => 'quantity_asc'])) }}">จำนวนน้อย
                                → มาก</a></li>
                        <li><a class="dropdown-item {{ request('sort') == 'quantity_desc' ? 'active' : '' }}"
                                href="{{ route('storehouse_records.index', array_merge(request()->all(), ['sort' => 'quantity_desc'])) }}">จำนวนมาก
                                → น้อย</a></li>
                    </ul>
                </div>

                <!-- Per Page -->
                @include('components.per-page-dropdown')

                <!-- Show Cancelled Pig Entry Checkbox -->
                <div class="form-check ms-2">
                    <input class="form-check-input" type="checkbox" id="showCancelledCheckboxEntry"
                        {{ request('show_cancelled') ? 'checked' : '' }}
                        onchange="toggleCancelledEntry()">
                    <label class="form-check-label" for="showCancelledCheckboxEntry">
                        <i class="bi bi-eye"></i> แสดงรุ่นที่ยกเลิก
                    </label>
                </div>

                <div class="ms-auto d-flex gap-2">
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                        data-bs-target="#createModal">
                        <i class="bi bi-plus-circle me-1"></i> เพิ่มหมูเข้า
                    </button>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-primary mb-0">
                <thead class="table-header-custom">
                    <tr>
                        <th class="text-center">
                            <a href="{{ route('pig_entry_records.index', array_merge(request()->all(), ['sort_by' => 'pig_entry_date', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}"
                                class="text-white text-decoration-none d-flex align-items-center justify-content-center gap-1">
                                วันที่
                                @if (request('sort_by') == 'pig_entry_date')
                                    <i class="bi bi-{{ request('sort_order') == 'asc' ? 'sort-up' : 'sort-down' }}"></i>
                                @else
                                    <i class="bi bi-arrow-down-up"></i>
                                @endif
                            </a>
                        </th>
                        <th class="text-center">ฟาร์ม</th>
                        <th class="text-center">รุ่น (Batch)</th>
                        <th class="text-center">
                            <a href="{{ route('pig_entry_records.index', array_merge(request()->all(), ['sort_by' => 'total_pig_amount', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}"
                                class="text-white text-decoration-none d-flex align-items-center justify-content-center gap-1">
                                จำนวนหมู
                                @if (request('sort_by') == 'total_pig_amount')
                                    <i class="bi bi-{{ request('sort_order') == 'asc' ? 'sort-up' : 'sort-down' }}"></i>
                                @else
                                    <i class="bi bi-arrow-down-up"></i>
                                @endif
                            </a>
                        </th>
                        <th class="text-center">
                            <a href="{{ route('pig_entry_records.index', array_merge(request()->all(), ['sort_by' => 'total_pig_weight', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}"
                                class="text-white text-decoration-none d-flex align-items-center justify-content-center gap-1">
                                น้ำหนักรวม
                                @if (request('sort_by') == 'total_pig_weight')
                                    <i class="bi bi-{{ request('sort_order') == 'asc' ? 'sort-up' : 'sort-down' }}"></i>
                                @else
                                    <i class="bi bi-arrow-down-up"></i>
                                @endif
                            </a>
                        </th>
                        <th class="text-center">
                            <a href="{{ route('pig_entry_records.index', array_merge(request()->all(), ['sort_by' => 'total_pig_price', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}"
                                class="text-white text-decoration-none d-flex align-items-center justify-content-center gap-1">
                                ราคาลูกหมู
                                @if (request('sort_by') == 'total_pig_price')
                                    <i class="bi bi-{{ request('sort_order') == 'asc' ? 'sort-up' : 'sort-down' }}"></i>
                                @else
                                    <i class="bi bi-arrow-down-up"></i>
                                @endif
                            </a>
                        </th>
                        <th class="text-center">ค่าน้ำหนักเกิน</th>
                        <th class="text-center">ค่าขนส่ง</th>
                        <th class="text-center">ราคารวม</th>
                        <th class="text-center">โน๊ต</th>
                        <th class="text-center">ใบเสร็จ</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pigEntryRecords as $record)
                        <tr data-row-click="#viewModal{{ $record->id }}" class="clickable-row">
                            <td class="text-center">{{ $record->pig_entry_date }}</td>
                            <td class="text-center">{{ $record->farm->farm_name ?? '-' }}</td>
                            <td class="text-center">{{ $record->batch->batch_code ?? '-' }}</td>
                            <td class="text-center"><strong>{{ $record->total_pig_amount }}</strong></td>
                            <td class="text-center">
                                <strong>{{ number_format($record->total_pig_weight, 2) }}</strong> กก.
                            </td>
                            <td class="text-center">{{ number_format($record->total_pig_price, 2) }} ฿</td>

                            {{-- คำนวณจาก batch->costs --}}
                            <td class="text-center">
                                {{ number_format($record->batch->costs->sum('excess_weight_cost') ?? 0, 2) }}
                                ฿
                            </td>
                            <td class="text-center">
                                {{ number_format($record->batch->costs->sum('transport_cost') ?? 0, 2) }}
                                ฿</td>
                            <td class="text-center">
                                <strong>{{ number_format(
                                    $record->total_pig_price +
                                        ($record->batch->costs->sum('excess_weight_cost') ?? 0) +
                                        ($record->batch->costs->sum('transport_cost') ?? 0),
                                    2,
                                ) }}
                                    ฿</strong>
                            </td>
                            <td class="text-center">{{ $record->note ?? '-' }}</td>
                            {{-- ดึงภาพจาก cloudinary --}}
                            <td class="text-center">
                                @if ($record->latestCost && !empty($record->latestCost->receipt_file))
                                    @php
                                        $file = (string) $record->latestCost->receipt_file;
                                    @endphp

                                    @if (is_string($file) && Str::endsWith($file, ['.jpg', '.jpeg', '.png']))
                                        <a href="{{ $file }}" target="_blank">
                                            <img src="{{ $file }}" alt="Receipt"
                                                style="max-width:100px; max-height:100px; cursor: pointer; border-radius: 4px; object-fit: cover; transition: transform 0.2s;"
                                                onmouseover="this.style.transform='scale(1.05)'"
                                                onmouseout="this.style.transform='scale(1)'"
                                                title="คลิกเพื่อดูภาพในแท็บใหม่">
                                        </a>
                                    @else
                                        <a href="{{ $file }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-download"></i> ดาวน์โหลด
                                        </a>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td class="text-center">
                                {{-- View Button --}}
                                <button type="button" class="btn btn-sm btn-info"
                                    onclick="event.stopPropagation(); new bootstrap.Modal(document.getElementById('viewModal{{ $record->id }}')).show();">
                                    <i class="bi bi-eye"></i>
                                </button>

                                {{-- Payment Button --}}
                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                    data-bs-target="#paymentModal{{ $record->id }}" onclick="event.stopPropagation();">
                                    <i class="bi bi-cash"></i>
                                </button>

                                {{-- Delete Button / Cancelled Badge --}}
                                @if ($record->batch && $record->batch->status === 'cancelled')
                                    <span class="badge bg-danger">
                                        <i class="bi bi-x-circle"></i> ยกเลิก
                                    </span>
                                @else
                                    <form action="{{ route('pig_entry_records.delete', $record->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-danger"
                                            onclick="event.stopPropagation(); if(confirm('คุณแน่ใจไหมว่าจะลบรายการนี้?')) { this.form.submit(); }">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="12" class="text-danger">❌ ไม่มีข้อมูล</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between mt-3">
            <div>
                แสดง {{ $pigEntryRecords->firstItem() ?? 0 }} ถึง {{ $pigEntryRecords->lastItem() ?? 0 }} จาก
                {{ $pigEntryRecords->total() ?? 0 }} แถว
            </div>
            <div>
                {{ $pigEntryRecords->withQueryString()->links() }}
            </div>
        </div>
    </div>

    {{-- Modal Create --}}
    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-dark text-light">
                <div class="modal-header">
                    <h5>เพิ่มหมูเข้า</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('pig_entry_records.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">

                        {{-- FARM DROPDOWN BUTTON --}}
                        <div class="mb-3">
                            <label>ฟาร์ม</label>
                            <div class="dropdown">
                                <button
                                    class="btn btn-primary dropdown-toggle w-100 d-flex justify-content-between align-items-center"
                                    type="button" id="createFarmDropdownBtn" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <span>เลือกฟาร์ม</span>
                                </button>
                                <ul class="dropdown-menu w-100" aria-labelledby="createFarmDropdownBtn"
                                    id="createFarmDropdownMenu">
                                    @foreach ($farms as $farm)
                                        <li>
                                            <a class="dropdown-item" href="#" data-farm-id="{{ $farm->id }}">
                                                {{ $farm->farm_name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                                <input type="hidden" name="farm_id" id="createFarmSelect" value="">
                            </div>
                        </div>

                        {{-- BATCH DROPDOWN BUTTON --}}
                        <div class="mb-3">
                            <label>รุ่น (Batch)</label>
                            <div class="dropdown">
                                <button
                                    class="btn btn-primary dropdown-toggle w-100 d-flex justify-content-between align-items-center"
                                    type="button" id="createBatchDropdownBtn" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <span>เลือกรุ่น</span>
                                </button>
                                <ul class="dropdown-menu w-100" aria-labelledby="createBatchDropdownBtn"
                                    id="createBatchDropdownMenu">
                                    <!-- จะ populate เมื่อเลือกฟาร์ม -->
                                </ul>
                                <input type="hidden" name="batch_id" id="createBatchSelect" value="">
                            </div>
                        </div>

                        {{-- BARN CHECKBOXES --}}
                        <div class="mb-3">
                            <label>เล้า (Barn) - สามารถเลือกได้หลายตัว</label>
                            <div class="border rounded p-3"
                                style="background-color: #495057; max-height: 150px; overflow-y: auto;">
                                <div id="createBarnCheckboxContainer">
                                    <!-- จะ populate เมื่อเลือกฟาร์ม -->
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>วันที่เข้า</label>
                            <input type="text" name="pig_entry_date" placeholer="ว/ด/ป ชม. นาที"
                                class="form-control dateWrapper" required>
                        </div>

                        <div class="mb-3">
                            <label>จำนวนหมู</label>
                            <input type="number" name="total_pig_amount" class="form-control" min="1" required>
                        </div>

                        <div class="mb-3">
                            <label>น้ำหนักรวม</label>
                            <input type="number" name="total_pig_weight" class="form-control" min="0"
                                step="0.01" required>
                        </div>

                        <div class="mb-3">
                            <label>ราคาลูกหมูรวม</label>
                            <input type="number" name="total_pig_price" class="form-control" min="0"
                                step="0.01" required>
                        </div>

                        <div class="mb-3">
                            <label>ค่าน้ำหนักส่วนเกิน</label>
                            <input type="number" name="excess_weight_cost" class="form-control" min="0"
                                step="0.01">
                        </div>

                        <div class="mb-3">
                            <label>ค่าขนส่ง</label>
                            <input type="number" name="transport_cost" class="form-control" min="0"
                                step="0.01">
                        </div>

                        <div class="mb-3">
                            <label>โน๊ต</label>
                            <textarea name="note" class="form-control"></textarea>
                        </div>

                        <div class="mb-3">
                            <label>ใบเสร็จ</label>
                            <input type="file" name="receipt_file" class="form-control"
                                accept=".jpg,.jpeg,.png,.pdf">
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
    {{-- End Create Modal --}}

    {{-- View Modals --}}
    @foreach ($pigEntryRecords as $record)
        <div class="modal fade" id="viewModal{{ $record->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-box-seam"></i> รายละเอียดการรับเข้าหมู
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="bi bi-info-circle"></i> ข้อมูลทั่วไป
                                </h6>
                                <table class="table table-secondary table-sm table-hover">
                                    <tr>
                                        <td width="35%"><strong>วันที่รับเข้า:</strong></td>
                                        <td>{{ $record->pig_entry_date }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>ฟาร์ม:</strong></td>
                                        <td>{{ $record->farm->farm_name ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>รุ่น:</strong></td>
                                        <td>{{ $record->batch->batch_code ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>จำนวนหมู:</strong></td>
                                        <td>
                                            <strong class="text-success">{{ $record->total_pig_amount }} ตัว</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>น้ำหนักรวม:</strong></td>
                                        <td>
                                            <strong>{{ number_format($record->total_pig_weight, 2) }} กก.</strong>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="bi bi-cash-coin"></i> ราคา
                                </h6>
                                <table class="table table-secondary table-sm table-hover">
                                    <tr>
                                        <td width="35%"><strong>ราคาหมู:</strong></td>
                                        <td>{{ number_format($record->total_pig_price, 2) }} ฿</td>
                                    </tr>
                                    <tr>
                                        <td><strong>น้ำหนักเกิน:</strong></td>
                                        <td>{{ number_format($record->batch->costs->sum('excess_weight_cost') ?? 0, 2) }} ฿
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>ค่าขนส่ง:</strong></td>
                                        <td>{{ number_format($record->batch->costs->sum('transport_cost') ?? 0, 2) }} ฿
                                        </td>
                                    </tr>
                                    <tr style="background-color: #e8f5e9;">
                                        <td><strong>รวมทั้งสิ้น:</strong></td>
                                        <td>
                                            <strong
                                                class="text-success">{{ number_format(
                                                    $record->total_pig_price +
                                                        ($record->batch->costs->sum('excess_weight_cost') ?? 0) +
                                                        ($record->batch->costs->sum('transport_cost') ?? 0),
                                                    2,
                                                ) }}
                                                ฿</strong>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        @if ($record->note)
                            <hr>
                            <h6 class="text-primary mb-2">
                                <i class="bi bi-chat-left-text"></i> หมายเหตุ
                            </h6>
                            <div class="bg-light p-3 rounded">
                                <p class="mb-0">{{ $record->note }}</p>
                            </div>
                        @endif
                        @if ($record->latestCost && !empty($record->latestCost->receipt_file))
                            <hr>
                            <h6 class="text-primary mb-2">
                                <i class="bi bi-receipt"></i> ใบเสร็จ
                            </h6>
                            @php
                                $file = (string) $record->latestCost->receipt_file;
                            @endphp
                            @if (is_string($file) && Str::endsWith($file, ['.jpg', '.jpeg', '.png']))
                                <img src="{{ $file }}" alt="Receipt" style="max-width:100%; height: auto;"
                                    class="rounded">
                            @else
                                <a href="{{ $file }}" target="_blank" class="btn btn-sm btn-primary"><i
                                        class="bi bi-download"></i> ดาวน์โหลดใบเสร็จ</a>
                            @endif
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> ปิด
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment Modal --}}
        <div class="modal fade" id="paymentModal{{ $record->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">บันทึกการชำระเงิน</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('pig_entry_records.update_payment', $record->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">ยอดที่ต้องชำระ</label>
                                <input type="text" class="form-control"
                                    value="{{ number_format($record->total_pig_price + ($record->batch->costs->sum('excess_weight_cost') ?? 0) + ($record->batch->costs->sum('transport_cost') ?? 0), 2) }} บาท"
                                    readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">จำนวนเงินที่ชำระ</label>
                                <input type="number" name="paid_amount" class="form-control" step="0.01"
                                    min="0"
                                    value="{{ $record->total_pig_price + ($record->batch->costs->sum('excess_weight_cost') ?? 0) + ($record->batch->costs->sum('transport_cost') ?? 0) }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">วิธีชำระเงิน <span class="text-danger">*</span></label>
                                <div class="dropdown">
                                    <button
                                        class="btn btn-primary dropdown-toggle w-100 d-flex justify-content-between align-items-center"
                                        type="button" id="paymentMethodDropdownBtn{{ $record->id }}"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <span>-- เลือกวิธีชำระเงิน --</span>

                                    </button>
                                    <ul class="dropdown-menu w-100" role="listbox">
                                        <li><a class="dropdown-item" href="#" data-payment-method="เงินสด"
                                                onclick="updatePaymentMethod(event, {{ $record->id }})">เงินสด</a></li>
                                        <li><a class="dropdown-item" href="#" data-payment-method="โอนเงิน"
                                                onclick="updatePaymentMethod(event, {{ $record->id }})">โอนเงิน</a>
                                        </li>
                                    </ul>
                                    <input type="hidden" name="payment_method" id="paymentMethod{{ $record->id }}"
                                        value="">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">อัปโหลดหลักฐานการชำระ <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" name="receipt_file"
                                    accept="image/*,application/pdf">
                                <small class="text-muted">รองรับไฟล์: JPG, PNG, PDF (สูงสุด 5MB)</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                            <button type="submit" class="btn btn-primary" id="paymentSubmitBtn{{ $record->id }}">บันทึก</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach


    @push('scripts')
        {{-- Toggle Show Cancelled Pig Entry --}}
        <script>
            function toggleCancelledEntry() {
                const checkbox = document.getElementById('showCancelledCheckboxEntry');
                const form = document.getElementById('filterForm');

                if (checkbox.checked) {
                    // Add show_cancelled parameter
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'show_cancelled';
                    input.value = '1';
                    form.appendChild(input);
                } else {
                    // Remove show_cancelled parameter
                    const input = form.querySelector('input[name="show_cancelled"]');
                    if (input) {
                        input.remove();
                    }
                }
                form.submit();
            }
        </script>

        {{-- Auto-submit filters --}}
        <script>
            // Snackbar notification function
            function showSnackbar(message, bgColor = "#dc3545") {
                // Create snackbar element if not exists
                let sb = document.getElementById("snackbar");
                if (!sb) {
                    sb = document.createElement('div');
                    sb.id = "snackbar";
                    sb.style.cssText = `
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        background-color: ${bgColor};
                        color: white;
                        padding: 15px 20px;
                        border-radius: 4px;
                        display: none;
                        z-index: 9999;
                        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                        font-size: 14px;
                        font-weight: 500;
                        white-space: pre-wrap;
                        word-wrap: break-word;
                    `;
                    document.body.appendChild(sb);
                }

                sb.textContent = message;
                sb.style.backgroundColor = bgColor;
                sb.style.display = "block";
                sb.classList.add("show");

                setTimeout(() => {
                    sb.classList.remove("show");
                    sb.style.display = "none";
                }, 5000);
            }

            // ใช้ class dateWrapper
            document.addEventListener('shown.bs.modal', function(event) {
                event.target.querySelectorAll('.dateWrapper').forEach(el => {
                    if (!el._flatpickr) {
                        flatpickr(el, {
                            enableTime: true,
                            dateFormat: "d/m/Y H:i",
                            maxDate: "today",
                        });
                    }
                });
            });

            // Update payment method dropdown
            function updatePaymentMethod(event, recordId) {
                event.preventDefault();
                event.stopPropagation();

                const paymentMethod = event.target.getAttribute('data-payment-method');
                const methodText = event.target.textContent.trim();

                const btnElement = document.getElementById('paymentMethodDropdownBtn' + recordId);
                const inputElement = document.getElementById('paymentMethod' + recordId);

                if (btnElement && inputElement) {
                    btnElement.querySelector('span').textContent = methodText;
                    inputElement.value = paymentMethod;

                    // ปิด dropdown โดยใช้ Bootstrap
                    const dropdown = bootstrap.Dropdown.getInstance(btnElement);
                    if (dropdown) {
                        dropdown.hide();
                    }
                }
            }

            // Add form submission validation for all payment forms
            document.addEventListener('DOMContentLoaded', function() {
                // Find all payment modals and add form validation
                document.querySelectorAll('[id^="paymentModal"]').forEach(modal => {
                    const form = modal.querySelector('form');
                    if (form) {
                        console.log('Found payment form in modal:', modal.id, 'Form action:', form.action);

                        // Add additional console logging on modal open
                        const modalBsModal = new bootstrap.Modal(modal);
                        modal.addEventListener('shown.bs.modal', function() {
                            const recordId = modal.id.replace('paymentModal', '');
                            const paymentBtn = document.getElementById('paymentSubmitBtn' + recordId);
                            console.log('Modal opened:', modal.id, 'Submit button:', paymentBtn?.id, 'Enabled:', !paymentBtn?.disabled);
                        });

                        form.addEventListener('submit', function(e) {
                            console.log('Payment form submit triggered for modal:', modal.id);
                            const recordId = modal.id.replace('paymentModal', '');
                            const paymentMethodInput = document.getElementById('paymentMethod' + recordId);
                            const receiptInput = form.querySelector('input[name="receipt_file"]');
                            const paidAmountInput = form.querySelector('input[name="paid_amount"]');

                            console.log('Validation check:', {
                                paid_amount: paidAmountInput?.value,
                                payment_method: paymentMethodInput?.value,
                                receipt_files: receiptInput?.files?.length,
                                form_action: form.action
                            });

                            let errors = [];

                            if (!paidAmountInput.value || parseFloat(paidAmountInput.value) <= 0) {
                                errors.push('❌ จำนวนเงินที่ชำระต้องมากกว่า 0');
                            }

                            if (!paymentMethodInput.value) {
                                errors.push('❌ กรุณาเลือกวิธีชำระเงิน');
                            }

                            if (!receiptInput.files.length) {
                                errors.push('❌ กรุณาอัปโหลดหลักฐานการชำระเงิน');
                            }

                            if (errors.length > 0) {
                                console.log('Validation errors found:', errors);
                                e.preventDefault();
                                showSnackbar(errors.join('\n'));
                                return false;
                            } else {
                                console.log('Validation passed, allowing form submission');
                            }
                        });
                    }
                });
            });
        </script>

        {{-- JS สำหรับ fetch barns + batches --}}        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const batches = @json($batches);
                const farms = @json($farms);

                // Elements
                const farmDropdownBtn = document.getElementById('createFarmDropdownBtn');
                const farmDropdownMenu = document.getElementById('createFarmDropdownMenu');
                const farmSelect = document.getElementById('createFarmSelect');

                const batchDropdownBtn = document.getElementById('createBatchDropdownBtn');
                const batchDropdownMenu = document.getElementById('createBatchDropdownMenu');
                const batchSelect = document.getElementById('createBatchSelect');

                const barnCheckboxContainer = document.getElementById('createBarnCheckboxContainer');

                // FARM DROPDOWN HANDLER
                farmDropdownMenu.addEventListener('click', function(e) {
                    if (e.target.classList.contains('dropdown-item')) {
                        e.preventDefault();
                        const farmId = e.target.getAttribute('data-farm-id');
                        const farmName = e.target.textContent.trim();

                        farmDropdownBtn.querySelector('span').textContent = farmName;
                        farmSelect.value = farmId;

                        // Reset batch
                        batchDropdownBtn.querySelector('span').textContent = 'เลือกรุ่น';
                        batchSelect.value = '';
                        batchDropdownMenu.innerHTML = '';

                        // Populate batches
                        const farmBatches = batches.filter(b => b.farm_id === parseInt(farmId));
                        farmBatches.forEach(batch => {
                            const li = document.createElement('li');
                            const a = document.createElement('a');
                            a.className = 'dropdown-item';
                            a.href = '#';
                            a.setAttribute('data-batch-id', batch.id);
                            a.textContent = batch.batch_code;
                            li.appendChild(a);
                            batchDropdownMenu.appendChild(li);
                        });

                        // Populate barns checkboxes
                        const farm = farms.find(f => f.id === parseInt(farmId));
                        if (farm && farm.barns) {
                            barnCheckboxContainer.innerHTML = '';

                            // Fetch barn capacity data
                            fetch('/get-barn-capacity/' + farmId)
                                .then(res => res.json())
                                .then(barnData => {
                                    farm.barns.forEach(barn => {
                                        const capacityInfo = barnData.find(b => b.id === barn.id);
                                        const available = capacityInfo ? capacityInfo
                                            .available_capacity : 0;
                                        const isFull = capacityInfo ? capacityInfo.is_full : false;

                                        const div = document.createElement('div');
                                        div.className = 'form-check';

                                        const isDisabled = isFull ? 'disabled' : '';
                                        const statusText = isFull ?
                                            `<span class="text-danger"> ❌ เต็มแล้ว</span>` :
                                            `<span class="text-success"> (เหลือ ${available} ตัว)</span>`;

                                        div.innerHTML = `
                                            <input type="checkbox" class="form-check-input barn-checkbox"
                                                name="barn_id[]" value="${barn.id}" id="createBarn_${barn.id}" ${isDisabled}>
                                            <label class="form-check-label" for="createBarn_${barn.id}">
                                                ${barn.barn_code} ${statusText}
                                            </label>
                                        `;
                                        barnCheckboxContainer.appendChild(div);
                                    });
                                })
                                .catch(err => console.error('Error fetching barn capacity:', err));
                        }
                    }
                });

                // BATCH DROPDOWN HANDLER
                batchDropdownMenu.addEventListener('click', function(e) {
                    if (e.target.classList.contains('dropdown-item')) {
                        e.preventDefault();
                        const batchId = e.target.getAttribute('data-batch-id');
                        const batchCode = e.target.textContent.trim();

                        batchDropdownBtn.querySelector('span').textContent = batchCode;
                        batchSelect.value = batchId;
                    }
                });

                // FORM VALIDATION
                const createForm = document.querySelector('#createModal form');
                if (createForm) {
                    createForm.addEventListener('submit', function(e) {
                        const farmId = farmSelect.value;
                        const batchId = batchSelect.value;
                        const barnCheckboxes = document.querySelectorAll(
                            '#createBarnCheckboxContainer .barn-checkbox:checked');

                        if (!farmId) {
                            e.preventDefault();
                            showSnackbar('กรุณาเลือกฟาร์ม');
                            return false;
                        }
                        if (!batchId) {
                            e.preventDefault();
                            showSnackbar('กรุณาเลือกรุ่น');
                            return false;
                        }
                        if (barnCheckboxes.length === 0) {
                            e.preventDefault();
                            showSnackbar('กรุณาเลือกเล้าอย่างน้อยหนึ่งตัว');
                            return false;
                        }
                    });
                }
            });
        </script>



        {{-- Auto-submit filters --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const filterForm = document.getElementById('filterForm');
                const farmFilter = document.getElementById('farmFilter');
                const batchFilter = document.getElementById('batchFilter');

                // Null check for elements - if form uses dropdown buttons, skip this script
                if (!filterForm || !farmFilter || !batchFilter) {
                    console.log('Filter form is using dropdown buttons, skipping select-based logic');
                    setupClickableRows(); // still call this
                    return;
                }

                const allFilters = filterForm.querySelectorAll('select');

                // เมื่อพยายามเลือก batch โดยที่ยังไม่เลือก farm
                batchFilter.addEventListener('mousedown', function(e) {
                    if (!farmFilter.value) {
                        e.preventDefault();
                        showSnackbar(' กรุณาเลือกฟาร์มก่อนเลือกรุ่น');
                        // Focus ไปที่ farm filter
                        farmFilter.focus();
                    }
                });

                batchFilter.addEventListener('focus', function(e) {
                    if (!farmFilter.value) {
                        showSnackbar(' กรุณาเลือกฟาร์มก่อนเลือกรุ่น');
                        // Focus ไปที่ farm filter
                        setTimeout(() => farmFilter.focus(), 100);
                    }
                });

                // เมื่อเลือกฟาร์ม
                farmFilter.addEventListener('change', function() {
                    const farmId = this.value;

                    if (farmId) {
                        // แสดง loading
                        batchFilter.innerHTML = '<option value="">กำลังโหลด...</option>';

                        // โหลด batches จาก API (ใช้ absolute url เพื่อให้ทำงานภายใต้ sub-folder ถ้ามี)
                        fetch('{{ url('get-batches') }}/' + farmId)
                            .then(response => response.json())
                            .then(data => {
                                batchFilter.innerHTML = '<option value="">รุ่นทั้งหมด</option>';
                                data.forEach(batch => {
                                    const option = document.createElement('option');
                                    option.value = batch.id;
                                    option.textContent = batch.batch_code;
                                    batchFilter.appendChild(option);
                                });
                                // Submit form หลังจากโหลด batches เสร็จ
                                filterForm.submit();
                            })
                            .catch(error => {
                                console.error('Error loading batches:', error);
                                batchFilter.innerHTML = '<option value="">เกิดข้อผิดพลาด</option>';
                                showSnackbar('เกิดข้อผิดพลาดในการโหลดรุ่น');
                            });
                    } else {
                        // ถ้าเลือก "เลือกฟาร์มก่อน" ให้รีเซ็ต batch filter
                        batchFilter.innerHTML = '<option value="">รุ่นทั้งหมด</option>';
                        // Submit form
                        filterForm.submit();
                    }
                });

                // Auto-submit สำหรับ filters อื่นๆ (ไม่รวม farm)
                allFilters.forEach(filter => {
                    if (filter.id !== 'farmFilter') {
                        filter.addEventListener('change', function() {
                            filterForm.submit();
                        });
                    }
                });

                // เรียกใช้ common table click handler
                setupClickableRows();
            });
        </script>
        <script src="{{ asset('admin/js/common-table-click.js') }}"></script>
    @endpush
@endsection
