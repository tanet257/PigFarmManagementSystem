@extends('layouts.admin')

@section('title', 'จัดการการรักษา')

@section('content')
    {{-- ========== DATA STORE DIVS ========== --}}
    {{-- เก็บข้อมูล JSON ไว้ใน data attributes เพื่อให้ JS modules อ่านได้ --}}
    <div id="treatmentsDataStore" data-treatments="{{ json_encode($treatments ?? []) }}" style="display:none;"></div>
    <div id="batchesDataStore" data-batches="{{ json_encode($batches ?? []) }}" style="display:none;"></div>
    <div id="medicinesDataStore" data-medicines="{{ json_encode($medicines ?? []) }}" style="display:none;"></div>

    <div class="container my-5">
        <!-- Header -->
        <div class="card-header">
            <h1 class="text-center mb-2">จัดการการรักษา</h1>
        </div>
        <div class="py-2"></div>

        <!-- Toolbar -->
        <div class="card-custom-secondary mb-3">
            <form method="GET" action="{{ route('treatments.index') }}" class="d-flex align-items-center gap-2 flex-wrap"
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
                                href="{{ route('treatments.index', array_merge(request()->except('selected_date'), [])) }}">วันที่ทั้งหมด</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'today' ? 'active' : '' }}"
                                href="{{ route('treatments.index', array_merge(request()->all(), ['selected_date' => 'today'])) }}">วันนี้</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'this_week' ? 'active' : '' }}"
                                href="{{ route('treatments.index', array_merge(request()->all(), ['selected_date' => 'this_week'])) }}">สัปดาห์นี้</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'this_month' ? 'active' : '' }}"
                                href="{{ route('treatments.index', array_merge(request()->all(), ['selected_date' => 'this_month'])) }}">เดือนนี้</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'this_year' ? 'active' : '' }}"
                                href="{{ route('treatments.index', array_merge(request()->all(), ['selected_date' => 'this_year'])) }}">ปีนี้</a>
                        </li>
                    </ul>
                </div>

                <!-- Farm Filter -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="farmFilterBtn"
                        data-bs-toggle="dropdown" data-farm-id="{{ request('farm_id') }}">
                        <i class="bi bi-building"></i>
                        {{ request('farm_id') ? $farms->find(request('farm_id'))->farm_name ?? 'ฟาร์ม' : 'ฟาร์มทั้งหมด' }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item {{ request('farm_id') == '' ? 'active' : '' }}"
                                href="{{ route('treatments.index', array_merge(request()->except('farm_id'), [])) }}">ฟาร์มทั้งหมด</a>
                        </li>
                        @foreach ($farms as $farm)
                            <li><a class="dropdown-item {{ request('farm_id') == $farm->id ? 'active' : '' }}"
                                    href="{{ route('treatments.index', array_merge(request()->all(), ['farm_id' => $farm->id])) }}">{{ $farm->farm_name }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Batch Filter -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="batchFilterBtn"
                        data-bs-toggle="dropdown" data-batch-id="{{ request('batch_id') }}">
                        <i class="bi bi-diagram-3"></i>
                        {{ request('batch_id') ? $batches->find(request('batch_id'))->batch_code ?? 'รุ่น' : 'รุ่นทั้งหมด' }}
                    </button>
                    <ul class="dropdown-menu" style="min-width: 250px !important;">
                        <li><a class="dropdown-item {{ request('batch_id') == '' ? 'active' : '' }}"
                                href="{{ route('treatments.index', array_merge(request()->except('batch_id'), [])) }}">รุ่นทั้งหมด</a>
                        </li>
                        @foreach ($batches as $batch)
                            <li><a class="dropdown-item {{ request('batch_id') == $batch->id ? 'active' : '' }}"
                                    href="{{ route('treatments.index', array_merge(request()->all(), ['batch_id' => $batch->id])) }}">{{ $batch->batch_code }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Status Filter -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="statusFilterBtn"
                        data-bs-toggle="dropdown">
                        <i class="bi bi-funnel"></i>
                        @if (request('status') == 'pending')
                            รอดำเนินการ
                        @elseif (request('status') == 'ongoing')
                            กำลังดำเนินการ
                        @elseif (request('status') == 'completed')
                            เสร็จสิ้น
                        @elseif (request('status') == 'stopped')
                            หยุดการรักษา
                        @else
                            สถานะทั้งหมด
                        @endif
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item {{ request('status') == '' ? 'active' : '' }}"
                                href="{{ route('treatments.index', array_merge(request()->except('status'), [])) }}">สถานะทั้งหมด</a>
                        </li>
                        <li><a class="dropdown-item {{ request('status') == 'pending' ? 'active' : '' }}"
                                href="{{ route('treatments.index', array_merge(request()->all(), ['status' => 'pending'])) }}">รอดำเนินการ</a>
                        </li>
                        <li><a class="dropdown-item {{ request('status') == 'ongoing' ? 'active' : '' }}"
                                href="{{ route('treatments.index', array_merge(request()->all(), ['status' => 'ongoing'])) }}">กำลังดำเนินการ</a>
                        </li>
                        <li><a class="dropdown-item {{ request('status') == 'completed' ? 'active' : '' }}"
                                href="{{ route('treatments.index', array_merge(request()->all(), ['status' => 'completed'])) }}">เสร็จสิ้น</a>
                        </li>
                        <li><a class="dropdown-item {{ request('status') == 'stopped' ? 'active' : '' }}"
                                href="{{ route('treatments.index', array_merge(request()->all(), ['status' => 'stopped'])) }}">หยุดการรักษา</a>
                        </li>
                    </ul>
                </div>

                <!-- Per Page -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-list"></i>
                        แสดง: {{ request('per_page', 10) }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item {{ request('per_page', 10) == 10 ? 'active' : '' }}"
                                href="{{ route('treatments.index', array_merge(request()->all(), ['per_page' => 10])) }}">10
                                รายการ</a></li>
                        <li><a class="dropdown-item {{ request('per_page', 10) == 25 ? 'active' : '' }}"
                                href="{{ route('treatments.index', array_merge(request()->all(), ['per_page' => 25])) }}">25
                                รายการ</a></li>
                        <li><a class="dropdown-item {{ request('per_page', 10) == 50 ? 'active' : '' }}"
                                href="{{ route('treatments.index', array_merge(request()->all(), ['per_page' => 50])) }}">50
                                รายการ</a></li>
                        <li><a class="dropdown-item {{ request('per_page', 10) == 100 ? 'active' : '' }}"
                                href="{{ route('treatments.index', array_merge(request()->all(), ['per_page' => 100])) }}">100
                                รายการ</a></li>
                    </ul>
                </div>
                <div class="ms-auto d-flex gap-2">
                    <button type="button" class="btn btn-success btn-sm" id="openTreatmentFormBtn">
                        <i class="bi bi-plus-circle me-1"></i> เพิ่มการรักษา
                    </button>
                </div>
            </form>
        </div>

        <!-- Export Section -->
        <div class="card-custom-secondary mb-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-download me-2 text-primary"></i>
                    <strong>ส่วนการส่งออก</strong>
                </div>

                <!-- Custom Date Range Filter for Export -->
                <div class="ms-auto d-flex gap-2 align-items-center">
                    <label class="text-nowrap small mb-0" style="min-width: 100px;">
                        <i class="bi bi-calendar-range"></i> ช่วงวันที่:
                    </label>
                    <input type="date" id="exportDateFrom" class="form-control form-control-sm"
                        style="width: 140px;">
                    <span class="text-nowrap small">ถึง</span>
                    <input type="date" id="exportDateTo" class="form-control form-control-sm"
                        style="width: 140px;">
                </div>

                <button type="button" class="btn btn-success btn-sm" id="exportCsvBtn" title="Export CSV">
                    <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
                </button>
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
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif



        <!-- Table Section -->
        <div class="table-responsive">
            <table class="table table-primary mb-0">
                <thead class="table-header-custom">
                    <tr>
                        <th width="5%">#</th>
                        <th width="8%">รหัส</th>
                        <th width="12%">วันที่วางแผน</th>
                        <th width="18%">โรค/อาการ</th>
                        <th width="14%">ยา/วัคซีน</th>
                        <th width="8%">โดส</th>
                        <th width="10%">ความถี่</th>
                        <th width="8%">ระยะเวลา</th>
                        <th width="10%">สถานะ</th>
                        <th width="7%">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($treatments as $treatment)
                        <tr class="treatment-row cursor-pointer" data-treatment-id="{{ $treatment->id }}"
                            style="cursor: pointer;">
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td class="text-center font-monospace small">
                                {{ $treatment->batch ? $treatment->batch->batch_code : '-' }}</td>
                            <td class="text-center">
                                @if ($treatment->planned_start_date)
                                    {{ \Carbon\Carbon::parse($treatment->planned_start_date)->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-start">
                                <small>{{ Str::limit($treatment->disease_name ?? 'ไม่ระบุ', 40) }}</small>
                            </td>
                            <td class="text-start">
                                <small>{{ Str::limit($treatment->medicine_name ?? 'ไม่ระบุ', 30) }}</small>
                            </td>
                            <td class="text-center">
                                <small>{{ $treatment->dosage ?? '-' }}</small>
                            </td>
                            <td class="text-center">
                                @php
                                    $freqLabels = [
                                        'once' => '1 ครั้ง',
                                        'daily' => 'วันละ 1 ครั้ง',
                                        'twice_daily' => 'วันละ 2 ครั้ง',
                                        'every_other_day' => 'วันเว้นวัน',
                                        'weekly' => 'สัปดาห์ละ 1 ครั้ง',
                                    ];
                                @endphp
                                <small>{{ $freqLabels[$treatment->frequency] ?? ($treatment->frequency ?? '-') }}</small>
                            </td>
                            <td class="text-center">
                                <small>{{ $treatment->planned_duration ?? 0 }} วัน</small>
                            </td>
                            <td class="text-center">
                                @switch($treatment->treatment_status)
                                    @case('pending')
                                        <span class="badge bg-warning text-dark">รอดำเนินการ</span>
                                    @break

                                    @case('ongoing')
                                        <span class="badge bg-primary">กำลังดำเนินการ</span>
                                    @break

                                    @case('completed')
                                        <span class="badge bg-success">เสร็จสิ้น</span>
                                    @break

                                    @case('stopped')
                                        <span class="badge bg-danger">หยุดการรักษา</span>
                                    @break

                                    @default
                                        <span class="badge bg-secondary">ไม่ระบุ</span>
                                @endswitch
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button type="button" class="btn btn-info btn-xs view-treatment"
                                        style="padding: 2px 5px; font-size: 11px;"
                                        data-treatment-id="{{ $treatment->id }}" title="ดูรายละเอียด">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-warning btn-xs edit-treatment"
                                        style="padding: 2px 5px; font-size: 11px;"
                                        data-treatment-id="{{ $treatment->id }}" title="แก้ไข">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-xs delete-treatment"
                                        style="padding: 2px 5px; font-size: 11px;"
                                        data-treatment-id="{{ $treatment->id }}" title="ลบ">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <i class="fa fa-folder-open fa-2x mb-2 text-secondary"></i>
                                        <h5 class="mb-0">ไม่พบข้อมูลการรักษา</h5>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($treatments->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $treatments->links() }}
                </div>
            @endif
        </div>
        </div>
        </div>

        <!-- Treatment Form Modal -->
        <div class="modal fade" id="treatmentFormModal" tabindex="-1" aria-labelledby="treatmentFormLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="treatmentFormLabel">บันทึกการรักษา</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="treatmentFormInModal">
                            @csrf
                            <input type="hidden" id="treatmentId" name="id" value="">

                            <!-- กลุ่มเป้าหมาย -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">กลุ่มเป้าหมายการรักษา</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- เลือกฟาร์ม -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">เลือกฟาร์ม <span class="text-danger">*</span></label>
                                            <div class="dropdown d-block">
                                                <button class="btn btn-md w-100 btn-primary dropdown-toggle" type="button"
                                                    id="treatmentFarmDropdownBtn" data-bs-toggle="dropdown"
                                                    style="display: flex; justify-content: space-between; align-items: center; text-align: left;">
                                                    <span>
                                                        <i class="bi bi-building me-2"></i>
                                                        <span id="treatmentFarmDropdownLabel">-- เลือกฟาร์ม --</span>
                                                    </span>
                                                </button>
                                                <ul class="dropdown-menu w-100" id="treatmentFarmDropdownMenu">
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
                                                <input type="hidden" name="farm_id" id="treatmentFarmId" value=""
                                                    required>
                                            </div>
                                        </div>

                                        <!-- เลือกรุ่น -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">เลือกรุ่น <span class="text-danger">*</span></label>
                                            <div class="dropdown d-block">
                                                <button class="btn btn-md w-100 btn-primary dropdown-toggle" type="button"
                                                    id="treatmentBatchDropdownBtn" data-bs-toggle="dropdown" disabled
                                                    style="display: flex; justify-content: space-between; align-items: center; text-align: left;">
                                                    <span>
                                                        <i class="bi bi-diagram-3 me-2"></i>
                                                        <span id="treatmentBatchDropdownLabel">-- เลือกฟาร์มก่อน --</span>
                                                    </span>
                                                </button>
                                                <ul class="dropdown-menu w-100" id="treatmentBatchDropdownMenu">
                                                    <!-- จะ populate ด้วย JavaScript เมื่อเลือกฟาร์ม -->
                                                </ul>
                                                <input type="hidden" name="batch_id" id="treatmentBatchId" value=""
                                                    required>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">ระดับการรักษา <span class="text-danger">*</span></label>
                                            <div class="d-flex gap-3">
                                                <div class="form-check">
                                                    <input class="form-check-input treatment-level-radio" type="radio"
                                                        name="treatment_level" id="levelBarn" value="barn" checked
                                                        required>
                                                    <label class="form-check-label" for="levelBarn">ระดับเล้า</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input treatment-level-radio" type="radio"
                                                        name="treatment_level" id="levelPen" value="pen" required>
                                                    <label class="form-check-label" for="levelPen">ระดับคอก</label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- ✅ Checkbox Table - Barn/Pen Selection (styled like pig_sales) -->
                                        <div class="col-md-12">
                                            <label class="form-label">เลือกเล้า/คอก <span class="text-danger">*</span></label>
                                            <div id="pen_selection_container">
                                                <div class="table-responsive mt-3">
                                                    <table class="table table-primary mb-0">
                                                        <thead class="table-header-custom"
                                                            style="position: sticky; top: 0; z-index: 10;">
                                                            <tr>
                                                                <th class="text-center" style="width: 45px;">
                                                                    <input type="checkbox" id="select_all_treatment_items"
                                                                        class="form-check-input form-check-input-sm">
                                                                </th>
                                                                <th>เล้า</th>
                                                                <th id="pen_header_col" style="display: none;">คอก</th>
                                                                <th class="text-center">จำนวนหมูที่เหลือ</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="treatmentPenTableBody" class="text-center">
                                                            <tr>
                                                                <td colspan="4" class="text-center py-4 text-muted">
                                                                    <i class="bi bi-info-circle me-2"></i>
                                                                    กรุณาเลือกฟาร์ม และรุ่น ก่อน
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <small class="text-muted d-block mt-2">
                                                <i class="bi bi-question-circle me-1"></i>
                                                หมายเหตุ: เลือก <strong id="level_hint">--</strong>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- โปรโตคอลการรักษา -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">โปรโตคอลการรักษา</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="treatmentDiseaseName" class="form-label">โรค/อาการ/สาเหตุที่ต้องใช้ยา
                                                <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="treatmentDiseaseName"
                                                name="disease_name" placeholder="เช่น ไข้หวัดสุกร" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="treatmentMedicineDropdown" class="form-label">ยา/วัคซีน <span
                                                    class="text-danger">*</span></label>
                                            <div class="dropdown">
                                                <button
                                                    class="btn btn-primary dropdown-toggle w-100 d-flex justify-content-between align-items-center treatment-medicine-dropdown-btn"
                                                    type="button" id="treatmentMedicineDropdown" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <span>เลือกยา/วัคซีน</span>
                                                </button>
                                                <ul class="dropdown-menu w-100 treatment-medicine-dropdown-menu"
                                                    aria-labelledby="treatmentMedicineDropdown">
                                                    <!-- ตัวเลือก populate หลังเลือก batch -->
                                                </ul>
                                                <input type="hidden" name="medicine_name" class="treatment-medicine-name"
                                                    value="" required>
                                                <input type="hidden" name="medicine_code" class="treatment-medicine-code"
                                                    value="" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="dosage" class="form-label">ขนาดยา/ตัว <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="dosage" name="dosage"
                                                    step="0.01" min="0" required>
                                                <span class="input-group-text">มล.</span>
                                            </div>
                                            {{-- แสดงการคำนวณจำนวนหน่วยสินค้า --}}
                                            <small id="dosageCalculationDisplay" class="d-block mt-2 text-muted"
                                                style="font-size: 0.85rem;">
                                                เลือกยาก่อนเพื่อดูการคำนวณ
                                            </small>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="frequency" class="form-label">ความถี่ในการให้ยา <span
                                                    class="text-danger">*</span></label>
                                            <div class="dropdown">
                                                <button
                                                    class="btn btn-primary dropdown-toggle w-100 text-start treatment-frequency-btn"
                                                    type="button" id="frequencyDropdown" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    เลือกความถี่
                                                </button>
                                                <ul class="dropdown-menu w-100" aria-labelledby="frequencyDropdown">
                                                    <li><a class="dropdown-item" href="#" data-frequency="once"
                                                            data-label="ครั้งเดียว">ครั้งเดียว</a></li>
                                                    <li><a class="dropdown-item" href="#" data-frequency="daily"
                                                            data-label="วันละครั้ง">วันละครั้ง</a></li>
                                                    <li><a class="dropdown-item" href="#" data-frequency="twice_daily"
                                                            data-label="วันละ 2 ครั้ง">วันละ 2 ครั้ง</a></li>
                                                    <li><a class="dropdown-item" href="#"
                                                            data-frequency="every_other_day"
                                                            data-label="วันเว้นวัน">วันเว้นวัน</a></li>
                                                    <li><a class="dropdown-item" href="#" data-frequency="weekly"
                                                            data-label="สัปดาห์ละครั้ง">สัปดาห์ละครั้ง</a></li>
                                                </ul>
                                                <input type="hidden" name="frequency" class="treatment-frequency"
                                                    value="" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">ปริมาณยาทั้งสิ้น (มล.)</label>
                                            <input type="number" class="form-control" id="total_doses" disabled
                                                style="background-color: #f8f9fa;">
                                            <small class="form-text text-muted">ขนาด/ตัว × จำนวนหมู × ความถี่ × วัน =
                                                ปริมาณรวม</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- วันเวลาและระยะเวลา -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">วันเวลาและระยะเวลาการรักษา</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <!-- วันที่เริ่มตามแผน -->
                                            <div class="mb-3">
                                                <label for="planned_start_date" class="form-label">วันที่เริ่มตามแผน <span
                                                        class="text-danger">*</span></label>
                                                <input type="date" class="form-control" id="planned_start_date"
                                                    name="planned_start_date" required>
                                                <div class="btn-group btn-group-sm mt-2 w-100" role="group">
                                                    <button type="button" class="btn btn-secondary quick-date"
                                                        data-days="0" style="flex: 1;">วันนี้</button>
                                                    <button type="button" class="btn btn-secondary quick-date"
                                                        data-days="1" style="flex: 1;">พรุ่งนี้</button>
                                                    <button type="button" class="btn btn-secondary quick-date"
                                                        data-days="7" style="flex: 1;">สัปดาห์หน้า</button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <!-- ระยะเวลาตามแผน -->
                                            <div class="mb-3">
                                                <label for="planned_duration" class="form-label">ระยะเวลาตามแผน (วัน) <span
                                                        class="text-danger">*</span></label>
                                                <input type="number" class="form-control" id="planned_duration"
                                                    name="planned_duration" min="1" required>
                                                <small class="form-text text-muted">วันที่สิ้นสุดตามแผน: <strong
                                                        id="planned_end_date">-</strong></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- สถานะและรายละเอียดอื่นๆ -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">สถานะและรายละเอียดเพิ่มเติม</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="treatmentStatus" class="form-label">สถานะการรักษา <span
                                                        class="text-danger">*</span></label>
                                                <div class="dropdown">
                                                    <button
                                                        class="btn btn-primary dropdown-toggle w-100 text-start treatment-status-btn"
                                                        type="button" id="statusDropdown" data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                        เลือกสถานะ
                                                    </button>
                                                    <ul class="dropdown-menu w-100" aria-labelledby="statusDropdown">
                                                        <li><a class="dropdown-item" href="#" data-status="pending"
                                                                data-label="รอดำเนินการ">รอดำเนินการ</a></li>
                                                        <li><a class="dropdown-item" href="#" data-status="ongoing"
                                                                data-label="กำลังดำเนินการ">กำลังดำเนินการ</a></li>
                                                        <li><a class="dropdown-item" href="#" data-status="completed"
                                                                data-label=" เสร็จสิ้น"> เสร็จสิ้น</a></li>
                                                        <li><a class="dropdown-item" href="#" data-status="stopped"
                                                                data-label=" หยุดการรักษา"> หยุดการรักษา</a></li>
                                                    </ul>
                                                    <input type="hidden" name="treatment_status" class="treatment-status"
                                                        value="" required>
                                                </div>
                                                <small class="text-muted d-block mt-2">
                                                     เมื่อเปลี่ยนเป็น <strong>เสร็จสิ้น</strong> หรือ
                                                    <strong>หยุดการรักษา</strong>
                                                    วันที่สิ้นสุดจะถูกบันทึกอัตโนมัติ
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="treatmentNote" class="form-label">หมายเหตุ</label>
                                                <textarea class="form-control" id="treatmentNote" name="note" rows="3"
                                                    placeholder="บันทึกเพิ่มเติม (ถ้ามี)"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="attachment" class="form-label">เอกสารแนบ</label>
                                        <input type="file" class="form-control" id="attachment" name="attachment"
                                            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                        <small class="form-text text-muted">แนบใบสั่งสัตวแพทย์หรือเอกสารที่เกี่ยวข้อง
                                            (ขนาดไม่เกิน 5 MB)</small>
                                    </div>

                                    {{--  Treatment Details Table Container (For viewing details) --}}
                                    <div id="treatmentDetailsTableContainer" style="display: none;"></div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-info" id="viewPenDetailsBtn" style="display: none;">
                            <i class="bi bi-file-earmark-text"></i> ดูรายละเอียดคอก
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                        <button type="button" class="btn btn-primary" id="saveTreatmentBtn">บันทึก</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteTreatmentModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">ยืนยันการลบ</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>คุณแน่ใจหรือไม่ว่าต้องการลบรายการนี้?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">ลบ</button>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
            {{-- ✅ Import modular JavaScript for Treatments --}}
            <script type="module">
                import {
                    initTreatmentsModule
                } from '/js/treatments/index.js';
                // Module will auto-initialize on page load
            </script>

            <script>
                // ✅ HOIST FUNCTIONS TO GLOBAL SCOPE (before DOMContentLoaded)
                // ✅ Wrapper functions that use the centralized labels from blade
                // These call the same logic but keep compatibility with inline code
                function getFrequencyLabel(freq) {
                    const labels = {
                        'once': 'ครั้งเดียว',
                        'daily': 'วันละครั้ง',
                        'twice_daily': 'วันละ 2 ครั้ง',
                        'every_other_day': 'วันเว้นวัน',
                        'weekly': 'สัปดาห์ละครั้ง'
                    };
                    return labels[freq] || '';
                }

                function getStatusLabel(status) {
                    const labels = {
                        'pending': 'รอดำเนินการ',
                        'ongoing': 'กำลังดำเนินการ',
                        'completed': 'เสร็จสิ้น',
                        'stopped': 'หยุดการรักษา'
                    };
                    return labels[status] || '';
                }

                function showSnackbar(msg, type = 'error') {
                    let box = document.getElementById('snackbarContainer');
                    if (!box) {
                        box = document.createElement('div');
                        box.id = 'snackbarContainer';
                        box.style =
                        `position:fixed;bottom:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:8px;`;
                        document.body.appendChild(box);
                    }
                    const div = document.createElement('div');
                    div.style = `
            padding:14px 20px;border-radius:6px;
            background:${type === 'success' ? '#28a745' : '#dc3545'};
            color:white;min-width:260px;font-weight:500;
        `;
                    div.textContent = msg;
                    box.appendChild(div);
                    setTimeout(() => div.remove(), 3500);
                }

                function createViewPanel() {
                    const panel = document.createElement('div');
                    panel.id = 'treatmentViewPanel';
                    panel.innerHTML = `
                        <div class="row g-0">
                            <div class="col-md-6 border-end px-4 py-3">
                                <h6 class="text-primary mb-3"><i class="bi bi-info-circle me-2"></i>ข้อมูลการรักษา</h6>
                                <table class="table table-sm table-borderless table-secondary">
                                    <tbody id="viewPanelInfo">
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6 px-4 py-3">
                                <h6 class="text-info mb-3"><i class="bi bi-list-check me-2"></i>รายละเอียดคอก</h6>
                                <div id="viewPanelDetails" style="max-height: 400px; overflow-y: auto;">
                                </div>
                            </div>
                        </div>
                    `;
                    document.getElementById('treatmentFormInModal').parentNode.insertBefore(panel, document.getElementById(
                        'treatmentFormInModal'));
                    return panel;
                }

                function renderTreatmentViewPanel(treatment, panel) {
                    const infoTable = panel.querySelector('#viewPanelInfo');
                    const freqLabel = getFrequencyLabel(treatment.frequency);
                    const statusLabel = getStatusLabel(treatment.treatment_status);
                    const statusClass = {
                        'pending': 'bg-warning text-dark',
                        'ongoing': 'bg-primary text-white',
                        'completed': 'bg-success text-white',
                        'stopped': 'bg-danger text-white'
                    } [treatment.treatment_status] || 'bg-secondary text-white';

                    infoTable.innerHTML = `
                        <tr>
                            <th style="width: 40%;">รหัส</th>
                            <td class="font-monospace"><strong>${treatment.id}</strong></td>
                        </tr>
                        <tr>
                            <th>รุ่น</th>
                            <td><strong>${treatment.batch ? treatment.batch.batch_code : '-'}</strong></td>
                        </tr>
                        <tr>
                            <th>ฟาร์ม</th>
                            <td>${treatment.batch && treatment.batch.farm ? treatment.batch.farm.farm_name : '-'}</td>
                        </tr>
                        <tr>
                            <th>โรค/อาการ</th>
                            <td><strong>${treatment.disease_name || '-'}</strong></td>
                        </tr>
                        <tr>
                            <th>ยา/วัคซีน</th>
                            <td><strong>${treatment.medicine_name || '-'}</strong></td>
                        </tr>
                        <tr>
                            <th>โดส</th>
                            <td>${treatment.dosage || '-'}</td>
                        </tr>
                        <tr>
                            <th>ความถี่</th>
                            <td>${freqLabel}</td>
                        </tr>
                        <tr>
                            <th>ระยะเวลา</th>
                            <td>${treatment.planned_duration || 0} วัน</td>
                        </tr>
                        <tr>
                            <th>วันที่วางแผน</th>
                            <td>${treatment.planned_start_date ? new Date(treatment.planned_start_date).toLocaleDateString('th-TH') : '-'}</td>
                        </tr>
                        <tr>
                            <th>สถานะ</th>
                            <td><span class="badge ${statusClass}">${statusLabel}</span></td>
                        </tr>
                        ${treatment.note ? `<tr>
                                        <th>หมายเหตุ</th>
                                        <td style="word-break: break-word;">${treatment.note}</td>
                                    </tr>` : ''}
                    `;

                    const detailsDiv = panel.querySelector('#viewPanelDetails');
                    if (treatment.details && treatment.details.length > 0) {
                        let detailsHTML = '<table class="table table-sm table-striped" style="font-size: 0.9rem;">';
                        detailsHTML +=
                            '<thead class="table-light"><tr><th class="text-center" style="width: 25%;">เล้า</th><th class="text-center" style="width: 25%;">คอก</th><th class="text-end" style="width: 25%;">ปริมาณ (ml)</th><th class="text-center" style="width: 25%;">วันที่</th></tr></thead><tbody>';

                        treatment.details.forEach(detail => {
                            const barnCode = detail.barn ? detail.barn.barn_code : '-';
                            const penCode = detail.pen ? detail.pen.pen_code : '-';
                            const qty = parseFloat(detail.quantity_used || 0).toFixed(1);
                            const date = detail.treatment_date ? new Date(detail.treatment_date).toLocaleDateString(
                                'th-TH') : '-';
                            detailsHTML +=
                                `<tr><td class="text-center">${barnCode}</td><td class="text-center">${penCode}</td><td class="text-end fw-bold">${qty}</td><td class="text-center small">${date}</td></tr>`;
                        });

                        detailsHTML += '</tbody></table>';
                        const totalQty = treatment.details.reduce((sum, d) => sum + parseFloat(d.quantity_used || 0), 0);
                        detailsHTML +=
                            `<div class="alert alert-info p-2 mb-0 small"><strong>รวมปริมาณ:</strong> ${totalQty.toFixed(1)} ml</div>`;

                        detailsDiv.innerHTML = detailsHTML;
                    } else {
                        detailsDiv.innerHTML = '<div class="alert alert-warning small mb-0">ไม่พบรายละเอียด</div>';
                    }
                }

                function displayTreatmentDetails(treatment, mode = 'view') {
                    console.log(`📺 [Treatments] Displaying treatment in ${mode} mode`, treatment);

                    const form = document.getElementById('treatmentFormInModal');
                    const viewPanel = document.getElementById('treatmentViewPanel') || createViewPanel();

                    form.style.display = mode === 'edit' ? 'block' : 'none';
                    viewPanel.style.display = mode === 'view' ? 'block' : 'none';

                    if (mode === 'view') {
                        renderTreatmentViewPanel(treatment, viewPanel);
                    }

                    document.getElementById('treatmentId').value = treatment.id;
                    document.getElementById('treatmentFarmId').value = treatment.farm_id || '';
                    document.getElementById('treatmentBatchId').value = treatment.batch_id || '';
                    document.getElementById('treatmentDiseaseName').value = treatment.disease_name || '';
                    document.querySelector('.treatment-medicine-name').value = treatment.medicine_name || '';
                    document.querySelector('.treatment-medicine-code').value = treatment.medicine_code || '';
                    document.getElementById('dosage').value = treatment.dosage || '';
                    document.querySelector('.treatment-frequency').value = treatment.frequency || '';
                    document.querySelector('.treatment-status').value = treatment.treatment_status || '';
                    document.getElementById('planned_start_date').value = treatment.planned_start_date || '';
                    document.getElementById('planned_duration').value = treatment.planned_duration || '';
                    document.getElementById('treatmentNote').value = treatment.note || '';

                    if (treatment.batch && treatment.batch.farm_id) {
                        document.getElementById('treatmentFarmDropdownLabel').textContent = 'ฟาร์ม #' + treatment.batch.farm_id;
                    }
                    if (treatment.batch && treatment.batch.batch_code) {
                        document.getElementById('treatmentBatchDropdownLabel').textContent = treatment.batch.batch_code;
                    }
                    document.querySelector('.treatment-medicine-dropdown-btn').textContent = treatment.medicine_name ||
                        'เลือกยา/วัคซีน';
                    document.querySelector('.treatment-frequency-btn').textContent = getFrequencyLabel(treatment.frequency) ||
                        'เลือกความถี่';
                    document.querySelector('.treatment-status-btn').textContent = getStatusLabel(treatment.treatment_status) ||
                        'เลือกสถานะ';

                    document.getElementById('pen_selection_container').style.display = mode === 'edit' ? 'block' : 'none';
                    document.getElementById('select_all_treatment_items').disabled = mode === 'view';

                    if (mode === 'view') {
                        document.querySelectorAll(
                                '#treatmentFormInModal input, #treatmentFormInModal textarea, #treatmentFormInModal select')
                            .forEach(input => {
                                input.disabled = true;
                            });
                        // ✅ Only disable modal dropdowns, NOT filter dropdowns
                        document.querySelectorAll('#treatmentFormInModal .dropdown-toggle').forEach(btn => btn.disabled = true);
                        document.getElementById('viewPenDetailsBtn').style.display = 'inline-block';
                    } else if (mode === 'edit') {
                        document.querySelectorAll(
                                '#treatmentFormInModal input, #treatmentFormInModal textarea, #treatmentFormInModal select')
                            .forEach(input => {
                                if (['planned_start_date', 'treatmentNote'].includes(input.id)) {
                                    input.disabled = false;
                                } else {
                                    input.disabled = true;
                                }
                            });

                        // ✅ Only disable modal dropdowns, NOT filter dropdowns
                        document.querySelectorAll('#treatmentFormInModal .dropdown-toggle').forEach(btn => {
                            if (btn.classList.contains('treatment-status-btn')) {
                                btn.disabled = false;
                            } else {
                                btn.disabled = true;
                            }
                        });

                        document.getElementById('viewPenDetailsBtn').style.display = 'none';
                    }

                    const saveBtn = document.getElementById('saveTreatmentBtn');
                    saveBtn.style.display = mode === 'view' ? 'none' : 'block';

                    if (mode === 'edit' && treatment.details && treatment.details.length > 0) {
                        const detailPenIds = treatment.details.map(d => d.pen_id);
                        document.querySelectorAll('#treatmentPenTableBody input[data-pen-id]').forEach(checkbox => {
                            if (detailPenIds.includes(parseInt(checkbox.dataset.penId))) {
                                checkbox.checked = true;
                            }
                        });
                    }

                    window.currentTreatmentData = treatment;
                    console.log(`✅ [Treatments] Treatment displayed in ${mode} mode`);
                }

                document.addEventListener('DOMContentLoaded', function() {

                    document.querySelectorAll('.dropdown-item[data-farm-id]').forEach(item => {
                        item.addEventListener('click', async function(e) {
                            e.preventDefault();
                            const farmId = this.dataset.farmId;
                            const farmBtn = document.getElementById('farmFilterBtn');
                            farmBtn.dataset.farmId = farmId;
                            farmBtn.innerHTML =
                                `<i class="bi bi-building"></i> ${this.textContent.trim()}`;

                            const batchBtn = document.getElementById('batchFilterBtn');
                            batchBtn.dataset.batchId = '';
                            batchBtn.innerHTML = `<i class="bi bi-diagram-3"></i> รุ่นทั้งหมด`;

                            const url = new URL(window.location);
                            url.searchParams.set('farm_id', farmId);
                            window.history.pushState({}, '', url);

                            await loadBatches(farmId);
                        });
                    });

                    async function loadBatches(farmId) {
                        try {
                            const res = await fetch(`/api/farms/${farmId}/batches`);
                            const batches = await res.json();
                            const menu = document.querySelector('#batchFilterBtn + .dropdown-menu');

                            menu.innerHTML =
                                `<li><a class="dropdown-item" href="#" data-batch-id="">รุ่นทั้งหมด</a></li>`;

                            batches.forEach(b => {
                                menu.innerHTML += `
                    <li><a class="dropdown-item" href="#" data-batch-id="${b.id}">${b.batch_code}</a></li>
                `;
                            });

                            attachBatchEventListeners();
                        } catch (e) {
                            showSnackbar('ไม่สามารถโหลดข้อมูลรุ่นได้', 'error');
                        }
                    }

                    function attachBatchEventListeners() {
                        document.querySelectorAll('.dropdown-item[data-batch-id]').forEach(item => {
                            item.addEventListener('click', e => {
                                e.preventDefault();
                                const batchId = item.dataset.batchId;
                                const btn = document.getElementById('batchFilterBtn');
                                btn.dataset.batchId = batchId;
                                btn.innerHTML = `<i class="bi bi-diagram-3"></i> ${item.textContent}`;
                            });
                        });
                    }

                    /* -------------------- Treatment Modal -------------------- */

                    // ✅ GLOBAL scope so row click can access it
                    window.treatmentModal = new bootstrap.Modal('#treatmentFormModal');
                    const form = document.getElementById('treatmentFormInModal');

                    // Farm Dropdown Elements
                    const treatmentFarmDropdownBtn = document.getElementById('treatmentFarmDropdownBtn');
                    const treatmentFarmDropdownLabel = document.getElementById('treatmentFarmDropdownLabel');
                    const treatmentFarmDropdownMenu = document.getElementById('treatmentFarmDropdownMenu');
                    const treatmentFarmId = document.getElementById('treatmentFarmId');

                    // Batch Dropdown Elements
                    const treatmentBatchDropdownBtn = document.getElementById('treatmentBatchDropdownBtn');
                    const treatmentBatchDropdownLabel = document.getElementById('treatmentBatchDropdownLabel');
                    const treatmentBatchDropdownMenu = document.getElementById('treatmentBatchDropdownMenu');
                    const treatmentBatchId = document.getElementById('treatmentBatchId');

                    document.getElementById('openTreatmentFormBtn').addEventListener('click', () => {
                        console.log('🎯 [Treatments Modal] Opening treatment form modal');

                        form.reset();
                        const treatmentIdInput = document.getElementById('treatmentId');
                        if (treatmentIdInput) treatmentIdInput.value = "";
                        form.querySelector('input[name="treatment_level"][value="barn"]').checked =
                        true; // ✅ Default: Barn level
                        document.getElementById('planned_start_date').valueAsDate = new Date();
                        document.getElementById('planned_duration').value = "";

                        // Reset frequency dropdown
                        document.querySelector('.treatment-frequency').value = "";
                        document.querySelector('.treatment-frequency-btn').textContent = "เลือกความถี่";

                        // Reset status dropdown
                        document.querySelector('.treatment-status').value = "";
                        document.querySelector('.treatment-status-btn').textContent = "เลือกสถานะ";

                        // Reset total_doses
                        document.getElementById('total_doses').value = "";

                        // Reset dropdowns
                        treatmentFarmDropdownLabel.textContent = '-- เลือกฟาร์ม --';
                        treatmentFarmId.value = '';
                        treatmentBatchDropdownLabel.textContent = '-- เลือกฟาร์มก่อน --';
                        treatmentBatchId.value = '';
                        treatmentBatchDropdownBtn.disabled = true;

                        console.log('✅ [Treatments Modal] Form reset complete - default to barn level');
                        treatmentModal.show();
                    });

                    // Farm Dropdown Event Handler
                    treatmentFarmDropdownMenu.addEventListener('click', function(e) {
                        if (e.target.classList.contains('dropdown-item')) {
                            e.preventDefault();
                            const farmId = e.target.getAttribute('data-farm-id');
                            const farmName = e.target.textContent.trim();

                            console.log('🏭 [Treatments Modal] Selected farm:', {
                                farmId,
                                farmName
                            });

                            // Update farm selection
                            treatmentFarmId.value = farmId;
                            treatmentFarmDropdownLabel.textContent = farmName;

                            // Reset batch dropdown
                            treatmentBatchId.value = '';
                            treatmentBatchDropdownLabel.textContent = farmId ? '-- เลือกรุ่น --' :
                                '-- เลือกฟาร์มก่อน --';
                            treatmentBatchDropdownBtn.disabled = !farmId;
                            treatmentBatchDropdownMenu.innerHTML = '';

                            // Load batches and medicines if farm is selected
                            if (farmId) {
                                console.log('✅ [Treatments Modal] Farm selected, loading batches and medicines...');
                                loadBatchesForFarm(farmId);
                                loadMedicinesForFarm(farmId);
                            } else {
                                // Reset pen table and medicines
                                console.log('🔄 [Treatments Modal] Resetting pen table and medicines');
                                document.getElementById('treatmentPenTableBody').innerHTML =
                                    '<tr><td colspan="4" class="text-center py-3">กรุณาเลือกฟาร์มและรุ่นก่อน</td></tr>';
                                document.querySelector('.treatment-medicine-dropdown-menu').innerHTML = '';
                                document.querySelector('.treatment-medicine-dropdown-btn').textContent =
                                    'เลือกยา/วัคซีน';
                            }
                        }
                    });

                    // Load batches for selected farm
                    async function loadBatchesForFarm(farmId) {
                        try {
                            console.log('🔄 [Treatments Modal] Loading batches for farm:', farmId);

                            const res = await fetch(`/api/farms/${farmId}/batches`);
                            console.log('📡 [Treatments Modal] API Response status:', res.status);

                            if (!res.ok) {
                                throw new Error(`HTTP error! status: ${res.status}`);
                            }

                            const contentType = res.headers.get('content-type');
                            console.log('📝 [Treatments Modal] Content-Type:', contentType);

                            if (!contentType || !contentType.includes('application/json')) {
                                const text = await res.text();
                                console.error('❌ [Treatments Modal] Expected JSON but got:', text.substring(0, 200));
                                throw new Error('API returned non-JSON response');
                            }

                            const data = await res.json();
                            console.log('✅ [Treatments Modal] Batches loaded:', data);

                            treatmentBatchDropdownMenu.innerHTML = '';
                            // Handle both response formats (with data wrapper and direct array)
                            const batchesArray = data.data || data;
                            batchesArray.forEach(batch => {
                                const li = document.createElement('li');
                                li.innerHTML = `
                                    <a class="dropdown-item" href="#" data-batch-id="${batch.id}">
                                        <i class="bi bi-diagram-3 me-2"></i>${batch.code}
                                    </a>
                                `;
                                treatmentBatchDropdownMenu.appendChild(li);
                            });

                            // Add click handlers to batch items
                            treatmentBatchDropdownMenu.querySelectorAll('.dropdown-item').forEach(item => {
                                item.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    const batchId = this.getAttribute('data-batch-id');
                                    const batchName = this.textContent.trim();

                                    console.log('📋 [Treatments Modal] Selected batch:', {
                                        batchId,
                                        batchName
                                    });

                                    treatmentBatchId.value = batchId;
                                    treatmentBatchDropdownLabel.textContent = batchName;

                                    // Load pen table when both farm and batch are selected
                                    const farmId = treatmentFarmId.value;
                                    if (farmId && batchId) {
                                        // ✅ Get current treatment level
                                        const level = document.querySelector(
                                            'input[name="treatment_level"]:checked')?.value || 'pen';
                                        loadPenTable(farmId, batchId, level);
                                    }
                                });
                            });

                        } catch (error) {
                            console.error('❌ [Treatments Modal] Error loading batches:', error);
                            console.error('📊 [Treatments Modal] Stack:', error.stack);
                            showSnackbar(`โหลดรุ่นไม่สำเร็จ: ${error.message}`, 'error');
                        }
                    }

                    /* -------------------- Load Medicines for Farm -------------------- */
                    /**
                     * โหลดรายการยา/วัคซีนจาก storehouse ของฟาร์ม
                     *
                     * @param farmId - Farm ID
                     *
                     * ✅ Filter: item_type = 'medicine' และ status != 'cancelled'
                     * ✅ Display: dropdown menu พร้อมเลือก
                     */
                    async function loadMedicinesForFarm(farmId) {
                        try {
                            console.log('💊 [Treatments Modal] Loading medicines for farm:', farmId);

                            const res = await fetch(`/api/medicines?farm_id=${farmId}`);
                            console.log('📡 [Treatments Modal] Medicines API Response status:', res.status);

                            if (!res.ok) {
                                throw new Error(`HTTP error! status: ${res.status}`);
                            }

                            const medicines = await res.json();
                            console.log('✅ [Treatments Modal] Medicines loaded:', medicines);

                            const medicineDropdownMenu = document.querySelector('.treatment-medicine-dropdown-menu');
                            medicineDropdownMenu.innerHTML = '';

                            // Handle both response formats (with data wrapper and direct array)
                            const medicinesArray = medicines.data || medicines;
                            if (!medicinesArray || medicinesArray.length === 0) {
                                console.log('⚠️ [Treatments Modal] No medicines found for farm ' + farmId);
                                medicineDropdownMenu.innerHTML = `
                                    <li><a class="dropdown-item disabled text-muted" href="#">
                                        <i class="bi bi-info-circle me-2"></i>ไม่พบยา/วัคซีน
                                    </a></li>
                                `;
                                return;
                            }

                            medicinesArray.forEach(medicine => {
                                const li = document.createElement('li');
                                const stockStatus = medicine.stock > 0 ?
                                    `<small class="text-success ms-2">(คงเหลือ: ${medicine.stock} ${medicine.unit})</small>` :
                                    `<small class="text-danger ms-2">(หมด)</small>`;

                                // ✅ Disable item if stock is 0 or less
                                const isOutOfStock = medicine.stock <= 0;
                                const disabledClass = isOutOfStock ? 'disabled text-muted' : '';
                                const pointerClass = isOutOfStock ? 'pe-none' : '';

                                li.innerHTML = `
                                    <a class="dropdown-item ${disabledClass} ${pointerClass}" href="#"
                                       data-medicine-id="${medicine.id}"
                                       data-medicine-code="${medicine.code}"
                                       data-medicine-name="${medicine.name}"
                                       data-medicine-unit="${medicine.unit}"
                                       ${isOutOfStock ? 'onclick="return false;"' : ''}>
                                        <i class="bi bi-capsule me-2"></i>${medicine.name} ${isOutOfStock ? '❌' : '✓'} ${stockStatus}
                                    </a>
                                `;
                                medicineDropdownMenu.appendChild(li);
                            });

                            // Add click handlers to medicine items (only for available ones)
                            medicineDropdownMenu.querySelectorAll('.dropdown-item:not(.disabled)').forEach(item => {
                                item.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    const medicineId = this.getAttribute('data-medicine-id');
                                    const medicineCode = this.getAttribute('data-medicine-code');
                                    const medicineName = this.getAttribute('data-medicine-name');
                                    const medicineUnit = this.getAttribute('data-medicine-unit');

                                    console.log('💊 [Treatments Modal] Selected medicine:', {
                                        medicineId,
                                        medicineCode,
                                        medicineName,
                                        medicineUnit
                                    });

                                    // Update hidden fields and dropdown label with unit
                                    document.querySelector('.treatment-medicine-name').value =
                                        medicineName;
                                    document.querySelector('.treatment-medicine-code').value =
                                        medicineCode;
                                    document.querySelector('.treatment-medicine-dropdown-btn span')
                                        .textContent = medicineName + ' (' + medicineUnit + ')';
                                });
                            });

                            console.log('✅ [Treatments Modal] Medicines dropdown populated with', medicinesArray.length,
                                'items');

                        } catch (error) {
                            console.error('❌ [Treatments Modal] Error loading medicines:', error);
                            console.error('📊 [Treatments Modal] Stack:', error.stack);
                            showSnackbar(`โหลดรายการยาผิดพลาด: ${error.message}`, 'error');
                        }
                    }

                    /* -------------------- Load Pen Table -------------------- */
                    /**
                     * โหลด checkbox table สำหรับเลือก barn/pen
                     *
                     * @param farmId - Farm ID
                     * @param batchId - Batch ID
                     * @param level - 'barn' หรือ 'pen' (จาก radio button)
                     *
                     * ✅ level = 'barn': แสดงแค่ level เล้า (group by barn)
                     * ✅ level = 'pen': แสดง level เล้า + คอก (detailed)
                     */
                    async function loadPenTable(farmId, batchId, level = 'pen') {
                        try {
                            console.log('🔄 [Treatments Modal] Loading pen table - farm:', farmId, 'batch:', batchId,
                                'level:', level);

                            // ✅ Use centralized BarnPenSelectionService API
                            const res = await fetch(`/api/barn-pen/selection?farm_id=${farmId}&batch_id=${batchId}`);
                            console.log('📡 [Treatments Modal] Pen API Response status:', res.status);

                            if (!res.ok) {
                                throw new Error(`HTTP error! status: ${res.status}`);
                            }

                            const response = await res.json();
                            console.log('✅ [Treatments Modal] API Response:', response);

                            if (!response.success) {
                                throw new Error(response.message || 'ไม่สามารถโหลดข้อมูลเล้า/คอกได้');
                            }

                            const data = response.data;
                            const tbody = document.getElementById('treatmentPenTableBody');

                            if (!data || data.length === 0) {
                                console.log('⚠️ [Treatments Modal] No pens found for this batch');
                                tbody.innerHTML =
                                    `<tr><td colspan="4" class="text-center py-4 text-muted">
                                        <i class="bi bi-info-circle me-2"></i>ไม่พบข้อมูลเล้า/คอก
                                    </td></tr>`;
                                return;
                            }

                            console.log('📊 [Treatments Modal] Processing ' + data.length + ' pens - level:', level);

                            tbody.innerHTML = "";
                            let lastBarn = null;
                            let barnRowCount = {};

                            // ✅ Count barns first
                            data.forEach(pen => {
                                if (!barnRowCount[pen.barn_id]) {
                                    barnRowCount[pen.barn_id] = 0;
                                }
                                if (level === 'barn') {
                                    // Barn level: count as 1 row per barn
                                    if (pen.barn_id !== lastBarn) {
                                        barnRowCount[pen.barn_id] = 1;
                                        lastBarn = pen.barn_id;
                                    }
                                } else {
                                    // Pen level: count all pens per barn
                                    barnRowCount[pen.barn_id]++;
                                }
                            });

                            lastBarn = null;
                            let barnCheckboxCreated = {};

                            // ✅ Render rows based on level
                            if (level === 'barn') {
                                // BARN LEVEL: One row per barn
                                // Always 4 columns: checkbox | barn | (pen-hidden) | count
                                const barnGroups = {};
                                data.forEach(pen => {
                                    if (!barnGroups[pen.barn_id]) {
                                        barnGroups[pen.barn_id] = [];
                                    }
                                    barnGroups[pen.barn_id].push(pen);
                                });

                                Object.keys(barnGroups).forEach(barnId => {
                                    const barnPens = barnGroups[barnId];
                                    const tr = document.createElement('tr');
                                    tr.className = 'barn-row';
                                    tr.dataset.barnId = barnId;

                                    const barnCheckbox = document.createElement('input');
                                    barnCheckbox.type = 'checkbox';
                                    barnCheckbox.className = 'form-check-input form-check-input-sm';
                                    barnCheckbox.dataset.barnId = barnId;

                                    // Store all pen IDs for this barn
                                    const barnPenIds = barnPens.map(p => p.id);
                                    barnCheckbox.dataset.penIds = JSON.stringify(barnPenIds);

                                    // ✅ คำนวณจำนวนหมูรวมในเล้าและเก็บไว้
                                    const barnTotalPigs = barnPens.reduce((sum, pen) => sum + parseInt(pen
                                        .current_pig_count), 0);
                                    barnCheckbox.dataset.pigCount = barnTotalPigs;
                                    // ✅ Also set as explicit attribute for CSS selector
                                    barnCheckbox.setAttribute('data-pig-count', barnTotalPigs);

                                    // Add event listener to barn checkbox
                                    barnCheckbox.addEventListener('change', function() {
                                        // Toggle all pen checkboxes in this barn
                                        barnPens.forEach(pen => {
                                            const penCheckbox = document.querySelector(
                                                `input[data-pen-id="${pen.id}"]`);
                                            if (penCheckbox) {
                                                penCheckbox.checked = this.checked;
                                                // ✅ Trigger change event so other listeners know about it
                                                penCheckbox.dispatchEvent(new Event('change', {
                                                    bubbles: true
                                                }));
                                            }
                                        });
                                        // ✅ Recalculate total doses when barn selection changes
                                        calculateTotalDoses();
                                    });

                                    // TD 1: Checkbox
                                    const td1 = document.createElement('td');
                                    td1.className = 'text-center';
                                    td1.appendChild(barnCheckbox);

                                    // TD 2: Barn name
                                    const td2 = document.createElement('td');
                                    td2.className = 'fw-bold';
                                    td2.textContent = barnPens[0].barn_code;

                                    // TD 3: Pen column (always present, but hidden via CSS for barn level)
                                    const td3 = document.createElement('td');
                                    td3.style.display = 'none';

                                    // TD 4: Total pigs in barn
                                    const totalPigs = barnPens.reduce((sum, pen) => sum + parseInt(pen
                                        .current_pig_count), 0);
                                    const td4 = document.createElement('td');
                                    td4.className = 'text-center';
                                    td4.innerHTML = `<strong>${totalPigs}</strong>`;

                                    tr.appendChild(td1);
                                    tr.appendChild(td2);
                                    tr.appendChild(td3);
                                    tr.appendChild(td4);

                                    tbody.appendChild(tr);

                                    // ✅ CREATE HIDDEN CHECKBOX ROWS FOR EACH PEN IN THIS BARN
                                    barnPens.forEach(pen => {
                                        const penCheckbox = document.createElement('input');
                                        penCheckbox.type = 'checkbox';
                                        penCheckbox.className = 'pen-checkbox';
                                        penCheckbox.name = 'selected_pens[]';
                                        penCheckbox.value = pen.id;
                                        penCheckbox.dataset.penId = pen.id;
                                        penCheckbox.dataset.barnId = pen.barn_id;
                                        penCheckbox.dataset.pigCount = pen
                                        .current_pig_count; // ✅ เก็บจำนวนหมู
                                        penCheckbox.style.display = 'none'; // Hidden from view
                                        tbody.appendChild(penCheckbox); // Add to table but hidden
                                    });

                                    console.log('🏠 [Treatments Modal] Added barn:', barnPens[0].barn_code,
                                        '- total pigs:', totalPigs, '- pens:', barnPenIds);
                                });

                            } else if (level === 'pen') {
                                // PEN LEVEL: One row per pen
                                // Always 4 columns: checkbox | barn | pen | count
                                data.forEach((pen, i) => {
                                    const tr = document.createElement('tr');
                                    tr.className = 'pen-row';
                                    tr.dataset.penId = pen.id;
                                    tr.dataset.barnId = pen.barn_id;

                                    // TD 1: Checkbox
                                    const checkbox = document.createElement('input');
                                    checkbox.type = 'checkbox';
                                    checkbox.className = 'form-check-input form-check-input-sm pen-checkbox';
                                    checkbox.name = 'selected_pens[]';
                                    checkbox.value = pen.id;
                                    checkbox.dataset.penId = pen.id;
                                    checkbox.dataset.barnId = pen.barn_id;
                                    checkbox.dataset.pigCount = pen.current_pig_count; // ✅ เก็บจำนวนหมู

                                    const td1 = document.createElement('td');
                                    td1.className = 'text-center';
                                    td1.appendChild(checkbox);

                                    // TD 2: Barn (check if new barn, then apply bg-light)
                                    const td2 = document.createElement('td');
                                    td2.className = 'fw-bold';
                                    td2.textContent = pen.barn_code;

                                    if (pen.barn_id !== lastBarn) {
                                        td2.classList.add('bg-light');
                                        lastBarn = pen.barn_id;
                                    }

                                    // TD 3: Pen number
                                    const td3 = document.createElement('td');
                                    td3.textContent = pen.pen_number;

                                    // TD 4: Pig count
                                    const td4 = document.createElement('td');
                                    td4.className = 'text-center';
                                    td4.innerHTML = `<strong>${pen.current_pig_count}</strong>`;

                                    tr.appendChild(td1);
                                    tr.appendChild(td2);
                                    tr.appendChild(td3);
                                    tr.appendChild(td4);

                                    tbody.appendChild(tr);
                                });

                                console.log('🖼️ [Treatments Modal] Added', data.length, 'pens');
                            }

                            console.log('✅ [Treatments Modal] Pen table rendered successfully - level:', level);

                        } catch (error) {
                            console.error('❌ [Treatments Modal] Error loading pen table:', error);
                            console.error('📊 [Treatments Modal] Stack:', error.stack);
                            showSnackbar(`โหลดข้อมูลเล้า/คอกผิดพลาด: ${error.message}`, 'error');
                        }
                    } /* ================== Treatment Level Handler ================== */
                    // ✅ เมื่อเปลี่ยน treatment_level (barn/pen) ให้ update table columns
                    document.querySelectorAll('.treatment-level-radio').forEach(radio => {
                        radio.addEventListener('change', function() {
                            const level = this.value;
                            console.log('📊 [Treatments Modal] Treatment level changed:', level);

                            const penHeaderCol = document.getElementById('pen_header_col');
                            const hint = document.getElementById('level_hint');

                            if (level === 'barn') {
                                console.log(
                                    '🏠 [Treatments Modal] Showing barn level only - hide pen column');
                                penHeaderCol.style.display = 'none';
                                hint.textContent = 'เล้า';
                            } else if (level === 'pen') {
                                console.log(
                                    '🖼️ [Treatments Modal] Showing barn + pen level - show pen column');
                                penHeaderCol.style.display = '';
                                hint.textContent = 'เล้า และ คอก';
                            }

                            // Reload pen table ถ้า farm และ batch ถูกเลือกแล้ว
                            const farmId = treatmentFarmId.value;
                            const batchId = treatmentBatchId.value;
                            if (farmId && batchId) {
                                loadPenTable(farmId, batchId, level);
                            }
                        });
                    });

                    /* ================== Frequency Dropdown Handler ================== */
                    document.querySelectorAll('.treatment-frequency-btn').forEach(btn => {
                        btn.parentElement.querySelector('.dropdown-menu').addEventListener('click', function(e) {
                            if (e.target.classList.contains('dropdown-item')) {
                                e.preventDefault();
                                const frequency = e.target.getAttribute('data-frequency');
                                const label = e.target.getAttribute('data-label');

                                console.log('⏱️ [Treatments Modal] Selected frequency:', frequency);

                                document.querySelector('.treatment-frequency').value = frequency;
                                document.querySelector('.treatment-frequency-btn').textContent = label;

                                // Calculate total doses
                                calculateTotalDoses();
                            }
                        });
                    });

                    /* ================== Status Dropdown Handler ================== */
                    document.querySelectorAll('.treatment-status-btn').forEach(btn => {
                        btn.parentElement.querySelector('.dropdown-menu').addEventListener('click', function(e) {
                            if (e.target.classList.contains('dropdown-item')) {
                                e.preventDefault();
                                const status = e.target.getAttribute('data-status');
                                const label = e.target.getAttribute('data-label');

                                console.log('📌 [Treatments Modal] Selected status:', status);

                                document.querySelector('.treatment-status').value = status;
                                document.querySelector('.treatment-status-btn').textContent = label;
                            }
                        });
                    });

                    /* ================== Calculate Total Doses ================== */
                    function calculateTotalDoses() {
                        const dosage = parseFloat(document.getElementById('dosage').value) || 0;
                        const frequency = document.querySelector('.treatment-frequency').value;
                        const planned_duration = parseFloat(document.getElementById('planned_duration').value) || 1;

                        // ✅ Get current treatment level (barn or pen)
                        const level = document.querySelector('input[name="treatment_level"]:checked')?.value || 'pen';

                        let totalPigs = 0;
                        let selectedCheckboxes = [];

                        if (level === 'barn') {
                            // 🏠 BARN LEVEL: Count only barn checkboxes (not hidden pen checkboxes)
                            // ✅ Selector: input[data-barn-id] without pen-checkbox class = barn checkbox only
                            selectedCheckboxes = document.querySelectorAll(
                                '#treatmentPenTableBody input:checked[data-barn-id]:not(.pen-checkbox)');
                            console.log('🏠 [Treatments Modal] BARN LEVEL - Found', selectedCheckboxes.length,
                                'selected barns');
                        } else {
                            // 🖼️ PEN LEVEL: Count only visible pen checkboxes with class pen-checkbox
                            selectedCheckboxes = document.querySelectorAll(
                                '#treatmentPenTableBody input:checked.pen-checkbox');
                            console.log('🖼️ [Treatments Modal] PEN LEVEL - Found', selectedCheckboxes.length,
                                'selected pens');
                        }

                        selectedCheckboxes.forEach(checkbox => {
                            const pigCount = parseInt(checkbox.dataset.pigCount) || 0;
                            totalPigs += pigCount;
                            console.log('🐷 [Treatments Modal] Selected -',
                                checkbox.dataset.penId ? `Pen: ${checkbox.dataset.penId}` :
                                `Barn: ${checkbox.dataset.barnId}`,
                                '- Pigs:', pigCount,
                                '- checked:', checkbox.checked,
                                '- data-pig-count:', checkbox.dataset.pigCount);
                        });

                        let multiplier = 1;
                        if (frequency === 'daily') multiplier = 1;
                        else if (frequency === 'twice_daily') multiplier = 2;
                        else if (frequency === 'every_other_day') multiplier = 0.5;
                        else if (frequency === 'weekly') multiplier = 0.142857; // 1/7
                        // 'once' = 1 (default)

                        // ✅ สูตร: dosage × จำนวนหมู × ความถี่ × ระยะเวลา
                        const total = (dosage * totalPigs * multiplier * planned_duration).toFixed(2);

                        document.getElementById('total_doses').value = total;
                        console.log('💊 [Treatments Modal] Total doses:', total,
                            '(dosage:', dosage,
                            '× total_pigs:', totalPigs,
                            '× frequency multiplier:', multiplier,
                            '× duration:', planned_duration, ')');
                    }

                    // Update total_doses when dosage, frequency, planned_duration, or pen selection changes
                    document.getElementById('dosage').addEventListener('change', calculateTotalDoses);
                    document.getElementById('planned_duration').addEventListener('change', calculateTotalDoses);
                    document.getElementById('select_all_treatment_items').addEventListener('change', calculateTotalDoses);

                    // Listen to individual pen checkboxes AND barn checkboxes
                    document.addEventListener('change', function(e) {
                        // Check for pen-checkbox class OR barn checkbox with data-barn-id attribute
                        if (e.target.classList.contains('pen-checkbox') || e.target.dataset.barnId || e.target
                            .dataset.penId) {
                            console.log('✅ [Treatments Modal] Selection changed - recalculating total doses');
                            calculateTotalDoses();
                        }
                    });

                    /* ================== Quick Date Buttons ================== */
                    document.querySelectorAll('.quick-date').forEach(btn => {
                        btn.addEventListener('click', function(e) {
                            e.preventDefault();
                            const days = parseInt(this.getAttribute('data-days'));
                            const date = new Date();
                            date.setDate(date.getDate() + days);
                            const dateStr = date.toISOString().split('T')[0];
                            document.getElementById('planned_start_date').value = dateStr;
                            console.log('📅 [Treatments Modal] Set planned_start_date to:', dateStr);

                            // Calculate and update planned_end_date display
                            updatePlannedEndDate();
                        });
                    });

                    /* ================== Update Planned End Date Display ================== */
                    function updatePlannedEndDate() {
                        const startDate = document.getElementById('planned_start_date').value;
                        const duration = parseInt(document.getElementById('planned_duration').value) || 0;

                        if (startDate && duration > 0) {
                            const start = new Date(startDate);
                            start.setDate(start.getDate() + duration);
                            const endDate = start.toISOString().split('T')[0];

                            // Convert to Thai format d/m/y
                            const [year, month, day] = endDate.split('-');
                            const thaiDate = `${day}/${month}/${year}`;

                            document.getElementById('planned_end_date').textContent = thaiDate;
                            console.log('📅 [Treatments Modal] Planned end date:', thaiDate, '(', endDate, ')');
                        }
                    }

                    // Update planned_end_date when planned_start_date or planned_duration changes
                    document.getElementById('planned_start_date').addEventListener('change', updatePlannedEndDate);
                    document.getElementById('planned_duration').addEventListener('change', updatePlannedEndDate);

                    /* ================== Select All Checkbox Handler ================== */
                    document.getElementById('select_all_treatment_items').addEventListener('change', function() {
                        console.log('☑️ [Treatments Modal] Select all:', this.checked);
                        document.querySelectorAll('#treatmentPenTableBody input[type="checkbox"]').forEach(ch => {
                            ch.checked = this.checked;
                        });
                    });

                    /* -------------------- Save Treatment -------------------- */
                    document.getElementById('saveTreatmentBtn').addEventListener('click', async () => {
                        if (!form.checkValidity()) return form.reportValidity();
                        if (!document.querySelector('#treatmentPenTableBody input:checked'))
                            return alert("กรุณาเลือกอย่างน้อย 1 คอก");

                        const treatmentIdInput = document.getElementById('treatmentId');
                        const id = treatmentIdInput ? treatmentIdInput.value : "";
                        const formData = new FormData(form);
                        const url = id ? `/api/treatments/${id}` : '/api/treatments';
                        const method = id ? 'PUT' : 'POST';

                        // ✅ Get current treatment level (barn or pen)
                        const level = document.querySelector('input[name="treatment_level"]:checked')?.value ||
                            'pen';

                        // Collect pen_ids based on treatment level
                        let penIds = [];
                        if (level === 'barn') {
                            // 🏠 BARN LEVEL: Get all hidden pen checkboxes that correspond to selected barns
                            const selectedBarns = document.querySelectorAll(
                                '#treatmentPenTableBody input:checked[data-barn-id]');
                            const selectedBarnIds = Array.from(selectedBarns).map(cb => cb.dataset.barnId);

                            // Find all hidden pen checkboxes in selected barns
                            const allPenCheckboxes = document.querySelectorAll(
                                '#treatmentPenTableBody input.pen-checkbox');
                            allPenCheckboxes.forEach(checkbox => {
                                if (selectedBarnIds.includes(checkbox.dataset.barnId)) {
                                    penIds.push(checkbox.dataset.penId);
                                }
                            });
                            console.log('🏠 [Treatments Modal] BARN LEVEL - Selected barns:', selectedBarnIds,
                                '- Pen IDs:', penIds);
                        } else {
                            // 🖼️ PEN LEVEL: Get checked visible pen checkboxes
                            const selectedPens = document.querySelectorAll(
                                '#treatmentPenTableBody input:checked.pen-checkbox');
                            penIds = Array.from(selectedPens).map(checkbox => checkbox.dataset.penId).filter(
                                id => id !== undefined);
                            console.log('�️ [Treatments Modal] PEN LEVEL - Selected pen_ids:', penIds);
                        }

                        // Add pen_ids to formData
                        penIds.forEach((penId, index) => {
                            formData.append(`pen_ids[${index}]`, penId);
                        });
                        console.log('📋 [Treatments Modal] Added pen_ids to formData:', penIds);

                        // ✅ DEBUG: Log all formData entries
                        console.log('📦 [Treatments Modal] FormData entries:');
                        for (let [key, value] of formData.entries()) {
                            console.log(`  ${key}: ${value}`);
                        }

                        // ✅ Check if medicine_name and medicine_code are in formData
                        const medicineName = formData.get('medicine_name');
                        const medicineCode = formData.get('medicine_code');
                        console.log('💊 [Treatments Modal] Medicine Info:', {
                            medicine_name: medicineName,
                            medicine_code: medicineCode,
                            farm_id: formData.get('farm_id'),
                            batch_id: formData.get('batch_id')
                        });

                        // Auto-set actual_end_date when status is completed or stopped
                        const treatmentStatusElement = document.querySelector('input[name="treatment_status"]');
                        const treatmentStatus = treatmentStatusElement ? treatmentStatusElement.value : '';
                        console.log('💾 [Treatments Modal] Treatment status:', treatmentStatus);

                        if (treatmentStatus === 'completed' || treatmentStatus === 'stopped') {
                            const today = new Date().toISOString().split('T')[0]; // YYYY-MM-DD
                            formData.set('actual_end_date', today);
                            console.log('📅 [Treatments Modal] Auto-setting actual_end_date to:', today);
                        }

                        // Convert to MySQL datetime format: YYYY-MM-DD HH:mm:ss
                        const now = new Date();
                        const mysqlDateTime = now.getFullYear() + '-' +
                            String(now.getMonth() + 1).padStart(2, '0') + '-' +
                            String(now.getDate()).padStart(2, '0') + ' ' +
                            String(now.getHours()).padStart(2, '0') + ':' +
                            String(now.getMinutes()).padStart(2, '0') + ':' +
                            String(now.getSeconds()).padStart(2, '0');
                        formData.append('effective_date', mysqlDateTime);

                        try {
                            console.log('📤 [Treatments Modal] Sending request to:', url, 'method:', method);

                            // Get CSRF token safely
                            const csrfElement = document.querySelector('meta[name="csrf-token"]');
                            if (!csrfElement) {
                                throw new Error('CSRF token not found in page');
                            }

                            const res = await fetch(url, {
                                method,
                                headers: {
                                    'X-CSRF-TOKEN': csrfElement.content
                                },
                                body: formData
                            });

                            const data = await res.json();
                            console.log('📥 [Treatments Modal] Response:', data);
                            console.log('📊 [Treatments Modal] Response status:', res.status);

                            // ✅ Check both success flag AND response status
                            if (!res.ok || !data.success) {
                                const errorMsg = data.message || `Error: ${res.status} ${res.statusText}`;
                                showSnackbar(`❌ ${errorMsg}`, 'error');
                                console.error('❌ [Treatments Modal] Error:', errorMsg);
                                return; // ✅ CRITICAL: Stop execution here
                            }

                            // ✅ Only proceed if response is OK
                            // Log to Laravel backend
                            fetch('/api/log', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfElement.content
                                },
                                body: JSON.stringify({
                                    action: 'treatment_save',
                                    method: method,
                                    treatment_id: id || data.data?.treatment?.id,
                                    pen_count: penIds.length,
                                    status: treatmentStatus,
                                    message: `${method === 'POST' ? 'Created' : 'Updated'} treatment with ${penIds.length} pens`,
                                    timestamp: new Date().toISOString()
                                })
                            }).catch(e => console.warn('⚠️ Log failed:', e));

                            showSnackbar('✅ บันทึกสำเร็จ', 'success');
                            window.treatmentModal.hide(); // ✅ Use global reference
                            setTimeout(() => location.reload(), 1200);

                        } catch (e) {
                            console.error('❌ [Treatments Modal] Exception Error:', e);
                            showSnackbar(`❌ ข้อผิดพลาด: ${e.message}`, 'error');
                        }
                    });

                    /* ================== View Treatment ================== */
                    document.querySelectorAll('.view-treatment').forEach(btn => {
                        btn.addEventListener('click', async function(e) {
                            e.preventDefault();
                            const treatmentId = this.getAttribute('data-treatment-id');

                            try {
                                console.log('👁️ [Treatments] Viewing treatment ID:', treatmentId);

                                const res = await fetch(`/api/treatments/${treatmentId}`);
                                if (!res.ok) throw new Error(`HTTP ${res.status}`);

                                const response = await res.json();
                                if (!response.success) throw new Error(response.message);

                                const treatment = response.data;
                                console.log('📋 [Treatments] Treatment data:', treatment);

                                // ✅ Display in modal (read-only mode)
                                displayTreatmentDetails(treatment, 'view');
                                treatmentModal.show();

                            } catch (error) {
                                console.error('❌ [Treatments] Error loading treatment:', error);
                                showSnackbar(`ไม่สามารถโหลดข้อมูลการรักษา: ${error.message}`, 'error');
                            }
                        });
                    });

                    /* ================== Edit Treatment ================== */
                    document.querySelectorAll('.edit-treatment').forEach(btn => {
                        btn.addEventListener('click', async function(e) {
                            e.preventDefault();
                            const treatmentId = this.getAttribute('data-treatment-id');

                            try {
                                console.log('✏️ [Treatments] Editing treatment ID:', treatmentId);

                                const res = await fetch(`/api/treatments/${treatmentId}`);
                                if (!res.ok) throw new Error(`HTTP ${res.status}`);

                                const response = await res.json();
                                if (!response.success) throw new Error(response.message);

                                const treatment = response.data;
                                console.log('📋 [Treatments] Treatment data for edit:', treatment);

                                // ✅ Display in modal (edit mode)
                                displayTreatmentDetails(treatment, 'edit');
                                treatmentModal.show();

                            } catch (error) {
                                console.error('❌ [Treatments] Error loading treatment for edit:',
                                error);
                                showSnackbar(`ไม่สามารถโหลดข้อมูลการรักษา: ${error.message}`, 'error');
                            }
                        });
                    });

                    /* ================== View Pen Details Modal ================== */
                    document.getElementById('viewPenDetailsBtn').addEventListener('click', () => {
                        const treatment = window.currentTreatmentData;
                        if (!treatment || !treatment.details) {
                            alert('ไม่พบข้อมูลรายละเอียด');
                            return;
                        }

                        let detailedHTML = '<table class="table table-sm table-bordered">';
                        detailedHTML += '<thead class="table-primary"><tr>';
                        detailedHTML +=
                            '<th>เล้า</th><th>คอก</th><th>จำนวนหมู</th><th>ปริมาณใช้ (ml)</th><th>วันที่</th><th>หมายเหตุ</th>';
                        detailedHTML += '</tr></thead><tbody>';

                        treatment.details.forEach(detail => {
                            const barnCode = detail.barn ? detail.barn.barn_code : '-';
                            const penCode = detail.pen ? detail.pen.pen_code : '-';
                            const currentPigs = detail.current_quantity || 0;
                            const quantityUsed = parseFloat(detail.quantity_used || 0).toFixed(2);
                            const treatmentDate = detail.treatment_date ? new Date(detail.treatment_date)
                                .toLocaleDateString('th-TH') : '-';
                            const note = detail.note || '-';

                            console.log(
                                `📊 [Pen Details] Pen: ${penCode}, Current Pigs: ${currentPigs}, Qty Used: ${quantityUsed}`
                                );

                            detailedHTML += `<tr>
                                <td class="fw-bold">${barnCode}</td>
                                <td>${penCode}</td>
                                <td class="text-center"><strong>${currentPigs}</strong></td>
                                <td class="text-end"><strong>${quantityUsed}</strong></td>
                                <td>${treatmentDate}</td>
                                <td>${note}</td>
                            </tr>`;
                        });

                        detailedHTML += '</tbody></table>';

                        // Create modal
                        const modalDiv = document.createElement('div');
                        modalDiv.innerHTML = `
                            <div class="modal fade" id="penDetailsViewModal" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-info text-white">
                                            <h5 class="modal-title">📋 รายละเอียดการรักษาต่อคอก</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            ${detailedHTML}
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;

                        document.body.appendChild(modalDiv);
                        const modal = new bootstrap.Modal(modalDiv.querySelector('.modal'));
                        modal.show();

                        modalDiv.querySelector('.modal').addEventListener('hidden.bs.modal', () => {
                            modalDiv.remove();
                        });
                    });
                });

                /* ================== CSV Export ================== */
                document.getElementById('exportCsvBtn').addEventListener('click', function() {
                    console.log('📥 [Treatments] Exporting CSV');

                    // Build query string from current page filters
                    const params = new URLSearchParams(window.location.search);

                    // Add export-only date range if specified
                    const dateFrom = document.getElementById('exportDateFrom').value;
                    const dateTo = document.getElementById('exportDateTo').value;

                    if (dateFrom) {
                        params.set('export_date_from', dateFrom);
                    }
                    if (dateTo) {
                        params.set('export_date_to', dateTo);
                    }

                    const url = `{{ route('treatments.export.csv') }}?${params.toString()}`;

                    // Trigger download
                    window.location.href = url;
                });

                /* ================== Clickable Rows ================== */
                document.querySelectorAll('.treatment-row').forEach(row => {
                    row.addEventListener('click', async function(e) {
                        // Ignore clicks on buttons
                        if (e.target.closest('button')) {
                            return;
                        }

                        e.preventDefault();
                        const treatmentId = this.getAttribute('data-treatment-id');

                        try {
                            console.log('👁️ [Treatments] Opening treatment from row click:', treatmentId);

                            const res = await fetch(`/api/treatments/${treatmentId}`);
                            if (!res.ok) throw new Error(`HTTP ${res.status}`);

                            const response = await res.json();
                            if (!response.success) throw new Error(response.message);

                            const treatment = response.data;
                            console.log('📋 [Treatments] Treatment data:', treatment);

                            // Display in modal (read-only mode)
                            displayTreatmentDetails(treatment, 'view');
                            window.treatmentModal.show(); // ✅ Use global reference

                        } catch (error) {
                            console.error('❌ [Treatments] Error loading treatment:', error);
                            showSnackbar(`ไม่สามารถโหลดข้อมูลการรักษา: ${error.message}`, 'error');
                        }
                    });
                });
            </script>
        @endpush


        <style>
            @keyframes slideIn {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }

                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }

            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }

                to {
                    transform: translateX(400px);
                    opacity: 0;
                }
            }
        </style>

    @endsection
