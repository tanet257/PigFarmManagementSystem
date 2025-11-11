@extends('layouts.admin')

@section('title', 'คาดการณ์กำไร')

@section('content')
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-12 card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0"><i class="bi bi-crystal-ball"></i> คาดการณ์กำไรแต่ละรุ่น</h2>
                    <a href="{{ route('dashboard.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> กลับ
                    </a>
                </div>
            </div>
        </div>

        {{-- Info Alert --}}
        <div class="alert alert-info" role="alert">
            <h6><i class="bi bi-info-circle"></i> วิธีการคำนวณ:</h6>
            <small>
                คาดการณ์กำไรจะคำนวณจากตัวชี้วัดปัจจุบันของรุ่น ได้แก่ ADG (Daily Weight Gain) และ FCR (Feed Conversion Ratio)
                เพื่อประมาณน้ำหนักสุดท้ายเมื่อหมูถึงขนาดขายมาตรฐาน
            </small>
        </div>

        {{-- No Data Message --}}
        @if (empty($projectedProfits))
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> ไม่มีรุ่นที่ยังไม่เสร็จสิ้นสำหรับคาดการณ์
            </div>
        @else
            {{-- Projected Profits Table --}}
            <div class="table-responsive">
                <table class="table table-primary">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 80px;">ลำดับที่</th>
                            <th style="min-width: 100px;">รหัสรุ่น</th>
                            <th style="min-width: 100px;">ฟาร์ม</th>
                            <th class="text-center">
                                ADG
                            </th>
                            <th class="text-center">
                                FCR
                            </th>
                            <th class="text-center">
                                น้ำหนักปัจจุบัน
                            </th>
                            <th class="text-center">
                                วันที่เลี้ยงปัจจุบัน
                            </th>
                            <th class="text-center">
                                น้ำหนักคาดการณ์
                            </th>
                            <th class="text-center">
                                วันคาดการณ์
                            </th>
                            <th class="text-center">
                                กำไรคาดการณ์<
                            </th>
                            <th class="text-center">
                                อัตราส่วนคาดการณ์
                            </th>
                            <th class="text-center">
                                ความแตกต่างจาก
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($projectedProfits as $index => $item)
                            @php
                                $profit = $item['profit'];
                                $projected = $item['projected'];
                                $comparison = $item['comparison'];

                                // Determine status color
                                $status_color = 'success';
                                if ($projected['projected_profit'] < 0) {
                                    $status_color = 'danger';
                                } elseif ($projected['projected_profit'] < $profit->total_revenue * 0.1) {
                                    $status_color = 'warning';
                                }
                            @endphp
                            <tr class="align-middle">
                                <td><strong>{{ $index + 1 }}</strong></td>
                                <td><strong>{{ $profit->batch?->batch_code ?? '-' }}</strong></td>
                                <td>{{ $profit->farm?->farm_name ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ $profit->adg ?? 0 }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $profit->fcr ?? 0 }}</span>
                                </td>
                                <td class="text-center">
                                    {{ $profit->ending_avg_weight ?? 0 }} kg
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">{{ $profit->days_in_farm ?? 0 }} วัน</span>
                                </td>
                                <td class="text-center">
                                    <strong class="text-primary">{{ $projected['projected_final_weight'] }} kg</strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">{{ $projected['projected_days_in_farm'] }} วัน</span>
                                </td>
                                <td class="text-center">
                                    <strong class="text-{{ $status_color }}">
                                        ฿{{ number_format($projected['projected_profit'], 2) }}
                                    </strong>
                                </td>
                                <td class="text-center">
                                    <strong class="text-{{ $status_color }}">
                                        {{ number_format($projected['projected_margin'], 2) }}%
                                    </strong>
                                </td>
                                <td class="text-center">
                                    @php
                                        $diff = $comparison['profit_difference'];
                                    @endphp
                                    <strong class="text-{{ $diff >= 0 ? 'success' : 'danger' }}">
                                        {{ $diff >= 0 ? '+' : '' }}฿{{ number_format($diff, 2) }}
                                    </strong>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Summary Section --}}
            <div class="row mt-5">
                <div class="col-md-3">
                    <div class="card border-primary">
                        <div class="card-body">
                            <h6 class="card-title text-muted">จำนวนรุ่นทั้งหมด</h6>
                            <h3 class="text-primary">{{ count($projectedProfits) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-success">
                        <div class="card-body">
                            <h6 class="card-title text-muted">กำไรคาดการณ์รวม</h6>
                            <h3 class="text-success">
                                ฿{{ number_format(collect($projectedProfits)->sum(fn($item) => $item['projected']['projected_profit']), 2) }}
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-info">
                        <div class="card-body">
                            <h6 class="card-title text-muted">อัตราส่วนกำไรเฉลี่ย</h6>
                            <h3 class="text-info">
                                {{ number_format(collect($projectedProfits)->avg(fn($item) => $item['projected']['projected_margin']), 2) }}%
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-warning">
                        <div class="card-body">
                            <h6 class="card-title text-muted">วันคาดการณ์เฉลี่ย</h6>
                            <h3 class="text-warning">
                                {{ number_format(collect($projectedProfits)->avg(fn($item) => $item['projected']['projected_days_in_farm']), 0) }} วัน
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Chart Section --}}
        @if (!empty($projectedProfits))
            <div class="row mt-5">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">กำไรคาดการณ์เปรียบเทียบ</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="projectedProfitChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">อัตราส่วนกำไรคาดการณ์</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="projectedMarginChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                @if (!empty($projectedProfits))
                    // Prepare data for charts
                    const batchCodes = @json(collect($projectedProfits)->map(fn($item) => $item['profit']->batch?->batch_code ?? 'Unknown'));
                    const currentProfits = @json(collect($projectedProfits)->map(fn($item) => $item['profit']->gross_profit ?? 0));
                    const projectedProfits = @json(collect($projectedProfits)->map(fn($item) => $item['projected']['projected_profit']));
                    const margins = @json(collect($projectedProfits)->map(fn($item) => $item['projected']['projected_margin']));

                    // Profit Comparison Chart
                    const profitCtx = document.getElementById('projectedProfitChart').getContext('2d');
                    new Chart(profitCtx, {
                        type: 'bar',
                        data: {
                            labels: batchCodes,
                            datasets: [
                                {
                                    label: 'กำไรปัจจุบัน',
                                    data: currentProfits,
                                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                                    borderColor: 'rgba(54, 162, 235, 1)',
                                    borderWidth: 1
                                },
                                {
                                    label: 'กำไรคาดการณ์',
                                    data: projectedProfits,
                                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                                    borderColor: 'rgba(75, 192, 192, 1)',
                                    borderWidth: 1
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top',
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
                                }
                            }
                        }
                    });

                    // Margin Chart
                    const marginCtx = document.getElementById('projectedMarginChart').getContext('2d');
                    new Chart(marginCtx, {
                        type: 'line',
                        data: {
                            labels: batchCodes,
                            datasets: [
                                {
                                    label: 'อัตราส่วนกำไรคาดการณ์ (%)',
                                    data: margins,
                                    borderColor: 'rgba(75, 192, 192, 1)',
                                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.4,
                                    pointBackgroundColor: 'rgba(75, 192, 192, 1)',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2,
                                    pointRadius: 5,
                                    pointHoverRadius: 7
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return value.toFixed(1) + '%';
                                        }
                                    }
                                }
                            }
                        }
                    });
                @endif
            });
        </script>
    @endpush
@endsection
