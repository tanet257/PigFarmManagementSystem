{{-- Treatment List Table --}}
<div class="card card-custom-secondary mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-pills"></i> บันทึกการให้ยา/วัคซีน</h5>
        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#treatmentModal">
            <i class="fas fa-plus"></i> เพิ่มการรักษา
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>โรค/ชื่อการรักษา</th>
                    <th>ยา</th>
                    <th>ปริมาณ</th>
                    <th>ระยะเวลา</th>
                    <th>วันที่</th>
                    <th>สถานะ</th>
                    <th>แอคชั่น</th>
                </tr>
            </thead>
            <tbody>
                @forelse($treatments as $treatment)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <strong>{{ $treatment->disease_name }}</strong>
                            @if($treatment->pen)
                                <br><small class="text-muted">คอก: {{ $treatment->pen->pen_name }}</small>
                            @endif
                        </td>
                        <td>{{ $treatment->medicine_name }}</td>
                        <td>
                            {{ $treatment->quantity }}{{ $treatment->unit ? ' ' . $treatment->unit : '' }}
                        </td>
                        <td>{{ $treatment->duration_days }} วัน</td>
                        <td>
                            <small>
                                {{ $treatment->treatment_start_date->format('d/m/Y') }}
                                @if($treatment->treatment_end_date)
                                    - {{ $treatment->treatment_end_date->format('d/m/Y') }}
                                @endif
                            </small>
                        </td>
                        <td>
                            @if($treatment->treatment_status === 'pending')
                                <span class="badge bg-secondary">รอเริ่มรักษา</span>
                            @elseif($treatment->treatment_status === 'ongoing')
                                <span class="badge bg-warning text-dark">กำลังรักษา</span>
                            @elseif($treatment->treatment_status === 'completed')
                                <span class="badge bg-success">จบการรักษา</span>
                            @else
                                <span class="badge bg-danger">หยุดการรักษา</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                {{-- View Detail --}}
                                <button type="button" class="btn btn-outline-primary"
                                        data-bs-toggle="modal" data-bs-target="#treatmentDetailModal"
                                        data-treatment-id="{{ $treatment->id }}"
                                        title="ดูรายละเอียด">
                                    <i class="fas fa-eye"></i>
                                </button>

                                {{-- Edit --}}
                                @if($treatment->treatment_status === 'pending')
                                    <button type="button" class="btn btn-outline-warning"
                                            data-bs-toggle="modal" data-bs-target="#treatmentModal"
                                            data-edit-treatment-id="{{ $treatment->id }}"
                                            title="แก้ไข">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                @endif

                                {{-- Status Actions --}}
                                @if($treatment->treatment_status === 'pending')
                                    <form action="{{ route('batch_treatment.start', $treatment) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success btn-sm"
                                                title="เริ่มการรักษา" onclick="return confirm('เริ่มการรักษาแล้ว?')">
                                            <i class="fas fa-play"></i>
                                        </button>
                                    </form>
                                @elseif($treatment->treatment_status === 'ongoing')
                                    <form action="{{ route('batch_treatment.complete', $treatment) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-info btn-sm"
                                                title="จบการรักษา" onclick="return confirm('จบการรักษาแล้ว?')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('batch_treatment.stop', $treatment) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-warning btn-sm"
                                                title="หยุดการรักษา" onclick="return confirm('หยุดการรักษา?')">
                                            <i class="fas fa-pause"></i>
                                        </button>
                                    </form>
                                @endif

                                {{-- Delete --}}
                                @if($treatment->treatment_status === 'pending')
                                    <form action="{{ route('batch_treatment.destroy', $treatment) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm"
                                                title="ลบ" onclick="return confirm('ลบแน่ใจ?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-3">
                            ยังไม่มีการบันทึกการให้ยา/วัคซีน
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Treatment Detail Modal --}}
<div class="modal fade" id="treatmentDetailModal" tabindex="-1" aria-labelledby="treatmentDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="treatmentDetailModalLabel">รายละเอียดการรักษา</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="treatmentDetailContent">
                {{-- Loaded via JavaScript --}}
            </div>
        </div>
    </div>
</div>

<script>
// Load treatment detail
document.querySelectorAll('[data-treatment-id]').forEach(btn => {
    btn.addEventListener('click', async function() {
        const treatmentId = this.dataset.treatmentId;
        // TODO: Load treatment detail via AJAX
        console.log('Load treatment detail:', treatmentId);
    });
});
</script>
