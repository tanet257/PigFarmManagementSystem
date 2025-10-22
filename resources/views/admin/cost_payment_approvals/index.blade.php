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

        {{-- Pending Payments Table --}}
        @if ($pendingPayments->count() > 0)
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-hourglass-split"></i> รอการอนุมัติ</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
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
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-success"
                                            data-bs-toggle="modal"
                                            data-bs-target="#approveModal{{ $payment->id }}"
                                            title="อนุมัติ">
                                            <i class="bi bi-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#rejectModal{{ $payment->id }}"
                                            title="ปฏิเสธ">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </td>
                                </tr>

                                {{-- Approve Modal --}}
                                <div class="modal fade" id="approveModal{{ $payment->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">ยืนยันการอนุมัติ</h5>
                                                <button type="button" class="btn-close"
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
                                            <div class="modal-header">
                                                <h5 class="modal-title">ยืนยันการปฏิเสธ</h5>
                                                <button type="button" class="btn-close"
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
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i> ไม่มีการอนุมัติที่รอการอนุมัติ
            </div>
        @endif
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
@endsection
