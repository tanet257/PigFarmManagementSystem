@extends('layouts.admin')

@section('title', 'บันทึกการขายหมู')

@section('content')
    <div class="container my-5">
        <div class="card-header">
            <h1 class="text-center">บันทึกการขายหมู (Pig Sales)</h1>
        </div>
        <div class="py-2"></div>

        {{-- Toolbar --}}
        <div class="card-custom-secondary mb-3">
            <form method="GET" action="{{ route('pig_sales.index') }}" class="d-flex align-items-center gap-2 flex-wrap"
                id="filterForm">
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
                                href="{{ route('pig_sales.index', array_merge(request()->except('selected_date'), [])) }}">วันที่ทั้งหมด</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'today' ? 'active' : '' }}"
                                href="{{ route('pig_sales.index', array_merge(request()->all(), ['selected_date' => 'today'])) }}">วันนี้</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'this_week' ? 'active' : '' }}"
                                href="{{ route('pig_sales.index', array_merge(request()->all(), ['selected_date' => 'this_week'])) }}">สัปดาห์นี้</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'this_month' ? 'active' : '' }}"
                                href="{{ route('pig_sales.index', array_merge(request()->all(), ['selected_date' => 'this_month'])) }}">เดือนนี้</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'this_year' ? 'active' : '' }}"
                                href="{{ route('pig_sales.index', array_merge(request()->all(), ['selected_date' => 'this_year'])) }}">ปีนี้</a>
                        </li>
                    </ul>
                </div>

                <!-- Farm Filter -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="farmFilterBtn"
                        data-bs-toggle="dropdown">
                        <i class="bi bi-building"></i>
                        {{ request('farm_id') ? $farms->find(request('farm_id'))->farm_name ?? 'ฟาร์ม' : 'ฟาร์มทั้งหมด' }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item {{ request('farm_id') == '' ? 'active' : '' }}"
                                href="{{ route('pig_sales.index', array_merge(request()->except('farm_id'), [])) }}">ฟาร์มทั้งหมด</a>
                        </li>
                        @foreach ($farms as $farm)
                            <li><a class="dropdown-item {{ request('farm_id') == $farm->id ? 'active' : '' }}"
                                    href="{{ route('pig_sales.index', array_merge(request()->all(), ['farm_id' => $farm->id])) }}">{{ $farm->farm_name }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Batch Filter -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="batchFilterBtn"
                        data-bs-toggle="dropdown">
                        <i class="bi bi-diagram-3"></i>
                        {{ request('batch_id') ? $batches->find(request('batch_id'))->batch_code ?? 'รุ่น' : 'รุ่นทั้งหมด' }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item {{ request('batch_id') == '' ? 'active' : '' }}"
                                href="{{ route('pig_sales.index', array_merge(request()->except('batch_id'), [])) }}">รุ่นทั้งหมด</a>
                        </li>
                        @foreach ($batches as $batch)
                            <li><a class="dropdown-item {{ request('batch_id') == $batch->id ? 'active' : '' }}"
                                    href="{{ route('pig_sales.index', array_merge(request()->all(), ['batch_id' => $batch->id])) }}">{{ $batch->batch_code }}</a>
                            </li>
                        @endforeach
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
                                href="{{ route('pig_sales.index', array_merge(request()->all(), ['sort' => 'name_asc'])) }}">ชื่อ
                                (ก-ฮ)</a></li>
                        <li><a class="dropdown-item {{ request('sort') == 'name_desc' ? 'active' : '' }}"
                                href="{{ route('pig_sales.index', array_merge(request()->all(), ['sort' => 'name_desc'])) }}">ชื่อ
                                (ฮ-ก)</a></li>
                        <li><a class="dropdown-item {{ request('sort') == 'quantity_asc' ? 'active' : '' }}"
                                href="{{ route('pig_sales.index', array_merge(request()->all(), ['sort' => 'quantity_asc'])) }}">จำนวนน้อย
                                → มาก</a></li>
                        <li><a class="dropdown-item {{ request('sort') == 'quantity_desc' ? 'active' : '' }}"
                                href="{{ route('pig_sales.index', array_merge(request()->all(), ['sort' => 'quantity_desc'])) }}">จำนวนมาก
                                → น้อย</a></li>
                    </ul>
                </div>

                <!-- Per Page -->
                <select name="per_page" class="per-page form-select form-select-sm w-20 filter-select-orange">
                    @foreach ([10, 25, 50, 100] as $n)
                        <option value="{{ $n }}" {{ request('per_page', 10) == $n ? 'selected' : '' }}>
                            {{ $n }} แถว</option>
                    @endforeach
                </select>

                <!-- Show Cancelled Pig Sales Checkbox -->
                <div class="form-check ms-2">
                    <input class="form-check-input" type="checkbox" id="showCancelledCheckboxSale"
                        {{ request('show_cancelled') ? 'checked' : '' }}
                        onchange="toggleCancelledSale()">
                    <label class="form-check-label" for="showCancelledCheckboxSale">
                        <i class="bi bi-eye"></i> แสดงการขายที่ยกเลิก
                    </label>
                </div>

                <div class="ms-auto d-flex gap-2">
                    <a class="btn btn-success btn-sm" href="{{ route('pig_sales.export.csv') }}">
                        <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
                    </a>
                    <a class="btn btn-danger btn-sm" href="{{ route('pig_sales.export.pdf') }}">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
                    </a>
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                        data-bs-target="#createModal">
                        <i class="bi bi-plus-circle me-1"></i> เพิ่มการขาย
                    </button>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-primary mb-0">
                <thead class="table-header-custom">
                    <tr>
                        <th class="text-center">เลขที่</th>
                        <th class="text-center">
                            <a href="{{ route('pig_sales.index', array_merge(request()->all(), ['sort_by' => 'date', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}"
                                class="text-white text-decoration-none d-flex align-items-center justify-content-center gap-1">
                                วันที่ขาย
                                @if (request('sort_by') == 'date')
                                    <i class="bi bi-{{ request('sort_order') == 'asc' ? 'sort-up' : 'sort-down' }}"></i>
                                @else
                                    <i class="bi bi-arrow-down-up"></i>
                                @endif
                            </a>
                        </th>
                        <th class="text-center">ลูกค้า</th>
                        <th class="text-center">ฟาร์ม</th>
                        <th class="text-center">รุ่น</th>
                        <th class="text-center">
                            <a href="{{ route('pig_sales.index', array_merge(request()->all(), ['sort_by' => 'quantity', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}"
                                class="text-white text-decoration-none d-flex align-items-center justify-content-center gap-1">
                                จำนวน
                                @if (request('sort_by') == 'quantity')
                                    <i class="bi bi-{{ request('sort_order') == 'asc' ? 'sort-up' : 'sort-down' }}"></i>
                                @else
                                    <i class="bi bi-arrow-down-up"></i>
                                @endif
                            </a>
                        </th>
                        <th class="text-center">น้ำหนัก (kg)</th>
                        <th class="text-center">
                            <a href="{{ route('pig_sales.index', array_merge(request()->all(), ['sort_by' => 'net_total', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}"
                                class="text-white text-decoration-none d-flex align-items-center justify-content-center gap-1">
                                ราคาสุทธิ
                                @if (request('sort_by') == 'net_total')
                                    <i class="bi bi-{{ request('sort_order') == 'asc' ? 'sort-up' : 'sort-down' }}"></i>
                                @else
                                    <i class="bi bi-arrow-down-up"></i>
                                @endif
                            </a>
                        </th>
                        <th class="text-center">สถานะชำระ</th>
                        <th class="text-center">สถานะอนุมัติ</th>
                        <th class="text-center">สลิป</th>
                        <th class="text-center">บันทึกโดย</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pigSales as $sell)
                        <tr data-row-click="#viewModal{{ $sell->id }}" class="clickable-row">
                            <td class="text-center">
                                <strong>{{ $sell->sale_number ?? 'SELL-' . str_pad($sell->id, 3, '0', STR_PAD_LEFT) }}</strong>
                            </td>
                            <td class="text-center">
                                {{ $sell->date ? \Carbon\Carbon::parse($sell->date)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="text-center">
                                {{ $sell->customer->customer_name ?? ($sell->buyer_name ?? '-') }}
                            </td>
                            <td class="text-center">{{ $sell->farm->farm_name ?? '-' }}</td>
                            <td class="text-center">{{ $sell->batch->batch_code ?? '-' }}</td>
                            <td class="text-center">
                                <strong>{{ number_format($sell->quantity) }} ตัว</strong>
                            </td>
                            <td class="text-center">
                                @if ($sell->actual_weight)
                                    {{ number_format($sell->actual_weight, 2) }}
                                    <small class="text-muted d-block">(ชั่งจริง)</small>
                                @else
                                    {{ number_format($sell->total_weight, 2) }}
                                @endif
                            </td>
                            <td class="text-center">
                                <strong>{{ number_format($sell->net_total ?? $sell->total_price, 2) }}</strong>

                                @if ($sell->shipping_cost > 0)
                                    <small class="text-info d-block">ค่าขนส่ง:
                                        {{ number_format($sell->shipping_cost, 2) }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($sell->payment_status == 'ยกเลิกการขาย')
                                    <span class="badge bg-danger">ยกเลิก</span>
                                @elseif ($sell->payment_status == 'ชำระแล้ว')
                                    <span class="badge bg-success">ชำระแล้ว</span>
                                @elseif ($sell->payment_status == 'ชำระบางส่วน')
                                    <span class="badge bg-warning">ชำระบางส่วน</span>
                                    <small class="d-block mt-1">คงเหลือ:
                                        {{ number_format($sell->balance, 2) }}</small>
                                @elseif ($sell->payment_status == 'เกินกำหนด')
                                    <span class="badge bg-danger">เกินกำหนด</span>
                                @else
                                    <span class="badge bg-secondary">รอชำระ</span>
                                    @if ($sell->due_date)
                                        <small
                                            class="d-block mt-1">{{ \Carbon\Carbon::parse($sell->due_date)->format('d/m/Y') }}</small>
                                    @endif
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($sell->approved_at)
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> อนุมัติแล้ว
                                    </span>
                                    <small class="text-muted d-block mt-1">
                                        โดย: {{ $sell->approvedBy->name ?? '-' }}
                                    </small>
                                @else
                                    <span class="badge bg-warning">
                                        <i class="bi bi-clock"></i> รออนุมัติ
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($sell->receipt_file)
                                    @php
                                        $file = (string) $sell->receipt_file;
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
                                        <a href="{{ $file }}" target="_blank" class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-file-earmark-pdf"></i> ดูสลิป
                                        </a>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <small class="text-muted">
                                    <i class="bi bi-person-fill"></i>
                                    {{ $sell->createdBy->name ?? '-' }}
                                </small>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-info"
                                    onclick="event.stopPropagation(); new bootstrap.Modal(document.getElementById('viewModal{{ $sell->id }}')).show();">
                                    <i class="bi bi-eye"></i>
                                </button>

                                @if (!$sell->approved_at && auth()->user()->hasPermission('approve_sales'))
                                    <button type="button" class="btn btn-sm btn-primary"
                                        onclick="event.stopPropagation(); new bootstrap.Modal(document.getElementById('approveModal{{ $sell->id }}')).show();"
                                        title="อนุมัติการขาย">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                @endif

                                @if ($sell->payment_status != 'ชำระแล้ว' && $sell->payment_status != 'ยกเลิกการขาย')
                                    <button type="button" class="btn btn-sm btn-success"
                                        onclick="event.stopPropagation(); new bootstrap.Modal(document.getElementById('paymentModal{{ $sell->id }}')).show();">
                                        <i class="bi bi-cash"></i>
                                    </button>
                                @endif

                                @if ($sell->payment_status === 'ยกเลิกการขาย')
                                    <span class="badge bg-danger">
                                        <i class="bi bi-x-circle"></i> ยกเลิก
                                    </span>
                                @else
                                    <form action="{{ route('pig_sales.cancel', $sell->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-danger"
                                            onclick="event.stopPropagation(); if(confirm('ต้องการยกเลิกการขายนี้หรือไม่?')) { this.form.submit(); }">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center text-danger">❌ ไม่มีข้อมูลการขาย
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-between mt-3">
        <div>
            แสดง {{ $pigSales->firstItem() ?? 0 }} ถึง {{ $pigSales->lastItem() ?? 0 }} จาก
            {{ $pigSales->total() ?? 0 }} แถว
        </div>
        <div>
            {{ $pigSales->withQueryString()->links() }}
        </div>
    </div>
    </div>


    {{-- Modals (นอก loop เพื่อไม่ให้รบกวน layout) --}}
    @foreach ($pigSales as $sell)
        {{-- View Detail Modal --}}
        <div class="modal fade" id="viewModal{{ $sell->id }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">รายละเอียดการขาย - {{ $sell->sale_number }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">ข้อมูลการขาย</h6>
                                <table class="table table-secondary table-sm table-hover">
                                    <tr>
                                        <td><strong>เลขที่:</strong></td>
                                        <td>{{ $sell->sale_number ?? 'SELL-' . str_pad($sell->id, 3, '0', STR_PAD_LEFT) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>วันที่ขาย:</strong></td>
                                        <td>{{ \Carbon\Carbon::parse($sell->date)->format('d/m/Y') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>ลูกค้า:</strong></td>
                                        <td>{{ $sell->customer->customer_name ?? $sell->buyer_name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>ฟาร์ม:</strong></td>
                                        <td>{{ $sell->farm->farm_name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>รุ่น:</strong></td>
                                        <td>{{ $sell->batch->batch_code }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>เล้า-คอก:</strong></td>
                                        <td>
                                            @if ($sell->pen)
                                                {{ $sell->pen->barn->barn_code ?? '' }} -
                                                {{ $sell->pen->pen_code }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>บันทึกโดย:</strong></td>
                                        <td>
                                            <i class="bi bi-person-fill text-primary"></i>
                                            {{ $sell->createdBy->name ?? '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>สถานะอนุมัติ:</strong></td>
                                        <td>
                                            @if ($sell->approved_at)
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i> อนุมัติแล้ว
                                                </span>
                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        <i class="bi bi-person-check-fill"></i>
                                                        โดย: {{ $sell->approvedBy->name ?? '-' }}
                                                    </small>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="bi bi-calendar-check"></i>
                                                        {{ \Carbon\Carbon::parse($sell->approved_at)->format('d/m/Y H:i') }}
                                                    </small>
                                                </div>
                                            @else
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-clock"></i> รออนุมัติ
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">รายละเอียด</h6>
                                <table class="table table-secondary table-sm table-hover">
                                    <tr>
                                        <td><strong>จำนวน:</strong></td>
                                        <td>{{ number_format($sell->quantity) }} ตัว</td>
                                    </tr>
                                    <tr>
                                        <td><strong>น้ำหนัก:</strong></td>
                                        <td>{{ number_format($sell->actual_weight ?? $sell->total_weight, 2) }}
                                            kg</td>
                                    </tr>
                                    <tr>
                                        <td><strong>ราคา/kg:</strong></td>
                                        <td>{{ number_format($sell->price_per_kg, 2) }} บาท
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>ราคา/ตัว:</strong></td>
                                        <td>
                                            <strong class="text-primary">{{ number_format($sell->price_per_pig ?? 0, 2) }}
                                                บาท</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>ราคารวม:</strong></td>
                                        <td>{{ number_format($sell->total_price, 2) }} บาท</td>
                                    </tr>

                                    <tr>
                                        <td><strong>ค่าขนส่ง:</strong></td>
                                        <td>{{ number_format($sell->shipping_cost, 2) }} บาท
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>ราคาสุทธิ:</strong></td>
                                        <td><strong>{{ number_format($sell->net_total ?? $sell->total_price, 2) }}
                                                บาท</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <hr>
                        <h6 class="text-primary">การชำระเงิน</h6>
                        <table class="table table-secondary table-sm table-hover">
                            <tr>
                                <td><strong>วิธีชำระ:</strong></td>
                                <td>{{ $sell->payment_method ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>สถานะ:</strong></td>
                                <td>
                                    @if ($sell->payment_status === 'ยกเลิกการขาย')
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle"></i> ยกเลิก
                                        </span>
                                    @elseif ($sell->payment_status == 'ชำระแล้ว')
                                        <span class="badge bg-success">ชำระแล้ว</span>
                                    @elseif ($sell->payment_status == 'ชำระบางส่วน')
                                        <span class="badge bg-warning">ชำระบางส่วน</span>
                                    @elseif ($sell->payment_status == 'เกินกำหนด')
                                        <span class="badge bg-danger">เกินกำหนด</span>
                                    @else
                                        <span class="badge bg-secondary">รอชำระ</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>ยอดที่ชำระแล้ว:</strong></td>
                                <td>{{ number_format($sell->paid_amount, 2) }} บาท</td>
                            </tr>
                            <tr>
                                <td><strong>คงเหลือ:</strong></td>
                                <td>{{ number_format($sell->balance, 2) }} บาท</td>
                            </tr>
                            @if ($sell->receipt_file)
                                <tr>
                                    <td><strong>สลิป:</strong></td>
                                    <td>
                                        @php
                                            $file = (string) $sell->receipt_file;
                                        @endphp

                                        @if (is_string($file) && Str::endsWith($file, ['.jpg', '.jpeg', '.png']))
                                            <a href="{{ $file }}" target="_blank">
                                                <img src="{{ $file }}" alt="Receipt" style="max-width: 100%; height: auto; max-height: 200px; border-radius: 4px;">
                                            </a>
                                        @else
                                            <a href="{{ $file }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-file-earmark-pdf"></i> ดูสลิป
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        </table>
                        @if ($sell->note)
                            <hr>
                            <h6 class="text-primary">หมายเหตุ</h6>
                            <p>{{ $sell->note }}</p>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Approve Modal --}}
        <div class="modal fade" id="approveModal{{ $sell->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-check-circle"></i> ยืนยันการอนุมัติการขาย
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('pig_sales.approve', $sell->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                คุณกำลังจะอนุมัติการขายนี้ กรุณาตรวจสอบข้อมูลให้ถูกต้องก่อนอนุมัติ
                            </div>

                            <table class="table table-secondary table-sm table-hover table-bordered">
                                <tr>
                                    <td class="bg-light" width="40%"><strong>เลขที่การขาย:</strong></td>
                                    <td>{{ $sell->sale_number }}</td>
                                </tr>
                                <tr>
                                    <td class="bg-light"><strong>ลูกค้า:</strong></td>
                                    <td>{{ $sell->customer->customer_name ?? $sell->buyer_name }}</td>
                                </tr>
                                <tr>
                                    <td class="bg-light"><strong>จำนวน:</strong></td>
                                    <td>{{ number_format($sell->quantity) }} ตัว</td>
                                </tr>
                                <tr>
                                    <td class="bg-light"><strong>ราคาสุทธิ:</strong></td>
                                    <td><strong
                                            class="text-success">{{ number_format($sell->net_total ?? $sell->total_price, 2) }}
                                            บาท</strong></td>
                                </tr>
                                <tr>
                                    <td class="bg-light"><strong>บันทึกโดย:</strong></td>
                                    <td>
                                        <i class="bi bi-person-fill text-primary"></i>
                                        {{ $sell->created_by ?? '-' }}
                                    </td>
                                </tr>
                            </table>

                            @if ($sell->created_by === auth()->user()->name)
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <strong>คำเตือน:</strong> คุณกำลังอนุมัติการขายที่ตัวเองสร้าง
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle"></i> ยกเลิก
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> ยืนยันอนุมัติ
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Payment Modal --}}
        <div class="modal fade" id="paymentModal{{ $sell->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">บันทึกการชำระเงิน</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('payments.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="pig_sale_id" value="{{ $sell->id }}">

                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">ยอดที่ต้องชำระ</label>
                                <input type="text" class="form-control"
                                    value="{{ number_format($sell->balance ?? ($sell->net_total ?? $sell->total_price), 2) }} บาท"
                                    readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">จำนวนเงินที่ชำระ <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control" step="0.01"
                                    min="0.01"
                                    max="{{ $sell->balance ?? ($sell->net_total ?? $sell->total_price) }}"
                                    value="{{ $sell->balance ?? ($sell->net_total ?? $sell->total_price) }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">วันที่ชำระ <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" class="form-control"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">วิธีชำระเงิน <span class="text-danger">*</span></label>
                                <div class="dropdown">
                                    <button
                                        class="btn btn-primary dropdown-toggle w-100 d-flex justify-content-between align-items-center"
                                        type="button" id="paymentMethodDropdownBtn{{ $sell->id }}"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <span>-- เลือกวิธีชำระเงิน --</span>
                                    </button>
                                    <ul class="dropdown-menu w-100" role="listbox">
                                        <li><a class="dropdown-item" href="#" data-payment-method="เงินสด"
                                                onclick="updatePaymentMethod(event, {{ $sell->id }})">เงินสด</a></li>
                                        <li><a class="dropdown-item" href="#" data-payment-method="โอนเงิน"
                                                onclick="updatePaymentMethod(event, {{ $sell->id }})">โอนเงิน</a></li>
                                        <li><a class="dropdown-item" href="#" data-payment-method="เช็ค"
                                                onclick="updatePaymentMethod(event, {{ $sell->id }})">เช็ค</a></li>
                                    </ul>
                                    <input type="hidden" name="payment_method" id="paymentMethod{{ $sell->id }}"
                                        value="" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">เลขที่อ้างอิง (โอนเงิน/เช็ค)</label>
                                <input type="text" name="reference_number" class="form-control"
                                    placeholder="เช่น เลขเช็ค, เลขอ้างอิงการโอน">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">ชื่อธนาคาร</label>
                                <input type="text" name="bank_name" class="form-control"
                                    placeholder="เช่น ธนาคารกรุงไทย">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">อัปโหลดหลักฐานการชำระ <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" name="receipt_file"
                                    accept=".jpg,.jpeg,.png,.pdf" required>
                                <small class="text-muted">รองรับไฟล์: JPG, JPEG, PNG, PDF (สูงสุด 5MB)</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">หมายเหตุ</label>
                                <textarea name="note" class="form-control" rows="2" placeholder="เช่น หมายเหตุเพิ่มเติม"></textarea>
                            </div>
                            <hr>
                            <div class="alert alert-warning">
                                <i class="bi bi-info-circle"></i>
                                <strong>เหตุผลการยกเลิก:</strong>
                                <p class="mb-0 mt-2">หากต้องการยกเลิกการขายนี้ กรุณากรอกเหตุผลด้านล่าง แล้วกดปุ่ม "ยกเลิกการขาย" แทนการชำระเงิน</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">หากต้องการยกเลิกการขาย - กรุณากรอกเหตุผล</label>
                                <textarea name="cancellation_reason" class="form-control" rows="3" placeholder="เช่น ลูกค้ายกเลิกการสั่งซื้อ, ข้อผิดพลาดในการบันทึก เป็นต้น"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                            <button type="submit" class="btn btn-primary">บันทึกการชำระเงิน</button>
                            <button type="button" class="btn btn-danger" onclick="event.stopPropagation(); if(document.querySelector('[name=cancellation_reason]').value.trim() === '') { alert('กรุณากรอกเหตุผลการยกเลิก'); return; } if(confirm('ต้องการยกเลิกการขายนี้หรือไม่?')) { const form = this.closest('form'); form.action = '{{ route('pig_sales.cancel', $sell->id) }}'; form.method = 'POST'; form.innerHTML += '<input type=\"hidden\" name=\"_method\" value=\"DELETE\">'; form.submit(); }">
                                <i class="bi bi-x-circle"></i> ยกเลิกการขาย
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Create Modal --}}
    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-cart-plus me-2"></i>บันทึกการขายหมูใหม่
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('pig_sales.store') }}" method="POST" id="pigSaleForm">
                    @csrf
                    <div class="modal-body">
                        {{-- Step 1: เลือกฟาร์มและรุ่น --}}
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-1-circle me-2"></i>เลือกฟาร์มและรุ่น</h6>
                            </div>
                            <div class="card-custom-quaternary">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">ฟาร์ม <span class="text-danger">*</span></label>
                                        <div class="dropdown">
                                            <button
                                                class="btn btn-primary dropdown-toggle shadow-sm w-100 d-flex justify-content-between align-items-center"
                                                type="button" id="farmDropdownBtn" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                                <span>-- เลือกฟาร์ม --</span>
                                            </button>
                                            <ul class="dropdown-menu w-100" id="farmDropdownMenu">
                                                @foreach ($farms as $farm)
                                                    <li><a class="dropdown-item" href="#"
                                                            data-farm-id="{{ $farm->id }}">{{ $farm->farm_name }}</a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            <input type="hidden" name="farm_id" id="farm_select_create" value=""
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">รุ่น <span class="text-danger">*</span></label>
                                        <div class="dropdown">
                                            <button
                                                class="btn btn-primary dropdown-toggle shadow-sm w-100 d-flex justify-content-between align-items-center"
                                                type="button" id="batchDropdownBtn" data-bs-toggle="dropdown"
                                                aria-expanded="false" disabled>
                                                <span>-- เลือกฟาร์มก่อน --</span>
                                            </button>
                                            <ul class="dropdown-menu w-100" id="batchDropdownMenu">
                                            </ul>
                                            <input type="hidden" name="batch_id" id="batch_select_create"
                                                value="" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Step 2: เลือกหมูที่จะขาย (ตาราง) --}}
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-2-circle me-2"></i>เลือกหมูที่จะขาย</h6>
                            </div>
                            <div class="card-custom-quaternary">
                                <div id="pen_selection_container">
                                    <div class="alert alert-warning">
                                        <i class="bi bi-info-circle"></i> กรุณาเลือกฟาร์มและรุ่นก่อน
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Step 3: ข้อมูลการขาย --}}
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-3-circle me-2"></i>ข้อมูลการขาย</h6>
                            </div>
                            <div class="card-custom-quaternary">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">วันที่ขาย <span class="text-danger">*</span></label>
                                        <input type="date" name="date" class="form-control"
                                            value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">ประเภทการขาย <span class="text-danger">*</span></label>
                                        <div class="dropdown">
                                            <button
                                                class="btn btn-primary dropdown-toggle w-100 d-flex justify-content-between align-items-center"
                                                type="button" id="sellTypeDropdownBtn" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                                <span>-- เลือกประเภท --</span>
                                            </button>
                                            <ul class="dropdown-menu w-100" id="sellTypeDropdownMenu">
                                                <li>
                                                    <a class="dropdown-item" href="#" data-sell-type="หมูปกติ">
                                                        ขายหมูปกติ
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#" data-sell-type="หมูตาย">
                                                        ขายหมูตาย
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#" data-sell-type="หมูคัดทิ้ง">
                                                        ขายหมูคัดทิ้ง
                                                    </a>
                                                </li>
                                            </ul>
                                            <input type="hidden" name="sell_type" id="sell_type_select" value="หมูปกติ"
                                                required>
                                        </div>
                                    </div>
                                </div>

                                {{-- ราคาอ้างอิงจาก CPF --}}
                                @if ($latestPrice)
                                    <div class="alert alert-info mb-3">
                                        <i class="bi bi-info-circle"></i>
                                        <strong>ราคาอ้างอิง CPF:</strong>
                                        {{ number_format($latestPrice['price_per_kg'], 2) }} บาท/กก.
                                        <small class="text-muted">(ณ วันที่
                                            {{ \Carbon\Carbon::parse($latestPrice['date'])->format('d/m/Y') }})</small>
                                    </div>
                                @endif

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">น้ำหนักรวมทั้งหมด (kg) <span
                                                class="text-danger">*</span></label>
                                        <input type="number" name="total_weight" id="total_weight_input"
                                            class="form-control" step="0.01" min="0.01" required
                                            placeholder="กรอกน้ำหนักรวมที่ชั่งได้">
                                        <small class="text-secondary">
                                            <i class="bi bi-info-circle"></i> น้ำหนักรวมของหมูทั้งหมดที่ขาย
                                        </small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">ราคาต่อ kg <span class="text-danger">*</span></label>
                                        <input type="number" name="price_per_kg" id="price_per_kg_input"
                                            class="form-control" step="0.01" min="0"
                                            value="{{ $latestPrice['price_per_kg'] ?? '' }}" required>
                                        <small class="text-secondary">
                                            <i class="bi bi-lightbulb"></i> ราคาแนะนำจาก CPF
                                        </small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">ค่าขนส่ง (นายหน้าหัก)</label>
                                        <input type="number" name="shipping_cost" id="shipping_cost_input"
                                            class="form-control" step="0.01" min="0" value="0">
                                        <small class="text-secondary">
                                            <i class="bi bi-info-circle"></i> ค่าขนส่งที่นายหน้าหักจากรายได้
                                        </small>
                                    </div>
                                </div>

                                {{-- สรุปการขาย --}}
                                <div class="card bg-light">
                                    <div class="card-custom-quaternary">
                                        <h6 class="card-title text-primary mb-3">
                                            <i class="bi bi-calculator"></i> สรุปการขาย
                                        </h6>
                                        <table class="table table-secondary table-sm table-hover mb-0">
                                            <tr>
                                                <td class="text-end"><strong>จำนวนหมูรวม:</strong></td>
                                                <td class="text-end"><span id="summary_total_quantity">0</span> ตัว
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-end"><strong>น้ำหนักรวม:</strong></td>
                                                <td class="text-end"><span id="summary_total_weight">0.00</span> kg
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-end"><strong>ราคารวม (ก่อนหักค่าขนส่ง):</strong></td>
                                                <td class="text-end"><span id="summary_total_price">0.00</span> บาท
                                                </td>
                                            </tr>
                                            <tr class="text-danger">
                                                <td class="text-end"><strong>หัก: ค่าขนส่ง</strong></td>
                                                <td class="text-end">-<span id="summary_shipping_cost">0.00</span> บาท
                                                </td>
                                            </tr>
                                            <tr class="table-success">
                                                <td class="text-end"><strong>รายได้สุทธิ:</strong></td>
                                                <td class="text-end fs-5 fw-bold"><span id="summary_net_total">0.00</span>
                                                    บาท</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                {{-- Hidden inputs สำหรับส่งข้อมูล --}}
                                <input type="hidden" name="total_quantity" id="hidden_total_quantity" value="0">
                                <input type="hidden" name="total_weight" id="hidden_total_weight" value="0">
                                <input type="hidden" name="total_price" id="hidden_total_price" value="0">
                                <input type="hidden" name="net_total" id="hidden_net_total" value="0">

                                @if ($latestPrice)
                                    <input type="hidden" name="cpf_reference_price"
                                        value="{{ $latestPrice['price_per_kg'] }}">
                                    <input type="hidden" name="cpf_reference_date" value="{{ $latestPrice['date'] }}">
                                @endif
                            </div>
                        </div>

                        {{-- ข้อมูลเพิ่มเติม --}}
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-4-circle me-2"></i>ข้อมูลเพิ่มเติม</h6>
                            </div>
                            <div class="card-custom-quaternary">
                                <div class="mb-3">
                                    <label class="form-label">ชื่อผู้ซื้อ <span class="text-danger">*</span></label>
                                    <input type="text" name="buyer_name" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">หมายเหตุ</label>
                                    <textarea name="note" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                <button type="submit" class="btn btn-success">บันทึก</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


    @push('scripts')
        {{-- Toggle Show Cancelled Pig Sales --}}
        <script>
            function toggleCancelledSale() {
                const checkbox = document.getElementById('showCancelledCheckboxSale');
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

        <style>
            .form-check-input-sm {
                width: 1rem;
                height: 1rem;
                margin-top: 0.15rem;
            }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Auto-submit form on filter change (for index page filters)
                const filterForm = document.getElementById('filterForm');
                if (filterForm) {
                    const filterSelects = filterForm.querySelectorAll('select');
                    filterSelects.forEach(function(select) {
                        select.addEventListener('change', function() {
                            filterForm.submit();
                        });
                    });
                }
            });

            // Get DOM elements
            const farmSelectInput = document.getElementById('farm_select_create');
            const batchSelectInput = document.getElementById('batch_select_create');
            const farmDropdownBtn = document.getElementById('farmDropdownBtn');
            const batchDropdownBtn = document.getElementById('batchDropdownBtn');
            const farmDropdownMenu = document.getElementById('farmDropdownMenu');
            const batchDropdownMenu = document.getElementById('batchDropdownMenu');
            const penSelectionContainer = document.getElementById('pen_selection_container');
            const farmSelect = farmSelectInput;
            const batchSelect = batchSelectInput;

            // ========== SELL TYPE DROPDOWN ==========
            const sellTypeDropdownMenu = document.getElementById('sellTypeDropdownMenu');
            const sellTypeDropdownBtn = document.getElementById('sellTypeDropdownBtn');
            const sellTypeSelect = document.getElementById('sell_type_select');

            if (sellTypeDropdownMenu) {
                sellTypeDropdownMenu.querySelectorAll('.dropdown-item').forEach(item => {
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        const sellType = this.getAttribute('data-sell-type');
                        const sellTypeText = this.textContent.trim();

                        // Update button text and hidden input
                        sellTypeDropdownBtn.querySelector('span').textContent = sellTypeText;
                        sellTypeSelect.value = sellType;
                    });
                });
            }

            // ========== FARM DROPDOWN ==========
            farmDropdownMenu.querySelectorAll('.dropdown-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const farmId = this.getAttribute('data-farm-id');
                    const farmName = this.textContent;

                    // Update farm select value and button
                    farmSelectInput.value = farmId;
                    farmDropdownBtn.querySelector('span').textContent = farmName;

                    // Reset batch
                    batchSelectInput.value = '';
                    batchDropdownBtn.querySelector('span').textContent = '-- เลือกฟาร์มก่อน --';
                    batchDropdownBtn.disabled = true;
                    batchDropdownMenu.innerHTML = '';

                    // Fetch batches for selected farm
                    if (farmId) {
                        batchDropdownBtn.disabled = false;
                        loadBatchesForFarm(farmId);
                    }

                    // Reset pen selection
                    penSelectionContainer.innerHTML =
                        '<div class="alert alert-info">กรุณาเลือกฟาร์มและรุ่นก่อน</div>';
                });
            });

            // ========== BATCH DROPDOWN ==========
            function loadBatchesForFarm(farmId) {
                fetch(`/pig_sales/batches-by-farm/${farmId}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Batch response:', data);
                        batchDropdownMenu.innerHTML = '';

                        if (data.success && data.batches && data.batches.length > 0) {
                            data.batches.forEach(batch => {
                                const li = document.createElement('li');
                                const a = document.createElement('a');
                                a.href = '#';
                                a.className = 'dropdown-item';
                                a.setAttribute('data-batch-id', batch.id);
                                a.textContent = `${batch.batch_code} (${batch.total_pigs} ตัว)`;
                                li.appendChild(a);
                                batchDropdownMenu.appendChild(li);
                            });

                            // Add event listeners to batch items
                            batchDropdownMenu.querySelectorAll('.dropdown-item').forEach(item => {
                                item.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    const batchId = this.getAttribute('data-batch-id');
                                    const batchName = this.textContent;

                                    batchSelectInput.value = batchId;
                                    batchDropdownBtn.querySelector('span').textContent = batchName;

                                    // Load pen selection table
                                    loadPenSelectionTable();
                                });
                            });
                        } else {
                            const li = document.createElement('li');
                            const span = document.createElement('span');
                            span.className = 'dropdown-item disabled';
                            span.textContent = '❌ ไม่พบรุ่นในฟาร์มนี้';
                            li.appendChild(span);
                            batchDropdownMenu.appendChild(li);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching batches:', error);
                        batchDropdownMenu.innerHTML =
                            '<li><span class="dropdown-item disabled">❌ เกิดข้อผิดพลาด</span></li>';
                    });
            }

            // ========= PEN SELECTION TABLE ==========
            function loadPenSelectionTable() {
                const farmId = farmSelectInput.value;
                const batchId = batchSelectInput.value;
                const container = document.getElementById('pen_selection_container');

                // Reset if no farm or batch selected
                if (!farmId || !batchId) {
                    container.innerHTML = '<div class="alert alert-info">กรุณาเลือกฟาร์มและรุ่นก่อน</div>';
                    return;
                }

                // Show loading
                container.innerHTML =
                    '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">กำลังโหลด...</p></div>';

                // Fetch pen allocation data using batch ID
                fetch(`/pig_sales/pens-by-batch/${batchId}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Pens response:', data); // DEBUG
                        if (data.success && data.data && data.data.length > 0) {
                            // Create table
                            let html = `
                                <div class="table-responsive mt-3">
                                    <table class="table table-primary mb-0">
                                        <thead class="table-header-custom">
                                            <tr>
                                                <th class="text-center" style="width: 45px;">
                                                    <input type="checkbox" id="select_all_pens" class="form-check-input form-check-input-sm">
                                                </th>
                                                <th>เล้า</th>
                                                <th>คอก</th>
                                                <th class="text-center">จำนวนหมูที่เหลือ</th>
                                                <th class="text-center">จำนวนหมูที่ขาย</th>
                                            </tr>
                                        </thead>
                                        <tbody>`;

                            data.data.forEach((pen, index) => {
                                const penId = pen.pen_id;
                                const maxQty = pen.current_quantity || 0;
                                html += `
                                    <tr class="pen-row" data-pen-id="${penId}">
                                        <td class="text-center">
                                            <input type="checkbox" class="form-check-input form-check-input-sm pen-checkbox" name="selected_pens[]" value="${penId}" data-pen-id="${penId}" data-max-qty="${maxQty}">
                                        </td>
                                        <td>${pen.barn_name || 'ไม่ระบุ'}</td>
                                        <td>${pen.pen_name || 'ไม่ระบุ'}</td>
                                        <td class="text-center"><strong>${maxQty}</strong></td>
                                        <td class="text-center">
                                            <input type="number" name="quantities[${penId}]"
                                                class="form-control form-control-sm quantity-input text-center"
                                                data-pen-id="${penId}"
                                                min="0" max="${maxQty}" value=""
                                                style="width: 100%;" disabled>
                                        </td>
                                    </tr>`;
                            });

                            html += `
                                        </tbody>
                                    </table>
                                </div>`;
                            container.innerHTML = html;

                            // Add event listeners for checkboxes
                            const selectAllCheckbox = document.getElementById('select_all_pens');
                            const penCheckboxes = document.querySelectorAll('.pen-checkbox');

                            selectAllCheckbox?.addEventListener('change', function() {
                                penCheckboxes.forEach(checkbox => {
                                    checkbox.checked = this.checked;
                                    toggleQuantityInput(checkbox);
                                });
                            });

                            penCheckboxes.forEach(checkbox => {
                                checkbox.addEventListener('change', function() {
                                    toggleQuantityInput(this);
                                    updateSelectAll();
                                });
                            });

                            function toggleQuantityInput(checkbox) {
                                const row = checkbox.closest('tr');
                                const quantityInput = row?.querySelector('.quantity-input');
                                if (quantityInput) {
                                    quantityInput.disabled = !checkbox.checked;
                                    if (checkbox.checked) {
                                        quantityInput.focus();
                                    } else {
                                        quantityInput.value = '0';
                                    }
                                }
                            }

                            function updateSelectAll() {
                                const allChecked = Array.from(penCheckboxes).every(cb => cb.checked);
                                const someChecked = Array.from(penCheckboxes).some(cb => cb.checked);
                                if (selectAllCheckbox) {
                                    selectAllCheckbox.checked = allChecked;
                                    selectAllCheckbox.indeterminate = someChecked && !allChecked;
                                }
                            }

                            // Add event listeners for quantity inputs
                            const quantityInputs = document.querySelectorAll('.quantity-input');
                            quantityInputs.forEach(input => {
                                input.addEventListener('input', function() {
                                    const maxQty = parseInt(this.getAttribute('max'));
                                    const currentValue = parseInt(this.value) || 0;

                                    // ตรวจสอบว่าเกินจำนวนที่มี
                                    if (currentValue > maxQty) {
                                        this.value = maxQty;
                                        showSnackbar(`จำนวนต้องไม่เกิน ${maxQty} ตัว`);
                                    }

                                    // อัปเดทจำนวนรวม
                                    calculateTotalQuantity();
                                });
                            });

                            // Calculate total quantity from all checked pens
                            function calculateTotalQuantity() {
                                let totalQty = 0;
                                const checkedPens = document.querySelectorAll('.pen-checkbox:checked');

                                checkedPens.forEach(checkbox => {
                                    const penId = checkbox.getAttribute('data-pen-id');
                                    const quantityInput = document.querySelector(
                                        `.quantity-input[data-pen-id="${penId}"]`);
                                    if (quantityInput) {
                                        totalQty += parseInt(quantityInput.value) || 0;
                                    }
                                });

                                // Update summary
                                const summaryQty = document.getElementById('summary_total_quantity');
                                const hiddenQty = document.getElementById('hidden_total_quantity');
                                if (summaryQty) summaryQty.textContent = totalQty;
                                if (hiddenQty) hiddenQty.value = totalQty;

                                // Update prices
                                calculatePrices();
                            }
                        } else {
                            container.innerHTML = '<div class="alert alert-warning">ไม่พบคอกที่มีหมูในรุ่นนี้</div>';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading pens:', error);
                        container.innerHTML = '<div class="alert alert-danger">เกิดข้อผิดพลาดในการโหลดคอก</div>';
                    });
            }

            // Calculate total price and net total from weight and price inputs
            function calculatePrices() {
                const totalWeightInput = document.getElementById('total_weight_input');
                const pricePerKgInput = document.getElementById('price_per_kg_input');
                const shippingCostInput = document.getElementById('shipping_cost_input');

                const summaryWeight = document.getElementById('summary_total_weight');
                const summaryPrice = document.getElementById('summary_total_price');
                const summaryShipping = document.getElementById('summary_shipping_cost');
                const summaryNetTotal = document.getElementById('summary_net_total');

                const hiddenWeight = document.getElementById('hidden_total_weight');
                const hiddenPrice = document.getElementById('hidden_total_price');
                const hiddenNetTotal = document.getElementById('hidden_net_total');

                // Get values
                const totalWeight = parseFloat(totalWeightInput?.value) || 0;
                const pricePerKg = parseFloat(pricePerKgInput?.value) || 0;
                const shippingCost = parseFloat(shippingCostInput?.value) || 0;

                // Calculate
                const totalPrice = totalWeight * pricePerKg;
                const netTotal = totalPrice - shippingCost;

                // Update summary display
                if (summaryWeight) summaryWeight.textContent = totalWeight.toLocaleString('th-TH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                if (summaryPrice) summaryPrice.textContent = totalPrice.toLocaleString('th-TH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                if (summaryShipping) summaryShipping.textContent = shippingCost.toLocaleString('th-TH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                if (summaryNetTotal) summaryNetTotal.textContent = netTotal.toLocaleString('th-TH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                // Update hidden inputs
                if (hiddenWeight) hiddenWeight.value = totalWeight.toFixed(2);
                if (hiddenPrice) hiddenPrice.value = totalPrice.toFixed(2);
                if (hiddenNetTotal) hiddenNetTotal.value = netTotal.toFixed(2);
            }

            // Add event listeners for weight, price, and shipping inputs
            const totalWeightInput = document.getElementById('total_weight_input');
            const pricePerKgInput = document.getElementById('price_per_kg_input');
            const shippingCostInput = document.getElementById('shipping_cost_input');

            if (totalWeightInput) {
                totalWeightInput.addEventListener('input', calculatePrices);
            }
            if (pricePerKgInput) {
                pricePerKgInput.addEventListener('input', calculatePrices);
            }
            if (shippingCostInput) {
                shippingCostInput.addEventListener('input', calculatePrices);
            }

            // Snackbar helper
            function showSnackbar(message) {
                // Simple alert or toast notification
                console.log(message);
            }

            // Update payment method dropdown
            function updatePaymentMethod(event, sellId) {
                event.preventDefault();
                const paymentMethod = event.target.getAttribute('data-payment-method');
                const methodText = event.target.textContent.trim();

                document.getElementById('paymentMethodDropdownBtn' + sellId)
                    .querySelector('span').textContent = methodText;
                document.getElementById('paymentMethod' + sellId).value = paymentMethod;
            }

            // ✅ Auto-refresh status columns every 5 seconds
            function autoRefreshPigSaleStatus() {
                const pigSaleRows = document.querySelectorAll('tbody tr[data-row-id]');
                if (pigSaleRows.length === 0) return;

                // Get all pig sale IDs
                const pigSaleIds = Array.from(pigSaleRows).map(row => row.getAttribute('data-row-id')).filter(Boolean);

                if (pigSaleIds.length === 0) return;

                // Fetch status for all pig sales
                fetch('{{ route("pig_sales.get_status_batch") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        pig_sale_ids: pigSaleIds
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.statuses) {
                        // Update each row with new status
                        Object.entries(data.statuses).forEach(([pigSaleId, status]) => {
                            updateRowStatus(pigSaleId, status);
                        });
                    }
                })
                .catch(error => console.error('Auto-refresh error:', error));
            }

            // Update individual row status
            function updateRowStatus(pigSaleId, status) {
                const row = document.querySelector(`tr[data-row-id="${pigSaleId}"]`);
                if (!row) return;

                const paymentStatusCell = row.querySelector('td:nth-child(9)'); // สถานะชำระ
                const approvalStatusCell = row.querySelector('td:nth-child(10)'); // สถานะอนุมัติ

                if (paymentStatusCell && status.payment_status) {
                    const paymentBadgeHTML = getPaymentStatusBadge(status);
                    paymentStatusCell.innerHTML = paymentBadgeHTML;
                }

                if (approvalStatusCell && status.approved_at !== null) {
                    const approvalBadgeHTML = `
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle"></i> อนุมัติแล้ว
                        </span>
                        <small class="text-muted d-block mt-1">
                            โดย: ${status.approved_by || '-'}
                        </small>
                    `;
                    approvalStatusCell.innerHTML = approvalBadgeHTML;
                }
            }

            // Get payment status badge HTML
            function getPaymentStatusBadge(status) {
                const paymentStatus = status.payment_status;
                let badgeHTML = '';

                if (paymentStatus === 'ชำระแล้ว') {
                    badgeHTML = '<span class="badge bg-success">ชำระแล้ว</span>';
                } else if (paymentStatus === 'ชำระบางส่วน') {
                    const balance = parseFloat(status.balance || 0).toFixed(2);
                    badgeHTML = `
                        <span class="badge bg-warning">ชำระบางส่วน</span>
                        <small class="d-block mt-1">คงเหลือ: ${balance}</small>
                    `;
                } else if (paymentStatus === 'เกินกำหนด') {
                    badgeHTML = '<span class="badge bg-danger">เกินกำหนด</span>';
                } else if (paymentStatus === 'ยกเลิกการขาย') {
                    badgeHTML = '<span class="badge bg-danger">ยกเลิก</span>';
                } else {
                    badgeHTML = '<span class="badge bg-secondary">รอชำระ</span>';
                }

                return badgeHTML;
            }

            // Start auto-refresh on page load
            document.addEventListener('DOMContentLoaded', function() {
                // Add data-row-id to all rows for tracking
                document.querySelectorAll('tbody tr').forEach((row, index) => {
                    const viewButton = row.querySelector('button[onclick*="viewModal"]');
                    if (viewButton) {
                        const match = viewButton.getAttribute('onclick').match(/viewModal(\d+)/);
                        if (match) {
                            row.setAttribute('data-row-id', match[1]);
                        }
                    }
                });

                // Refresh status every 5 seconds
                setInterval(autoRefreshPigSaleStatus, 5000);

                // Initial refresh after 2 seconds
                setTimeout(autoRefreshPigSaleStatus, 2000);
            });

            // เรียกใช้ common table click handler
            setupClickableRows();
        </script>
        <script src="{{ asset('admin/js/common-table-click.js') }}"></script>
    @endpush
@endsection
