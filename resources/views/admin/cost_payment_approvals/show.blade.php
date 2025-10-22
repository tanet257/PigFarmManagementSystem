@extends('layouts.admin')

@section('title', 'รายละเอียดการชำระเงินค่าใช้จ่าย')

@section('content')
    <div class="container my-5">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">รายละเอียดการชำระเงิน</h5>
                    </div>
                    <div class="card-body">
                        {{-- Status Badge --}}
                        <div class="mb-3">
                            <strong>สถานะ:</strong>
                            @switch($payment->status)
                                @case('pending')
                                    <span class="badge bg-warning">รอการอนุมัติ</span>
                                    @break
                                @case('approved')
                                    <span class="badge bg-success">อนุมัติแล้ว</span>
                                    @break
                                @case('rejected')
                                    <span class="badge bg-danger">ปฏิเสธแล้ว</span>
                                    @break
                            @endswitch
                        </div>

                        {{-- Cost Type --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">ประเภทค่าใช้จ่าย:</label>
                                <p>
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
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">จำนวนเงิน:</label>
                                <p class="h5 text-success">฿{{ number_format($payment->amount, 2) }}</p>
                            </div>
                        </div>

                        {{-- Batch Info --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">รุ่นหมู:</label>
                                <p>{{ $payment->cost->batch->batch_code ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">วันที่บันทึก:</label>
                                <p>{{ $payment->cost->date->format('d/m/Y') ?? 'N/A' }}</p>
                            </div>
                        </div>

                        {{-- Cost Details --}}
                        @if ($payment->cost->note)
                            <div class="mb-3">
                                <label class="form-label fw-bold">หมายเหตุ:</label>
                                <p>{{ $payment->cost->note }}</p>
                            </div>
                        @endif

                        {{-- Approval Info --}}
                        @if ($payment->status !== 'pending')
                            <hr>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">ผู้อนุมัติ:</label>
                                    <p>{{ $payment->approver->name ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">วันที่อนุมัติ:</label>
                                    <p>{{ $payment->approved_date?->format('d/m/Y H:i') ?? 'N/A' }}</p>
                                </div>
                            </div>

                            @if ($payment->reason)
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        @if ($payment->status === 'rejected')
                                            เหตุผลการปฏิเสธ:
                                        @else
                                            หมายเหตุการอนุมัติ:
                                        @endif
                                    </label>
                                    <p>{{ $payment->reason }}</p>
                                </div>
                            @endif
                        @endif

                        {{-- Action Buttons --}}
                        @if ($payment->status === 'pending')
                            <div class="mt-4">
                                <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                    data-bs-target="#approveModal">
                                    <i class="bi bi-check"></i> อนุมัติ
                                </button>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                    data-bs-target="#rejectModal">
                                    <i class="bi bi-x"></i> ปฏิเสธ
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Back Button --}}
                <div class="mt-3">
                    <a href="{{ route('cost_payment_approvals.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> กลับไปที่รายการ
                    </a>
                </div>
            </div>

            {{-- Timeline Sidebar --}}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-clock-history"></i> ประวัติการเปลี่ยนแปลง</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            {{-- Created --}}
                            <div class="timeline-item">
                                <div class="timeline-marker bg-primary"></div>
                                <div class="timeline-content">
                                    <p class="small text-muted">{{ $payment->created_at->format('d/m/Y H:i') }}</p>
                                    <p class="mb-0">บันทึกการชำระเงิน</p>
                                </div>
                            </div>

                            {{-- Approved/Rejected --}}
                            @if ($payment->status !== 'pending')
                                <div class="timeline-item">
                                    <div
                                        class="timeline-marker bg-{{ $payment->status === 'approved' ? 'success' : 'danger' }}">
                                    </div>
                                    <div class="timeline-content">
                                        <p class="small text-muted">{{ $payment->approved_date?->format('d/m/Y H:i') }}
                                        </p>
                                        <p class="mb-0">
                                            @if ($payment->status === 'approved')
                                                อนุมัติโดย {{ $payment->approver->name ?? 'N/A' }}
                                            @else
                                                ปฏิเสธโดย {{ $payment->approver->name ?? 'N/A' }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Approve Modal --}}
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ยืนยันการอนุมัติ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="approveForm" action="{{ route('cost_payment_approvals.approve', $payment->id) }}"
                    method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>ต้องการอนุมัติการชำระเงินจำนวน <strong>฿{{ number_format($payment->amount, 2) }}</strong>
                            ใช่หรือไม่?</p>
                        <div class="mb-3">
                            <label for="note" class="form-label">หมายเหตุ (ไม่บังคับ)</label>
                            <textarea class="form-control" id="note" name="note" rows="2"
                                placeholder="เพิ่มหมายเหตุ..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check"></i> อนุมัติ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ยืนยันการปฏิเสธ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="rejectForm" action="{{ route('cost_payment_approvals.reject', $payment->id) }}"
                    method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>ต้องการปฏิเสธการชำระเงินจำนวน <strong>฿{{ number_format($payment->amount, 2) }}</strong>
                            ใช่หรือไม่?</p>
                        <div class="mb-3">
                            <label for="reason" class="form-label">เหตุผล <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="reason" name="reason" rows="3"
                                placeholder="ระบุเหตุผล..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-x"></i> ปฏิเสธ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 20px;
        }

        .timeline-item:not(:last-child)::before {
            content: '';
            position: absolute;
            left: -25px;
            top: 20px;
            bottom: -20px;
            width: 2px;
            background: #ddd;
        }

        .timeline-marker {
            position: absolute;
            left: -32px;
            top: 0;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            border: 2px solid #fff;
        }

        .timeline-content p {
            margin: 0;
        }
    </style>

    <script>
        document.getElementById('approveForm')?.addEventListener('submit', function (e) {
            e.preventDefault();
            submitForm(this);
        });

        document.getElementById('rejectForm')?.addEventListener('submit', function (e) {
            e.preventDefault();
            submitForm(this);
        });

        function submitForm(form) {
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
                        location.href = "{{ route('cost_payment_approvals.index') }}";
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
