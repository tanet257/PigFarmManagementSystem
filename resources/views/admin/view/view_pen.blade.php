@extends('layouts.admin')

@section('title', 'All Pen')

@section('content')
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card-header">
                    <h2 class="mb-0">
                        <i class="bi bi-box-seam"></i> จัดการเล้า
                    </h2>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-list"></i> รายการเล้าทั้งหมด
                        </h5>
                    </div>
                    <div class="card-body">
                        @if ($pens->isEmpty())
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> ไม่มีข้อมูลเล้า
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead class="table-light">
                                        <tr>
                                            <th>
                                                <i class="bi bi-building"></i> รหัสเล้า
                                            </th>
                                            <th>
                                                <i class="bi bi-house"></i> เล้าที่
                                            </th>
                                            <th>
                                                <i class="bi bi-people"></i> ความจุ (ตัว)
                                            </th>
                                            <th>
                                                <i class="bi bi-circle"></i> สถานะ
                                            </th>
                                            <th>
                                                <i class="bi bi-chat"></i> หมายเหตุ
                                            </th>
                                            <th>
                                                <i class="bi bi-calendar"></i> วันที่
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pens as $pen)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-info">
                                                        {{ $pen->barn_id }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong>{{ $pen->pen_id }}</strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        {{ $pen->pig_capacity }} ตัว
                                                    </span>
                                                </td>
                                                <td>
                                                    @if ($pen->status == 'active')
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-check-circle"></i> ใช้งาน
                                                        </span>
                                                    @elseif ($pen->status == 'inactive')
                                                        <span class="badge bg-warning">
                                                            <i class="bi bi-exclamation-circle"></i> ปิดการใช้
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary">
                                                            {{ $pen->status }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $pen->note ?? '-' }}
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        {{ $pen->date ? date('d/m/Y', strtotime($pen->date)) : '-' }}
                                                    </small>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
