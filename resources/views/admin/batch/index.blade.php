@extends('layouts.admin')

@section('title', 'จัดการรุ่นหมู')

@section('content')
    <style>
        /* Enhanced Error Styling for Form Fields */
        .form-control.is-invalid,
        .form-check-input.is-invalid {
            border-color: #dc3545 !important;
            border-width: 2px;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
            background-color: #fff5f5;
        }

        .form-control.is-invalid:focus {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.5);
        }

        /* Error message styling */
        .is-invalid~small.text-danger {
            display: block;
            margin-top: 0.25rem;
            font-weight: 500;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Highlight the barn selection error */
        #barn_selection_container .alert {
            animation: pulse 0.5s;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }
    </style>
    <div class="container my-5">
        <!-- Header -->
        <div class="card-header">
            <h1 class="text-center mb-2">จัดการรุ่นหมู</h1>
        </div>
        <div class="py-2"></div>

        {{-- Toolbar --}}
        <div class="card-custom-secondary mb-3">
            <form method="GET" action="{{ route('batch.index') }}" class="d-flex align-items-center gap-2 flex-wrap"
                id="filterForm">

                <!-- Date Filter -->
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
                    <ul class="dropdown-menu" style="min-width: 200px;">
                        <li><a class="dropdown-item {{ request('selected_date') == '' ? 'active' : '' }}"
                                href="{{ route('batch.index', array_merge(request()->except('selected_date'), [])) }}">วันที่ทั้งหมด</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'today' ? 'active' : '' }}"
                                href="{{ route('batch.index', array_merge(request()->all(), ['selected_date' => 'today'])) }}">วันนี้</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'this_week' ? 'active' : '' }}"
                                href="{{ route('batch.index', array_merge(request()->all(), ['selected_date' => 'this_week'])) }}">สัปดาห์นี้</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'this_month' ? 'active' : '' }}"
                                href="{{ route('batch.index', array_merge(request()->all(), ['selected_date' => 'this_month'])) }}">เดือนนี้</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'this_year' ? 'active' : '' }}"
                                href="{{ route('batch.index', array_merge(request()->all(), ['selected_date' => 'this_year'])) }}">ปีนี้</a>
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
                                href="{{ route('batch.index', array_merge(request()->except('farm_id'), [])) }}">ฟาร์มทั้งหมด</a>
                        </li>
                        @foreach ($farms as $farm)
                            <li><a class="dropdown-item {{ request('farm_id') == $farm->id ? 'active' : '' }}"
                                    href="{{ route('batch.index', array_merge(request()->all(), ['farm_id' => $farm->id])) }}">{{ $farm->farm_name }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Batch Filter -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="batchFilterBtn"
                        data-bs-toggle="dropdown">
                        <i class="bi bi-diagram-3"></i>
                        {{ request('batch_id') ? $allBatches->find(request('batch_id'))->batch_code ?? 'รุ่น' : 'รุ่นทั้งหมด' }}
                    </button>
                    <ul class="dropdown-menu" style="min-width: 250px !important;">
                        <li><a class="dropdown-item {{ request('batch_id') == '' ? 'active' : '' }}"
                                href="{{ route('batch.index', array_merge(request()->except('batch_id'), [])) }}">รุ่นทั้งหมด</a>
                        </li>
                        @foreach ($allBatches as $batch)
                            <li><a class="dropdown-item {{ request('batch_id') == $batch->id ? 'active' : '' }}"
                                    href="{{ route('batch.index', array_merge(request()->all(), ['batch_id' => $batch->id])) }}">{{ $batch->batch_code }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Sort Dropdown -->
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
                    <ul class="dropdown-menu " style="min-width: 150px !important;">
                        <li><a class="dropdown-item {{ request('sort') == 'name_asc' ? 'active' : '' }}"
                                href="{{ route('batch.index', array_merge(request()->all(), ['sort' => 'name_asc'])) }}">ชื่อ
                                (ก-ฮ)</a></li>
                        <li><a class="dropdown-item {{ request('sort') == 'name_desc' ? 'active' : '' }}"
                                href="{{ route('batch.index', array_merge(request()->all(), ['sort' => 'name_desc'])) }}">ชื่อ
                                (ฮ-ก)</a></li>
                        <li><a class="dropdown-item {{ request('sort') == 'quantity_asc' ? 'active' : '' }}"
                                href="{{ route('batch.index', array_merge(request()->all(), ['sort' => 'quantity_asc'])) }}">จำนวนน้อย
                                → มาก</a></li>
                        <li><a class="dropdown-item {{ request('sort') == 'quantity_desc' ? 'active' : '' }}"
                                href="{{ route('batch.index', array_merge(request()->all(), ['sort' => 'quantity_desc'])) }}">จำนวนมาก
                                → น้อย</a></li>
                    </ul>
                </div>

                <!-- Per Page Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-list"></i>
                        แสดง: {{ request('per_page', 10) }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item {{ request('per_page', 10) == 10 ? 'active' : '' }}"
                                href="{{ route('batch.index', array_merge(request()->all(), ['per_page' => 10])) }}">10
                                รายการ</a></li>
                        <li><a class="dropdown-item {{ request('per_page', 10) == 25 ? 'active' : '' }}"
                                href="{{ route('batch.index', array_merge(request()->all(), ['per_page' => 25])) }}">25
                                รายการ</a></li>
                        <li><a class="dropdown-item {{ request('per_page', 10) == 50 ? 'active' : '' }}"
                                href="{{ route('batch.index', array_merge(request()->all(), ['per_page' => 50])) }}">50
                                รายการ</a></li>
                        <li><a class="dropdown-item {{ request('per_page', 10) == 100 ? 'active' : '' }}"
                                href="{{ route('batch.index', array_merge(request()->all(), ['per_page' => 100])) }}">100
                                รายการ</a></li>
                    </ul>
                </div>

                <div class="ms-auto d-flex gap-2">
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                        data-bs-target="#createBatchModal">
                        <i class="bi bi-plus-circle me-2"></i>
                        สร้างรุ่นใหม่
                    </button>
                </div>
            </form>
        </div>

        {{-- Export Section --}}
        <div class="card-custom-secondary mb-3">
            <div class="d-flex justify-content-between align-items-center flex-nowrap w-100 gap-2">

                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-download me-2 text-primary"></i>
                    <strong>ส่วนการส่งออก</strong>
                </div>

                <div class="ms-auto d-flex gap-2 align-items-center flex-nowrap">
                    <label class="text-nowrap small mb-0" style="min-width: 100px;">
                        <i class="bi bi-calendar-range"></i> ช่วงวันที่:
                    </label>
                    <input type="date" id="exportDateFrom" class="form-control form-control-sm" style="width: 140px;">
                    <span class="text-nowrap small">ถึง</span>
                    <input type="date" id="exportDateTo" class="form-control form-control-sm" style="width: 140px;">
                </div>

                <button type="button" class="btn btn-success btn-sm" id="exportCsvBtn">
                    <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
                </button>

            </div>
        </div>

    </div>

    @if (session('success'))
        @if (session('showToast'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    showSnackbar("{{ session('success') }}", "#28a745");
                });
            </script>
        @else
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @endif @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <small class="text-muted d-block mb-1">รุ่นทั้งหมด</small>
                                <h5 class="mb-0">{{ $batches->count() ?? 0 }}</h5>
                            </div>
                            <i class="fa fa-pigs fa-2x text-primary opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <small class="text-muted d-block mb-1">กำลังเลี้ยง</small>
                                <h5 class="mb-0 text-success">
                                    {{ $batches->where('status', 'raising')->count() ?? 0 }}
                                </h5>
                            </div>
                            <i class="fa fa-heartbeat fa-2x text-success opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <small class="text-muted d-block mb-1">กำลังขาย</small>
                                <h5 class="mb-0 text-warning">
                                    {{ $batches->where('status', 'selling')->count() ?? 0 }}
                                </h5>
                            </div>
                            <i class="fa fa-store fa-2x text-warning opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <small class="text-muted d-block mb-1">เสร็จแล้ว</small>
                                <h5 class="mb-0 text-secondary">
                                    {{ $batches->where('status', 'closed')->count() ?? 0 }}
                                </h5>
                            </div>
                            <i class="fa fa-check-circle fa-2x text-secondary opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Batches Table -->
        <div class="card border-0 shadow-sm" style="overflow: visible !important;">
            <div class="card-header bg-light">
                <h5 class="mb-0">รายละเอียดรุ่นหมู</h5>
            </div>
            <div class="card-body p-0" style="overflow: visible !important;">
                @if ($batches && $batches->count() > 0)
                    <div class="table-responsive" style="overflow-x: visible !important; overflow-y: visible !important;">
                        <table class="table table-hover table-primary mb-0" style="position: relative;">
                            <thead class="table-dark">
                                <tr>
                                    <th>รหัสรุ่น</th>
                                    <th>วันที่สร้าง</th>
                                    <th>จำนวนหมู</th>
                                    <th>น้ำหนักรวม</th>
                                    <th>สถานะ</th>
                                    <th>สถานะ Payment</th>
                                    <th class="text-center" style="width: 200px;">การกระทำ</th>
                                </tr>
                            </thead>
                            <tbody style="overflow: visible !important;">
                                @foreach ($batches as $batch)
                                    <tr class="batch-row" style="cursor: pointer;"
                                        onclick="event.target.closest('.batch-actions') || openViewModal({{ $batch->id }})">
                                        <td>
                                            <strong>{{ $batch->batch_code }}</strong>
                                        </td>
                                        <td>{{ $batch->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ $batch->total_pig_amount ?? 0 }} ตัว
                                            </span>
                                        </td>
                                        <td>
                                            {{ $batch->total_pig_weight ?? 0 }} kg
                                        </td>
                                        <td>
                                            @switch($batch->status)
                                                @case('draft')
                                                    <span class="badge bg-secondary">
                                                        <i class="fa fa-circle-notch me-1"></i>ร่าง
                                                    </span>
                                                @break

                                                @case('raising')
                                                    <span class="badge bg-success">
                                                        <i class="fa fa-heartbeat me-1"></i>กำลังเลี้ยง
                                                    </span>
                                                @break

                                                @case('selling')
                                                    <span class="badge bg-warning">
                                                        <i class="fa fa-store me-1"></i>กำลังขาย
                                                    </span>
                                                @break

                                                @case('closed')
                                                    <span class="badge bg-dark">
                                                        <i class="fa fa-check-circle me-1"></i>เสร็จแล้ว
                                                    </span>
                                                @break

                                                @default
                                                    <span class="badge bg-light text-dark">{{ $batch->status }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            @php
                                                // Get latest batch payment cost with receipt file
                                                $latestCost = $batch
                                                    ->costs()
                                                    ->where('cost_type', 'piglet')
                                                    ->latest()
                                                    ->first();

                                                // Check if payment has been recorded (must have receipt_file)
                                                $isPaymentRecorded = $latestCost && $latestCost->receipt_file;
                                                $latestPayment = $isPaymentRecorded
                                                    ? $latestCost->payments()->latest()->first()
                                                    : null;
                                                $paymentStatus = $latestPayment ? $latestPayment->status : null;
                                            @endphp
                                            @if ($isPaymentRecorded && $paymentStatus)
                                                @switch($paymentStatus)
                                                    @case('approved')
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-check-circle me-1"></i>อนุมัติ
                                                        </span>
                                                    @break

                                                    @case('pending')
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="bi bi-hourglass-split me-1"></i>รออนุมัติ
                                                        </span>
                                                    @break

                                                    @case('rejected')
                                                        <span class="badge bg-danger">
                                                            <i class="bi bi-x-circle me-1"></i>ปฏิเสธ
                                                        </span>
                                                    @break

                                                    @default
                                                        <span class="badge bg-secondary">-</span>
                                                @endswitch
                                            @else
                                                <span class="badge bg-info ">
                                                    <i class="bi bi-hourglass-split me-1"></i>รอชำระเงิน</span>
                                            @endif
                                        </td>
                                        <td class="batch-actions">
                                            @php
                                                // Check if payment has been recorded (receipt_file exists)
                                                $latestCost = $batch
                                                    ->costs()
                                                    ->where('cost_type', 'piglet')
                                                    ->latest()
                                                    ->first();
                                                $isPaymentRecorded = $latestCost && $latestCost->receipt_file;
                                            @endphp

                                            <!-- Payment Button - Show only if payment NOT recorded -->
                                            @if (!$isPaymentRecorded)
                                                <button type="button" class="btn btn-sm btn-success"
                                                    onclick="event.stopPropagation(); new bootstrap.Modal(document.getElementById('paymentModal{{ $batch->id }}')).show();"
                                                    title="บันทึกการชำระเงิน">
                                                    <i class="bi bi-cash me-1"></i>
                                                </button>
                                            @endif

                                            <!-- Edit Status Button -->
                                            <button type="button" class="btn btn-sm"
                                                style="background-color: #ffc107 !important; border-color: #ffc107 !important;"
                                                id="statusBtn{{ $batch->id }}"
                                                onclick="event.stopPropagation(); showStatusDropdown(event, {{ $batch->id }});"
                                                title="แก้ไขสถานะ">
                                                <i class="bi bi-pencil me-1"></i>
                                            </button>


                                            <!-- Delete Button -->
                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick="event.stopPropagation(); if(confirm('คุณแน่ใจไหมว่าจะลบรายการนี้?')) { deleteBatch({{ $batch->id }}); }"
                                                title="ลบรายการ">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox fa-3x text-muted mb-3 d-block" style="font-size: 3rem;"></i>
                        <h5 class="text-muted">ยังไม่มีรุ่นหมู</h5>
                        <p class="text-muted mb-3">เริ่มต้นด้วยการสร้างรุ่นใหม่</p>
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                            data-bs-target="#createBatchModal">
                            <i class="bi bi-plus-circle me-2"></i>
                            สร้างรุ่นใหม่
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Guide Card -->
        <div class="card border-info mt-4">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="fa fa-lightbulb me-2"></i>
                    วิธีการใช้งาน
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6 class="fw-bold mb-2">
                            <i class="fa fa-step-forward me-2 text-primary"></i>
                            ขั้นตอนที่ 1: สร้างรุ่น
                        </h6>
                        <p class="small mb-0">คลิก "สร้างรุ่นใหม่" ใส่รหัสรุ่นและข้อมูลพื้นฐาน</p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="fw-bold mb-2">
                            <i class="fa fa-step-forward me-2 text-success"></i>
                            ขั้นตอนที่ 2: บันทึกเข้าหมู
                        </h6>
                        <p class="small mb-0">ระบุจำนวน น้ำหนัก และราคาของหมูที่เข้ามา</p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="fw-bold mb-2">
                            <i class="fa fa-step-forward me-2 text-warning"></i>
                            ขั้นตอนที่ 3: จัดการ
                        </h6>
                        <p class="small mb-0">ระบบสร้างสถานะ "กำลังเลี้ยง" โดยอัตโนมัติ</p>
                    </div>
                </div>
            </div>
        </div>
        </div>
        </div>

        <!-- Floating Status Dropdown Menu (Rendered outside table) -->
        <div id="statusDropdownContainer"></div>

        @push('scripts')
            <script>
                // ===== SHOW STATUS DROPDOWN AT CURSOR =====
                function showStatusDropdown(event, batchId) {
                    event.stopPropagation();

                    // Get batch status from data attribute
                    const statusCell = event.target.closest('tr');
                    const currentStatus = statusCell.getAttribute('data-batch-status') || 'raising';

                    // Build dropdown HTML
                    const statusOptions = [{
                            value: 'raising',
                            label: 'กำลังเลี้ยง',
                            icon: 'heart'
                        },
                        {
                            value: 'selling',
                            label: 'กำลังขาย',
                            icon: 'cart-check'
                        },
                        {
                            value: 'closed',
                            label: 'เสร็จแล้ว',
                            icon: 'check-circle'
                        },
                        {
                            value: 'cancelled',
                            label: 'ยกเลิก',
                            icon: 'x-circle'
                        }
                    ];

                    // Get button position (relative to viewport)
                    const button = event.target;
                    const buttonRect = button.getBoundingClientRect();

                    // Create dropdown with absolute position to follow scroll
                    const container = document.getElementById('statusDropdownContainer');
                    container.innerHTML = `
                    <div class="dropdown-menu show" style="position: fixed; top: ${buttonRect.bottom + 5}px; left: ${buttonRect.left}px; min-width: 160px; z-index: 9999; display: block; max-height: 200px; overflow-y: auto;">
                        <h6 class="dropdown-header text-nowrap">เปลี่ยนสถานะเป็น:</h6>
                        ${statusOptions.map(opt => `
                                                    <a class="dropdown-item ${opt.value === currentStatus ? 'active fw-bold' : ''}" href="#" onclick="event.preventDefault(); event.stopPropagation(); updateBatchStatus(${batchId}, '${opt.value}'); document.getElementById('statusDropdownContainer').innerHTML = '';">
                                                        <i class="bi bi-${opt.icon} me-2"></i>${opt.label}
                                                    </a>
                                                `).join('')}
                    </div>
                `;

                    // Update position on scroll to keep dropdown visible
                    let scrollListener = function() {
                        const newButtonRect = button.getBoundingClientRect();
                        const dropdown = container.querySelector('.dropdown-menu');
                        if (dropdown) {
                            dropdown.style.top = (newButtonRect.bottom + 5) + 'px';
                            dropdown.style.left = newButtonRect.left + 'px';
                        }
                    };

                    window.addEventListener('scroll', scrollListener, true);

                    // Close dropdown when clicking elsewhere
                    let closeListener = function(e) {
                        if (!e.target.closest('#statusDropdownContainer') && !e.target.closest('button[id^="statusBtn"]')) {
                            container.innerHTML = '';
                            window.removeEventListener('scroll', scrollListener, true);
                            document.removeEventListener('click', closeListener);
                        }
                    };

                    document.addEventListener('click', closeListener);
                }

                // ===== OPEN VIEW MODAL =====
                function openViewModal(batchId) {
                    window.handleModal.openModal('viewBatchModal' + batchId);
                } // ===== UPDATE BATCH STATUS VIA AJAX =====
                function updateBatchStatus(batchId, newStatus) {
                    if (!confirm('ตัวจริงจะเปลี่ยนสถานะหรือ?')) {
                        return;
                    }

                    fetch(`/batch/${batchId}/update-status`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({
                                status: newStatus
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showNotification(data.message || 'อัปเดตสถานะสำเร็จ', 'success');
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                showNotification(data.message || 'เกิดข้อผิดพลาด', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification('เกิดข้อผิดพลาด: ' + error.message, 'error');
                        });
                }

                // ===== DELETE BATCH =====
                function deleteBatch(batchId) {
                    fetch(`/batch/${batchId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '',
                                'X-Requested-With': 'XMLHttpRequest',
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showNotification(data.message || 'ลบสำเร็จ', 'success');
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                showNotification(data.message || 'เกิดข้อผิดพลาด', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification('เกิดข้อผิดพลาด: ' + error.message, 'error');
                        });
                }

                // ===== SHOW NOTIFICATION =====
                function showNotification(message, type = 'success') {
                    const bgColor = type === 'success' ? '#28a745' : '#dc3545';
                    let notification = document.getElementById('notification-toast');

                    if (!notification) {
                        notification = document.createElement('div');
                        notification.id = 'notification-toast';
                        notification.style.cssText = `
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        z-index: 9999;
                    `;
                        document.body.appendChild(notification);
                    }

                    notification.innerHTML = `
                    <div class="alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show shadow" role="alert" style="min-width: 300px;">
                        <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                        <strong>${message}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;

                    setTimeout(() => {
                        if (notification.firstChild) {
                            notification.firstChild.remove();
                        }
                    }, 5000);
                }
            </script>
        @endpush <!-- Create/Edit Batch Modal -->
        <div class="modal fade" id="createBatchModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-plus-circle me-2"></i>
                            สร้างรุ่นใหม่ + บันทึกเข้าหมู
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="createBatchForm" action="{{ route('batch_entry.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="row">
                                <!-- Batch Info Section -->
                                <div class="col-md-6">
                                    <h6 class="mb-3 fw-bold">
                                        <i class="bi bi-info-circle me-2"></i>ข้อมูลรุ่น
                                    </h6>

                                    <div class="mb-3">
                                        <label for="batch_code" class="form-label">รหัสรุ่น <span
                                                class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control @error('batch_code') is-invalid @enderror" id="batch_code"
                                            name="batch_code" placeholder="เช่น F1-B001-2025" required>
                                        @error('batch_code')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="note" class="form-label">หมายเหตุ</label>
                                        <textarea class="form-control" id="note" name="note" rows="2" placeholder="หมายเหตุเพิ่มเติม"></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">เลือกฟาร์ม <span class="text-danger">*</span></label>
                                        <div class="dropdown d-block">
                                            <button class="btn btn-md w-100 btn-primary dropdown-toggle" type="button"
                                                id="farmDropdownBtn" data-bs-toggle="dropdown"
                                                style="display: flex; justify-content: space-between; align-items: center; text-align: left;">
                                                <span>
                                                    <i class="bi bi-building me-2"></i>
                                                    <span id="farmDropdownLabel">-- เลือกฟาร์ม --</span>
                                                </span>
                                            </button>
                                            <ul class="dropdown-menu w-100">
                                                <li><a class="dropdown-item" href="#" data-farm-id="">
                                                        <i class="bi bi-x-circle me-2"></i>-- เลือกฟาร์ม --
                                                    </a></li>
                                                @foreach ($farms ?? [] as $farm)
                                                    <li><a class="dropdown-item farm-option" href="#"
                                                            data-farm-id="{{ $farm->id }}">
                                                            <i class="bi bi-building me-2"></i>{{ $farm->farm_name }}
                                                        </a></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <input type="hidden" name="farm_id" id="farm_id_create" value=""
                                            required>
                                        @error('farm_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Entry Info Section -->
                                <div class="col-md-6">
                                    <h6 class="mb-3 fw-bold">
                                        <i class="bi bi-box-seam me-2"></i>บันทึกเข้าหมู
                                    </h6>

                                    <div class="mb-3">
                                        <label for="pig_entry_date" class="form-label">วันที่เข้าหมู <span
                                                class="text-danger">*</span></label>
                                        <input type="date"
                                            class="form-control @error('pig_entry_date') is-invalid @enderror"
                                            id="pig_entry_date" name="pig_entry_date"
                                            value="{{ old('pig_entry_date', date('Y-m-d')) }}" required>
                                        @error('pig_entry_date')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="total_pig_amount" class="form-label">จำนวนหมู <span
                                                class="text-danger">*</span></label>
                                        <input type="number"
                                            class="form-control @error('total_pig_amount') is-invalid @enderror"
                                            id="total_pig_amount" name="total_pig_amount" placeholder="0" min="1"
                                            value="{{ old('total_pig_amount') }}" required>
                                        @error('total_pig_amount')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="total_pig_weight" class="form-label">น้ำหนักรวม (kg) <span
                                                class="text-danger">*</span></label>
                                        <input type="number"
                                            class="form-control @error('total_pig_weight') is-invalid @enderror"
                                            id="total_pig_weight" name="total_pig_weight" placeholder="0.00"
                                            step="0.01" min="0.1" value="{{ old('total_pig_weight') }}"
                                            required>
                                        @error('total_pig_weight')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="total_pig_price" class="form-label">ราคารวม (บาท) <span
                                                class="text-danger">*</span></label>
                                        <input type="number"
                                            class="form-control @error('total_pig_price') is-invalid @enderror"
                                            id="total_pig_price" name="total_pig_price" placeholder="0.00"
                                            step="0.01" min="0" value="{{ old('total_pig_price') }}" required>
                                        @error('total_pig_price')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="transport_cost" class="form-label">ค่าขนส่ง (บาท)</label>
                                        <input type="number"
                                            class="form-control @error('transport_cost') is-invalid @enderror"
                                            id="transport_cost" name="transport_cost" placeholder="0.00" step="0.00"
                                            min="0" value="{{ old('transport_cost', '') }}">
                                        @error('transport_cost')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Summary Preview Section -->
                                    <div class="card bg-light border-0">
                                        <div class="card-body p-3">
                                            <h6 class="card-title mb-3">
                                                <i class="bi bi-calculator me-2"></i>สรุปค่าเฉลี่ย (คำนวณอัตโนมัติ)
                                            </h6>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <small class="text-muted d-block">น้ำหนักเฉลี่ย/ตัว</small>
                                                    <strong id="display_average_weight">0.00</strong> <span
                                                        class="text-muted">kg</span>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted d-block">ราคาเฉลี่ย/ตัว</small>
                                                    <strong id="display_average_price">0.00</strong> <span
                                                        class="text-muted">บาท</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Hidden inputs for actual values -->
                                    <input type="hidden" name="average_weight_per_pig" id="average_weight_per_pig">
                                    <input type="hidden" name="average_price_per_pig" id="average_price_per_pig">
                                </div>
                            </div>

                            {{-- Barn Selection Section (เลือกเล้า) --}}
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6 class="mb-3 fw-bold">
                                        <i class="bi bi-diagram-3 me-2"></i>จัดสรรเล้า/คอก (สำหรับบันทึกเข้าหมู)
                                    </h6>
                                    <div id="barn_selection_container">
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle"></i> กรุณาเลือกฟาร์มก่อน
                                        </div>
                                    </div>
                                </div>
                            </div>


                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary"
                                data-bs-dismiss="modal">ยกเลิก</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>
                                บันทึกข้อมูล
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @push('scripts')
            <script>
                // ========== AUTO-CALCULATE AVERAGE WEIGHT & PRICE PER PIG ==========
                document.addEventListener('DOMContentLoaded', function() {
                    const totalPigAmountInput = document.getElementById('total_pig_amount');
                    const totalPigWeightInput = document.getElementById('total_pig_weight');
                    const totalPigPriceInput = document.getElementById('total_pig_price');
                    const averageWeightPerPigInput = document.getElementById('average_weight_per_pig');
                    const averagePricePerPigInput = document.getElementById('average_price_per_pig');
                    const displayAverageWeight = document.getElementById('display_average_weight');
                    const displayAveragePrice = document.getElementById('display_average_price');
                    const createBatchForm = document.getElementById('createBatchForm');
                    const submitButton = createBatchForm?.querySelector('button[type="submit"]');

                    // ✅ Function to validate barn selection and update submit button
                    function updateSubmitButtonState() {
                        const barnCheckboxes = document.querySelectorAll('.barn-checkbox');
                        const anyChecked = Array.from(barnCheckboxes).some(cb => cb.checked);

                        if (submitButton) {
                            if (anyChecked) {
                                submitButton.disabled = false;
                                submitButton.classList.remove('disabled');
                                submitButton.title = '';
                            } else {
                                submitButton.disabled = true;
                                submitButton.classList.add('disabled');
                                submitButton.title = 'กรุณาเลือกเล้า/คอกอย่างน้อยหนึ่งตัว';
                            }
                        }
                    }

                    // ✅ Initial state: disable submit button
                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.title = 'กรุณาเลือกเล้า/คอกอย่างน้อยหนึ่งตัว';
                    }

                    // Function to calculate averages
                    function calculateAverages() {
                        const totalAmount = parseFloat(totalPigAmountInput.value) || 0;
                        const totalWeight = parseFloat(totalPigWeightInput.value) || 0;
                        const totalPrice = parseFloat(totalPigPriceInput.value) || 0;

                        if (totalAmount > 0) {
                            // Calculate average weight
                            const avgWeight = totalWeight > 0 ? (totalWeight / totalAmount).toFixed(2) : 0;
                            averageWeightPerPigInput.value = avgWeight;
                            displayAverageWeight.textContent = parseFloat(avgWeight).toLocaleString('th-TH', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });

                            // Calculate average price
                            const avgPrice = totalPrice > 0 ? (totalPrice / totalAmount).toFixed(2) : 0;
                            averagePricePerPigInput.value = avgPrice;
                            displayAveragePrice.textContent = parseFloat(avgPrice).toLocaleString('th-TH', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        } else {
                            averageWeightPerPigInput.value = '';
                            averagePricePerPigInput.value = '';
                            displayAverageWeight.textContent = '0.00';
                            displayAveragePrice.textContent = '0.00';
                        }
                    }

                    // Add event listeners to all inputs for auto-calculate
                    totalPigAmountInput?.addEventListener('input', calculateAverages);
                    totalPigWeightInput?.addEventListener('input', calculateAverages);
                    totalPigPriceInput?.addEventListener('input', calculateAverages);

                    // ========== FARM DROPDOWN SELECTION ==========
                    const farmDropdownBtn = document.getElementById('farmDropdownBtn');
                    const farmDropdownLabel = document.getElementById('farmDropdownLabel');
                    const farmHiddenInput = document.getElementById('farm_id_create');
                    const farmOptions = document.querySelectorAll('.farm-option');
                    const barnSelectionContainer = document.getElementById('barn_selection_container');

                    // Add event listeners to farm dropdown items
                    farmOptions.forEach(option => {
                        option.addEventListener('click', function(e) {
                            e.preventDefault();
                            const farmId = this.getAttribute('data-farm-id');
                            const farmName = this.textContent.trim().replace(/^\s*/, '').replace(/\s*$/,
                                '');

                            // Update hidden input
                            farmHiddenInput.value = farmId;

                            // Update button label and color
                            if (farmId) {
                                farmDropdownLabel.textContent = farmName.replace(/^[\s\S]*?(?=.)/m, '')
                                    .trim();
                                farmDropdownBtn.classList.remove('btn-outline-primary');
                                farmDropdownBtn.classList.add('btn-primary');
                            } else {
                                farmDropdownLabel.textContent = '-- เลือกฟาร์ม --';
                                farmDropdownBtn.classList.add('btn-outline-primary');
                                farmDropdownBtn.classList.remove('btn-primary');
                            }

                            // Load barn selection
                            if (farmId) {
                                loadBarnSelectionTable(farmId);
                            } else {
                                barnSelectionContainer.innerHTML =
                                    '<div class="alert alert-info"><i class="bi bi-info-circle"></i> กรุณาเลือกฟาร์ม</div>';
                            }
                        });
                    });

                    // Also handle regular close option
                    const closeOption = document.querySelector('.dropdown-menu a[data-farm-id=""]');
                    if (closeOption) {
                        closeOption.addEventListener('click', function(e) {
                            e.preventDefault();
                            farmHiddenInput.value = '';
                            farmDropdownLabel.textContent = '-- เลือกฟาร์ม --';
                            farmDropdownBtn.classList.add('btn-outline-primary');
                            farmDropdownBtn.classList.remove('btn-primary');
                            barnSelectionContainer.innerHTML =
                                '<div class="alert alert-info"><i class="bi bi-info-circle"></i> กรุณาเลือกฟาร์ม</div>';
                        });
                    }

                    // ========== FORM SUBMISSION VALIDATION ==========
                    // Form submission ไม่ใช้ AJAX - ให้ browser handle ตามปกติ
                    // Server-side validation จะ return error พร้อมข้อมูล preserved

                    createBatchForm?.addEventListener('submit', function(e) {
                        const barnCheckboxes = document.querySelectorAll('.barn-checkbox');
                        const anyChecked = Array.from(barnCheckboxes).some(cb => cb.checked);

                        // ✅ Check if at least one barn is selected
                        if (!anyChecked) {
                            e.preventDefault();

                            // Show toast error
                            showToastError('❌ ข้อผิดพลาด', 'กรุณาเลือกเล้า/คอกอย่างน้อยหนึ่งตัว');
                        }
                    });

                    // ✅ Helper function to show toast error
                    function showToastError(title, message) {
                        // Create toast element
                        const toastHTML = `
                        <div class="toast align-items-center border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" style="min-width: 350px;">
                            <div class="d-flex bg-danger text-white rounded">
                                <div class="toast-body">
                                    <strong>${title}</strong><br>
                                    <small>${message}</small>
                                </div>
                                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                        </div>
                    `;

                        // Create container if not exists
                        let toastContainer = document.getElementById('toastContainer');
                        if (!toastContainer) {
                            toastContainer = document.createElement('div');
                            toastContainer.id = 'toastContainer';
                            toastContainer.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999;';
                            document.body.appendChild(toastContainer);
                        }

                        // Add toast to container
                        toastContainer.insertAdjacentHTML('beforeend', toastHTML);

                        // Get the newly added toast and show it
                        const toastElements = toastContainer.querySelectorAll('.toast');
                        const lastToast = toastElements[toastElements.length - 1];
                        const bsToast = new bootstrap.Toast(lastToast, {
                            autohide: true,
                            delay: 4000
                        });
                        bsToast.show();

                        // Remove toast element after it's hidden
                        lastToast.addEventListener('hidden.bs.toast', function() {
                            lastToast.remove();
                        });
                    }

                    // ========== BARN/PEN SELECTION FOR BATCH CREATE ==========

                    function loadBarnSelectionTable(farmId) {
                        barnSelectionContainer.innerHTML =
                            '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">กำลังโหลด...</p></div>';

                        // ใช้ endpoint เฉพาะสำหรับการสำรองจัดสรรเล้า - แสดงทั้งเล้าที่ว่างและมีหมู
                        fetch(`/pig_sales/barns-by-farm-for-allocation/${farmId}`)
                            .then(response => response.json())
                            .then(data => {
                                console.log('Barns response:', data);

                                if (data.success && data.data && data.data.length > 0) {
                                    let html = `
                                    <div class="table-responsive mt-3">
                                        <table class="table table-secondary table-sm table-hover mb-0">
                                            <thead class="table-header-custom">
                                                <tr>
                                                    <th class="text-center" style="width: 50px;">
                                                        <input type="checkbox" id="select_all_barns" class="form-check-input form-check-input-sm">
                                                    </th>
                                                    <th style="width: 120px;">รหัสเล้า</th>
                                                    <th class="text-center" style="width: 120px;">กำลังเลี้ยง</th>
                                                    <th class="text-center" style="width: 120px;">ยังจุได้</th>
                                                    <th style="width: 150px;">สถานะ</th>
                                                </tr>
                                            </thead>
                                            <tbody>`;

                                    data.data.forEach(barn => {
                                        const barnId = barn.barn_id;
                                        const totalPigs = barn.total_pigs || 0;
                                        const availableCapacity = barn.available_capacity || 0;
                                        const isEmpty = totalPigs === 0;
                                        const statusBadge = isEmpty ?
                                            '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>พร้อมใช้งาน</span>' :
                                            '<span class="badge bg-warning text-dark"><i class="bi bi-exclamation-circle me-1"></i>กำลังเลี้ยง</span>';

                                        html += `
                                        <tr>
                                            <td class="text-center align-middle">
                                                <input type="checkbox" class="form-check-input form-check-input-sm barn-checkbox" name="barn_ids[]" value="${barnId}" data-total-pigs="${totalPigs}">
                                            </td>
                                            <td class="align-middle"><strong>${barn.barn_code || '-'}</strong></td>
                                            <td class="text-center align-middle">
                                                <span class="badge ${totalPigs > 0 ? 'bg-info' : 'bg-secondary'}">
                                                    ${totalPigs} ตัว
                                                </span>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge ${availableCapacity > 0 ? 'bg-success' : 'bg-danger'}">
                                                    ${availableCapacity} ตัว
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                ${statusBadge}
                                            </td>
                                        </tr>`;
                                    });

                                    html += `
                                            </tbody>
                                        </table>
                                    </div>`;

                                    barnSelectionContainer.innerHTML = html;

                                    // Add event listeners
                                    const selectAllCheckbox = document.getElementById('select_all_barns');
                                    const barnCheckboxes = document.querySelectorAll('.barn-checkbox');

                                    selectAllCheckbox?.addEventListener('change', function() {
                                        barnCheckboxes.forEach(checkbox => {
                                            checkbox.checked = this.checked;
                                        });

                                        // ✅ Update submit button state
                                        updateSubmitButtonState();
                                    });

                                    barnCheckboxes.forEach(checkbox => {
                                        checkbox.addEventListener('change', function() {
                                            const allChecked = Array.from(barnCheckboxes).every(cb => cb
                                                .checked);
                                            const someChecked = Array.from(barnCheckboxes).some(cb => cb
                                                .checked);
                                            if (selectAllCheckbox) {
                                                selectAllCheckbox.checked = allChecked;
                                                selectAllCheckbox.indeterminate = someChecked && !
                                                    allChecked;
                                            }

                                            // ✅ Update submit button state
                                            updateSubmitButtonState();
                                        });
                                    });
                                } else {
                                    barnSelectionContainer.innerHTML =
                                        '<div class="alert alert-warning"><i class="bi bi-exclamation-triangle"></i> ไม่พบเล้าในฟาร์มนี้</div>';
                                }
                            })
                            .catch(error => {
                                console.error('Error loading barns:', error);
                                barnSelectionContainer.innerHTML =
                                    '<div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> เกิดข้อผิดพลาดในการโหลดเล้า</div>';
                            });
                    }
                });
            </script>
        @endpush

        {{-- Payment Modal for Each Batch --}}
        @foreach ($batches as $batch)
            <!-- View Batch Modal -->
            <div class="modal fade" id="viewBatchModal{{ $batch->id }}" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-light">
                            <h5 class="modal-title">
                                <i class="bi bi-eye me-2"></i>รายละเอียดรุ่นหมู
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="fw-bold mb-3">ข้อมูลทั่วไป</h6>
                                    <div class="row mb-2">
                                        <div class="col-5"><strong>รหัสรุ่น:</strong></div>
                                        <div class="col-7">{{ $batch->batch_code }}</div>
                                    </div>

                                    <div class="row mb-2">
                                        <div class="col-5"><strong>วันที่สร้าง:</strong></div>
                                        <div class="col-7">{{ $batch->created_at->format('d/m/Y H:i') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold mb-3">ข้อมูลหมู</h6>
                                    <div class="row mb-2">
                                        <div class="col-5"><strong>จำนวนหมู:</strong></div>
                                        <div class="col-7"><span
                                                class="badge bg-info">{{ $batch->total_pig_amount ?? 0 }}
                                                ตัว</span></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-5"><strong>น้ำหนักรวม:</strong></div>
                                        <div class="col-7">{{ $batch->total_pig_weight ?? 0 }} kg</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-5"><strong>ราคารวม:</strong></div>
                                        <div class="col-7">{{ number_format($batch->total_pig_price ?? 0, 2) }} บาท</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-5"><strong>สถานะ:</strong></div>
                                        <div class="col-7">
                                            @switch($batch->status)
                                                @case('draft')
                                                    <span class="badge bg-secondary"><i
                                                            class="fa fa-circle-notch me-1"></i>ร่าง</span>
                                                @break

                                                @case('raising')
                                                    <span class="badge bg-success"><i
                                                            class="fa fa-heartbeat me-1"></i>กำลังเลี้ยง</span>
                                                @break

                                                @case('selling')
                                                    <span class="badge bg-warning"><i class="fa fa-store me-1"></i>กำลังขาย</span>
                                                @break

                                                @case('closed')
                                                    <span class="badge bg-dark"><i
                                                            class="fa fa-check-circle me-1"></i>เสร็จแล้ว</span>
                                                @break

                                                @default
                                                    <span class="badge bg-light text-dark">{{ $batch->status }}</span>
                                            @endswitch
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <h6 class="fw-bold mb-3">บันทึกเข้าหมู</h6>
                            @if ($batch->pig_entry_records && $batch->pig_entry_records->count() > 0)
                                @foreach ($batch->pig_entry_records as $entry)
                                    <div class="card mb-3 border-0 bg-light">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>วันที่เข้า:</strong>
                                                        {{ $entry->pig_entry_date->format('d/m/Y') }}</p>
                                                    <p class="mb-1"><strong>จำนวน:</strong>
                                                        {{ $entry->total_pig_amount ?? 0 }} ตัว
                                                    </p>
                                                    <p class="mb-0"><strong>รวม:</strong>
                                                        {{ number_format($entry->total_pig_price ?? 0, 2) }} บาท</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>น้ำหนักรวม:</strong>
                                                        {{ $entry->total_pig_weight ?? 0 }} kg</p>
                                                    <p class="mb-1"><strong>น้ำหนักเฉลี่ย:</strong>
                                                        {{ number_format(($entry->total_pig_weight ?? 0) / max($entry->total_pig_amount ?? 1, 1), 2) }}
                                                        kg</p>
                                                    <p class="mb-0"><strong>ราคาเฉลี่ย:</strong>
                                                        {{ number_format(($entry->total_pig_price ?? 0) / max($entry->total_pig_amount ?? 1, 1), 2) }}
                                                        บาท</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted">ยังไม่มีบันทึกเข้าหมู</p>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="paymentModal{{ $batch->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">บันทึกการชำระเงิน - {{ $batch->batch_code }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="paymentForm{{ $batch->id }}" class="paymentForm"
                            data-batch-id="{{ $batch->id }}" action="{{ route('batch.update_payment', $batch->id) }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="cost_type" value="batch">
                            <input type="hidden" name="batch_id" value="{{ $batch->id }}">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">ยอดที่ต้องชำระ</label>
                                    @php
                                        $totalCost = $batch->pig_entry_records()->sum('total_pig_price');
                                        $totalCost = (float) ($totalCost ?? 0);
                                    @endphp
                                    <input type="text" class="form-control"
                                        value="{{ number_format($totalCost, 2) }} บาท" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">จำนวนเงินที่ชำระ <span class="text-danger">*</span></label>
                                    @php
                                        $amountDisplay = $totalCost;
                                        if (old('amount')) {
                                            $amountDisplay = old('amount');
                                        }
                                    @endphp
                                    <input type="number" name="amount"
                                        class="form-control @error('amount') is-invalid @enderror" step="0.01"
                                        min="0.01" value="{{ $amountDisplay }}" placeholder="0.00" required>
                                    @error('amount')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">วิธีชำระเงิน <span class="text-danger">*</span></label>
                                    <div class="dropdown">
                                        <button
                                            class="btn btn-primary dropdown-toggle w-100 d-flex justify-content-between align-items-center"
                                            type="button" id="paymentMethodDropdownBtn{{ $batch->id }}"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <span id="paymentMethodText{{ $batch->id }}">-- เลือกวิธีชำระเงิน --</span>
                                        </button>
                                        <ul class="dropdown-menu w-100" role="listbox">
                                            <li><a class="dropdown-item" href="#" data-payment-method="เงินสด"
                                                    onclick="updatePaymentMethod(event, {{ $batch->id }})">เงินสด</a>
                                            </li>
                                            <li><a class="dropdown-item" href="#" data-payment-method="โอนเงิน"
                                                    onclick="updatePaymentMethod(event, {{ $batch->id }})">โอนเงิน</a>
                                            </li>
                                            <li><a class="dropdown-item" href="#" data-payment-method="เช็ค"
                                                    onclick="updatePaymentMethod(event, {{ $batch->id }})">เช็ค</a>
                                            </li>
                                        </ul>
                                        <input type="hidden" name="action_type" id="paymentMethod{{ $batch->id }}"
                                            value="" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">อัปโหลดหลักฐานการชำระ <span
                                            class="text-danger">*</span></label>
                                    <input type="file" class="form-control" name="receipt_file"
                                        accept="image/*,application/pdf" required>
                                    <small class="text-muted">รองรับไฟล์: JPG, PNG, PDF (สูงสุด 5MB)</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">หมายเหตุ</label>
                                    <textarea name="reason" class="form-control" rows="2" placeholder="เช่น ชำระตามเอกสารใบเรียก"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                <button type="submit" class="btn btn-primary">บันทึกการชำระเงิน</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

        @push('scripts')
            <script>
                function updatePaymentMethod(event, recordId) {
                    event.preventDefault();
                    event.stopPropagation();

                    const paymentMethod = event.target.getAttribute('data-payment-method');
                    const methodText = event.target.textContent.trim();

                    const textElement = document.getElementById('paymentMethodText' + recordId);
                    const inputElement = document.getElementById('paymentMethod' + recordId);
                    const btnElement = document.getElementById('paymentMethodDropdownBtn' + recordId);

                    if (textElement && inputElement) {
                        textElement.textContent = methodText;
                        inputElement.value = paymentMethod;

                        // Close dropdown
                        const dropdown = bootstrap.Dropdown.getInstance(btnElement);
                        if (dropdown) {
                            dropdown.hide();
                        }
                    }
                }

                // ===== HANDLE PAYMENT FORM SUBMIT (AJAX) =====
                document.querySelectorAll('.paymentForm').forEach(form => {
                    form.addEventListener('submit', async (e) => {
                        e.preventDefault();

                        const formData = new FormData(form);
                        const batchId = form.getAttribute('data-batch-id');
                        const modal = bootstrap.Modal.getInstance(document.getElementById(
                            `paymentModal${batchId}`));

                        try {
                            const response = await fetch(form.action, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });

                            const data = await response.json();

                            if (data.success) {
                                showNotification('✅ บันทึกการชำระเงินสำเร็จ', 'success');
                                if (modal) modal.hide();
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                showNotification('❌ ' + (data.message || 'เกิดข้อผิดพลาด'), 'error');
                            }
                        } catch (error) {
                            console.error('Payment error:', error);
                            showNotification('❌ เกิดข้อผิดพลาด: ' + error.message, 'error');
                        }
                    });
                });

                // ===== HANDLE CREATE BATCH FORM SUBMIT (AJAX) =====
                const createBatchForm = document.getElementById('createBatchForm');
                if (createBatchForm) {
                    createBatchForm.addEventListener('submit', async (e) => {
                        e.preventDefault();

                        const formData = new FormData(createBatchForm);
                        const modal = bootstrap.Modal.getInstance(document.getElementById('createBatchModal'));

                        try {
                            const response = await fetch(createBatchForm.action, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });

                            const data = await response.json();

                            if (data.success) {
                                showNotification('✅ ' + (data.message || 'สร้างรุ่นและบันทึกเข้าหมูสำเร็จ'), 'success');
                                if (modal) modal.hide();
                                createBatchForm.reset();
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                showNotification('❌ ' + (data.message || 'เกิดข้อผิดพลาด'), 'error');
                            }
                        } catch (error) {
                            console.error('Create batch error:', error);
                            showNotification('❌ เกิดข้อผิดพลาด: ' + error.message, 'error');
                        }
                    });
                }

                function showSnackbar(message, bgColor = "#dc3545") {
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

                    setTimeout(() => {
                        sb.style.display = "none";
                    }, 5000);
                }

                // Export CSV
                document.getElementById('exportCsvBtn').addEventListener('click', function() {
                    console.log('📥 [Batch] Exporting CSV');
                    const params = new URLSearchParams(window.location.search);
                    const dateFrom = document.getElementById('exportDateFrom').value;
                    const dateTo = document.getElementById('exportDateTo').value;
                    if (dateFrom) params.set('export_date_from', dateFrom);
                    if (dateTo) params.set('export_date_to', dateTo);
                    const url = `{{ route('batch.export.csv') }}?${params.toString()}`;
                    window.location.href = url;
                });
            </script>
        @endpush
    @endsection
