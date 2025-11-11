@extends('layouts.admin')

@section('title', 'บันทึกประจำวัน')

@section('content')
    <div class="container my-5">
        <div class="card-header">
            <h1 class="text-center">บันทึกประจำวัน (Dairy Records)</h1>
        </div>
        <div class="py-2"></div>

        {{-- Toolbar --}}
        <div class="card-custom-secondary mb-3">
            <form method="GET" action="{{ route('dairy_records.index') }}" class="d-flex align-items-center gap-2 flex-wrap">

                <!-- Date Filter (Calendar) -->
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
                                href="{{ route('dairy_records.index', array_merge(request()->except('selected_date'), [])) }}">วันที่ทั้งหมด</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'today' ? 'active' : '' }}"
                                href="{{ route('dairy_records.index', array_merge(request()->all(), ['selected_date' => 'today'])) }}">วันนี้</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'this_week' ? 'active' : '' }}"
                                href="{{ route('dairy_records.index', array_merge(request()->all(), ['selected_date' => 'this_week'])) }}">สัปดาห์นี้</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'this_month' ? 'active' : '' }}"
                                href="{{ route('dairy_records.index', array_merge(request()->all(), ['selected_date' => 'this_month'])) }}">เดือนนี้</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'this_year' ? 'active' : '' }}"
                                href="{{ route('dairy_records.index', array_merge(request()->all(), ['selected_date' => 'this_year'])) }}">ปีนี้</a>
                        </li>
                    </ul>
                </div>

                <!-- Farm Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-building"></i>
                        {{ request('farm_id') ? $farms->find(request('farm_id'))->farm_name ?? 'ฟาร์ม' : 'ฟาร์มทั้งหมด' }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item farm-link" data-farm-id=""
                                href="{{ route('dairy_records.index', array_merge(request()->except(['farm_id', 'batch_id']), [])) }}">ฟาร์มทั้งหมด</a>
                        </li>
                        @foreach ($farms as $farm)
                            <li><a class="dropdown-item farm-link {{ request('farm_id') == $farm->id ? 'active' : '' }}"
                                    data-farm-id="{{ $farm->id }}"
                                    href="{{ route('dairy_records.index', array_merge(request()->except('batch_id'), ['farm_id' => $farm->id])) }}">
                                    {{ $farm->farm_name }}
                                </a></li>
                        @endforeach
                    </ul>
                </div>

                <!-- Batch Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-layers"></i>
                        {{ request('batch_id') ? $batches->find(request('batch_id'))->batch_code ?? 'รุ่น' : 'รุ่นทั้งหมด' }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item"
                                href="{{ route('dairy_records.index', array_merge(request()->except('batch_id'), [])) }}">รุ่นทั้งหมด</a>
                        </li>
                        @foreach ($batches as $batch)
                            <li><a class="dropdown-item {{ request('batch_id') == $batch->id ? 'active' : '' }}"
                                    href="{{ route('dairy_records.index', array_merge(request()->all(), ['batch_id' => $batch->id])) }}">
                                    {{ $batch->batch_code }}
                                </a></li>
                        @endforeach
                    </ul>
                </div>

                <!-- Barn Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-house"></i>
                        {{ request('barn_id') ? $barns->find(request('barn_id'))->barn_code ?? 'เล้า' : 'เล้าทั้งหมด' }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item"
                                href="{{ route('dairy_records.index', array_merge(request()->except('barn_id'), [])) }}">เล้าทั้งหมด</a>
                        </li>
                        @foreach ($barns as $barn)
                            <li><a class="dropdown-item {{ request('barn_id') == $barn->id ? 'active' : '' }}"
                                    href="{{ route('dairy_records.index', array_merge(request()->all(), ['barn_id' => $barn->id])) }}">
                                    {{ $barn->barn_code }}
                                </a></li>
                        @endforeach
                    </ul>
                </div>

                <!-- Type Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-filter"></i>
                        @if (request('type') == 'food')
                            อาหาร
                        @elseif(request('type') == 'treatment')
                            การรักษา
                        @elseif(request('type') == 'death')
                            หมูตาย
                        @else
                            ประเภททั้งหมด
                        @endif
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item"
                                href="{{ route('dairy_records.index', array_merge(request()->except('type'), [])) }}">ประเภททั้งหมด</a>
                        </li>
                        <li><a class="dropdown-item {{ request('type') == 'food' ? 'active' : '' }}"
                                href="{{ route('dairy_records.index', array_merge(request()->all(), ['type' => 'food'])) }}">อาหาร</a>
                        </li>
                        <li><a class="dropdown-item {{ request('type') == 'treatment' ? 'active' : '' }}"
                                href="{{ route('dairy_records.index', array_merge(request()->all(), ['type' => 'treatment'])) }}">การรักษา</a>
                        </li>
                        <li><a class="dropdown-item {{ request('type') == 'death' ? 'active' : '' }}"
                                href="{{ route('dairy_records.index', array_merge(request()->all(), ['type' => 'death'])) }}">หมูตาย</a>
                        </li>
                    </ul>
                </div>

                <!-- Sort Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-sort-down"></i>
                        @if (request('sort_by') == 'date')
                            @if (request('sort_order') == 'asc')
                                วันที่ (เก่า → ใหม่)
                            @else
                                วันที่ (ใหม่ → เก่า)
                            @endif
                        @else
                            เรียงตาม
                        @endif
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item {{ request('sort_by') == 'date' && request('sort_order') == 'desc' ? 'active' : '' }}"
                                href="{{ route('dairy_records.index', array_merge(request()->all(), ['sort_by' => 'date', 'sort_order' => 'desc'])) }}">วันที่
                                (ใหม่ → เก่า)</a></li>
                        <li><a class="dropdown-item {{ request('sort_by') == 'date' && request('sort_order') == 'asc' ? 'active' : '' }}"
                                href="{{ route('dairy_records.index', array_merge(request()->all(), ['sort_by' => 'date', 'sort_order' => 'asc'])) }}">วันที่
                                (เก่า → ใหม่)</a></li>
                    </ul>
                </div>

                <!-- Per Page -->
                @include('components.per-page-dropdown')



                <!-- Show Cancelled Batches Checkbox -->
                <div class="form-check ms-2">
                    <input class="form-check-input" type="checkbox" id="showCancelledCheckboxDairy"
                        {{ request('show_cancelled') ? 'checked' : '' }} onchange="toggleCancelledDairy()">
                    <label class="form-check-label" for="showCancelledCheckboxDairy" title="แสดงรายการที่ยกเลิก">
                        <i class="bi bi-eye"></i>
                    </label>
                </div>

                <!-- Right side buttons -->
                <div class="ms-auto d-flex gap-2">
                    <a href="{{ route('dairy_records.record') }}" class="btn btn-success btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> บันทึกใหม่
                    </a>
                </div>
            </form>
        </div>

        {{-- Export Section --}}
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
                    <input type="date" id="exportDateFrom" class="form-control form-control-sm" style="width: 140px;">
                    <span class="text-nowrap small">ถึง</span>
                    <input type="date" id="exportDateTo" class="form-control form-control-sm" style="width: 140px;">
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success btn-sm" id="exportCsvBtn">
                        <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
                    </button>

                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-primary mb-0">
                <thead class="table-header-custom">
                    <tr>
                        <th class="text-center">ลำดับ</th>
                        <th class="text-center">
                            <a href="{{ route('dairy_records.index', array_merge(request()->all(), ['sort_by' => 'date', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}"
                                class="text-white text-decoration-none d-flex align-items-center justify-content-center gap-1">
                                วันที่
                                @if (request('sort_by') == 'date')
                                    <i class="bi bi-{{ request('sort_order') == 'asc' ? 'sort-up' : 'sort-down' }}"></i>
                                @else
                                    <i class="bi bi-arrow-down-up"></i>
                                @endif
                            </a>
                        </th>
                        <th class="text-center">ฟาร์ม</th>
                        <th class="text-center">รุ่น</th>
                        <th class="text-center">เล้า</th>
                        <th class="text-center">ประเภท</th>
                        <th class="text-center">รายละเอียด</th>
                        <th class="text-center">จำนวน</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dairyRecords as $index => $record)
                        @if ($record->batch && ($record->batch->status !== 'cancelled' || request('show_cancelled')))
                            <tr class="clickable-row" data-row-click="#viewModal{{ $record->id }}">
                                <td class="text-center">{{ $dairyRecords->firstItem() + $index }}</td>
                                <td class="text-center">{{ \Carbon\Carbon::parse($record->date)->format('d/m/Y') }}</td>
                                <td class="text-center">{{ $record->batch->farm->farm_name ?? '-' }}</td>
                                <td class="text-center">{{ $record->batch->batch_code ?? '-' }}</td>
                                <td class="text-center">{{ $record->display_barn }}</td>
                                <td class="text-center">
                                    @php
                                        $badges = [];

                                        // ใช้ typesByRecord array จาก controller
                                        if (!empty($typesByRecord[$record->id])) {
                                            foreach ($typesByRecord[$record->id] as $type) {
                                                if ($type === 'feed') {
                                                    $badges[] = '<span class="badge bg-success">อาหาร</span>';
                                                } elseif ($type === 'medicine') {
                                                    $badges[] = '<span class="badge bg-warning">ยา</span>';
                                                } elseif ($type === 'death') {
                                                    $badges[] = '<span class="badge bg-danger">หมูตาย</span>';
                                                }
                                            }
                                        }

                                        if (empty($badges)) {
                                            $badges[] = '-';
                                        }
                                    @endphp
                                    {!! implode(' ', $badges) !!}
                                </td>
                                <td class="text-center">{{ Str::limit($record->display_details ?? '-', 30) }}</td>
                                <td class="text-center">{{ $record->display_quantity }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                        data-bs-target="#viewModal{{ $record->id }}" onclick="event.stopPropagation()">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                        data-bs-target="#editModal{{ $record->id }}" onclick="event.stopPropagation()">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-danger">ไม่มีข้อมูลบันทึกประจำวัน</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-between mt-3">
            <div>
                แสดง {{ $dairyRecords->firstItem() ?? 0 }} ถึง {{ $dairyRecords->lastItem() ?? 0 }} จาก
                {{ $dairyRecords->total() ?? 0 }} แถว
            </div>
            <div>
                {{ $dairyRecords->withQueryString()->onEachSide(1)->links() }}
            </div>
        </div>
    </div>

    {{-- Modals --}}
        @foreach ($dairyRecords as $record)
        @php
            // ใช้ typesByRecord array จาก controller
            $recordTypes = $typesByRecord[$record->id] ?? [];
            $isFeed = in_array('feed', $recordTypes);
            $isMedicine = in_array('medicine', $recordTypes);
            $isDeath = in_array('death', $recordTypes);
            $formAction = '#';
            $methodField = '';

            // Calculate typeBadges for this record
            $typeBadges = [];
            if ($isFeed) {
                $typeBadges[] = '<span class="badge bg-success">อาหาร</span>';
            }
            if ($isMedicine) {
                $typeBadges[] = '<span class="badge bg-warning">ยา</span>';
            }
            if ($isDeath) {
                $typeBadges[] = '<span class="badge bg-danger">หมูตาย</span>';
            }
            $typeBadge = empty($typeBadges) ? '-' : implode(' ', $typeBadges);

            // ตั้ง formAction สำหรับการแก้ไข (ใช้ประเภทแรก)
            if ($isFeed) {
                $feedUses = $record->dairy_storehouse_uses->filter(fn($use) => optional($use->storehouse)->item_type === 'food');
                $useId = $feedUses->first()->id ?? 0;
                $formAction = route('dairy_records.update_feed', [
                    'dairyId' => $record->id,
                    'useId' => $useId,
                    'type' => 'food',
                ]);
            } elseif ($isMedicine) {
                $btId = $record->batch_treatments->first()->id ?? 0;
                $formAction = route('dairy_records.update_medicine', [
                    'dairyId' => $record->id,
                    'btId' => $btId,
                    'type' => 'treatment',
                ]);
            } elseif ($isDeath) {
                $pigDeathId = $record->pig_deaths->first()->id ?? 0;
                $formAction = route('dairy_records.update_pigdeath', ['id' => $pigDeathId]);
            }
        @endphp

        <!-- View Modal -->
        <div class="modal fade" id="viewModal{{ $record->id }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-file-earmark-text"></i> รายละเอียดบันทึกประจำวัน
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <!-- ข้อมูลหลัก -->
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="bi bi-info-circle"></i> ข้อมูลทั่วไป
                                </h6>
                                <table class="table table-secondary table-sm table-hover">
                                    <tr>
                                        <td width="35%"><strong>วันที่:</strong></td>
                                        <td>{{ \Carbon\Carbon::parse($record->date)->format('d/m/Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>ฟาร์ม:</strong></td>
                                        <td>{{ $record->batch->farm->farm_name ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>รุ่น:</strong></td>
                                        <td>{{ $record->batch->batch_code ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>เล้า:</strong></td>
                                        <td>{{ $record->display_barn }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>ประเภท:</strong></td>
                                        <td>{!! $typeBadge !!}</td>
                                    </tr>
                                </table>
                            </div>

                            <!-- ข้อมูลเพิ่มเติม / จำนวน / รายละเอียด -->
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="bi bi-file-text"></i> รายละเอียด
                                </h6>
                                <table class="table table-secondary table-sm table-hover">
                                    <tr>
                                        <td width="35%"><strong>จำนวน:</strong></td>
                                        <td>{{ $record->display_quantity }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>หมายเหตุ:</strong></td>
                                        <td>{{ $record->display_details ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> ปิด
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- End View Modal -->

        <!-- Edit Modal -->
        <div class="modal fade" id="editModal{{ $record->id }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">แก้ไขบันทึกประจำวัน</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <form action="{{ $formAction }}" method="POST">
                        @csrf
                        @if ($isFeed || $isMedicine || $isDeath)
                            @method('PUT')
                        @endif
                        <div class="modal-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label>วันที่</label>
                                    <input type="text" name="date" class="thai-datepicker form-control bg-white"
                                        value="{{ \Carbon\Carbon::parse($record->date)->format('d/m/Y H:i') }}"
                                        autocomplete="off" placeholder="วัน-เดือน-ปี ชั่วโมง:นาที" required>
                                </div>
                                <div class="col-md-4">
                                    <label>ฟาร์ม</label>
                                    <input type="text" class="form-control form-disabled"
                                        value="{{ $record->batch->farm->farm_name ?? '-' }}" disabled>
                                </div>
                                <div class="col-md-4">
                                    <label>รุ่น</label>
                                    <input type="text" class="form-control form-disabled"
                                        value="{{ $record->batch->batch_code ?? '-' }}" disabled>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label>เล้า</label>
                                    <input type="text" class="form-control form-disabled"
                                        value="{{ $record->display_barn }}" disabled>
                                </div>
                                <div class="col-md-4">
                                    <label>ประเภท</label>
                                    <div>{!! $typeBadge !!}</div>
                                </div>
                                <div class="col-md-4">
                                    <label>จำนวน</label>
                                    <input type="number" name="quantity" class="form-control"
                                        value="{{ $record->display_quantity }}" min="0" required>
                                </div>
                            </div>

                            @if ($isMedicine)
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label>สถานะ</label>
                                        <input type="text" name="status" class="form-control"
                                            value="{{ $record->batch_treatments->first()->status ?? '' }}">
                                    </div>
                                </div>
                            @endif

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label>รายละเอียด</label>
                                    <textarea class="form-control form-disabled" rows="2" disabled>{{ $record->display_details }}</textarea>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label>หมายเหตุ</label>
                                    <textarea name="note" class="form-control" rows="2">{{ $record->note }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">บันทึกการแก้ไข</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- End Edit Modal -->
    @endforeach

    @push('scripts')
        <!-- Toggle Show Cancelled Batches -->
        <script>
            function toggleCancelledDairy() {
                const checkbox = document.getElementById('showCancelledCheckboxDairy');
                const form = document.querySelector('form[method="GET"]');

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

        <!-- Datepicker JS (flatpickr) -->
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://npmcdn.com/flatpickr/dist/l10n/th.js"></script>
        <!-- Datepicker JS (flatpickr) -->
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                flatpickr('.thai-datepicker', {
                    enableTime: true,
                    dateFormat: 'd/m/Y H:i', // แสดงวันที่แบบ วัน-เดือน-ปี ชั่วโมง:นาที
                    time_24hr: true,
                    locale: 'th',
                    onClose: function(selectedDates, dateStr, instance) {
                        // แปลงปีไทยเป็น ค.ศ. ก่อน submit
                        if (dateStr) {
                            let parts = dateStr.split('-'); // ["15","10","2568 06:53"]
                            let day = parts[0];
                            let month = parts[1];
                            let yearAndTime = parts[2].split(' ');
                            let year = parseInt(yearAndTime[0]);
                            let time = yearAndTime[1] ?? '00:00';

                            if (year > 2500) year = year - 543; // แปลงเป็น ค.ศ.

                            instance.input.value = `${day}-${month}-${year} ${time}`;
                        }
                    }
                });
            });
        </script>

        {{-- Export Functionality --}}
        <script>
            document.getElementById('exportCsvBtn').addEventListener('click', function() {
                console.log('📥 [Dairy Records] Exporting CSV');
                const params = new URLSearchParams(window.location.search);
                const dateFrom = document.getElementById('exportDateFrom').value;
                const dateTo = document.getElementById('exportDateTo').value;
                if (dateFrom) params.set('export_date_from', dateFrom);
                if (dateTo) params.set('export_date_to', dateTo);
                const url = `{{ route('dairy_records.export.csv') }}?${params.toString()}`;
                window.location.href = url;
            });

            
        </script>



        {{-- Farm to Batch Filter Script --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const farmLinks = document.querySelectorAll('.farm-link');
                const batchDropdownMenu = document.getElementById('batchDropdownMenu');
                const batchDropdownBtn = document.getElementById('batchDropdownBtn');

                farmLinks.forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const farmId = this.getAttribute('data-farm-id');
                        const targetUrl = this.getAttribute('href');

                        if (farmId) {
                            fetch('/get-batches/' + farmId)
                                .then(response => response.json())
                                .then(data => {
                                    updateBatchDropdown(data, targetUrl);
                                    window.location.href = targetUrl;
                                })
                                .catch(error => {
                                    console.error('Error loading batches:', error);
                                    window.location.href = targetUrl;
                                });
                        } else {
                            window.location.href = targetUrl;
                        }
                    });
                });

                function updateBatchDropdown(batches, currentUrl) {
                    const url = new URL(currentUrl, window.location.origin);
                    const params = new URLSearchParams(url.search);
                    params.delete('batch_id');
                    const baseUrl = url.pathname + '?' + params.toString();

                    let html = `<li><a class="dropdown-item" href="${baseUrl}">รุ่นทั้งหมด</a></li>`;
                    batches.forEach(batch => {
                        const batchParams = new URLSearchParams(params);
                        batchParams.set('batch_id', batch.id);
                        const batchUrl = url.pathname + '?' + batchParams.toString();
                        html += `<li><a class="dropdown-item" href="${batchUrl}">${batch.batch_code}</a></li>`;
                    });
                    batchDropdownMenu.innerHTML = html;
                    batchDropdownBtn.innerHTML = '<i class="bi bi-layers"></i> รุ่นทั้งหมด';
                }
            });
        </script>
        <script src="{{ asset('admin/js/common-table-click.js') }}"></script>
    @endpush
@endsection
