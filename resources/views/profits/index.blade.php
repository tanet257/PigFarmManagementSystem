@extends('layouts.admin')

@section('title', 'สรุปผลกำไร')

@section('content')

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="mb-4"> สรุปผลกำไร (Profit Summary)</h2>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('profits.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="farm_filter" class="form-label">ฟาร์ม</label>
                    <select name="farm_id" id="farm_filter" class="form-select">
                        <option value="">-- เลือกฟาร์ม --</option>
                        @foreach($farms as $farm)
                            <option value="{{ $farm->id }}" {{ request('farm_id') == $farm->id ? 'selected' : '' }}>
                                {{ $farm->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="batch_filter" class="form-label">รุ่น</label>
                    <select name="batch_id" id="batch_filter" class="form-select">
                        <option value="">-- เลือกรุ่น --</option>
                        @foreach($batches as $batch)
                            <option value="{{ $batch->id }}" {{ request('batch_id') == $batch->id ? 'selected' : '' }}>
                                {{ $batch->batch_code }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="status_filter" class="form-label">สถานะ</label>
                    <select name="status" id="status_filter" class="form-select">
                        <option value="">-- ทั้งหมด --</option>
                        <option value="incomplete" {{ request('status') == 'incomplete' ? 'selected' : '' }}>ยังไม่เสร็จ</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>เสร็จสิ้น</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">🔍 ค้นหา</button>
                    <a href="{{ route('profits.index') }}" class="btn btn-secondary">🔄 รีเซ็ต</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <h5 class="card-title text-muted">รายได้รวม</h5>
                    <h3 class="text-primary">฿{{ number_format($totalRevenue, 2) }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <h5 class="card-title text-muted">ต้นทุนรวม</h5>
                    <h3 class="text-warning">฿{{ number_format($totalCost, 2) }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <h5 class="card-title text-muted">กำไรรวม</h5>
                    <h3 class="text-success">฿{{ number_format($totalProfit, 2) }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <h5 class="card-title text-muted">อัตราส่วนกำไร</h5>
                    <h3 class="text-info">{{ number_format($avgProfitMargin, 2) }}%</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Profits Table -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">📋 รายละเอียดกำไรแต่ละรุ่น</h5>
        </div>
        <div class="card-body">
            @if($profits->isEmpty())
                <div class="alert alert-info">
                    ไม่มีข้อมูลกำไร
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>รหัสรุ่น</th>
                                <th>ฟาร์ม</th>
                                <th>รายได้</th>
                                <th>ต้นทุน</th>
                                <th>กำไร</th>
                                <th>อัตราส่วน%</th>
                                <th>กำไร/ตัว</th>
                                <th>จำนวนหมูขาย</th>
                                <th>จำนวนหมูตาย</th>
                                <th>สถานะ</th>
                                <th>ดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($profits as $profit)
                                <tr>
                                    <td>
                                        <strong>{{ $profit->batch?->batch_code ?? 'N/A' }}</strong>
                                    </td>
                                    <td>{{ $profit->farm?->name ?? 'N/A' }}</td>
                                    <td class="text-primary">฿{{ number_format($profit->total_revenue, 2) }}</td>
                                    <td class="text-warning">฿{{ number_format($profit->total_cost, 2) }}</td>
                                    <td class="text-success fw-bold">฿{{ number_format($profit->gross_profit, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $profit->profit_margin_percent >= 20 ? 'success' : ($profit->profit_margin_percent >= 10 ? 'warning' : 'danger') }}">
                                            {{ number_format($profit->profit_margin_percent, 2) }}%
                                        </span>
                                    </td>
                                    <td>฿{{ number_format($profit->profit_per_pig, 2) }}</td>
                                    <td>{{ $profit->total_pig_sold }}</td>
                                    <td>
                                        @if($profit->total_pig_dead > 0)
                                            <span class="badge bg-danger">{{ $profit->total_pig_dead }}</span>
                                        @else
                                            <span class="badge bg-success">0</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $profit->status == 'completed' ? 'success' : 'warning' }}">
                                            {{ $profit->status == 'completed' ? 'เสร็จสิ้น' : 'ยังไม่เสร็จ' }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#profitDetailModal{{ $profit->id }}" title="ดูรายละเอียด">
                                            ดู
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $profits->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modals for Profit Details -->
@foreach($profits as $profit)
    <div class="modal fade" id="profitDetailModal{{ $profit->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">📊 รายละเอียดกำไร - {{ $profit->batch?->batch_code ?? 'N/A' }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>ฟาร์ม:</strong> {{ $profit->farm?->name ?? 'N/A' }}<br>
                            <strong>รุ่น:</strong> {{ $profit->batch?->batch_code ?? 'N/A' }}<br>
                            <strong>สถานะ:</strong> {{ $profit->status }}
                        </div>
                        <div class="col-md-6">
                            <strong>วันเริ่มต้น:</strong> {{ $profit->period_start?->format('d/m/Y') ?? 'N/A' }}<br>
                            <strong>วันสิ้นสุด:</strong> {{ $profit->period_end?->format('d/m/Y') ?? 'N/A' }}<br>
                            <strong>จำนวนวัน:</strong> {{ $profit->days_in_farm }} วัน
                        </div>
                    </div>

                    <hr>

                    <h6 class="mb-3">💰 สรุปรายได้-ต้นทุน</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>รายได้รวม:</strong> <span class="text-primary">฿{{ number_format($profit->total_revenue, 2) }}</span></p>
                            <p><strong>ต้นทุนรวม:</strong> <span class="text-warning">฿{{ number_format($profit->total_cost, 2) }}</span></p>
                            <p><strong>กำไรขั้นต้น:</strong> <span class="text-success">฿{{ number_format($profit->gross_profit, 2) }}</span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>อัตราส่วนกำไร:</strong> {{ number_format($profit->profit_margin_percent, 2) }}%</p>
                            <p><strong>กำไร/ตัวหมู:</strong> ฿{{ number_format($profit->profit_per_pig, 2) }}</p>
                            <p><strong>ค่าเฉลี่ยต้นทุน/ตัว:</strong> ฿{{ number_format($profit->total_cost / max($profit->total_pig_sold, 1), 2) }}</p>
                        </div>
                    </div>

                    <hr>

                    <h6 class="mb-3">📌 การแบ่งแยกต้นทุน</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p>🌾 ค่าอาหาร: <span class="float-end text-warning">฿{{ number_format($profit->feed_cost, 2) }}</span></p>
                            <p>💊 ค่ายา/วัคซีน: <span class="float-end text-warning">฿{{ number_format($profit->medicine_cost, 2) }}</span></p>
                            <p>🚚 ค่าขนส่ง: <span class="float-end text-warning">฿{{ number_format($profit->transport_cost, 2) }}</span></p>
                        </div>
                        <div class="col-md-6">
                            <p>👷 ค่าแรงงาน: <span class="float-end text-warning">฿{{ number_format($profit->labor_cost, 2) }}</span></p>
                            <p>💡 ค่ากระแสไฟ/น้ำ: <span class="float-end text-warning">฿{{ number_format($profit->utility_cost, 2) }}</span></p>
                            <p>📋 ค่าใช้สอยอื่นๆ: <span class="float-end text-warning">฿{{ number_format($profit->other_cost, 2) }}</span></p>
                        </div>
                    </div>

                    <hr>

                    <h6 class="mb-3">🐷 ข้อมูลการขายหมู</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>จำนวนหมูขาย:</strong> {{ $profit->total_pig_sold }} ตัว</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>จำนวนหมูตาย:</strong> {{ $profit->total_pig_dead }} ตัว</p>
                        </div>
                    </div>

                    <!-- Profit Details Items -->
                    @if($profit->profitDetails->isNotEmpty())
                        <hr>
                        <h6 class="mb-3">🔍 รายละเอียดต้นทุนแต่ละรายการ</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>หมวดหมู่</th>
                                        <th>รายการ</th>
                                        <th class="text-end">จำนวนเงิน</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($profit->profitDetails as $detail)
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary">{{ $detail->cost_category }}</span>
                                            </td>
                                            <td>{{ $detail->item_name }}</td>
                                            <td class="text-end text-warning">฿{{ number_format($detail->amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

@endsection
