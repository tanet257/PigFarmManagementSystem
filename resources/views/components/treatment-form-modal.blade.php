{{-- Treatment Modal Form --}}
@php
    $isEdit = isset($treatment);
@endphp

<div class="modal fade" id="treatmentModal" tabindex="-1" aria-labelledby="treatmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="treatmentModalLabel">
                    {{ $isEdit ? 'แก้ไขการรักษา' : 'เพิ่มการรักษา/ยา/วัคซีน' }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="treatmentForm" action="{{ route('batch_treatment.store', $dairyRecord) }}" method="POST">
                @csrf
                @if($isEdit)
                    @method('PATCH')
                @endif

                <div class="modal-body">
                    <!-- Disease Name -->
                    <div class="mb-3">
                        <label for="disease_name" class="form-label">ชื่อโรค/ไข้ <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="disease_name" name="disease_name"
                               value="{{ old('disease_name', $treatment->disease_name ?? '') }}" required>
                        @error('disease_name')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Medicine Name & Code -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="medicine_name" class="form-label">ชื่อยา/วัคซีน <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="medicine_name" name="medicine_name"
                                       value="{{ old('medicine_name', $treatment->medicine_name ?? '') }}" required>
                                @error('medicine_name')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="medicine_code" class="form-label">รหัสยา</label>
                                <input type="text" class="form-control" id="medicine_code" name="medicine_code"
                                       value="{{ old('medicine_code', $treatment->medicine_code ?? '') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Quantity & Unit -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="quantity" class="form-label">ปริมาณที่ใช้ <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quantity" name="quantity"
                                       step="0.01" value="{{ old('quantity', $treatment->quantity ?? '') }}" required>
                                @error('quantity')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="unit" class="form-label">หน่วย</label>
                                <input type="text" class="form-control" id="unit" name="unit"
                                       placeholder="กรัม/ขวด/แคปซูล" value="{{ old('unit', $treatment->unit ?? '') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Treatment Duration -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="treatment_start_date" class="form-label">วันเริ่มให้ยา <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="treatment_start_date" name="treatment_start_date"
                                       value="{{ old('treatment_start_date', $treatment->treatment_start_date ?? now()->format('Y-m-d')) }}"
                                       required onchange="calculateEndDate()">
                                @error('treatment_start_date')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="duration_days" class="form-label">ระยะเวลา (วัน) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="duration_days" name="duration_days"
                                       min="1" max="365" value="{{ old('duration_days', $treatment->duration_days ?? 5) }}"
                                       required onchange="calculateEndDate()">
                                @error('duration_days')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="treatment_end_date" class="form-label">วันจบการให้ยา</label>
                                <input type="date" class="form-control" id="treatment_end_date" name="treatment_end_date"
                                       value="{{ old('treatment_end_date', $treatment->treatment_end_date ?? '') }}" disabled>
                                <small class="text-muted">คำนวณอัตโนมัติ</small>
                            </div>
                        </div>
                    </div>

                    <!-- Dosage -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="first_day_dosage" class="form-label">ปริมาณยา วันแรก</label>
                                <input type="number" class="form-control" id="first_day_dosage" name="first_day_dosage"
                                       step="0.01" value="{{ old('first_day_dosage', $treatment->first_day_dosage ?? '') }}">
                                <small class="text-muted">กรัม/ml/หน่วย</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="daily_dosage" class="form-label">ปริมาณยา วันต่อมา</label>
                                <input type="number" class="form-control" id="daily_dosage" name="daily_dosage"
                                       step="0.01" value="{{ old('daily_dosage', $treatment->daily_dosage ?? '') }}">
                                <small class="text-muted">กรัม/ml/หน่วย ต่อวัน</small>
                            </div>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="batch_id" class="form-label">รุ่นหมู</label>
                                <select class="form-select" id="batch_id" name="batch_id">
                                    <option value="">-- เลือกรุ่น (ถ้า) --</option>
                                    @if(isset($batches))
                                        @foreach($batches as $batch)
                                            <option value="{{ $batch->id }}"
                                                {{ old('batch_id', $treatment->batch_id ?? '') == $batch->id ? 'selected' : '' }}>
                                                {{ $batch->batch_code }} ({{ $batch->initial_pig_count }} หมู)
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="pen_id" class="form-label">คอก</label>
                                <select class="form-select" id="pen_id" name="pen_id">
                                    <option value="">-- เลือกคอก (ถ้า) --</option>
                                    @if(isset($pens))
                                        @foreach($pens as $pen)
                                            <option value="{{ $pen->id }}"
                                                {{ old('pen_id', $treatment->pen_id ?? '') == $pen->id ? 'selected' : '' }}>
                                                {{ $pen->pen_name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Note -->
                    <div class="mb-3">
                        <label for="note" class="form-label">หมายเหตุ</label>
                        <textarea class="form-control" id="note" name="note" rows="2"
                                  placeholder="เช่น วิธีการให้ยา, ผลการรักษา, ข้อสังเกต">{{ old('note', $treatment->note ?? '') }}</textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> บันทึก
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function calculateEndDate() {
    const startDate = document.getElementById('treatment_start_date').value;
    const durationDays = parseInt(document.getElementById('duration_days').value) || 0;

    if (startDate && durationDays > 0) {
        const start = new Date(startDate);
        const end = new Date(start);
        end.setDate(end.getDate() + durationDays - 1);

        const year = end.getFullYear();
        const month = String(end.getMonth() + 1).padStart(2, '0');
        const day = String(end.getDate()).padStart(2, '0');

        document.getElementById('treatment_end_date').value = `${year}-${month}-${day}`;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', calculateEndDate);
</script>
