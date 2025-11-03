@extends('layouts.admin')

@section('title', 'อนุมัติการชำระเงินค่าใช้จ่าย')

@section('content')
    <div class="container my-5">
        <div class="card-header">
            <h1 class="text-center">อนุมัติการชำระเงินค่าใช้จ่าย (Cost Payment Approvals)</h1>
        </div>
        <div class="py-2"></div>

        {{-- Status Summary --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center bg-warning text-white">
                    <div class="card-status-summary">
                        <h3>{{ $pendingPayments->total() }}</h3>
                        <p class="mb-0"><i class="bi bi-hourglass-split"></i> รอการอนุมัติ</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center bg-success text-white">
                    <div class="card-status-summary">
                        <h3>{{ $approvedPayments->count() ?? 0 }}</h3>
                        <p class="mb-0"><i class="bi bi-check-circle"></i> อนุมัติแล้ว</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center bg-danger text-white">
                    <div class="card-status-summary">
                        <h3>{{ $rejectedPayments->count() ?? 0 }}</h3>
                        <p class="mb-0"><i class="bi bi-x-circle"></i> ปฏิเสธแล้ว</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs Navigation --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <ul class="nav nav-tabs mb-0 flex-grow-1" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="pending-tab" data-bs-toggle="tab" href="#pending" role="tab">
                        <i class="bi bi-hourglass-split"></i> รอการอนุมัติ
                        <span class="badge bg-warning ms-2">{{ $pendingPayments->total() ?? 0 }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="approved-tab" data-bs-toggle="tab" href="#approved" role="tab">
                        <i class="bi bi-check-circle"></i> อนุมัติแล้ว
                        <span class="badge bg-success ms-2">{{ $approvedPayments->count() ?? 0 }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="rejected-tab" data-bs-toggle="tab" href="#rejected" role="tab">
                        <i class="bi bi-x-circle"></i> ปฏิเสธแล้ว
                        <span class="badge bg-danger ms-2">{{ $rejectedPayments->count() ?? 0 }}</span>
                    </a>
                </li>
            </ul>
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
                <button type="button" class="btn btn-success btn-sm" id="exportCsvBtn">
                    <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
                </button>
            </div>
        </div>

        {{-- Tab Content --}}
        <div class="tab-content">
            {{-- PENDING TAB --}}
            <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">
        @if ($pendingPayments->count() > 0)
            <div class="table-responsive">
                <table class="table table-primary mb-0" id="costPaymentsTable">
                    <thead class="table-header-custom">
                            <tr>
                                <th class="text-center">ลำดับ</th>
                                <th>ประเภทค่าใช้จ่าย</th>
                                <th>ชื่อรุ่นหมู</th>
                                <th class="text-end">จำนวนเงิน</th>
                                <th>บันทึกวันที่</th>
                                <th class="text-center">การกระทำ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingPayments as $payment)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>
                                        @switch($payment->cost->cost_type)
                                            @case('piglet')
                                                <span class="badge bg-primary">ลูกหมู</span>
                                                @break
                                            @case('feed')
                                                <span class="badge bg-info">อาหาร</span>
                                                @break
                                            @case('medicine')
                                                <span class="badge bg-danger">ยา</span>
                                                @break
                                            @case('wage')
                                                <span class="badge bg-success">เงินเดือน</span>
                                                @break
                                            @case('shipping')
                                                <span class="badge bg-secondary">ขนส่ง</span>
                                                @break
                                            @case('electric_bill')
                                                <span class="badge bg-warning">ค่าไฟฟ้า</span>
                                                @break
                                            @case('water_bill')
                                                <span class="badge bg-info">ค่าน้ำ</span>
                                                @break
                                            @case('other')
                                                <span class="badge bg-dark">อื่น ๆ</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>{{ $payment->cost->batch->batch_code ?? 'N/A' }}</td>
                                    <td class="text-end"><strong>฿{{ number_format($payment->amount, 2) }}</strong></td>
                                    <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('cost_payment_approvals.show', $payment->id) }}"
                                            class="btn btn-sm btn-info" title="ดูรายละเอียด">
                                            <i class="bi bi-eye"></i> ดู
                                        </a>
                                        <button type="button" class="btn btn-sm btn-success"
                                            data-bs-toggle="modal"
                                            data-bs-target="#approveModal{{ $payment->id }}"
                                            title="อนุมัติ">
                                            <i class="bi bi-check"></i> อนุมัติ
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#rejectModal{{ $payment->id }}"
                                            title="ปฏิเสธ">
                                            <i class="bi bi-x"></i> ปฏิเสธ
                                        </button>
                                    </td>
                                </tr>

                                {{-- Approve Modal --}}
                                <div class="modal fade" id="approveModal{{ $payment->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title">ยืนยันการอนุมัติ</h5>
                                                <button type="button" class="btn-close btn-close-white"
                                                    data-bs-dismiss="modal"></button>
                                            </div>
                                            <form id="approveForm{{ $payment->id }}"
                                                action="{{ route('cost_payment_approvals.approve', $payment->id) }}"
                                                method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <p>ต้องการอนุมัติการชำระเงินจำนวน
                                                        <strong>฿{{ number_format($payment->amount, 2) }}</strong>
                                                        สำหรับ <strong>{{ $payment->cost->cost_type }}</strong>
                                                        ใช่หรือไม่?
                                                    </p>
                                                    <div class="mb-3">
                                                        <label for="note{{ $payment->id }}"
                                                            class="form-label">หมายเหตุ (ไม่บังคับ)</label>
                                                        <textarea class="form-control" id="note{{ $payment->id }}"
                                                            name="note" rows="2" placeholder="เพิ่มหมายเหตุ..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">ยกเลิก</button>
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="bi bi-check"></i> อนุมัติ
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- Reject Modal --}}
                                <div class="modal fade" id="rejectModal{{ $payment->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title">ยืนยันการปฏิเสธ</h5>
                                                <button type="button" class="btn-close btn-close-white"
                                                    data-bs-dismiss="modal"></button>
                                            </div>
                                            <form id="rejectForm{{ $payment->id }}"
                                                action="{{ route('cost_payment_approvals.reject', $payment->id) }}"
                                                method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <p>ต้องการปฏิเสธการชำระเงินจำนวน
                                                        <strong>฿{{ number_format($payment->amount, 2) }}</strong>
                                                        ใช่หรือไม่?
                                                    </p>
                                                    <div class="mb-3">
                                                        <label for="rejectReason{{ $payment->id }}"
                                                            class="form-label">เหตุผล <span
                                                                class="text-danger">*</span></label>
                                                        <textarea class="form-control" id="rejectReason{{ $payment->id }}"
                                                            name="reason" rows="3" placeholder="ระบุเหตุผล..." required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">ยกเลิก</button>
                                                    <button type="submit" class="btn btn-danger">
                                                        <i class="bi bi-x"></i> ปฏิเสธ
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="bi bi-check-circle"></i> ไม่มีการอนุมัติที่รอการอนุมัติ
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $pendingPayments->links() }}
            </div>
        @else
            <div class="alert alert-info">
                <i class="bi bi-check-circle"></i> ไม่มีการอนุมัติที่รอการอนุมัติ
            </div>
        @endif
            </div>

            {{-- APPROVED TAB --}}
            <div class="tab-pane fade" id="approved" role="tabpanel" aria-labelledby="approved-tab">
        @if ($approvedPayments->count() > 0)
            <div class="table-responsive">
                <table class="table table-primary mb-0" id="approvedTable">
                    <thead class="table-header-custom">
                            <tr>
                                <th class="text-center">ลำดับ</th>
                                <th>ประเภทค่าใช้จ่าย</th>
                                <th>ชื่อรุ่นหมู</th>
                                <th class="text-end">จำนวนเงิน</th>
                                <th>อนุมัติโดย</th>
                                <th>อนุมัติวันที่</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($approvedPayments as $payment)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>
                                        @switch($payment->cost->cost_type)
                                            @case('piglet')
                                                <span class="badge bg-primary">ลูกหมู</span>
                                                @break
                                            @case('feed')
                                                <span class="badge bg-info">อาหาร</span>
                                                @break
                                            @case('medicine')
                                                <span class="badge bg-danger">ยา</span>
                                                @break
                                            @case('wage')
                                                <span class="badge bg-success">เงินเดือน</span>
                                                @break
                                            @case('shipping')
                                                <span class="badge bg-secondary">ขนส่ง</span>
                                                @break
                                            @case('electric_bill')
                                                <span class="badge bg-warning">ค่าไฟฟ้า</span>
                                                @break
                                            @case('water_bill')
                                                <span class="badge bg-info">ค่าน้ำ</span>
                                                @break
                                            @case('other')
                                                <span class="badge bg-dark">อื่น ๆ</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>{{ $payment->cost->batch->batch_code ?? 'N/A' }}</td>
                                    <td class="text-end"><strong>฿{{ number_format($payment->amount, 2) }}</strong></td>
                                    <td>{{ $payment->approver->name ?? '-' }}</td>
                                    <td>{{ $payment->approved_date ? $payment->approved_date->format('d/m/Y H:i') : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox"></i> ยังไม่มีการอนุมัติ
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="alert alert-info">
                <i class="bi bi-inbox"></i> ยังไม่มีการอนุมัติ
            </div>
        @endif
            </div>

            {{-- REJECTED TAB --}}
            <div class="tab-pane fade" id="rejected" role="tabpanel" aria-labelledby="rejected-tab">
        @if ($rejectedPayments->count() > 0)
            <div class="table-responsive">
                <table class="table table-primary mb-0" id="rejectedTable">
                    <thead class="table-header-custom">
                            <tr>
                                <th class="text-center">ลำดับ</th>
                                <th>ประเภทค่าใช้จ่าย</th>
                                <th>ชื่อรุ่นหมู</th>
                                <th class="text-end">จำนวนเงิน</th>
                                <th>ปฏิเสธโดย</th>
                                <th>เหตุผล</th>
                                <th>ปฏิเสธวันที่</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rejectedPayments as $payment)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>
                                        @switch($payment->cost->cost_type)
                                            @case('piglet')
                                                <span class="badge bg-primary">ลูกหมู</span>
                                                @break
                                            @case('feed')
                                                <span class="badge bg-info">อาหาร</span>
                                                @break
                                            @case('medicine')
                                                <span class="badge bg-danger">ยา</span>
                                                @break
                                            @case('wage')
                                                <span class="badge bg-success">เงินเดือน</span>
                                                @break
                                            @case('shipping')
                                                <span class="badge bg-secondary">ขนส่ง</span>
                                                @break
                                            @case('electric_bill')
                                                <span class="badge bg-warning">ค่าไฟฟ้า</span>
                                                @break
                                            @case('water_bill')
                                                <span class="badge bg-info">ค่าน้ำ</span>
                                                @break
                                            @case('other')
                                                <span class="badge bg-dark">อื่น ๆ</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>{{ $payment->cost->batch->batch_code ?? '-' }}</td>
                                    <td class="text-end">฿{{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ $payment->rejecter->name ?? '-' }}</td>
                                    <td>
                                        @if($payment->reason)
                                            <span class="d-inline-block text-truncate" style="max-width: 200px;" title="{{ $payment->reason }}">
                                                {{ $payment->reason }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $payment->rejected_at ? $payment->rejected_at->format('d/m/Y H:i') : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox"></i> ยังไม่มีการปฏิเสธ
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="alert alert-info">
                <i class="bi bi-inbox"></i> ยังไม่มีการปฏิเสธ
            </div>
        @endif
            </div>
        </div>

    <style>
        .card-status-summary {
            padding: 20px 10px;
        }

        .card-status-summary h3 {
            font-size: 2.5rem;
            margin-bottom: 5px;
        }

        .table-header-custom {
            background-color: #f8f9fa;
        }
    </style>

    <script>
        // AJAX submit untuk approve/reject
        @forelse($pendingPayments as $payment)
            document.getElementById('approveForm{{ $payment->id }}')?.addEventListener('submit', function (e) {
                e.preventDefault();
                submitApprovalForm(this, '{{ $payment->id }}', 'approve');
            });

            document.getElementById('rejectForm{{ $payment->id }}')?.addEventListener('submit', function (e) {
                e.preventDefault();
                submitApprovalForm(this, '{{ $payment->id }}', 'reject');
            });
        @empty
        @endforelse

        function submitApprovalForm(form, paymentId, action) {
            const formData = new FormData(form);
            const url = form.action;

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        'Accept': 'application/json',
                    },
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('เกิดข้อผิดพลาด: ' + error);
                });
        }
    </script>

    <script>
        document.getElementById('exportCsvBtn').addEventListener('click', function() {
            console.log('📥 [Cost Payment Approvals] Exporting CSV');
            const params = new URLSearchParams(window.location.search);
            const dateFrom = document.getElementById('exportDateFrom').value;
            const dateTo = document.getElementById('exportDateTo').value;
            if (dateFrom) params.set('export_date_from', dateFrom);
            if (dateTo) params.set('export_date_to', dateTo);
            const url = `{{ route('cost_payment_approvals.export.csv') }}?${params.toString()}`;
            window.location.href = url;
        });
    </script>
@endsection
