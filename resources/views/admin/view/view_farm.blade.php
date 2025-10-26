@extends('layouts.admin')

@section('title', 'All Farm')

@section('content')
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card-header">
                    <h2 class="mb-0">
                        <i class="bi bi-building"></i> จัดการฟาร์ม
                    </h2>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-list"></i> รายการฟาร์มทั้งหมด
                        </h5>
                    </div>
                    <div class="card-body">
                        @if ($farms->isEmpty())
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> ไม่มีข้อมูลฟาร์ม
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
                                                <i class="bi bi-building"></i> ชื่อฟาร์ม
                                            </th>
                                            <th>
                                                <i class="bi bi-box-seam"></i> ความจุเล้า
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($farms as $key => $farm)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-secondary">{{ $key + 1 }}</span>
                                                </td>
                                                <td>
                                                    <strong>{{ $farm->farm_name }}</strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        {{ $farm->barn_capacity ?? '-' }}
                                                    </span>
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
