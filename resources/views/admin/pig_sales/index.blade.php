@extends('layouts.admin')

@section('title', 'บันทึกการขายหมู')

@section('content')
    <div class="container my-5">
        <div class="card-header">
            <h1 class="text-center">บันทึกการขายหมู (Pig Sales)</h1>
        </div>
        <div class="py-2"></div>

    {{-- Toolbar --}}
    <div class="card-custom-secondary mb-3">
        <form method="GET" action="{{ route('pig_sale.index') }}" class="d-flex align-items-center gap-2 flex-wrap">
            <!-- Date Filter Dropdown (Orange) -->
            <div class="dropdown">
                <button class="btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" 
                    style="background-color: #FF6500; color: white; border: none;">
                    <i class="bi bi-calendar"></i> 
                    @if(request('selected_date') == 'today') วันนี้
                    @elseif(request('selected_date') == 'this_week') สัปดาห์นี้
                    @elseif(request('selected_date') == 'this_month') เดือนนี้
                    @elseif(request('selected_date') == 'this_year') ปีนี้
                    @else วันที่ทั้งหมด
                    @endif
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('pig_sale.index', array_merge(request()->except('selected_date'), [])) }}">วันที่ทั้งหมด</a></li>
                    <li><a class="dropdown-item {{ request('selected_date') == 'today' ? 'active' : '' }}" 
                        href="{{ route('pig_sale.index', array_merge(request()->all(), ['selected_date' => 'today'])) }}">วันนี้</a></li>
                    <li><a class="dropdown-item {{ request('selected_date') == 'this_week' ? 'active' : '' }}" 
                        href="{{ route('pig_sale.index', array_merge(request()->all(), ['selected_date' => 'this_week'])) }}">สัปดาห์นี้</a></li>
                    <li><a class="dropdown-item {{ request('selected_date') == 'this_month' ? 'active' : '' }}" 
                        href="{{ route('pig_sale.index', array_merge(request()->all(), ['selected_date' => 'this_month'])) }}">เดือนนี้</a></li>
                    <li><a class="dropdown-item {{ request('selected_date') == 'this_year' ? 'active' : '' }}" 
                        href="{{ route('pig_sale.index', array_merge(request()->all(), ['selected_date' => 'this_year'])) }}">ปีนี้</a></li>
                </ul>
            </div>

            <!-- Farm Filter Dropdown (Dark Blue) -->
            <div class="dropdown">
                <button class="btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" 
                    style="background-color: #1E3E62; color: white; border: none;">
                    <i class="bi bi-building"></i> {{ request('farm_id') ? ($farms->find(request('farm_id'))->farm_name ?? 'ฟาร์ม') : 'ฟาร์มทั้งหมด' }}
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('pig_sale.index', array_merge(request()->except('farm_id'), [])) }}">ฟาร์มทั้งหมด</a></li>
                    @foreach ($farms as $farm)
                        <li><a class="dropdown-item {{ request('farm_id') == $farm->id ? 'active' : '' }}" 
                            href="{{ route('pig_sale.index', array_merge(request()->all(), ['farm_id' => $farm->id])) }}">
                            {{ $farm->farm_name }}
                        </a></li>
                    @endforeach
                </ul>
            </div>

            <!-- Batch Filter Dropdown (Dark Blue) -->
            <div class="dropdown">
                <button class="btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" 
                    style="background-color: #1E3E62; color: white; border: none;">
                    <i class="bi bi-layers"></i> {{ request('batch_id') ? ($batches->find(request('batch_id'))->batch_code ?? 'รุ่น') : 'รุ่นทั้งหมด' }}
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('pig_sale.index', array_merge(request()->except('batch_id'), [])) }}">รุ่นทั้งหมด</a></li>
                    @foreach ($batches as $batch)
                        <li><a class="dropdown-item {{ request('batch_id') == $batch->id ? 'active' : '' }}" 
                            href="{{ route('pig_sale.index', array_merge(request()->all(), ['batch_id' => $batch->id])) }}">
                            {{ $batch->batch_code }}
                        </a></li>
                    @endforeach
                </ul>
            </div>

            <div class="ms-auto d-flex gap-2">
                <a class="btn btn-outline-success btn-sm" href="{{ route('pig_sale.export.csv') }}">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> CSV
                </a>
                <a class="btn btn-outline-danger btn-sm" href="{{ route('pig_sale.export.pdf') }}">        {{-- Table --}}
        <div class="card-custom-secondary mt-3">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-header-custom">
                        <tr>
                            <th class="text-center">เลขที่</th>
                            <th class="text-center">
                                <div class="d-flex justify-content-center align-items-center">
                                    <span>วันที่ขาย</span>
                                    <div class="icon-vertical ms-2">
                                        <i class="fa fa-arrow-up increment" onclick="sortTable('sell_date','asc')"></i>
                                        <i class="fa fa-arrow-down decrement" onclick="sortTable('sell_date','desc')"></i>
                                    </div>
                                </div>
                            </th>
                            <th class="text-center">ลูกค้า</th>
                            <th class="text-center">ฟาร์ม</th>
                            <th class="text-center">รุ่น</th>
                            <th class="text-center">
                                <div class="d-flex justify-content-center align-items-center">
                                    <span>จำนวน</span>
                                    <div class="icon-vertical ms-2">
                                        <i class="fa fa-arrow-up increment" onclick="sortTable('quantity','asc')"></i>
                                        <i class="fa fa-arrow-down decrement" onclick="sortTable('quantity','desc')"></i>
                                    </div>
                                </div>
                            </th>
                            <th class="text-center">น้ำหนัก (kg)</th>
                            <th class="text-center">
                                <div class="d-flex justify-content-center align-items-center">
                                    <span>ราคาสุทธิ</span>
                                    <div class="icon-vertical ms-2">
                                        <i class="fa fa-arrow-up increment" onclick="sortTable('net_total','asc')"></i>
                                        <i class="fa fa-arrow-down decrement" onclick="sortTable('net_total','desc')"></i>
                                    </div>
                                </div>
                            </th>
                            <th class="text-center">สถานะอนุมัติ</th>
                            <th class="text-center">สถานะชำระ</th>
                            <th class="text-center">บันทึกโดย</th>
                            <th class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pigSales as $sell)
                            <tr class="clickable-row" data-bs-toggle="modal"
                                data-bs-target="#viewModal{{ $sell->id }}">
                                <td class="text-center">
                                    <strong>{{ $sell->sale_number ?? 'SELL-' . str_pad($sell->id, 3, '0', STR_PAD_LEFT) }}</strong>
                                </td>
                                <td class="text-center">
                                    {{ $sell->sell_date ? \Carbon\Carbon::parse($sell->sell_date)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="text-center">
                                    {{ $sell->customer->customer_name ?? ($sell->buyer_name ?? '-') }}
                                </td>
                                <td class="text-center">{{ $sell->farm->farm_name ?? '-' }}</td>
                                <td class="text-center">{{ $sell->batch->batch_code ?? '-' }}</td>
                                <td class="text-center">
                                    <strong>{{ number_format($sell->quantity) }} ตัว</strong>
                                </td>
                                <td class="text-center">
                                    @if ($sell->actual_weight)
                                        {{ number_format($sell->actual_weight, 2) }}
                                        <small class="text-muted d-block">(ชั่งจริง)</small>
                                    @else
                                        {{ number_format($sell->total_weight, 2) }}
                                    @endif
                                </td>
                                <td class="text-center">
                                    <strong>{{ number_format($sell->net_total ?? $sell->total_price, 2) }}</strong>

                                    @if ($sell->shipping_cost > 0)
                                        <small class="text-info d-block">ค่าขนส่ง:
                                            {{ number_format($sell->shipping_cost, 2) }}</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($sell->payment_status == 'ชำระแล้ว')
                                        <span class="badge bg-success">ชำระแล้ว</span>
                                    @elseif ($sell->payment_status == 'ชำระบางส่วน')
                                        <span class="badge bg-warning">ชำระบางส่วน</span>
                                        <small class="d-block mt-1">คงเหลือ:
                                            {{ number_format($sell->balance, 2) }}</small>
                                    @elseif ($sell->payment_status == 'เกินกำหนด')
                                        <span class="badge bg-danger">เกินกำหนด</span>
                                    @else
                                        <span class="badge bg-secondary">รอชำระ</span>
                                        @if ($sell->due_date)
                                            <small
                                                class="d-block mt-1">{{ \Carbon\Carbon::parse($sell->due_date)->format('d/m/Y') }}</small>
                                        @endif
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($sell->approved_at)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> อนุมัติแล้ว
                                        </span>
                                        <small class="text-muted d-block mt-1">
                                            โดย: {{ $sell->approvedBy->name ?? '-' }}
                                        </small>
                                    @else
                                        <span class="badge bg-warning">
                                            <i class="bi bi-clock"></i> รออนุมัติ
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <small class="text-muted">
                                        <i class="bi bi-person-fill"></i> {{ $sell->createdBy->name ?? '-' }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                        data-bs-target="#viewModal{{ $sell->id }}">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    @if (!$sell->approved_at && auth()->user()->hasPermission('approve_sales'))
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#approveModal{{ $sell->id }}" title="อนุมัติการขาย">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    @endif

                                    @if ($sell->payment_status != 'ชำระแล้ว')
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                            data-bs-target="#paymentModal{{ $sell->id }}">
                                            <i class="bi bi-cash"></i>
                                        </button>
                                    @endif

                                    <form action="{{ route('pig_sale.cancel', $sell->id) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('ต้องการยกเลิกการขายนี้หรือไม่?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-danger">❌ ไม่มีข้อมูลการขาย</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-between mt-3">
            <div>
                แสดง {{ $pigSales->firstItem() ?? 0 }} ถึง {{ $pigSales->lastItem() ?? 0 }} จาก
                {{ $pigSales->total() ?? 0 }} แถว
            </div>
            <div>
                {{ $pigSales->withQueryString()->links() }}
            </div>
        </div>
    </div>
    </div>

    {{-- Modals (นอก loop เพื่อไม่ให้รบกวน layout) --}}
    @foreach ($pigSales as $sell)
        {{-- View Detail Modal --}}
        <div class="modal fade" id="viewModal{{ $sell->id }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">รายละเอียดการขาย - {{ $sell->sale_number }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">ข้อมูลการขาย</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>เลขที่:</strong></td>
                                        <td>{{ $sell->sale_number ?? 'SELL-' . str_pad($sell->id, 3, '0', STR_PAD_LEFT) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>วันที่ขาย:</strong></td>
                                        <td>{{ \Carbon\Carbon::parse($sell->sell_date)->format('d/m/Y') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>ลูกค้า:</strong></td>
                                        <td>{{ $sell->customer->customer_name ?? $sell->buyer_name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>ฟาร์ม:</strong></td>
                                        <td>{{ $sell->farm->farm_name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>รุ่น:</strong></td>
                                        <td>{{ $sell->batch->batch_code }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>เล้า-คอก:</strong></td>
                                        <td>
                                            @if ($sell->pen)
                                                {{ $sell->pen->barn->barn_code ?? '' }} -
                                                {{ $sell->pen->pen_code }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>บันทึกโดย:</strong></td>
                                        <td>
                                            <i class="bi bi-person-fill text-primary"></i>
                                            {{ $sell->createdBy->name ?? '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>สถานะอนุมัติ:</strong></td>
                                        <td>
                                            @if ($sell->approved_at)
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i> อนุมัติแล้ว
                                                </span>
                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        <i class="bi bi-person-check-fill"></i>
                                                        โดย: {{ $sell->approvedBy->name ?? '-' }}
                                                    </small>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="bi bi-calendar-check"></i>
                                                        {{ \Carbon\Carbon::parse($sell->approved_at)->format('d/m/Y H:i') }}
                                                    </small>
                                                </div>
                                            @else
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-clock"></i> รออนุมัติ
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">รายละเอียด</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>จำนวน:</strong></td>
                                        <td>{{ number_format($sell->quantity) }} ตัว</td>
                                    </tr>
                                    <tr>
                                        <td><strong>น้ำหนัก:</strong></td>
                                        <td>{{ number_format($sell->actual_weight ?? $sell->total_weight, 2) }}
                                            kg</td>
                                    </tr>
                                    <tr>
                                        <td><strong>ราคา/kg:</strong></td>
                                        <td>{{ number_format($sell->price_per_kg, 2) }} บาท
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>ราคา/ตัว:</strong></td>
                                        <td>
                                            <strong class="text-primary">{{ number_format($sell->price_per_pig ?? 0, 2) }}
                                                บาท</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>ราคารวม:</strong></td>
                                        <td>{{ number_format($sell->total_price, 2) }} บาท</td>
                                    </tr>

                                    <tr>
                                        <td><strong>ค่าขนส่ง:</strong></td>
                                        <td>{{ number_format($sell->shipping_cost, 2) }} บาท
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>ราคาสุทธิ:</strong></td>
                                        <td><strong>{{ number_format($sell->net_total ?? $sell->total_price, 2) }}
                                                บาท</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <hr>
                        <h6 class="text-primary">การชำระเงิน</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>วิธีชำระ:</strong></td>
                                <td>{{ $sell->payment_method ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>สถานะ:</strong></td>
                                <td>{{ $sell->payment_status }}</td>
                            </tr>
                            <tr>
                                <td><strong>ยอดที่ชำระแล้ว:</strong></td>
                                <td>{{ number_format($sell->paid_amount, 2) }} บาท</td>
                            </tr>
                            <tr>
                                <td><strong>คงเหลือ:</strong></td>
                                <td>{{ number_format($sell->balance, 2) }} บาท</td>
                            </tr>
                        </table>
                        @if ($sell->note)
                            <hr>
                            <h6 class="text-primary">หมายเหตุ</h6>
                            <p>{{ $sell->note }}</p>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Approve Modal --}}
        <div class="modal fade" id="approveModal{{ $sell->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-check-circle"></i> ยืนยันการอนุมัติการขาย
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('pig_sale.approve', $sell->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                คุณกำลังจะอนุมัติการขายนี้ กรุณาตรวจสอบข้อมูลให้ถูกต้องก่อนอนุมัติ
                            </div>

                            <table class="table table-sm table-bordered">
                                <tr>
                                    <td class="bg-light" width="40%"><strong>เลขที่การขาย:</strong></td>
                                    <td>{{ $sell->sale_number }}</td>
                                </tr>
                                <tr>
                                    <td class="bg-light"><strong>ลูกค้า:</strong></td>
                                    <td>{{ $sell->customer->customer_name ?? $sell->buyer_name }}</td>
                                </tr>
                                <tr>
                                    <td class="bg-light"><strong>จำนวน:</strong></td>
                                    <td>{{ number_format($sell->quantity) }} ตัว</td>
                                </tr>
                                <tr>
                                    <td class="bg-light"><strong>ราคาสุทธิ:</strong></td>
                                    <td><strong
                                            class="text-success">{{ number_format($sell->net_total ?? $sell->total_price, 2) }}
                                            บาท</strong></td>
                                </tr>
                                <tr>
                                    <td class="bg-light"><strong>บันทึกโดย:</strong></td>
                                    <td>
                                        <i class="bi bi-person-fill text-primary"></i>
                                        {{ $sell->created_by ?? '-' }}
                                    </td>
                                </tr>
                            </table>

                            @if ($sell->created_by === auth()->user()->name)
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <strong>คำเตือน:</strong> คุณกำลังอนุมัติการขายที่ตัวเองสร้าง
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle"></i> ยกเลิก
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> ยืนยันอนุมัติ
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Payment Modal --}}
        <div class="modal fade" id="paymentModal{{ $sell->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">บันทึกการชำระเงิน</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('pig_sale.upload_receipt', $sell->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">ยอดที่ต้องชำระ</label>
                                <input type="text" class="form-control"
                                    value="{{ number_format($sell->balance ?? ($sell->net_total ?? $sell->total_price), 2) }} บาท"
                                    readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">จำนวนเงินที่ชำระ</label>
                                <input type="number" name="paid_amount" class="form-control" step="0.01"
                                    min="0"
                                    value="{{ $sell->balance ?? ($sell->net_total ?? $sell->total_price) }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">วิธีชำระเงิน</label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="เงินสด">เงินสด</option>
                                    <option value="โอนเงิน">โอนเงิน</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">อัปโหลดหลักฐานการชำระ (ถ้ามี)</label>
                                <input type="file" class="form-control" name="receipt_file"
                                    accept="image/*,application/pdf">
                                <small class="text-muted">รองรับไฟล์: JPG, PNG, PDF (สูงสุด
                                    5MB)</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                            <button type="submit" class="btn btn-primary">บันทึก</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Create Modal --}}
    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-cart-plus me-2"></i>บันทึกการขายหมูใหม่
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('pig_sale.create') }}" method="POST" id="pigSellForm">
                    @csrf
                    <div class="modal-body">
                        {{-- Step 1: เลือกฟาร์มและรุ่น --}}
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-1-circle me-2"></i>เลือกฟาร์มและรุ่น</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">ฟาร์ม <span class="text-danger">*</span></label>
                                        <select name="farm_id" id="farm_select_create" class="form-select" required>
                                            <option value="">-- เลือกฟาร์ม --</option>
                                            @foreach ($farms as $farm)
                                                <option value="{{ $farm->id }}">{{ $farm->farm_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">รุ่น <span class="text-danger">*</span></label>
                                        <select name="batch_id" id="batch_select_create" class="form-select" required>
                                            <option value="">-- เลือกฟาร์มก่อน --</option>
                                            @foreach ($batches as $batch)
                                                <option value="{{ $batch->id }}"
                                                    data-farm-id="{{ $batch->farm_id }}">
                                                    {{ $batch->batch_code }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Step 2: เลือกหมูที่จะขาย (ตาราง) --}}
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-2-circle me-2"></i>เลือกหมูที่จะขาย</h6>
                            </div>
                            <div class="card-body">
                                <div id="pen_selection_container">
                                    <div class="alert alert-warning">
                                        <i class="bi bi-info-circle"></i> กรุณาเลือกฟาร์มและรุ่นก่อน
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Step 3: ข้อมูลการขาย --}}
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-3-circle me-2"></i>ข้อมูลการขาย</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">วันที่ขาย <span class="text-danger">*</span></label>
                                        <input type="date" name="sell_date" class="form-control"
                                            value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">ประเภทการขาย <span class="text-danger">*</span></label>
                                        <select name="sell_type" class="form-select" required>
                                            <option value="">-- เลือกประเภท --</option>
                                            <option value="หมูปกติ" selected>ขายหมูปกติ</option>
                                            <option value="หมูตาย">ขายหมูตาย</option>
                                            <option value="หมูคัดทิ้ง">ขายหมูคัดทิ้ง</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- ราคาอ้างอิงจาก CPF --}}
                                @if ($latestPrice)
                                    <div class="alert alert-info mb-3">
                                        <i class="bi bi-info-circle"></i>
                                        <strong>ราคาอ้างอิง CPF:</strong>
                                        {{ number_format($latestPrice['price_per_kg'], 2) }} บาท/กก.
                                        <small class="text-muted">(ณ วันที่
                                            {{ \Carbon\Carbon::parse($latestPrice['date'])->format('d/m/Y') }})</small>
                                    </div>
                                @endif

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">น้ำหนักรวมทั้งหมด (kg) <span
                                                class="text-danger">*</span></label>
                                        <input type="number" name="total_weight" id="total_weight_input"
                                            class="form-control" step="0.01" min="0.01" required
                                            placeholder="กรอกน้ำหนักรวมที่ชั่งได้">
                                        <small class="text-secondary">
                                            <i class="bi bi-info-circle"></i> น้ำหนักรวมของหมูทั้งหมดที่ขาย
                                        </small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">ราคาต่อ kg <span class="text-danger">*</span></label>
                                        <input type="number" name="price_per_kg" id="price_per_kg_input"
                                            class="form-control" step="0.01" min="0"
                                            value="{{ $latestPrice['price_per_kg'] ?? '' }}" required>
                                        <small class="text-secondary">
                                            <i class="bi bi-lightbulb"></i> ราคาแนะนำจาก CPF
                                        </small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">ค่าขนส่ง (นายหน้าหัก)</label>
                                        <input type="number" name="shipping_cost" id="shipping_cost_input"
                                            class="form-control" step="0.01" min="0" value="0">
                                        <small class="text-secondary">
                                            <i class="bi bi-info-circle"></i> ค่าขนส่งที่นายหน้าหักจากรายได้
                                        </small>
                                    </div>
                                </div>

                                {{-- สรุปการขาย --}}
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary mb-3">
                                            <i class="bi bi-calculator"></i> สรุปการขาย
                                        </h6>
                                        <table class="table table-sm mb-0">
                                            <tr>
                                                <td class="text-end"><strong>จำนวนหมูรวม:</strong></td>
                                                <td class="text-end"><span id="summary_total_quantity">0</span> ตัว
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-end"><strong>น้ำหนักรวม:</strong></td>
                                                <td class="text-end"><span id="summary_total_weight">0.00</span> kg
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-end"><strong>ราคารวม (ก่อนหักค่าขนส่ง):</strong></td>
                                                <td class="text-end"><span id="summary_total_price">0.00</span> บาท
                                                </td>
                                            </tr>
                                            <tr class="text-danger">
                                                <td class="text-end"><strong>หัก: ค่าขนส่ง</strong></td>
                                                <td class="text-end">-<span id="summary_shipping_cost">0.00</span> บาท
                                                </td>
                                            </tr>
                                            <tr class="table-success">
                                                <td class="text-end"><strong>รายได้สุทธิ:</strong></td>
                                                <td class="text-end fs-5 fw-bold"><span id="summary_net_total">0.00</span>
                                                    บาท</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                {{-- Hidden inputs สำหรับส่งข้อมูล --}}
                                <input type="hidden" name="total_quantity" id="hidden_total_quantity" value="0">
                                <input type="hidden" name="total_weight" id="hidden_total_weight" value="0">
                                <input type="hidden" name="total_price" id="hidden_total_price" value="0">
                                <input type="hidden" name="net_total" id="hidden_net_total" value="0">

                                @if ($latestPrice)
                                    <input type="hidden" name="cpf_reference_price"
                                        value="{{ $latestPrice['price_per_kg'] }}">
                                    <input type="hidden" name="cpf_reference_date" value="{{ $latestPrice['date'] }}">
                                @endif
                            </div>
                        </div>

                        {{-- ข้อมูลเพิ่มเติม --}}
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-4-circle me-2"></i>ข้อมูลเพิ่มเติม</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">ชื่อผู้ซื้อ <span class="text-danger">*</span></label>
                                    <input type="text" name="buyer_name" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">หมายเหตุ</label>
                                    <textarea name="note" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                <button type="submit" class="btn btn-success">บันทึก</button>
                            </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Choices.js for select elements in modals only
            const selectElements = document.querySelectorAll('.modal select');
            const choicesInstances = {};

            selectElements.forEach(function(select) {
                const isMultiple = select.hasAttribute('multiple');
                choicesInstances[select.id] = new Choices(select, {
                    searchEnabled: true,
                    searchPlaceholderValue: 'ค้นหา...',
                    noResultsText: 'ไม่พบข้อมูล',
                    itemSelectText: 'คลิกเพื่อเลือก',
                    shouldSort: false,
                    removeItemButton: isMultiple, // แสดงปุ่มลบถ้าเป็น multiple
                    allowHTML: true
                });
            });

            // AJAX: Step 1 - ดึง Barns จาก Farm
            const batchSelect = document.getElementById('batch_select_create');
            const barnSelect = document.getElementById('barn_select_create');
            const penSelect = document.getElementById('pen_select_create');
            const farmSelect = document.getElementById('farm_select_create');

            if (farmSelect && barnSelect) {
                farmSelect.addEventListener('change', function() {
                    const farmId = this.value;

                    // รีเซ็ต barn และ pen
                    if (choicesInstances['barn_select_create']) {
                        choicesInstances['barn_select_create'].clearChoices();
                        choicesInstances['barn_select_create'].setChoices([{
                            value: '',
                            label: '-- เลือกฟาร์มก่อน --',
                            selected: true,
                            disabled: true
                        }]);
                    }
                    if (choicesInstances['pen_select_create']) {
                        choicesInstances['pen_select_create'].clearChoices();
                        choicesInstances['pen_select_create'].setChoices([{
                            value: '',
                            label: '-- เลือกเล้าก่อน --',
                            selected: true,
                            disabled: true
                        }]);
                    }

                    if (!farmId) return;

                    // Loading state for barn
                    if (choicesInstances['barn_select_create']) {
                        choicesInstances['barn_select_create'].clearChoices();
                        choicesInstances['barn_select_create'].setChoices([{
                            value: '',
                            label: 'กำลังโหลดเล้า...',
                            selected: true,
                            disabled: true
                        }]);
                    }

                    // AJAX call - ดึง Barns ที่มีหมู
                    fetch(`/pig_sale/barns-by-farm/${farmId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.data.length > 0) {
                                const choices = [{
                                    value: '',
                                    label: '-- เลือกเล้า --',
                                    selected: true,
                                    disabled: true
                                }];

                                data.data.forEach(barn => {
                                    choices.push({
                                        value: barn.barn_id,
                                        label: barn.display_text,
                                        selected: false
                                    });
                                });

                                if (choicesInstances['barn_select_create']) {
                                    choicesInstances['barn_select_create'].clearChoices();
                                    choicesInstances['barn_select_create'].setChoices(choices);
                                }
                            } else {
                                if (choicesInstances['barn_select_create']) {
                                    choicesInstances['barn_select_create'].clearChoices();
                                    choicesInstances['barn_select_create'].setChoices([{
                                        value: '',
                                        label: '❌ ไม่พบเล้าที่มีหมูในฟาร์มนี้',
                                        selected: true,
                                        disabled: true
                                    }]);
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            if (choicesInstances['barn_select_create']) {
                                choicesInstances['barn_select_create'].clearChoices();
                                choicesInstances['barn_select_create'].setChoices([{
                                    value: '',
                                    label: '❌ เกิดข้อผิดพลาด',
                                    selected: true,
                                    disabled: true
                                }]);
                            }
                        });
                });
            }

            // AJAX: Step 2 - ดึง Pens จาก Barn
            if (barnSelect && penSelect) {
                barnSelect.addEventListener('change', function() {
                    const barnId = this.value;
                    const farmId = farmSelect.value;

                    if (!barnId) {
                        // รีเซ็ต pen dropdown
                        if (choicesInstances['pen_select_create']) {
                            choicesInstances['pen_select_create'].clearChoices();
                            choicesInstances['pen_select_create'].setChoices([{
                                value: '',
                                label: '-- เลือกเล้าก่อน --',
                                selected: true,
                                disabled: true
                            }]);
                        }
                        return;
                    }

                    // Loading state
                    if (choicesInstances['pen_select_create']) {
                        choicesInstances['pen_select_create'].clearChoices();
                        choicesInstances['pen_select_create'].setChoices([{
                            value: '',
                            label: 'กำลังโหลดคอก...',
                            selected: true,
                            disabled: true
                        }]);
                    }

                    // AJAX call - ดึง Pens ในเล้านี้
                    fetch(`/pig_sale/pens-by-barn/${barnId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.data.length > 0) {
                                const choices = [];

                                data.data.forEach(pen => {
                                    choices.push({
                                        value: pen.pen_id,
                                        label: pen.display_text,
                                        selected: false
                                    });
                                });

                                if (choicesInstances['pen_select_create']) {
                                    choicesInstances['pen_select_create'].clearChoices();
                                    choicesInstances['pen_select_create'].setChoices(choices);
                                }
                            } else {
                                if (choicesInstances['pen_select_create']) {
                                    choicesInstances['pen_select_create'].clearChoices();
                                    choicesInstances['pen_select_create'].setChoices([{
                                        value: '',
                                        label: '❌ ไม่พบคอกที่มีหมูในเล้านี้',
                                        selected: true,
                                        disabled: true
                                    }]);
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            if (choicesInstances['pen_select_create']) {
                                choicesInstances['pen_select_create'].clearChoices();
                                choicesInstances['pen_select_create'].setChoices([{
                                    value: '',
                                    label: '❌ เกิดข้อผิดพลาด',
                                    selected: true,
                                    disabled: true
                                }]);
                            }
                        });
                });
            }

            // Filter batch by farm
            if (farmSelect && batchSelect) {
                farmSelect.addEventListener('change', function() {
                    const farmId = this.value;
                    const batchOptions = batchSelect.querySelectorAll('option');

                    batchOptions.forEach(option => {
                        if (option.value === '') {
                            option.style.display = 'block';
                            return;
                        }

                        const optionFarmId = option.getAttribute('data-farm-id');
                        if (!farmId || optionFarmId === farmId) {
                            option.style.display = 'block';
                        } else {
                            option.style.display = 'none';
                        }
                    });

                    // Reset batch
                    if (choicesInstances['batch_select_create']) {
                        choicesInstances['batch_select_create'].setChoiceByValue('');
                    }

                    // Load pen selection table
                    loadPenSelectionTable();
                });

                // Load table when batch changes
                batchSelect.addEventListener('change', function() {
                    loadPenSelectionTable();
                });
            }

            // Load pen selection table based on farm and batch
            function loadPenSelectionTable() {
                const farmId = farmSelect.value;
                const batchId = batchSelect.value;
                const container = document.getElementById('pen_selection_container');

                // ต้องเลือกทั้งฟาร์มและรุ่นก่อน
                if (!farmId || !batchId) {
                    container.innerHTML =
                        '<div class="alert alert-warning mb-0"><i class="fas fa-exclamation-triangle"></i> กรุณาเลือกฟาร์มและรุ่นก่อน</div>';
                    return;
                }

                // Show loading
                container.innerHTML =
                    '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">กำลังโหลด...</span></div><div class="mt-2">กำลังโหลดข้อมูลคอก...</div></div>';

                // Fetch pens from farm
                fetch(`/pig_sale/pens-by-farm/${farmId}`)
                    .then(response => {
                        console.log('Response status:', response.status);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(result => {
                        console.log('Received result:', result);

                        // Extract data from response
                        const data = result.data || result;

                        if (!Array.isArray(data)) {
                            console.error('Data is not an array:', data);
                            throw new Error('Invalid data format received');
                        }

                        if (data.length === 0) {
                            container.innerHTML =
                                '<div class="alert alert-info mb-0"><i class="bi bi-info-circle"></i> ไม่มีคอกที่มีหมูในฟาร์มนี้</div>';
                            return;
                        }

                        // Get selected batch code
                        const selectedBatchOption = batchSelect.options[batchSelect.selectedIndex];
                        const selectedBatchCode = selectedBatchOption ? selectedBatchOption.text : '';
                        console.log('Selected batch code:', selectedBatchCode);

                        // Filter pens by selected batch
                        const pensInBatch = data.filter(pen => pen.batch_code === selectedBatchCode);
                        console.log('Pens in batch:', pensInBatch);

                        if (pensInBatch.length === 0) {
                            container.innerHTML =
                                '<div class="alert alert-warning mb-0"><i class="fas fa-exclamation-triangle"></i> ไม่มีหมูในรุ่นนี้ที่สามารถขายได้</div>';
                            return;
                        }

                        // Generate table
                        let tableHTML = `
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 50px;">
                                                <input type="checkbox" id="select_all_pens" class="form-check-input">
                                            </th>
                                            <th style="width: 120px;">เล้า</th>
                                            <th style="width: 120px;">คอก</th>
                                            <th class="text-end" style="width: 150px;">มีหมู (ตัว)</th>
                                            <th style="width: 200px;">จำนวนขาย (ตัว)</th>
                                        </tr>
                                    </thead>
                                    <tbody>`;

                        pensInBatch.forEach(pen => {
                            tableHTML += `
                                <tr class="pen-row">
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input pen-checkbox"
                                               name="selected_pens[]" value="${pen.pen_id}"
                                               data-pen-id="${pen.pen_id}">
                                    </td>
                                    <td><strong>${pen.barn_name}</strong></td>
                                    <td><strong>${pen.pen_name}</strong></td>
                                    <td class="text-end">
                                        <span class="badge bg-info">${pen.current_quantity}</span>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm quantity-input"
                                               name="quantities[${pen.pen_id}]"
                                               data-pen-id="${pen.pen_id}"
                                               min="1" max="${pen.current_quantity}"
                                               placeholder="0"
                                               disabled>
                                    </td>
                                </tr>`;
                        });

                        tableHTML += `
                                    </tbody>
                                </table>
                            </div>`;

                        container.innerHTML = tableHTML;

                        // Add event listeners
                        setupPenSelectionListeners();
                    })
                    .catch(error => {
                        console.error('Error loading pens:', error);
                        container.innerHTML =
                            `<div class="alert alert-danger mb-0">
                                <i class="fas fa-times-circle"></i>
                                <strong>เกิดข้อผิดพลาดในการโหลดข้อมูล</strong><br>
                                <small>${error.message}</small><br>
                                <small class="text-muted">กรุณาเปิด Console (F12) เพื่อดูรายละเอียด</small>
                            </div>`;
                    });
            }

            // Setup event listeners for pen selection table
            function setupPenSelectionListeners() {
                // Select all checkbox
                const selectAllCheckbox = document.getElementById('select_all_pens');
                if (selectAllCheckbox) {
                    selectAllCheckbox.addEventListener('change', function() {
                        const penCheckboxes = document.querySelectorAll('.pen-checkbox');
                        penCheckboxes.forEach(checkbox => {
                            checkbox.checked = this.checked;
                            togglePenInputs(checkbox);
                        });
                        calculateTotals();
                    });
                }

                // Individual pen checkboxes
                const penCheckboxes = document.querySelectorAll('.pen-checkbox');
                penCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        togglePenInputs(this);
                        calculateTotals();
                    });
                });

                // Quantity and weight inputs
                const quantityInputs = document.querySelectorAll('.quantity-input');

                quantityInputs.forEach(input => {
                    input.addEventListener('input', calculateTotals);
                });
            }

            // Toggle enable/disable inputs based on checkbox
            function togglePenInputs(checkbox) {
                const penId = checkbox.dataset.penId;
                const quantityInput = document.querySelector(`.quantity-input[data-pen-id="${penId}"]`);

                if (checkbox.checked) {
                    quantityInput.disabled = false;
                    quantityInput.focus();
                } else {
                    quantityInput.disabled = true;
                    quantityInput.value = '';
                }
            }

            // Calculate totals from selected pens
            function calculateTotals() {
                let totalQuantity = 0;

                // Sum up all selected pens
                const penCheckboxes = document.querySelectorAll('.pen-checkbox:checked');
                penCheckboxes.forEach(checkbox => {
                    const penId = checkbox.dataset.penId;
                    const quantityInput = document.querySelector(`.quantity-input[data-pen-id="${penId}"]`);

                    const quantity = parseFloat(quantityInput.value) || 0;
                    totalQuantity += quantity;
                });

                // Update summary display
                const summaryQuantity = document.getElementById('summary_total_quantity');
                if (summaryQuantity) summaryQuantity.textContent = totalQuantity.toLocaleString();

                // Update hidden input
                const hiddenQuantity = document.getElementById('hidden_total_quantity');
                if (hiddenQuantity) hiddenQuantity.value = totalQuantity;

                // Calculate prices (use weight from input field instead)
                calculatePrices();
            }

            // Calculate total price and net total
            function calculatePrices() {
                const totalWeightInput = document.getElementById('total_weight_input');
                const pricePerKgInput = document.getElementById('price_per_kg_input');
                const shippingCostInput = document.getElementById('shipping_cost_input');

                const summaryWeight = document.getElementById('summary_total_weight');
                const summaryPrice = document.getElementById('summary_total_price');
                const summaryShipping = document.getElementById('summary_shipping_cost');
                const summaryNetTotal = document.getElementById('summary_net_total');

                const hiddenWeight = document.getElementById('hidden_total_weight');
                const hiddenPrice = document.getElementById('hidden_total_price');
                const hiddenNetTotal = document.getElementById('hidden_net_total');

                // Get values
                const totalWeight = parseFloat(totalWeightInput?.value) || 0;
                const pricePerKg = parseFloat(pricePerKgInput?.value) || 0;
                const shippingCost = parseFloat(shippingCostInput?.value) || 0;

                // Calculate (หักค่าขนส่ง)
                const totalPrice = totalWeight * pricePerKg;
                const netTotal = totalPrice - shippingCost; // เปลี่ยนจาก + เป็น -

                // Update summary display
                if (summaryWeight) summaryWeight.textContent = totalWeight.toLocaleString('th-TH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                if (summaryPrice) summaryPrice.textContent = totalPrice.toLocaleString('th-TH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                if (summaryShipping) summaryShipping.textContent = shippingCost.toLocaleString('th-TH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                if (summaryNetTotal) summaryNetTotal.textContent = netTotal.toLocaleString('th-TH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                // Update hidden inputs
                if (hiddenWeight) hiddenWeight.value = totalWeight.toFixed(2);
                if (hiddenPrice) hiddenPrice.value = totalPrice.toFixed(2);
                if (hiddenNetTotal) hiddenNetTotal.value = netTotal.toFixed(2);
            }

            // Listen to weight, price and shipping changes
            const totalWeightInput = document.getElementById('total_weight_input');
            const pricePerKgInput = document.getElementById('price_per_kg_input');
            const shippingCostInput = document.getElementById('shipping_cost_input');

            if (totalWeightInput) {
                totalWeightInput.addEventListener('input', calculatePrices);
            }

            if (pricePerKgInput) {
                pricePerKgInput.addEventListener('input', calculatePrices);
            }

            if (shippingCostInput) {
                shippingCostInput.addEventListener('input', calculatePrices);
            }
        });

        function sortTable(column, order) {
            const url = new URL(window.location.href);
            url.searchParams.set('sort_by', column);
            url.searchParams.set('order', order);
            window.location.href = url.toString();
        }
    </script>

    {{-- Custom CSS --}}
    <style>
        .clickable-row {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .clickable-row:hover {
            background-color: #FFF5E6 !important;
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(255, 91, 34, 0.15);
    }

    .clickable-row:active {
        transform: translateY(0);
    }

    /* ป้องกันปุ่มใน column จัดการไม่ให้ trigger modal */
    .clickable-row td:last-child {
        pointer-events: none;
    }

    .clickable-row td:last-child>* {
        pointer-events: auto;
    }

    .dropdown-menu .dropdown-item.active {
        background-color: #FF6500;
        color: white;
    }
</style>

@push('scripts')
<script src="{{ asset('admin/js/common-dropdowns.js') }}"></script>
@endpush
@endsection