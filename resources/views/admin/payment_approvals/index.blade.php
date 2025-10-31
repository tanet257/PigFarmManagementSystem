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
                        <h3>{{ $pendingPigSales->total() ?? 0 }}</h3>
                        <p class="mb-0"><i class="bi bi-hourglass-split"></i> รอการอนุมัติ</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center bg-success text-white">
                    <div class="card-status-summary">
                        <h3>{{ $approvedPigSales->total() ?? 0 }}</h3>
                        <p class="mb-0"><i class="bi bi-check-circle"></i> อนุมัติแล้ว</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center bg-danger text-white">
                    <div class="card-status-summary">
                        <h3>{{ $rejectedPigSales->total() ?? 0 }}</h3>
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
                        <span class="badge bg-warning ms-2">{{ ($pendingPayments->total() ?? 0) + ($pendingPigSales->total() ?? 0) }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="cancel-requests-tab" data-bs-toggle="tab" href="#cancel-requests" role="tab">
                        <i class="bi bi-x-lg"></i> คำขอยกเลิก
                        <span class="badge bg-secondary ms-2">{{ $pendingCancelSales->total() ?? 0 }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="approved-tab" data-bs-toggle="tab" href="#approved" role="tab">
                        <i class="bi bi-check-circle"></i> อนุมัติแล้ว
                        <span class="badge bg-success ms-2">{{ $approvedPigSales->total() ?? 0 }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="rejected-tab" data-bs-toggle="tab" href="#rejected" role="tab">
                        <i class="bi bi-x-circle"></i> ปฏิเสธแล้ว
                        <span class="badge bg-danger ms-2">{{ $rejectedPigSales->total() ?? 0 }}</span>
                    </a>
                </li>
            </ul>
            <!-- Export Buttons for each tab -->
            <div class="ms-2 d-flex gap-2">
                <button class="btn btn-sm btn-success" id="exportPendingBtn" onclick="exportTableToCSV('#pendingTable', 'การอนุมัติชำระเงิน_' + new Date().toISOString().split('T')[0], [5])" title="ส่งออก CSV">
                    <i class="bi bi-file-earmark-spreadsheet"></i> CSV
                </button>
            </div>
        </div>

        {{-- Tab Content --}}
        <div class="tab-content">
            {{-- Tab: Combined Pending Approvals (Payments + Pig Sales) --}}
            <div class="tab-pane fade show active" id="pending" role="tabpanel">
                @if($pendingPayments->count() > 0 || $pendingPigSales->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-primary mb-0" id="pendingTable">
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
                                {{-- Display Pending Payments --}}
                                @forelse($pendingPayments as $index => $payment)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-info">
                                                <i class="bi bi-wallet"></i> ชำระเงิน
                                            </span>
                                        </td>
                                        <td>
                                            <strong>บันทึกชำระเงิน</strong><br>
                                            เลขที่: {{ $payment->payment_number ?? 'N/A' }}<br>
                                            <small>{{ $payment->pigSale->farm->farm_name ?? 'N/A' }} | {{ $payment->pigSale->batch->batch_code ?? 'N/A' }}</small><br>
                                            วิธีชำระ:
                                            @switch($payment->payment_method ?? '')
                                                @case('cash') <span class="badge bg-success">สด</span> @break
                                                @case('transfer') <span class="badge bg-info">โอน</span> @break
                                                @case('cheque') <span class="badge bg-warning">เช็ค</span> @break
                                                @default <span class="badge bg-secondary">{{ $payment->payment_method ?? '-' }}</span>
                                            @endswitch
                                            <br>
                                            <strong>จำนวนเงิน: ฿{{ number_format($payment->amount ?? 0, 2) }}</strong>
                                        </td>
                                        <td class="text-center">{{ $payment->recordedBy->name ?? 'N/A' }}</td>
                                        <td class="text-center">{{ \Carbon\Carbon::parse($payment->created_at)->format('d/m/Y H:i') }}</td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <form method="POST" action="{{ route('payment_approvals.approve_payment', $payment->id) }}" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-success btn-sm" title="อนุมัติ" onclick="return confirm('อนุมัติการชำระเงินนี้ใช่หรือไม่?')">
                                                        <i class="bi bi-check"></i> อนุมัติ
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('payment_approvals.reject_payment', $payment->id) }}" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="ปฏิเสธ" onclick="return confirm('ปฏิเสธการชำระเงินนี้ใช่หรือไม่?')">
                                                        <i class="bi bi-x"></i> ปฏิเสธ
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                @endforelse

                                {{-- Display Pending PigSales --}}
                                @forelse($pendingPigSales as $index => $pigSale)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration + ($pendingPayments->count() ?? 0) }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $pigSale->sell_type === 'หมูตาย' ? 'danger' : 'info' }}">
                                                {{ $pigSale->sell_type ?? 'หมูปกติ' }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>บันทึกการขายหมู</strong><br>
                                            ฟาร์ม: {{ $pigSale->farm->farm_name ?? '-' }}<br>
                                            รุ่น: {{ $pigSale->batch->batch_code ?? '-' }}<br>
                                            ผู้ซื้อ: {{ $pigSale->buyer_name ?? '-' }}<br>
                                            <strong>จำนวน:</strong> {{ $pigSale->quantity }} ตัว | <strong>ราคาต่อตัว:</strong> ฿{{ number_format($pigSale->price_per_pig ?? 0, 2) }} | <strong>ราคารวม:</strong> ฿{{ number_format($pigSale->net_total, 2) }}
                                        </td>
                                        <td class="text-center">{{ $pigSale->createdBy->name ?? '-' }}</td>
                                        <td class="text-center">{{ $pigSale->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#approvePigSaleModal{{ $pigSale->id }}" title="อนุมัติ">
                                                    <i class="bi bi-check"></i> อนุมัติ
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#rejectPigSaleModal{{ $pigSale->id }}" title="ปฏิเสธ">
                                                    <i class="bi bi-x"></i> ปฏิเสธ
                                                </button>
                                            </div>
                                        </td>
                                    </tr>                                    {{-- Approve PigSale Modal --}}
                                    <div class="modal fade" id="approvePigSaleModal{{ $pigSale->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-success text-white">
                                                    <h5 class="modal-title">อนุมัติการขายหมู</h5>
                                                    <button type="button" class="btn-close btn-close-white"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('payment_approvals.approve_pig_sale', $pigSale->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="modal-body">
                                                        <div class="alert alert-info">
                                                            <strong>การขายหมู:</strong><br>
                                                            ประเภท: {{ $pigSale->sell_type ?? 'หมูปกติ' }}<br>
                                                            จำนวน: {{ $pigSale->quantity }} ตัว<br>
                                                            ราคาต่อตัว: ฿{{ number_format($pigSale->price_per_pig ?? 0, 2) }}<br>
                                                            ราคารวม: ฿{{ number_format($pigSale->net_total, 2) }}
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

                                    {{-- Reject PigSale Modal --}}
                                    <div class="modal fade" id="rejectPigSaleModal{{ $pigSale->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title">ปฏิเสธการขายหมู</h5>
                                                    <button type="button" class="btn-close btn-close-white"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('payment_approvals.reject_pig_sale', $pigSale->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="modal-body">
                                                        <div class="alert alert-warning">
                                                            <strong>การขายหมู:</strong><br>
                                                            จำนวน: {{ $pigSale->quantity }} ตัว | ราคารวม: ฿{{ number_format($pigSale->net_total, 2) }}
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
                @else
                    <div class="alert alert-info text-center py-4">
                        <i class="bi bi-check-circle"></i> ไม่มีรายการรอการอนุมัติ
                    </div>
                @endif
            </div>

            {{-- NEW: Tab: Cancel Requests --}}
            <div class="tab-pane fade" id="cancel-requests" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-primary mb-0">
                        <thead class="table-header-custom">
                            <tr>
                                <th class="text-center">ลำดับ</th>
                                <th class="text-center">ประเภท</th>
                                <th class="text-center">รายละเอียด</th>
                                <th class="text-center">ผู้ขอยกเลิก</th>
                                <th class="text-center">วันที่ขอ</th>
                                <th class="text-center">การกระทำ</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Display Cancel Requests --}}
                            @forelse($pendingCancelSales as $index => $pigSale)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $pigSale->sell_type === 'หมูตาย' ? 'danger' : 'info' }}">
                                            {{ $pigSale->sell_type ?? 'หมูปกติ' }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>คำขอยกเลิกการขายหมู</strong><br>
                                        ฟาร์ม: {{ $pigSale->farm->farm_name ?? '-' }}<br>
                                        รุ่น: {{ $pigSale->batch->batch_code ?? '-' }}<br>
                                        ผู้ซื้อ: {{ $pigSale->buyer_name ?? '-' }}<br>
                                        จำนวน: {{ $pigSale->quantity }} ตัว | ราคารวม: {{ number_format($pigSale->net_total, 2) }} ฿
                                    </td>
                                    <td class="text-center">{{ $pigSale->createdBy->name ?? '-' }}</td>
                                    <td class="text-center">{{ $pigSale->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                            data-bs-target="#approveCancelModal{{ $pigSale->id }}">
                                            <i class="bi bi-check"></i> อนุมัติ
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                            data-bs-target="#rejectCancelModal{{ $pigSale->id }}">
                                            <i class="bi bi-x"></i> ปฏิเสธ
                                        </button>
                                    </td>
                                </tr>

                                {{-- Approve Cancel Modal --}}
                                <div class="modal fade" id="approveCancelModal{{ $pigSale->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title">อนุมัติการยกเลิกการขายหมู</h5>
                                                <button type="button" class="btn-close btn-close-white"
                                                    data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('payment_approvals.approve_cancel_sale', $pigSale->id) }}"
                                                method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <div class="modal-body">
                                                    <div class="alert alert-info">
                                                        <strong>การขายหมู:</strong><br>
                                                        ประเภท: {{ $pigSale->sell_type ?? 'หมูปกติ' }}<br>
                                                        จำนวน: {{ $pigSale->quantity }} ตัว<br>
                                                        ราคารวม: {{ number_format($pigSale->net_total, 2) }} ฿
                                                    </div>
                                                    <div class="alert alert-warning">
                                                        <strong>หมายเหตุ:</strong><br>
                                                        หลังจากอนุมัติการยกเลิกนี้ การขายหมูจะถูกยกเลิกและหมูจะถูกคืนไปยังหมวด "บันทึกแล้ว"
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
                                <div class="modal fade" id="rejectCancelModal{{ $pigSale->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title">ปฏิเสธการยกเลิกการขายหมู</h5>
                                                <button type="button" class="btn-close btn-close-white"
                                                    data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('payment_approvals.reject_cancel_sale', $pigSale->id) }}"
                                                method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <div class="modal-body">
                                                    <div class="alert alert-warning">
                                                        <strong>การขายหมู:</strong><br>
                                                        จำนวน: {{ $pigSale->quantity }} ตัว | ราคารวม: {{ number_format($pigSale->net_total, 2) }} ฿
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
                                <tr>
                                    <td colspan="6" class="text-center text-muted">ไม่มีคำขอยกเลิกการขายหมู</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $pendingCancelSales->links() }}
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
                            {{-- Display Approved PigSales --}}
                            @forelse($approvedPigSales as $index => $pigSale)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $pigSale->sell_type === 'หมูตาย' ? 'danger' : 'info' }}">
                                            {{ $pigSale->sell_type ?? 'หมูปกติ' }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>บันทึกการขายหมู</strong><br>
                                        ฟาร์ม: {{ $pigSale->farm->farm_name ?? '-' }}<br>
                                        รุ่น: {{ $pigSale->batch->batch_code ?? '-' }}<br>
                                        จำนวน: {{ $pigSale->quantity }} ตัว | ราคา: {{ number_format($pigSale->net_total, 2) }} ฿
                                    </td>
                                    <td class="text-center">{{ $pigSale->createdBy->name ?? '-' }}</td>
                                    <td class="text-center">{{ $pigSale->approved_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td class="text-center">-</td>
                                </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $approvedPigSales->links() }}
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
                            {{-- Display Rejected PigSales --}}
                            @forelse($rejectedPigSales as $index => $pigSale)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $pigSale->sell_type === 'หมูตาย' ? 'danger' : 'info' }}">
                                            {{ $pigSale->sell_type ?? 'หมูปกติ' }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>บันทึกการขายหมู</strong><br>
                                        ฟาร์ม: {{ $pigSale->farm->farm_name ?? '-' }}<br>
                                        รุ่น: {{ $pigSale->batch->batch_code ?? '-' }}<br>
                                        จำนวน: {{ $pigSale->quantity }} ตัว | ราคา: {{ number_format($pigSale->net_total, 2) }} ฿
                                    </td>
                                    <td class="text-center">{{ $pigSale->createdBy->name ?? '-' }}</td>
                                    <td class="text-center">{{ $pigSale->rejected_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td>{{ $pigSale->rejection_reason ?? '-' }}</td>
                                    <td class="text-center">-</td>
                                </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $rejectedPigSales->links() }}
            </div>



        @endsection
