@extends('layouts.admin')

@section('title', 'อนุมัติการชำระเงิน')

@section('content')
    <div class="container my-5">
        <div class="card-header">
            <h1 class="text-center">อนุมัติการชำระเงิน (Payment Approvals)</h1>
        </div>
        <div class="py-2"></div>

        {{-- Status Summary --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center bg-warning text-white">
                    <div class="card-status-summary">
                        <h3>{{ ($pendingPayments->total() ?? 0) + ($pendingCancelRequests->total() ?? 0) + ($pendingNotifications->total() ?? 0) }}</h3>
                        <p class="mb-0"><i class="bi bi-hourglass-split"></i> รอการอนุมัติ</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center bg-success text-white">
                    <div class="card-status-summary">
                        <h3>{{ ($approvedPayments->total() ?? 0) + ($approvedCancelRequests->total() ?? 0) + ($approvedNotifications->total() ?? 0) }}</h3>
                        <p class="mb-0"><i class="bi bi-check-circle"></i> อนุมัติแล้ว</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center bg-danger text-white">
                    <div class="card-status-summary">
                        <h3>{{ ($rejectedPayments->total() ?? 0) + ($rejectedCancelRequests->total() ?? 0) + ($rejectedNotifications->total() ?? 0) }}</h3>
                        <p class="mb-0"><i class="bi bi-x-circle"></i> ปฏิเสธแล้ว</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs Navigation --}}
        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="pending-tab" data-bs-toggle="tab" href="#pending" role="tab">
                    <i class="bi bi-hourglass-split"></i> รอการอนุมัติ
                    <span class="badge bg-warning ms-2">{{ ($pendingPayments->total() ?? 0) + ($pendingCancelRequests->total() ?? 0) + ($pendingNotifications->total() ?? 0) }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="approved-tab" data-bs-toggle="tab" href="#approved" role="tab">
                    <i class="bi bi-check-circle"></i> อนุมัติแล้ว
                    <span class="badge bg-success ms-2">{{ ($approvedPayments->total() ?? 0) + ($approvedCancelRequests->total() ?? 0) + ($approvedNotifications->total() ?? 0) }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="rejected-tab" data-bs-toggle="tab" href="#rejected" role="tab">
                    <i class="bi bi-x-circle"></i> ปฏิเสธแล้ว
                    <span class="badge bg-danger ms-2">{{ ($rejectedPayments->total() ?? 0) + ($rejectedCancelRequests->total() ?? 0) + ($rejectedNotifications->total() ?? 0) }}</span>
                </a>
            </li>
        </ul>

        {{-- Tab Content --}}
        <div class="tab-content">
            {{-- Tab: Pending Approvals --}}
            <div class="tab-pane fade show active" id="pending" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-primary mb-0">
                        <thead class="table-header-custom">
                            <tr>
                                <th class="text-center">ลำดับ</th>
                                <th class="text-center">ประเภท</th>
                                <th class="text-center">รายละเอียด</th>
                                <th class="text-center">ผู้บันทึก</th>
                                <th class="text-center">วันที่</th>
                                <th class="text-center">การกระทำ</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Display Payment Records (PigSale Payments) --}}
                            @forelse($pendingPayments as $index => $payment)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">การขายหมู</span>
                                    </td>
                                    <td>
                                        <strong>บันทึกการชำระเงิน</strong><br>
                                        ฟาร์ม: {{ $payment->pigSale->farm->farm_name ?? '-' }}<br>
                                        รุ่น: {{ $payment->pigSale->batch->batch_code ?? '-' }}<br>
                                        ผู้ซื้อ: {{ $payment->pigSale->buyer_name ?? '-' }}<br>
                                        จำนวน: {{ number_format($payment->amount, 2) }} ฿
                                    </td>
                                    <td class="text-center">{{ $payment->recordedBy->name ?? '-' }}</td>
                                    <td class="text-center">{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('payment_approvals.detail', $payment->id) }}"
                                            class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i> ดู
                                        </a>
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                            data-bs-target="#approvePaymentModal{{ $payment->id }}">
                                            <i class="bi bi-check"></i> อนุมัติ
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                            data-bs-target="#rejectPaymentModal{{ $payment->id }}">
                                            <i class="bi bi-x"></i> ปฏิเสธ
                                        </button>
                                    </td>
                                </tr>

                                {{-- Approve Payment Modal --}}
                                <div class="modal fade" id="approvePaymentModal{{ $payment->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title">อนุมัติการชำระเงิน</h5>
                                                <button type="button" class="btn-close btn-close-white"
                                                    data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('payment_approvals.approve_payment', $payment->id) }}"
                                                method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <div class="modal-body">
                                                    <div class="alert alert-info">
                                                        <strong>การชำระเงิน:</strong><br>
                                                        จำนวน: {{ number_format($payment->amount, 2) }} ฿<br>
                                                        วิธีชำระ: {{ $payment->payment_method ?? '-' }}
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">หมายเหตุการอนุมัติ (ไม่จำเป็น)</label>
                                                        <textarea name="approval_notes" class="form-control" rows="3"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">ยกเลิก</button>
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="bi bi-check-circle"></i> อนุมัติ
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- Reject Payment Modal --}}
                                <div class="modal fade" id="rejectPaymentModal{{ $payment->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title">ปฏิเสธการชำระเงิน</h5>
                                                <button type="button" class="btn-close btn-close-white"
                                                    data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('payment_approvals.reject_payment', $payment->id) }}"
                                                method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <div class="modal-body">
                                                    <div class="alert alert-warning">
                                                        <strong>การชำระเงิน:</strong><br>
                                                        จำนวน: {{ number_format($payment->amount, 2) }} ฿
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">เหตุผลในการปฏิเสธ <span
                                                                class="text-danger">*</span></label>
                                                        <textarea name="reject_reason" class="form-control" rows="3" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">ยกเลิก</button>
                                                    <button type="submit" class="btn btn-danger">
                                                        <i class="bi bi-x-circle"></i> ปฏิเสธ
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                            @endforelse

                            {{-- Display Notification Records (if any PigSale payment notifications) --}}
                            @forelse($pendingNotifications as $index => $notification)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-center">
                                        @if ($notification->type === 'payment_recorded_pig_entry')
                                            <span class="badge bg-info">การรับเข้าหมู</span>
                                        @else
                                            <span class="badge bg-primary">การขายหมู</span>
                                        @endif
                                    </td>
                                    <td>{{ $notification->message }}</td>
                                    <td class="text-center">{{ $notification->relatedUser->name ?? '-' }}</td>
                                    <td class="text-center">{{ $notification->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('payment_approvals.detail', $notification->id) }}"
                                            class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i> ดู
                                        </a>
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                            data-bs-target="#approveModal{{ $notification->id }}">
                                            <i class="bi bi-check"></i> อนุมัติ
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                            data-bs-target="#rejectModal{{ $notification->id }}">
                                            <i class="bi bi-x"></i> ปฏิเสธ
                                        </button>
                                    </td>
                                </tr>

                                {{-- Approve Modal --}}
                                <div class="modal fade" id="approveModal{{ $notification->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title">อนุมัติการชำระเงิน</h5>
                                                <button type="button" class="btn-close btn-close-white"
                                                    data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('payment_approvals.approve', $notification->id) }}"
                                                method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="alert alert-info">
                                                        <strong>ประเภท:</strong>
                                                        @if ($notification->type === 'payment_recorded_pig_entry')
                                                            การรับเข้าหมู
                                                        @else
                                                            การขายหมู
                                                        @endif
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">หมายเหตุการอนุมัติ (ไม่จำเป็น)</label>
                                                        <textarea name="approval_notes" class="form-control" rows="3"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">ยกเลิก</button>
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="bi bi-check-circle"></i> อนุมัติ
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- Reject Modal --}}
                                <div class="modal fade" id="rejectModal{{ $notification->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title">ปฏิเสธการชำระเงิน</h5>
                                                <button type="button" class="btn-close btn-close-white"
                                                    data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('payment_approvals.reject', $notification->id) }}"
                                                method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="alert alert-warning">
                                                        <strong>ประเภท:</strong>
                                                        @if ($notification->type === 'payment_recorded_pig_entry')
                                                            การรับเข้าหมู
                                                        @else
                                                            การขายหมู
                                                        @endif
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">เหตุผลในการปฏิเสธ <span
                                                                class="text-danger">*</span></label>
                                                        <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">ยกเลิก</button>
                                                    <button type="submit" class="btn btn-danger">
                                                        <i class="bi bi-x-circle"></i> ปฏิเสธ
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">ไม่มีรายการรอการอนุมัติ</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $pendingNotifications->links() }}

                {{-- Cancel Requests Section --}}
                @if ($pendingCancelRequests && $pendingCancelRequests->count() > 0)
                    <hr class="my-4">
                    <h5 class="mb-3">
                        <i class="bi bi-exclamation-circle"></i> ขอยกเลิกการขายหมู
                        <span class="badge bg-warning text-dark">{{ $pendingCancelRequests->count() }}</span>
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-warning mb-0">
                            <thead class="table-header-custom">
                                <tr>
                                    <th class="text-center">ลำดับ</th>
                                    <th class="text-center">เลขที่ขาย</th>
                                    <th class="text-center">จำนวนหมู</th>
                                    <th class="text-center">ผู้ขอยกเลิก</th>
                                    <th class="text-center">วันที่ขอยกเลิก</th>
                                    <th class="text-center">เหตุผล</th>
                                    <th class="text-center">การกระทำ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingCancelRequests as $index => $cancelRequest)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="text-center">
                                            {{ $cancelRequest->relatedModel === 'PigSale'
                                                ? \App\Models\PigSale::find($cancelRequest->related_model_id)?->id
                                                : '-' }}
                                        </td>
                                        <td class="text-center">
                                            {{ $cancelRequest->relatedModel === 'PigSale'
                                                ? \App\Models\PigSale::find($cancelRequest->related_model_id)?->quantity . ' ตัว'
                                                : '-' }}
                                        </td>
                                        <td class="text-center">{{ $cancelRequest->relatedUser->name ?? '-' }}</td>
                                        <td class="text-center">{{ $cancelRequest->created_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ Str::limit($cancelRequest->message, 50) }}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                                data-bs-target="#approveCancelModal{{ $cancelRequest->id }}">
                                                <i class="bi bi-check"></i> อนุมัติ
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                data-bs-target="#rejectCancelModal{{ $cancelRequest->id }}">
                                                <i class="bi bi-x"></i> ปฏิเสธ
                                            </button>
                                        </td>
                                    </tr>

                                    {{-- Approve Cancel Modal --}}
                                    <div class="modal fade" id="approveCancelModal{{ $cancelRequest->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-success text-white">
                                                    <h5 class="modal-title">อนุมัติการยกเลิกการขาย</h5>
                                                    <button type="button" class="btn-close btn-close-white"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('payment_approvals.approve_cancel_sale', $cancelRequest->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="modal-body">
                                                        <div class="alert alert-warning">
                                                            <strong>เหตุผลการขอยกเลิก:</strong>
                                                            <p class="mb-0">{{ $cancelRequest->message }}</p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">หมายเหตุการอนุมัติ (ไม่จำเป็น)</label>
                                                            <textarea name="approval_notes" class="form-control" rows="3"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">ยกเลิก</button>
                                                        <button type="submit" class="btn btn-success">
                                                            <i class="bi bi-check-circle"></i> อนุมัติการยกเลิก
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Reject Cancel Modal --}}
                                    <div class="modal fade" id="rejectCancelModal{{ $cancelRequest->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title">ปฏิเสธการยกเลิกการขาย</h5>
                                                    <button type="button" class="btn-close btn-close-white"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('payment_approvals.reject_cancel_sale', $cancelRequest->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="modal-body">
                                                        <div class="alert alert-warning">
                                                            <strong>เหตุผลการขอยกเลิก:</strong>
                                                            <p class="mb-0">{{ $cancelRequest->message }}</p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">เหตุผลในการปฏิเสธ <span
                                                                    class="text-danger">*</span></label>
                                                            <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">ยกเลิก</button>
                                                        <button type="submit" class="btn btn-danger">
                                                            <i class="bi bi-x-circle"></i> ปฏิเสธ
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Tab: Approved --}}
            <div class="tab-pane fade" id="approved" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-primary mb-0">
                        <thead class="table-header-custom">
                            <tr>
                                <th class="text-center">ลำดับ</th>
                                <th class="text-center">ประเภท</th>
                                <th class="text-center">รายละเอียด</th>
                                <th class="text-center">ผู้บันทึก</th>
                                <th class="text-center">อนุมัติเมื่อ</th>
                                <th class="text-center">การกระทำ</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Display Approved Payment Records --}}
                            @forelse($approvedPayments as $index => $payment)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">การขายหมู</span>
                                    </td>
                                    <td>
                                        <strong>บันทึกการชำระเงิน</strong><br>
                                        ฟาร์ม: {{ $payment->pigSale->farm->farm_name ?? '-' }}<br>
                                        รุ่น: {{ $payment->pigSale->batch->batch_code ?? '-' }}<br>
                                        จำนวน: {{ number_format($payment->amount, 2) }} ฿
                                    </td>
                                    <td class="text-center">{{ $payment->recordedBy->name ?? '-' }}</td>
                                    <td class="text-center">{{ $payment->approved_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('payment_approvals.detail', $payment->id) }}"
                                            class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i> ดู
                                        </a>
                                    </td>
                                </tr>
                            @empty
                            @endforelse

                            {{-- Display Approved Notifications --}}
                            @forelse($approvedNotifications as $index => $notification)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration + count($approvedPayments) }}</td>
                                    <td class="text-center">
                                        @if ($notification->type === 'payment_recorded_pig_entry')
                                            <span class="badge bg-info">การรับเข้าหมู</span>
                                        @else
                                            <span class="badge bg-primary">การขายหมู</span>
                                        @endif
                                    </td>
                                    <td>{{ $notification->message }}</td>
                                    <td class="text-center">{{ $notification->relatedUser->name ?? '-' }}</td>
                                    <td class="text-center">{{ $notification->read_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('payment_approvals.detail', $notification->id) }}"
                                            class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i> ดู
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                @if(count($approvedPayments) == 0)
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">ไม่มีรายการที่อนุมัติแล้ว</td>
                                    </tr>
                                @endif
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $approvedPayments->links() }}

                {{-- Approved Cancel Requests Section --}}
                @if ($approvedCancelRequests && $approvedCancelRequests->count() > 0)
                    <hr class="my-4">
                    <h5 class="mb-3">
                        <i class="bi bi-check-circle"></i> ยกเลิกการขายแล้ว
                        <span class="badge bg-success">{{ $approvedCancelRequests->count() }}</span>
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-success mb-0">
                            <thead class="table-header-custom">
                                <tr>
                                    <th class="text-center">ลำดับ</th>
                                    <th class="text-center">เลขที่ขาย</th>
                                    <th class="text-center">จำนวนหมู</th>
                                    <th class="text-center">ผู้ขอยกเลิก</th>
                                    <th class="text-center">อนุมัติเมื่อ</th>
                                    <th class="text-center">การกระทำ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($approvedCancelRequests as $index => $cancelRequest)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="text-center">
                                            {{ $cancelRequest->relatedModel === 'PigSale'
                                                ? \App\Models\PigSale::find($cancelRequest->related_model_id)?->id
                                                : '-' }}
                                        </td>
                                        <td class="text-center">
                                            {{ $cancelRequest->relatedModel === 'PigSale'
                                                ? \App\Models\PigSale::find($cancelRequest->related_model_id)?->quantity . ' ตัว'
                                                : '-' }}
                                        </td>
                                        <td class="text-center">{{ $cancelRequest->relatedUser->name ?? '-' }}</td>
                                        <td class="text-center">{{ $cancelRequest->updated_at->format('d/m/Y H:i') }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('payment_approvals.detail', $cancelRequest->id) }}"
                                                class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i> ดู
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Tab: Rejected --}}
            <div class="tab-pane fade" id="rejected" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-primary mb-0">
                        <thead class="table-header-custom">
                            <tr>
                                <th class="text-center">ลำดับ</th>
                                <th class="text-center">ประเภท</th>
                                <th class="text-center">รายละเอียด</th>
                                <th class="text-center">ผู้บันทึก</th>
                                <th class="text-center">ปฏิเสธเมื่อ</th>
                                <th class="text-center">เหตุผล</th>
                                <th class="text-center">การกระทำ</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Display Rejected Payment Records --}}
                            @forelse($rejectedPayments as $index => $payment)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">การขายหมู</span>
                                    </td>
                                    <td>
                                        <strong>บันทึกการชำระเงิน</strong><br>
                                        ฟาร์ม: {{ $payment->pigSale->farm->farm_name ?? '-' }}<br>
                                        รุ่น: {{ $payment->pigSale->batch->batch_code ?? '-' }}<br>
                                        จำนวน: {{ number_format($payment->amount, 2) }} ฿
                                    </td>
                                    <td class="text-center">{{ $payment->recordedBy->name ?? '-' }}</td>
                                    <td class="text-center">{{ $payment->rejected_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td>{{ $payment->reject_reason ?? '-' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('payment_approvals.detail', $payment->id) }}"
                                            class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i> ดู
                                        </a>
                                    </td>
                                </tr>
                            @empty
                            @endforelse

                            {{-- Display Rejected Notifications --}}
                            @forelse($rejectedNotifications as $index => $notification)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration + count($rejectedPayments) }}</td>
                                    <td class="text-center">
                                        @if ($notification->type === 'payment_recorded_pig_entry')
                                            <span class="badge bg-info">การรับเข้าหมู</span>
                                        @else
                                            <span class="badge bg-primary">การขายหมู</span>
                                        @endif
                                    </td>
                                    <td>{{ $notification->message }}</td>
                                    <td class="text-center">{{ $notification->relatedUser->name ?? '-' }}</td>
                                    <td class="text-center">{{ $notification->read_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td>{{ $notification->approval_notes ?? '-' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('payment_approvals.detail', $notification->id) }}"
                                            class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i> ดู
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                @if(count($rejectedPayments) == 0)
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">ไม่มีรายการที่ปฏิเสธ</td>
                                    </tr>
                                @endif
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $rejectedPayments->links() }}

                {{-- Display Rejected Notifications (continued) --}}
                @forelse($rejectedNotifications as $index => $notification)
                    <tr>
                        <td class="text-center">{{ $loop->iteration + count($rejectedPayments) }}</td>
                        <td class="text-center">
                            @if ($notification->type === 'payment_recorded_pig_entry')
                                <span class="badge bg-info">การรับเข้าหมู</span>
                            @else
                                <span class="badge bg-primary">การขายหมู</span>
                            @endif
                        </td>
                        <td>{{ $notification->message }}</td>
                        <td class="text-center">{{ $notification->relatedUser->name ?? '-' }}</td>
                        <td class="text-center">{{ $notification->read_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td>{{ $notification->approval_notes ?? '-' }}</td>
                        <td class="text-center">
                            <a href="{{ route('payment_approvals.detail', $notification->id) }}"
                                class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> ดู
                            </a>
                        </td>
                    </tr>
                @empty
                    @if(count($rejectedPayments) == 0)
                        <tr>
                            <td colspan="7" class="text-center text-muted">ไม่มีรายการที่ปฏิเสธ</td>
                        </tr>
                    @endif
                @endforelse

                {{-- Rejected Cancel Requests Section --}}
                @if ($rejectedCancelRequests && $rejectedCancelRequests->count() > 0)
                    <hr class="my-4">
                    <h5 class="mb-3">
                        <i class="bi bi-x-circle"></i> ปฏิเสธการยกเลิก
                        <span class="badge bg-danger">{{ $rejectedCancelRequests->count() }}</span>
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-danger mb-0">
                            <thead class="table-header-custom">
                                <tr>
                                    <th class="text-center">ลำดับ</th>
                                    <th class="text-center">เลขที่ขาย</th>
                                    <th class="text-center">จำนวนหมู</th>
                                    <th class="text-center">ผู้ขอยกเลิก</th>
                                    <th class="text-center">ปฏิเสธเมื่อ</th>
                                    <th class="text-center">เหตุผลปฏิเสธ</th>
                                    <th class="text-center">การกระทำ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rejectedCancelRequests as $index => $cancelRequest)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="text-center">
                                            {{ $cancelRequest->relatedModel === 'PigSale'
                                                ? \App\Models\PigSale::find($cancelRequest->related_model_id)?->id
                                                : '-' }}
                                        </td>
                                        <td class="text-center">
                                            {{ $cancelRequest->relatedModel === 'PigSale'
                                                ? \App\Models\PigSale::find($cancelRequest->related_model_id)?->quantity . ' ตัว'
                                                : '-' }}
                                        </td>
                                        <td class="text-center">{{ $cancelRequest->relatedUser->name ?? '-' }}</td>
                                        <td class="text-center">{{ $cancelRequest->updated_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ Str::limit($cancelRequest->approval_notes, 50) }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('payment_approvals.detail', $cancelRequest->id) }}"
                                                class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i> ดู
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
