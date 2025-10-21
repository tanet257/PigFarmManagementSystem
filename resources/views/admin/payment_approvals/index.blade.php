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
                        <h3>{{ $pendingNotifications->total() }}</h3>
                        <p class="mb-0"><i class="bi bi-hourglass-split"></i> รอการอนุมัติ</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center bg-success text-white">
                    <div class="card-status-summary">
                        <h3>{{ $approvedNotifications->total() }}</h3>
                        <p class="mb-0"><i class="bi bi-check-circle"></i> อนุมัติแล้ว</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center bg-danger text-white">
                    <div class="card-status-summary">
                        <h3>{{ $rejectedNotifications->total() }}</h3>
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
                    <span class="badge bg-warning ms-2">{{ $pendingNotifications->total() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="approved-tab" data-bs-toggle="tab" href="#approved" role="tab">
                    <i class="bi bi-check-circle"></i> อนุมัติแล้ว
                    <span class="badge bg-success ms-2">{{ $approvedNotifications->total() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="rejected-tab" data-bs-toggle="tab" href="#rejected" role="tab">
                    <i class="bi bi-x-circle"></i> ปฏิเสธแล้ว
                    <span class="badge bg-danger ms-2">{{ $rejectedNotifications->total() }}</span>
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
                            @forelse($approvedNotifications as $index => $notification)
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
                                    <td class="text-center">{{ $notification->read_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('payment_approvals.detail', $notification->id) }}"
                                            class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i> ดู
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">ไม่มีรายการที่อนุมัติแล้ว</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $approvedNotifications->links() }}
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
                            @forelse($rejectedNotifications as $index => $notification)
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
                                <tr>
                                    <td colspan="7" class="text-center text-muted">ไม่มีรายการที่ปฏิเสธ</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $rejectedNotifications->links() }}
            </div>
        </div>
    </div>
@endsection
