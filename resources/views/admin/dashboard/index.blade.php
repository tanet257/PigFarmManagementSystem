@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-12 card-header">
                <h2 class="mb-4">Dashboard (สรุปผลกำไร)</h2>
            </div>
        </div>

        <!-- Filters -->
        <div class="card-custom-secondary mb-3">
            <form method="GET" action="{{ route('dashboard.index') }}" class="d-flex align-items-center gap-2 flex-wrap"
                id="filterForm">
                <!-- Farm Filter -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="farmFilterBtn"
                        data-bs-toggle="dropdown">
                        <i class="bi bi-building"></i>
                        {{ request('farm_id') ? $farms->find(request('farm_id'))->name ?? 'ฟาร์ม' : 'ฟาร์มทั้งหมด' }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item {{ request('farm_id') == '' ? 'active' : '' }}"
                                href="{{ route('dashboard.index', array_merge(request()->except('farm_id'), [])) }}">ฟาร์มทั้งหมด</a>
                        </li>
                        @foreach ($farms as $farm)
                            <li><a class="dropdown-item {{ request('farm_id') == $farm->id ? 'active' : '' }}"
                                    href="{{ route('dashboard.index', array_merge(request()->all(), ['farm_id' => $farm->id])) }}">{{ $farm->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Batch Filter -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="batchFilterBtn"
                        data-bs-toggle="dropdown">
                        <i class="bi bi-diagram-3"></i>
                        {{ request('batch_id') ? $batches->find(request('batch_id'))->batch_code ?? 'รุ่น' : 'รุ่นทั้งหมด' }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item {{ request('batch_id') == '' ? 'active' : '' }}"
                                href="{{ route('dashboard.index', array_merge(request()->except('batch_id'), [])) }}">รุ่นทั้งหมด</a>
                        </li>
                        @foreach ($batches as $batch)
                            <li><a class="dropdown-item {{ request('batch_id') == $batch->id ? 'active' : '' }}"
                                    href="{{ route('dashboard.index', array_merge(request()->all(), ['batch_id' => $batch->id])) }}">{{ $batch->batch_code }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Status Filter -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="statusFilterBtn"
                        data-bs-toggle="dropdown">
                        <i class="bi bi-filter"></i>
                        @if (request('status') == 'incomplete')
                            ยังไม่เสร็จ
                        @elseif(request('status') == 'completed')
                            เสร็จสิ้น
                        @else
                            สถานะทั้งหมด
                        @endif
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item {{ request('status') == '' ? 'active' : '' }}"
                                href="{{ route('dashboard.index', array_merge(request()->except('status'), [])) }}">สถานะทั้งหมด</a>
                        </li>
                        <li><a class="dropdown-item {{ request('status') == 'incomplete' ? 'active' : '' }}"
                                href="{{ route('dashboard.index', array_merge(request()->all(), ['status' => 'incomplete'])) }}">ยังไม่เสร็จ</a>
                        </li>
                        <li><a class="dropdown-item {{ request('status') == 'completed' ? 'active' : '' }}"
                                href="{{ route('dashboard.index', array_merge(request()->all(), ['status' => 'completed'])) }}">เสร็จสิ้น</a>
                        </li>
                    </ul>
                </div>

                <a href="{{ route('dashboard.index') }}" class="btn btn-secondary btn-sm ms-auto">
                    <i class="bi bi-arrow-clockwise"></i> รีเซ็ต
                </a>
            </form>
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

        <!-- KPI Metrics Row -->
        <div class="row mb-4">
            @php
                // คำนวณค่าเฉลี่ย KPI จากทั้งหมดผลกำไร
                $avgAdg = 0;
                $avgFcr = 0;
                $avgFcg = 0;
                $totalFeedBags = 0;
                $totalFeedKg = 0;

                foreach ($profits as $profit) {
                    if ($profit->days_in_farm > 0) {
                        $adg = (($profit->batch?->average_weight_per_pig ?? 0) - ($profit->starting_avg_weight ?? 0)) / $profit->days_in_farm;
                        $avgAdg += $adg;
                    }

                    $fcr = ($profit->total_weight_gained ?? 0) > 0 ? ($profit->total_feed_kg ?? 0) / $profit->total_weight_gained : 0;
                    $avgFcr += $fcr;

                    $fcg = ($profit->total_weight_gained ?? 0) > 0 ? ($profit->feed_cost ?? 0) / $profit->total_weight_gained : 0;
                    $avgFcg += $fcg;

                    $totalFeedBags += $profit->total_feed_bags ?? 0;
                    $totalFeedKg += $profit->total_feed_kg ?? 0;
                }

                $profitCount = $profits->count() > 0 ? $profits->count() : 1;
                $avgAdg = $avgAdg / $profitCount;
                $avgFcr = $avgFcr / $profitCount;
                $avgFcg = $avgFcg / $profitCount;
            @endphp
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body">
                        <h5 class="card-title text-muted">ADG (kg/ตัว/วัน)</h5>
                        <h3 class="text-primary">{{ $avgAdg > 0 ? number_format($avgAdg, 2) : '-' }}</h3>
                        <small class="text-muted">Average Daily Gain</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-secondary">
                    <div class="card-body">
                        <h5 class="card-title text-muted">FCR (kg/kg)</h5>
                        <h3 class="text-secondary">{{ $avgFcr > 0 ? number_format($avgFcr, 3) : '-' }}</h3>
                        <small class="text-muted">Feed Conversion Ratio</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-dark">
                    <div class="card-body">
                        <h5 class="card-title text-muted">FCG (บาท/kg)</h5>
                        <h3 class="text-dark">{{ $avgFcg > 0 ? '฿' . number_format($avgFcg, 2) : '-' }}</h3>
                        <small class="text-muted">Feed Cost per kg Gain</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-secondary">
                    <div class="card-body">
                        <h5 class="card-title text-muted">อาหารรวม</h5>
                        <h3 class="text-secondary">{{ $totalFeedBags > 0 ? number_format($totalFeedBags) : '0' }}</h3>
                        <small class="text-muted">กระสอบ / {{ number_format($totalFeedKg, 2) }} กก.</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><i class="bi bi-pie-chart"></i> โครงสร้างต้นทุน (Cost Breakdown)</h5>
                    </div>
                    <div class="card-body d-flex justify-content-center">
                        <div style="width: 100%; max-width: 400px;">
                            <canvas id="costBreakdownChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-cash-flow"></i> รายได้ - ต้นทุน - กำไร</h5>
                    </div>
                    <div class="card-body d-flex justify-content-center">
                        <div style="width: 100%; max-width: 400px;">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ✅ NEW Charts Row -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-graph-up"></i> ต้นทุน-กำไรรายเดือนปี {{ now()->year }}</h5>
                    </div>
                    <div class="card-body d-flex justify-content-center">
                        <div style="width: 100%; max-width: 500px;">
                            <canvas id="monthlyCostProfitChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="bi bi-speedometer"></i> ประสิทธิภาพการเลี้ยง (FCG)</h5>
                    </div>
                    <div class="card-body d-flex justify-content-center">
                        <div style="width: 100%; max-width: 500px;">
                            <canvas id="fcgPerformanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profits Table -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-table"></i> รายละเอียดกำไรแต่ละรุ่น</h5>
            </div>
            <div class="card-body">
                @if ($profits->isEmpty())
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
                                    <th>ADG</th>
                                    <th>FCR</th>
                                    <th>FCG</th>
                                    <th>จำนวนหมูขาย</th>
                                    <th>จำนวนหมูตายที่ขาย</th>
                                    <th>สถานะ</th>
                                    <th>ดำเนินการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($profits as $profit)
                                    <tr>
                                        <td>
                                            <strong>{{ $profit->batch?->batch_code ?? 'N/A' }}</strong>
                                        </td>
                                        <td>{{ $profit->farm?->name ?? 'N/A' }}</td>
                                        <td class="text-primary">฿{{ number_format($profit->total_revenue, 2) }}</td>
                                        <td class="text-warning">฿{{ number_format($profit->total_cost, 2) }}</td>
                                        <td class="text-success fw-bold">฿{{ number_format($profit->gross_profit, 2) }}
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $profit->profit_margin_percent >= 20 ? 'success' : ($profit->profit_margin_percent >= 10 ? 'warning' : 'danger') }}">
                                                {{ number_format($profit->profit_margin_percent, 2) }}%
                                            </span>
                                        </td>
                                        <td>฿{{ number_format($profit->profit_per_pig, 2) }}</td>
                                        <td>
                                            @php
                                                // ADG = (ending_weight - starting_weight) / days
                                                $adg = $profit->days_in_farm > 0
                                                    ? (($profit->batch?->average_weight_per_pig ?? 0) - ($profit->starting_avg_weight ?? 0)) / $profit->days_in_farm
                                                    : 0;
                                                $adg = max($adg, 0); // ไม่ให้เป็นลบ
                                            @endphp
                                            <span class="badge bg-info">{{ $adg > 0 ? number_format($adg, 3) : '-' }}</span>
                                        </td>
                                        <td>
                                            @php
                                                // FCR = total_feed_kg / total_weight_gained
                                                $fcr = ($profit->total_weight_gained ?? 0) > 0
                                                    ? ($profit->total_feed_kg ?? 0) / $profit->total_weight_gained
                                                    : 0;
                                            @endphp
                                            <span class="badge bg-secondary">{{ $fcr > 0 ? number_format($fcr, 3) : '-' }}</span>
                                        </td>
                                        <td>
                                            @php
                                                // FCG = feed_cost / total_weight_gained
                                                $fcg = ($profit->total_weight_gained ?? 0) > 0
                                                    ? ($profit->feed_cost ?? 0) / $profit->total_weight_gained
                                                    : 0;
                                            @endphp
                                            <span class="badge bg-dark">{{ $fcg > 0 ? '฿' . number_format($fcg, 2) : '-' }}</span>
                                        </td>
                                        <td>{{ $profit->total_pig_sold }}</td>
                                        <td>
                                            @if ($profit->total_pig_dead > 0)
                                                <span class="badge bg-danger">{{ $profit->total_pig_dead }}</span>
                                            @else
                                                <span class="badge bg-success">0</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $profit->status == 'completed' ? 'success' : 'warning' }}">
                                                {{ $profit->status == 'completed' ? 'เสร็จสิ้น' : 'ยังไม่เสร็จ' }}
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                                data-bs-target="#profitDetailModal{{ $profit->id }}"
                                                title="ดูรายละเอียด">
                                                <i class="bi bi-eye"></i>
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
    @foreach ($profits as $profit)
        @php
            // คำนวณ KPI แบบ Dynamic สำหรับ Modal
            $adg_modal = $profit->days_in_farm > 0
                ? (($profit->batch?->average_weight_per_pig ?? 0) - ($profit->starting_avg_weight ?? 0)) / $profit->days_in_farm
                : 0;
            $adg_modal = max($adg_modal, 0);

            $fcr_modal = ($profit->total_weight_gained ?? 0) > 0
                ? ($profit->total_feed_kg ?? 0) / $profit->total_weight_gained
                : 0;

            $fcg_modal = ($profit->total_weight_gained ?? 0) > 0
                ? ($profit->feed_cost ?? 0) / $profit->total_weight_gained
                : 0;
        @endphp
        <div class="modal fade" id="profitDetailModal{{ $profit->id }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title"><i class="bi bi-graph-up"></i> รายละเอียดกำไร - {{ $profit->batch?->batch_code ?? 'N/A' }}</h5>
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

                        <h6 class="mb-3"><i class="bi bi-cash-coin"></i> สรุปรายได้-ต้นทุน</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>รายได้รวม:</strong> <span
                                        class="text-primary">฿{{ number_format($profit->total_revenue, 2) }}</span></p>
                                <p><strong>ต้นทุนรวม:</strong> <span
                                        class="text-warning">฿{{ number_format($profit->total_cost, 2) }}</span></p>
                                <p><strong>กำไรขั้นต้น:</strong> <span
                                        class="text-success">฿{{ number_format($profit->gross_profit, 2) }}</span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>อัตราส่วนกำไร:</strong> {{ number_format($profit->profit_margin_percent, 2) }}%
                                </p>
                                <p><strong>กำไร/ตัวหมู:</strong> ฿{{ number_format($profit->profit_per_pig, 2) }}</p>
                                <p><strong>ค่าเฉลี่ยต้นทุน/ตัว:</strong>
                                    ฿{{ number_format($profit->total_cost / max($profit->total_pig_sold, 1), 2) }}</p>
                            </div>
                        </div>

                        <hr>

                        <h6 class="mb-3"><i class="bi bi-speedometer2"></i> KPI Metrics (ตัวชี้วัดประสิทธิภาพ)</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p>
                                    <strong><i class="bi bi-graph-up"></i> ADG (kg/ตัว/วัน):</strong>
                                    <span class="text-primary fw-bold">
                                        @if ($adg_modal > 0)
                                            {{ number_format($adg_modal, 3) }}
                                        @else
                                            -
                                        @endif
                                    </span>
                                </p>
                                <p>
                                    <strong><i class="bi bi-speedometer"></i> น้ำหนักเริ่มต้น:</strong> {{ $profit->starting_avg_weight ?? '-' }} kg/ตัว
                                </p>
                                <p>
                                    <strong><i class="bi bi-basket"></i> อาหารรวม:</strong> {{ $profit->total_feed_bags ?? 0 }} กระสอบ /
                                    {{ number_format($profit->total_feed_kg ?? 0, 2) }} kg
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p>
                                    <strong><i class="bi bi-percent"></i> FCR (kg/kg):</strong>
                                    <span class="text-secondary fw-bold">
                                        @if ($fcr_modal > 0)
                                            {{ number_format($fcr_modal, 3) }}
                                        @else
                                            -
                                        @endif
                                    </span>
                                </p>
                                <p>
                                    <strong><i class="bi bi-cash-coin"></i> FCG (บาท/kg):</strong>
                                    <span class="text-dark fw-bold">
                                        @if ($fcg_modal > 0)
                                            ฿{{ number_format($fcg_modal, 2) }}
                                        @else
                                            -
                                        @endif
                                    </span>
                                </p>
                                <p>
                                    <strong><i class="bi bi-scale"></i> น้ำหนักท้ายลง:</strong> {{ $profit->ending_avg_weight ?? '-' }} kg/ตัว
                                </p>
                            </div>
                        </div>

                        <hr>

                        <h6 class="mb-3"><i class="bi bi-list-check"></i> การแบ่งแยกต้นทุน</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p><i class="bi bi-bag"></i> ค่าอาหาร: <span
                                        class="float-end text-warning">฿{{ number_format($profit->feed_cost, 2) }}</span>
                                </p>
                                <p><i class="bi bi-capsule"></i> ค่ายา/วัคซีน: <span
                                        class="float-end text-warning">฿{{ number_format($profit->medicine_cost, 2) }}</span>
                                </p>
                                <p><i class="bi bi-truck"></i> ค่าขนส่ง: <span
                                        class="float-end text-warning">฿{{ number_format($profit->transport_cost, 2) }}</span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><i class="bi bi-person-workspace"></i> ค่าแรงงาน: <span
                                        class="float-end text-warning">฿{{ number_format($profit->labor_cost, 2) }}</span>
                                </p>
                                <p><i class="bi bi-lightning"></i> ค่ากระแสไฟ/น้ำ: <span
                                        class="float-end text-warning">฿{{ number_format($profit->utility_cost, 2) }}</span>
                                </p>
                                <p><i class="bi bi-file-earmark"></i> ค่าใช้สอยอื่นๆ: <span
                                        class="float-end text-warning">฿{{ number_format($profit->other_cost, 2) }}</span>
                                </p>
                            </div>
                        </div>

                        <hr>

                        <h6 class="mb-3"><i class="bi bi-graph-up-arrow"></i> ข้อมูลการขายหมู</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>จำนวนหมูขาย:</strong> {{ $profit->total_pig_sold }} ตัว</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>จำนวนหมูตายที่ขาย:</strong> {{ $profit->total_pig_dead }} ตัว</p>
                            </div>
                        </div>

                        <!-- Profit Details Items -->
                        @if ($profit->profitDetails->isNotEmpty())
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
                                        @foreach ($profit->profitDetails as $detail)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-secondary">{{ $detail->cost_category }}</span>
                                                </td>
                                                <td>{{ $detail->item_name }}</td>
                                                <td class="text-end text-warning">฿{{ number_format($detail->amount, 2) }}
                                                </td>
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

    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ข้อมูลจากฝั่ง PHP (Laravel Blade)
            const totalRevenue = {{ $totalRevenue }};
            const totalCost = {{ $totalCost }};
            const totalProfit = {{ $totalProfit }};

            // Cost breakdown data
            const feedCost = {{ $feedCost }};
            const medicineCost = {{ $medicineCost }};
            const transportCost = {{ $transportCost }};
            const laborCost = {{ $laborCost }};
            const utilityCost = {{ $utilityCost }};
            const otherCost = {{ $otherCost }};

            // ✅ Chart 1: Cost Breakdown (Left side - Pie Chart)
            const ctx1 = document.getElementById('costBreakdownChart').getContext('2d');
            const costData = [feedCost, medicineCost, transportCost, laborCost, utilityCost, otherCost];
            const totalCostBreakdown = costData.reduce((a, b) => a + b, 0);

            // คำนวณ percentages สำหรับ labels
            const costPercentages = costData.map(val => totalCostBreakdown > 0 ? ((val / totalCostBreakdown) * 100).toFixed(1) : 0);
            const costLabels = [
                'ค่าอาหาร (' + costPercentages[0] + '%)',
                'ค่ายา/วัคซีน (' + costPercentages[1] + '%)',
                'ค่าขนส่ง (' + costPercentages[2] + '%)',
                'ค่าแรงงาน (' + costPercentages[3] + '%)',
                'ค่ากระแสไฟ/น้ำ (' + costPercentages[4] + '%)',
                'ค่าใช้สอยอื่นๆ (' + costPercentages[5] + '%)'
            ];

            new Chart(ctx1, {
                type: 'doughnut',
                data: {
                    labels: costLabels,
                    datasets: [{
                        data: costData,
                        backgroundColor: [
                            '#FF6384', // red
                            '#36A2EB', // blue
                            '#FFCE56', // yellow
                            '#4BC0C0', // teal
                            '#9966FF', // purple
                            '#FF9F40' // orange
                        ],
                        borderColor: [
                            '#FF6384',
                            '#36A2EB',
                            '#FFCE56',
                            '#4BC0C0',
                            '#9966FF',
                            '#FF9F40'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            enabled: true,
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            padding: 12,
                            titleFont: {
                                size: 13
                            },
                            bodyFont: {
                                size: 12
                            },
                            callbacks: {
                                label: function(context) {
                                    const value = context.raw;
                                    const percentage = totalCostBreakdown > 0 ? ((value / totalCostBreakdown) * 100).toFixed(1) : 0;
                                    return `${percentage}% (฿${value.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })})`;
                                }
                            }
                        }
                    }
                }
                });

            // ✅ Chart 2: Revenue - Cost - Profit (Right side - Bar Chart)
            const ctx2 = document.getElementById('revenueChart').getContext('2d');
            const revenueData = [totalRevenue, totalCost, totalProfit];
            const totalAmount = totalRevenue + totalCost + totalProfit;

            // คำนวณ percentages สำหรับ bar chart
            const revenuePercentages = revenueData.map(val => totalAmount > 0 ? ((val / totalAmount) * 100).toFixed(1) : 0);
            const barLabels = [
                'รายได้ (' + revenuePercentages[0] + '%)',
                'ต้นทุน (' + revenuePercentages[1] + '%)',
                'กำไร (' + revenuePercentages[2] + '%)'
            ];

            new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: barLabels,
                    datasets: [{
                        label: 'จำนวนเงิน (บาท)',
                        data: revenueData,
                        backgroundColor: [
                            '#28a745', // green for revenue
                            '#ffc107', // yellow for cost
                            '#20c997' // teal for profit
                        ],
                        borderColor: [
                            '#1e7e34',
                            '#e0a800',
                            '#0d6efd'
                        ],
                        borderWidth: 2,
                        borderRadius: 5
                    }]
                },
                options: {
                    indexAxis: 'y', // Horizontal bar chart
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: true,
                            labels: {
                                padding: 15,
                                font: {
                                    size: 13
                                }
                            }
                        },
                        tooltip: {
                            enabled: true,
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            padding: 12,
                            titleFont: {
                                size: 13
                            },
                            bodyFont: {
                                size: 12
                            },
                            callbacks: {
                                label: function(context) {
                                    const value = context.raw;
                                    const percentage = totalAmount > 0 ? ((value / totalAmount) * 100).toFixed(1) : 0;
                                    return `${percentage}% (฿${value.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })})`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '฿' + value.toLocaleString('th-TH');
                                }
                            }
                        }
                    }
                }
                });

            // ✅ Chart 3: Monthly Cost-Profit (Line Chart)
            loadMonthlyCostProfitChart();

            // ✅ Chart 4: FCG Performance (Line Chart)
            loadFcgPerformanceChart();

            // ✅ AUTO-REFRESH: Update charts every 30 seconds
            // When new cost type (wage, electric_bill, water_bill) is recorded,
            // dashboard automatically updates to show latest KPIs
            setInterval(function() {
                // Reload main page content to update summary cards
                fetch(window.location.href)
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        // Update summary cards only (not reload entire page)
                        const oldSummary = document.querySelector('.row.mb-4');
                        const newSummary = doc.querySelector('.row.mb-4');

                        if (newSummary) {
                            oldSummary.replaceWith(newSummary);
                        }

                        // Reload all charts
                        loadMonthlyCostProfitChart();
                        loadFcgPerformanceChart();
                    })
                    .catch(error => console.error('Auto-refresh error:', error));
            }, 30000); // 30 seconds

            // ✅ Helper function: Load Monthly Cost-Profit Chart
            function loadMonthlyCostProfitChart() {
                const params = new URLSearchParams({
                    farm_id: '{{ request("farm_id") }}',
                    batch_id: '{{ request("batch_id") }}',
                    status: '{{ request("status") }}'
                });

                fetch(`/api/dashboard/monthly-cost-profit?${params}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const ctx3 = document.getElementById('monthlyCostProfitChart').getContext('2d');
                            new Chart(ctx3, {
                                type: 'line',
                                data: {
                                    labels: data.months,
                                    datasets: [
                                        {
                                            label: 'ต้นทุน (฿)',
                                            data: data.cost,
                                            borderColor: '#ffc107',
                                            backgroundColor: 'rgba(255, 193, 7, 0.1)',
                                            borderWidth: 3,
                                            fill: true,
                                            tension: 0.3,
                                            pointRadius: 4,
                                            pointBackgroundColor: '#ffc107'
                                        },
                                        {
                                            label: 'กำไร (฿)',
                                            data: data.profit,
                                            borderColor: '#28a745',
                                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                                            borderWidth: 3,
                                            fill: true,
                                            tension: 0.3,
                                            pointRadius: 4,
                                            pointBackgroundColor: '#28a745'
                                        }
                                    ]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: true,
                                    plugins: {
                                        legend: {
                                            position: 'top',
                                            labels: {
                                                font: { size: 12 },
                                                padding: 15
                                            }
                                        },
                                        tooltip: {
                                            backgroundColor: 'rgba(0,0,0,0.8)',
                                            padding: 12,
                                            callbacks: {
                                                label: function(context) {
                                                    const value = context.raw;
                                                    return context.dataset.label + ': ฿' + value.toLocaleString('th-TH', { maximumFractionDigits: 2 });
                                                }
                                            }
                                        }
                                    },
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            ticks: {
                                                callback: function(value) {
                                                    return '฿' + value.toLocaleString('th-TH', { maximumFractionDigits: 0 });
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                        }
                    })
                    .catch(error => console.error('Error loading monthly chart:', error));
            }

            // ✅ Helper function: Load FCG Performance Chart
            function loadFcgPerformanceChart() {
                const params = new URLSearchParams({
                    farm_id: '{{ request("farm_id") }}',
                    batch_id: '{{ request("batch_id") }}',
                    status: '{{ request("status") }}'
                });

                fetch(`/api/dashboard/fcg-performance?${params}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const ctx4 = document.getElementById('fcgPerformanceChart').getContext('2d');

                            new Chart(ctx4, {
                                type: 'line',
                                data: {
                                    labels: data.batches,
                                    datasets: [{
                                        label: 'FCG (บาท/kg)',
                                        data: data.fcg,
                                        borderColor: '#dc3545',
                                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                                        borderWidth: 3,
                                        fill: true,
                                        tension: 0.4,
                                        pointRadius: 6,
                                        pointBackgroundColor: '#dc3545',
                                        pointBorderColor: '#fff',
                                        pointBorderWidth: 2,
                                        pointHoverRadius: 8
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: true,
                                    plugins: {
                                        legend: {
                                            display: true,
                                            labels: {
                                                font: {
                                                    size: 14
                                                }
                                            }
                                        },
                                        tooltip: {
                                            backgroundColor: 'rgba(0,0,0,0.8)',
                                            padding: 12,
                                            callbacks: {
                                                label: function(context) {
                                                    const value = context.raw;
                                                    let status = '';
                                                    if (value <= 10) status = ' (ยอดเยี่ยม)';
                                                    else if (value <= 15) status = ' (ดี)';
                                                    else if (value <= 20) status = ' (พอใจ)';
                                                    else status = ' (ต้องปรับปรุง)';
                                                    return '฿' + value.toLocaleString('th-TH', { maximumFractionDigits: 2 }) + status;
                                                }
                                            }
                                        }
                                    },
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            ticks: {
                                                callback: function(value) {
                                                    return '฿' + value.toLocaleString('th-TH');
                                                }
                                            }
                                        },
                                        x: {
                                            grid: {
                                                display: false
                                            }
                                        }
                                    }
                                }
                            });
                        }
                    })
                    .catch(error => console.error('Error loading FCG chart:', error));
            }
        });
    </script>

@endsection
