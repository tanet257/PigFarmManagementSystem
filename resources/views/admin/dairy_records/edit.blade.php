@extends('layouts.admin')

@section('title', 'แก้ไขบันทึกประจำวัน')

@section('content')
    <div class="container my-5">
        <div class="card-header">
            <h1 class="text-center">แก้ไขบันทึกประจำวัน (Edit Dairy Record)</h1>
        </div>
        <div class="py-2"></div>

        <form action="{{ route('dairy_records.update', $record->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row mb-3">
                <div class="col-md-4">
                    <label>วันที่</label>
                    <input type="text" name="date" class="form-control"
                        value="{{ \Carbon\Carbon::parse($record->updated_at)->format('d/m/Y H:i') }}" required>
                </div>
                <div class="col-md-4">
                    <label>ฟาร์ม</label>
                    <input type="text" class="form-control" value="{{ $record->batch->farm->farm_name ?? '-' }}"
                        disabled>
                </div>
                <div class="col-md-4">
                    <label>รุ่น</label>
                    <input type="text" class="form-control" value="{{ $record->batch->batch_code ?? '-' }}" disabled>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label>เล้า</label>
                    <input type="text" class="form-control" value="{{ $record->display_barn }}" disabled>
                </div>
                <div class="col-md-4">
                    <label>ประเภท</label>
                    @php
                        $typeBadge = '-';
                        if ($record->dairy_storehouse_uses->count()) {
                            $typeBadge = '<span class="badge bg-success">อาหาร</span>';
                        } elseif ($record->batch_treatments->count()) {
                            $typeBadge = '<span class="badge bg-warning">การรักษา</span>';
                        } elseif ($record->pig_deaths->count()) {
                            $typeBadge = '<span class="badge bg-danger">หมูตาย</span>';
                        }
                    @endphp
                    <div>{!! $typeBadge !!}</div>
                </div>
                <div class="col-md-4">
                    <label>จำนวน</label>
                    <input type="text" name="quantity" class="form-control" value="{{ $record->display_quantity }}">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-12">
                    <label>รายละเอียด</label>
                    <textarea name="details" class="form-control" rows="2">{{ $record->display_details }}</textarea>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-12 text-end">
                    <button type="submit" class="btn btn-success">บันทึกการแก้ไข</button>
                    <a href="{{ route('dairy_records.index') }}" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </div>
        </form>
    </div>
@endsection
