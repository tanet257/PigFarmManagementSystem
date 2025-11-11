@extends('layouts.admin')

@section('title', 'รายละเอียดการชำระเงิน')

@section('content')
    <div class="container my-5">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="bi bi-receipt"></i>
                    รายละเอียดการชำระเงิน
                    @if ($type === 'pig_entry')
                        (การรับเข้าหมู)
                    @else
                        (การขายหมู)
                    @endif
                </h4>
            </div>

            <div class="card-body">
                <div class="alert alert-info">
                    <strong>สถานะ:</strong>
                    @if ($notification->approval_status === 'pending')
                        <span class="badge bg-warning">รอการอนุมัติ</span>
                    @elseif ($notification->approval_status === 'approved')
                        <span class="badge bg-success">อนุมัติแล้ว</span>
                    @else
                        <span class="badge bg-danger">ปฏิเสธแล้ว</span>
                    @endif
                </div>

                @if ($type === 'pig_entry')
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="text-primary mb-3">
                                <i class="bi bi-info-circle"></i> ข้อมูลการรับเข้าหมู
                            </h5>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%"><strong>ฟาร์ม:</strong></td>
                                    <td>{{ $paymentData->farm->farm_name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>รุ่น (Batch):</strong></td>
                                    <td>{{ $paymentData->batch->batch_code ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>วันที่รับเข้า:</strong></td>
                                    <td>{{ $paymentData->pig_entry_date }}</td>
                                </tr>
                                <tr>
                                    <td><strong>จำนวนหมู:</strong></td>
                                    <td><strong class="text-success">{{ $paymentData->total_pig_amount }} ตัว</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>น้ำหนักรวม:</strong></td>
                                    <td>{{ number_format($paymentData->total_pig_weight, 2) }} กก.</td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h5 class="text-primary mb-3">
                                <i class="bi bi-cash-coin"></i> ข้อมูลการชำระเงิน
                            </h5>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%"><strong>ราคาหมู:</strong></td>
                                    <td>{{ number_format($paymentData->total_pig_price, 2) }} ฿</td>
                                </tr>
                                <tr>
                                    <td><strong>น้ำหนักเกิน:</strong></td>
                                    <td>{{ number_format($paymentData->batch?->costs->sum('excess_weight_cost') ?? 0, 2) }} ฿
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>ค่าขนส่ง:</strong></td>
                                    <td>{{ number_format($paymentData->batch?->costs->sum('transport_cost') ?? 0, 2) }} ฿
                                    </td>
                                </tr>
                                <tr style="background-color: #e8f5e9;">
                                    <td><strong>รวมทั้งสิ้น:</strong></td>
                                    <td>
                                        <strong class="text-success">
                                            {{ number_format(
                                                $paymentData->total_pig_price +
                                                    ($paymentData->batch->costs->sum('excess_weight_cost') ?? 0) +
                                                    ($paymentData->batch->costs->sum('transport_cost') ?? 0),
                                                2,
                                            ) }}
                                            ฿
                                        </strong>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                @else
                    {{-- Pig Sale Details --}}
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="text-primary mb-3">
                                <i class="bi bi-info-circle"></i> ข้อมูลการขายหมู
                            </h5>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%"><strong>ฟาร์ม:</strong></td>
                                    <td>{{ $paymentData->farm->farm_name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>รุ่น (Batch):</strong></td>
                                    <td>{{ $paymentData->batch->batch_code ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>วันที่ขาย:</strong></td>
                                    <td>{{ $paymentData->date }}</td>
                                </tr>
                                <tr>
                                    <td><strong>จำนวนหมู:</strong></td>
                                    <td><strong class="text-success">{{ $paymentData->quantity }} ตัว</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>น้ำหนักรวม:</strong></td>
                                    <td>{{ number_format($paymentData->total_weight, 2) }} กก.</td>
                                </tr>
                                <tr>
                                    <td><strong>ผู้ซื้อ:</strong></td>
                                    <td>{{ $paymentData->buyer_name }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h5 class="text-primary mb-3">
                                <i class="bi bi-cash-coin"></i> ข้อมูลการชำระเงิน
                            </h5>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%"><strong>ราคาต่อ kg:</strong></td>
                                    <td>{{ number_format($paymentData->price_per_kg, 2) }} ฿</td>
                                </tr>
                                <tr>
                                    <td><strong>ราคารวม:</strong></td>
                                    <td>{{ number_format($paymentData->total_price, 2) }} ฿</td>
                                </tr>
                                <tr>
                                    <td><strong>ค่าขนส่ง:</strong></td>
                                    <td>{{ number_format($paymentData->shipping_cost ?? 0, 2) }} ฿</td>
                                </tr>
                                <tr style="background-color: #e8f5e9;">
                                    <td><strong>ยอดรวมสุทธิ:</strong></td>
                                    <td>
                                        <strong class="text-success">{{ number_format($paymentData->net_total, 2) }}
                                            ฿</strong>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Approval/Rejection Information --}}
                @if ($notification->approval_status !== 'pending')
                    <hr>
                    <div class="alert {{ $notification->approval_status === 'approved' ? 'alert-success' : 'alert-danger' }}">
                        <strong>
                            @if ($notification->approval_status === 'approved')
                                <i class="bi bi-check-circle"></i> อนุมัติเรียบร้อยแล้ว
                            @else
                                <i class="bi bi-x-circle"></i> ปฏิเสธแล้ว
                            @endif
                        </strong>
                        <p class="mb-0 mt-2">
                            <strong>หมายเหตุ:</strong> {{ $notification->approval_notes ?? '-' }}
                        </p>
                    </div>
                @endif

                {{-- Action Buttons for Admin Approval --}}
                @if ($notification->approval_status === 'pending' && auth()->user()->hasRole('admin'))
                    <hr>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success" data-bs-toggle="modal"
                            data-bs-target="#approveModal">
                            <i class="bi bi-check-circle"></i> อนุมัติ
                        </button>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                            data-bs-target="#rejectModal">
                            <i class="bi bi-x-circle"></i> ปฏิเสธ
                        </button>
                        <a href="{{ route('payment_approvals.index') }}" class="btn btn-secondary ms-auto">
                            <i class="bi bi-arrow-left"></i> กลับ
                        </a>
                    </div>
                @else
                    <a href="{{ route('payment_approvals.index') }}" class="btn btn-secondary mt-3">
                        <i class="bi bi-arrow-left"></i> กลับ
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Approve Modal --}}
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">อนุมัติการชำระเงิน</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('payment_approvals.approve', $notification->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">หมายเหตุการอนุมัติ (ไม่จำเป็น)</label>
                            <textarea name="approval_notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> อนุมัติ
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
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">ปฏิเสธการชำระเงิน</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('payment_approvals.reject', $notification->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">เหตุผลในการปฏิเสธ <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-x-circle"></i> ปฏิเสธ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
