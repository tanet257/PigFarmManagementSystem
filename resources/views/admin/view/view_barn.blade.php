@extends('layouts.admin')

@section('title', 'All Barn')

@section('content')
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card-header">
                    <h2 class="mb-0">
                        <i class="bi bi-shop"></i> จัดการเล้า
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
                        @if ($barns->isEmpty())
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> ไม่มีข้อมูลเล้า
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead class="table-light">
                                        <tr>
                                            <th>
                                                <i class="bi bi-hash"></i> ลำดับ
                                            </th>
                                            <th>
                                                <i class="bi bi-barcode"></i> รหัสเล้า
                                            </th>
                                            <th>
                                                <i class="bi bi-people"></i> ความจุหมู (ตัว)
                                            </th>
                                            <th>
                                                <i class="bi bi-box-seam"></i> ความจุม่อ (อ่าง)
                                            </th>
                                            <th>
                                                <i class="bi bi-chat"></i> หมายเหตุ
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($barns as $key => $barn)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-secondary">{{ $key + 1 }}</span>
                                                </td>
                                                <td>
                                                    <strong>{{ $barn->barn_code }}</strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        {{ $barn->pig_capacity }} ตัว
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-warning">
                                                        {{ $barn->pen_capacity }} อ่าง
                                                    </span>
                                                </td>
                                                <td>
                                                    {{ $barn->note ?? '-' }}
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
