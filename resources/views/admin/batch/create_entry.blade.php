@extends('layouts.admin')

@section('title', 'สร้างรุ่นใหม่ พร้อมเพิ่มหมู')

@section('content')

    <div class="container my-5">

        <!-- Header -->

        <div class="card-header mb-4">

            <h1 class="text-center">สร้างรุ่นใหม่ พร้อมเพิ่มหมู</h1>

        </div>

    <div class="container my-5">    <div class="container my-5">

        <!-- Main Form Card -->

        <div class="card shadow-lg">        <!-- Header -->        <!-- Header -->

            <div class="card-body p-4">

                <form id="batchEntryForm" method="POST" action="{{ route('batch.storeWithEntry') }}">        <div class="card-header mb-4">        <div class="card-header">

                    @csrf

            <h1 class="text-center">สร้างรุ่นใหม่ พร้อมเพิ่มหมู</h1>            <h1 class="text-center mb-2">สร้างรุ่นใหม่ + บันทึกเข้าหมู</h1>

                    <!-- Farm Selection -->

                    <div class="row mb-4">        </div>            <p class="text-muted text-center">บันทึกข้อมูลรุ่นและการเข้าหมูในครั้งเดียว</p>

                        <div class="col-md-6">

                            <label for="farm_id" class="form-label fw-bold">ฟาร์ม</label>        </div>

                            <select class="form-select @error('farm_id') is-invalid @enderror" id="farm_id" name="farm_id" required>

                                <option value="">-- เลือกฟาร์ม --</option>        <!-- Main Form Card -->        <div class="py-2"></div>

                                @foreach ($farm ? [$farm] : [] as $f)

                                    <option value="{{ $f->id }}" selected>{{ $f->farm_name }}</option>        <div class="card shadow-lg">

                                @endforeach

                            </select>            <div class="card-body p-4">        @if ($errors->any())

                            @error('farm_id')

                                <div class="invalid-feedback d-block">{{ $message }}</div>                <form id="batchEntryForm" method="POST" action="{{ route('batch.storeWithEntry') }}">            <div class="alert alert-danger alert-dismissible fade show" role="alert">

                            @enderror

                        </div>                    @csrf                <i class="fas fa-exclamation-circle me-2"></i>



                        <!-- Batch Code -->                <strong>เกิดข้อผิดพลาด:</strong>

                        <div class="col-md-6">

                            <label for="batch_code" class="form-label fw-bold">รหัสรุ่น</label>                    <!-- Farm Selection -->                <ul class="mb-0 mt-2">

                            <input type="text" class="form-control @error('batch_code') is-invalid @enderror" id="batch_code" name="batch_code"

                                   placeholder="เช่น B20250101001" required>                    <div class="row mb-4">                    @foreach ($errors->all() as $error)

                            @error('batch_code')

                                <div class="invalid-feedback d-block">{{ $message }}</div>                        <div class="col-md-6">                        <li>{{ $error }}</li>

                            @enderror

                        </div>                            <label for="farm_id" class="form-label fw-bold">ฟาร์ม</label>                    @endforeach

                    </div>

                            <select class="form-select" id="farm_id" name="farm_id" required>                </ul>

                    <!-- Barn Selection Section -->

                    <div class="mb-4">                                <option value="">-- เลือกฟาร์ม --</option>                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

                        <label class="form-label fw-bold d-block mb-3">เลือกเล้า</label>

                        <div class="row g-3">                                @foreach ($farm ? [$farm] : [] as $f)            </div>

                            @foreach ($barns as $barn)

                                <div class="col-md-6">                                    <option value="{{ $f->id }}" selected>{{ $f->farm_name }}</option>        @endif

                                    <div class="card border-primary h-100">

                                        <div class="card-body">                                @endforeach

                                            <div class="form-check">

                                                <input class="form-check-input barn-checkbox" type="checkbox"                             </select>        <form action="{{ route('batch_entry.store') }}" method="POST" enctype="multipart/form-data" id="batchEntryForm">

                                                       id="barn_{{ $barn->id }}" name="barns[]"

                                                       value="{{ $barn->id }}" data-barn-id="{{ $barn->id }}"                            @error('farm_id')            @csrf

                                                       data-barn-code="{{ $barn->barn_code }}"

                                                       data-capacity="{{ $barn->pig_capacity }}">                                <div class="text-danger small mt-1">{{ $message }}</div>

                                                <label class="form-check-label fw-bold" for="barn_{{ $barn->id }}">

                                                    {{ $barn->barn_code }}                            @enderror            <div class="row">

                                                </label>

                                            </div>                        </div>                <!-- Column 1: Batch Information -->

                                            <small class="text-muted d-block mt-2">

                                                <i class="bi bi-info-circle"></i>                <div class="col-lg-6 mb-4">

                                                ความจุ: {{ $barn->pig_capacity }} ตัว

                                            </small>                        <!-- Batch Code -->                    <div class="card border-0 shadow-sm">

                                        </div>

                                    </div>                        <div class="col-md-6">                        <div class="card-header bg-primary text-white">

                                </div>

                            @endforeach                            <label for="batch_code" class="form-label fw-bold">รหัสรุ่น</label>                            <h5 class="mb-0">

                        </div>

                        @error('barns')                            <input type="text" class="form-control" id="batch_code" name="batch_code"                                 <i class="fas fa-info-circle me-2"></i>

                            <div class="text-danger small mt-2">{{ $message }}</div>

                        @enderror                                   placeholder="เช่น B20250101001" required>                                ข้อมูลรุ่น

                    </div>

                            @error('batch_code')                            </h5>

                    <!-- Total Pig Amount -->

                    <div class="row mb-4">                                <div class="text-danger small mt-1">{{ $message }}</div>                        </div>

                        <div class="col-md-6">

                            <label for="total_pig_amount" class="form-label fw-bold">จำนวนหมูทั้งหมด</label>                            @enderror                        <div class="card-body">

                            <input type="number" class="form-control @error('total_pig_amount') is-invalid @enderror" id="total_pig_amount"

                                   name="total_pig_amount" placeholder="จำนวนหมูที่จะเพิ่ม"                         </div>                            <!-- Batch Code -->

                                   min="1" required>

                            @error('total_pig_amount')                    </div>                            <div class="mb-3">

                                <div class="invalid-feedback d-block">{{ $message }}</div>

                            @enderror                                <label for="batch_code" class="form-label">

                        </div>

                    <!-- Barn Selection Section -->                                    รหัสรุ่น <span class="text-danger">*</span>

                        <!-- Note -->

                        <div class="col-md-6">                    <div class="mb-4">                                </label>

                            <label for="note" class="form-label fw-bold">หมายเหตุ</label>

                            <textarea class="form-control" id="note" name="note" rows="3"                         <label class="form-label fw-bold d-block mb-3">เลือกเล้า</label>                                <input type="text" class="form-control @error('batch_code') is-invalid @enderror"

                                      placeholder="หมายเหตุเพิ่มเติม (ไม่บังคับ)"></textarea>

                        </div>                        <div class="row g-3">                                    id="batch_code" name="batch_code" value="{{ old('batch_code') }}"

                    </div>

                            @foreach ($barns as $barn)                                    placeholder="เช่น B001, B002" required>

                    <!-- Pen Allocation Preview -->

                    <div class="card bg-light mb-4" id="allocationPreview" style="display: none;">                                <div class="col-md-6">                                @error('batch_code')

                        <div class="card-header bg-primary text-white">

                            <h5 class="mb-0"><i class="bi bi-info-circle"></i> ตัวอย่างการจัดสรรหมู</h5>                                    <div class="card border-primary h-100">                                    <small class="invalid-feedback d-block">{{ $message }}</small>

                        </div>

                        <div class="card-body">                                        <div class="card-body">                                @enderror

                            <div class="table-responsive">

                                <table class="table table-sm mb-0">                                            <div class="form-check">                            </div>

                                    <thead class="table-light">

                                        <tr>                                                <input class="form-check-input barn-checkbox" type="checkbox"

                                            <th>เล้า</th>

                                            <th class="text-center">จำนวนคอก</th>                                                       id="barn_{{ $barn->id }}" name="barns[]"                             <!-- Barns Selection -->

                                            <th class="text-center">หมูต่อคอก</th>

                                            <th class="text-center">รวมหมู</th>                                                       value="{{ $barn->id }}" data-barn-id="{{ $barn->id }}"                            <div class="mb-3">

                                        </tr>

                                    </thead>                                                       data-barn-code="{{ $barn->barn_code }}"                                <label for="barn_ids" class="form-label">

                                    <tbody id="allocationTableBody">

                                    </tbody>                                                       data-capacity="{{ $barn->pig_capacity }}">                                    คอก <span class="text-danger">*</span>

                                </table>

                            </div>                                                <label class="form-check-label fw-bold" for="barn_{{ $barn->id }}">                                </label>

                        </div>

                    </div>                                                    {{ $barn->barn_code }}                                <select class="form-select @error('barn_ids') is-invalid @enderror" id="barn_ids"



                    <!-- Error Alert -->                                                </label>                                    name="barn_ids[]" multiple required>

                    <div id="errorAlert" class="alert alert-danger d-none" role="alert">

                        <i class="bi bi-exclamation-circle"></i>                                            </div>                                    @forelse($barns as $barn)

                        <span id="errorMessage"></span>

                    </div>                                            <small class="text-muted d-block mt-2">                                        <option value="{{ $barn->id }}"



                    <!-- Buttons -->                                                <i class="bi bi-info-circle"></i>                                            {{ in_array($barn->id, old('barn_ids', [])) ? 'selected' : '' }}>

                    <div class="d-flex gap-2 justify-content-end">

                        <a href="{{ route('batch.index') }}" class="btn btn-secondary">                                                ความจุ: {{ $barn->pig_capacity }} ตัว                                            {{ $barn->barn_code }} - {{ $barn->barn_name }}

                            <i class="bi bi-x-circle"></i> ยกเลิก

                        </a>                                            </small>                                        </option>

                        <button type="submit" class="btn btn-success">

                            <i class="bi bi-check-circle"></i> สร้างรุ่นและเพิ่มหมู                                        </div>                                    @empty

                        </button>

                    </div>                                    </div>                                        <option disabled>ไม่มีคอกที่พร้อมใช้งาน</option>

                </form>

            </div>                                </div>                                    @endforelse

        </div>

    </div>                            @endforeach                                </select>



    <script>                        </div>                                <small class="text-muted d-block mt-1">

        document.addEventListener('DOMContentLoaded', function() {

            const barnCheckboxes = document.querySelectorAll('.barn-checkbox');                        @error('barns')                                    <i class="fas fa-info-circle me-1"></i>

            const totalPigAmount = document.getElementById('total_pig_amount');

            const allocationPreview = document.getElementById('allocationPreview');                            <div class="text-danger small mt-2">{{ $message }}</div>                                    เลือกคอก 1 ตัว หรือมากกว่า

            const allocationTableBody = document.getElementById('allocationTableBody');

            const errorAlert = document.getElementById('errorAlert');                        @enderror                                </small>

            const errorMessage = document.getElementById('errorMessage');

                    </div>                                @error('barn_ids')

            function updateAllocationPreview() {

                const selectedBarns = Array.from(barnCheckboxes)                                    <small class="invalid-feedback d-block">{{ $message }}</small>

                    .filter(cb => cb.checked)

                    .map(cb => ({                    <!-- Total Pig Amount -->                                @enderror

                        id: cb.dataset.barnId,

                        code: cb.dataset.barnCode,                    <div class="row mb-4">                            </div>

                        capacity: parseInt(cb.dataset.capacity)

                    }));                        <div class="col-md-6">



                const totalPigs = parseInt(totalPigAmount.value) || 0;                            <label for="total_pig_amount" class="form-label fw-bold">จำนวนหมูทั้งหมด</label>                            <!-- Batch Note -->



                if (selectedBarns.length === 0 || totalPigs === 0) {                            <input type="number" class="form-control" id="total_pig_amount"                             <div class="mb-0">

                    allocationPreview.style.display = 'none';

                    errorAlert.classList.add('d-none');                                   name="total_pig_amount" placeholder="จำนวนหมูที่จะเพิ่ม"                                 <label for="note" class="form-label">หมายเหตุเพิ่มเติม</label>

                    return;

                }                                   min="1" required>                                <textarea class="form-control @error('note') is-invalid @enderror" id="note" name="note" rows="3"



                // Calculate total barn capacity                            @error('total_pig_amount')                                    placeholder="หมายเหตุเกี่ยวกับรุ่นนี้">{{ old('note') }}</textarea>

                const totalCapacity = selectedBarns.reduce((sum, barn) => sum + barn.capacity, 0);

                                <div class="text-danger small mt-1">{{ $message }}</div>                                @error('note')

                if (totalPigs > totalCapacity) {

                    errorAlert.classList.remove('d-none');                            @enderror                                    <small class="invalid-feedback d-block">{{ $message }}</small>

                    errorMessage.textContent = `จำนวนหมู (${totalPigs}) เกินความจุรวม (${totalCapacity})`;

                    allocationPreview.style.display = 'none';                        </div>                                @enderror

                    return;

                }                            </div>



                errorAlert.classList.add('d-none');                        <!-- Note -->                        </div>



                // Calculate pigs per pen (estimate)                        <div class="col-md-6">                    </div>

                const totalPens = selectedBarns.reduce((sum, barn) => sum + 20, 0); // 20 pens per barn

                const pigsPerPen = Math.floor(totalPigs / totalPens);                            <label for="note" class="form-label fw-bold">หมายเหตุ</label>                </div>

                const remainingPigs = totalPigs % totalPens;

                            <textarea class="form-control" id="note" name="note" rows="3"

                // Generate preview table

                let html = '';                                      placeholder="หมายเหตุเพิ่มเติม (ไม่บังคับ)"></textarea>                <!-- Column 2: Pig Entry Information -->

                let pigCounter = 0;

                        </div>                <div class="col-lg-6 mb-4">

                selectedBarns.forEach(barn => {

                    const penCount = 20;                    </div>                    <div class="card border-0 shadow-sm">

                    let barnTotal = pigsPerPen * penCount;

                                            <div class="card-header bg-success text-white">

                    // Distribute remaining pigs to this barn

                    if (pigCounter + penCount <= totalPens && remainingPigs > 0) {                    <!-- Pen Allocation Preview -->                            <h5 class="mb-0">

                        const remainForThisBarn = Math.min(remainingPigs, penCount);

                        barnTotal += remainForThisBarn;                    <div class="card bg-light mb-4" id="allocationPreview" style="display: none;">                                <i class="fas fa-truck me-2"></i>

                        pigCounter += remainForThisBarn;

                    }                        <div class="card-header bg-primary text-white">                                ข้อมูลเข้าหมู



                    html += `                            <h5 class="mb-0"><i class="bi bi-info-circle"></i> ตัวอย่างการจัดสรรหมู</h5>                            </h5>

                        <tr>

                            <td>${barn.code}</td>                        </div>                        </div>

                            <td class="text-center">${penCount}</td>

                            <td class="text-center">${pigsPerPen}${remainingPigs > 0 ? '+' : ''}</td>                        <div class="card-body">                        <div class="card-body">

                            <td class="text-center"><strong>${barnTotal}</strong></td>

                        </tr>                            <div class="table-responsive">                            <!-- Pig Entry Date -->

                    `;

                    pigCounter += pigsPerPen * penCount;                                <table class="table table-sm mb-0">                            <div class="mb-3">

                });

                                    <thead class="table-light">                                <label for="pig_entry_date" class="form-label">

                allocationTableBody.innerHTML = html;

                allocationPreview.style.display = 'block';                                        <tr>                                    วันเข้าหมู <span class="text-danger">*</span>

            }

                                            <th>เล้า</th>                                </label>

            // Event listeners

            barnCheckboxes.forEach(cb => {                                            <th class="text-center">จำนวนคอก</th>                                <input type="date" class="form-control @error('pig_entry_date') is-invalid @enderror"

                cb.addEventListener('change', updateAllocationPreview);

            });                                            <th class="text-center">หมูต่อคอก</th>                                    id="pig_entry_date" name="pig_entry_date"



            totalPigAmount.addEventListener('input', updateAllocationPreview);                                            <th class="text-center">รวมหมู</th>                                    value="{{ old('pig_entry_date', now()->format('Y-m-d')) }}" required>



            // Form submission                                        </tr>                                @error('pig_entry_date')

            document.getElementById('batchEntryForm').addEventListener('submit', function(e) {

                const selectedBarns = Array.from(barnCheckboxes).filter(cb => cb.checked);                                    </thead>                                    <small class="invalid-feedback d-block">{{ $message }}</small>

                const totalPigs = parseInt(totalPigAmount.value) || 0;

                                    <tbody id="allocationTableBody">                                @enderror

                if (selectedBarns.length === 0) {

                    e.preventDefault();                                    </tbody>                            </div>

                    errorAlert.classList.remove('d-none');

                    errorMessage.textContent = 'กรุณาเลือกเล้าอย่างน้อย 1 เล้า';                                </table>

                    return;

                }                            </div>                            <!-- Total Pig Amount -->



                if (totalPigs === 0) {                        </div>                            <div class="mb-3">

                    e.preventDefault();

                    errorAlert.classList.remove('d-none');                    </div>                                <label for="total_pig_amount" class="form-label">

                    errorMessage.textContent = 'กรุณากรอกจำนวนหมู';

                    return;                                    จำนวนหมู <span class="text-danger">*</span>

                }

                    <!-- Error Alert -->                                </label>

                // Calculate total capacity

                const totalCapacity = selectedBarns.reduce((sum, cb) => sum + parseInt(cb.dataset.capacity), 0);                    <div id="errorAlert" class="alert alert-danger d-none" role="alert">                                <input type="number" class="form-control @error('total_pig_amount') is-invalid @enderror"

                if (totalPigs > totalCapacity) {

                    e.preventDefault();                        <i class="bi bi-exclamation-circle"></i>                                    id="total_pig_amount" name="total_pig_amount" value="{{ old('total_pig_amount') }}"

                    errorAlert.classList.remove('d-none');

                    errorMessage.textContent = `จำนวนหมู (${totalPigs}) เกินความจุรวม (${totalCapacity})`;                        <span id="errorMessage"></span>                                    min="1" placeholder="เช่น 100" required>

                    return;

                }                    </div>                                @error('total_pig_amount')

            });

        });                                    <small class="invalid-feedback d-block">{{ $message }}</small>

    </script>

                    <!-- Buttons -->                                @enderror

    <style>

        .barn-checkbox {                    <div class="d-flex gap-2 justify-content-end">                            </div>

            cursor: pointer;

            width: 1.25rem;                        <a href="{{ route('batch.index') }}" class="btn btn-secondary">

            height: 1.25rem;

        }                            <i class="bi bi-x-circle"></i> ยกเลิก                            <!-- Total Pig Weight -->



        .card.border-primary {                        </a>                            <div class="mb-3">

            transition: all 0.3s ease;

        }                        <button type="submit" class="btn btn-success">                                <label for="total_pig_weight" class="form-label">



        .card.border-primary:hover {                            <i class="bi bi-check-circle"></i> สร้างรุ่นและเพิ่มหมู                                    น้ำหนักรวม (kg) <span class="text-danger">*</span>

            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);

        }                        </button>                                </label>



        .card-header.bg-primary {                    </div>                                <input type="number" class="form-control @error('total_pig_weight') is-invalid @enderror"

            background-color: #0d6efd !important;

        }                </form>                                    id="total_pig_weight" name="total_pig_weight" value="{{ old('total_pig_weight') }}"

    </style>

@endsection            </div>                                    step="0.01" min="0.1" placeholder="เช่น 1200" required


        </div>                                    @change="calculateAverageWeight()">

    </div>                                @error('total_pig_weight')

                                    <small class="invalid-feedback d-block">{{ $message }}</small>

    <script>                                @enderror

        document.addEventListener('DOMContentLoaded', function() {                            </div>

            const barnCheckboxes = document.querySelectorAll('.barn-checkbox');

            const totalPigAmount = document.getElementById('total_pig_amount');                            <!-- Average Weight Per Pig (Auto-calculated) -->

            const allocationPreview = document.getElementById('allocationPreview');                            <div class="mb-3">

            const allocationTableBody = document.getElementById('allocationTableBody');                                <label for="average_weight_per_pig" class="form-label">

            const errorAlert = document.getElementById('errorAlert');                                    น้ำหนักเฉลี่ยต่อตัว (kg) <span class="text-danger">*</span>

            const errorMessage = document.getElementById('errorMessage');                                </label>

                                <div class="input-group">

            function updateAllocationPreview() {                                    <input type="number"

                const selectedBarns = Array.from(barnCheckboxes)                                        class="form-control @error('average_weight_per_pig') is-invalid @enderror"

                    .filter(cb => cb.checked)                                        id="average_weight_per_pig" name="average_weight_per_pig"

                    .map(cb => ({                                        value="{{ old('average_weight_per_pig') }}" step="0.01" min="0.1"

                        id: cb.dataset.barnId,                                        readonly required>

                        code: cb.dataset.barnCode,                                    <button class="btn btn-outline-secondary" type="button" id="calcWeightBtn">

                        capacity: parseInt(cb.dataset.capacity)                                        <i class="fas fa-calculator me-1"></i>คำนวณ

                    }));                                    </button>

                                </div>

                const totalPigs = parseInt(totalPigAmount.value) || 0;                                <small class="text-muted d-block mt-1">คำนวณจากน้ำหนักรวม ÷ จำนวนหมู</small>

                                @error('average_weight_per_pig')

                if (selectedBarns.length === 0 || totalPigs === 0) {                                    <small class="invalid-feedback d-block">{{ $message }}</small>

                    allocationPreview.style.display = 'none';                                @enderror

                    errorAlert.classList.add('d-none');                            </div>

                    return;                        </div>

                }                    </div>

                </div>

                // Calculate total barn capacity            </div>

                const totalCapacity = selectedBarns.reduce((sum, barn) => sum + barn.capacity, 0);

            <div class="row">

                if (totalPigs > totalCapacity) {                <!-- Column 3: Pricing Information -->

                    errorAlert.classList.remove('d-none');                <div class="col-lg-6 mb-4">

                    errorMessage.textContent = `จำนวนหมู (${totalPigs}) เกินความจุรวม (${totalCapacity})`;                    <div class="card border-0 shadow-sm">

                    allocationPreview.style.display = 'none';                        <div class="card-header bg-info text-white">

                    return;                            <h5 class="mb-0">

                }                                <i class="fas fa-money-bill-wave me-2"></i>

                                ข้อมูลราคา

                errorAlert.classList.add('d-none');                            </h5>

                        </div>

                // Calculate pigs per pen (estimate)                        <div class="card-body">

                const totalPens = selectedBarns.reduce((sum, barn) => sum + 20, 0); // 20 pens per barn                            <!-- Average Price Per Pig -->

                const pigsPerPen = Math.floor(totalPigs / totalPens);                            <div class="mb-3">

                const remainingPigs = totalPigs % totalPens;                                <label for="average_price_per_pig" class="form-label">

                                    ราคาเฉลี่ยต่อตัว (บาท) <span class="text-danger">*</span>

                // Generate preview table                                </label>

                let html = '';                                <input type="number"

                let pigCounter = 0;                                    class="form-control @error('average_price_per_pig') is-invalid @enderror"

                                    id="average_price_per_pig" name="average_price_per_pig"

                selectedBarns.forEach(barn => {                                    value="{{ old('average_price_per_pig') }}" step="0.01" min="0"

                    const penCount = 20;                                    placeholder="เช่น 80" required @change="calculateTotalPrice()">

                    let barnTotal = pigsPerPen * penCount;                                @error('average_price_per_pig')

                                                        <small class="invalid-feedback d-block">{{ $message }}</small>

                    // Distribute remaining pigs to this barn                                @enderror

                    if (pigCounter + penCount <= totalPens && remainingPigs > 0) {                            </div>

                        const remainForThisBarn = Math.min(remainingPigs, penCount);

                        barnTotal += remainForThisBarn;                            <!-- Total Price (Display only) -->

                        pigCounter += remainForThisBarn;                            <div class="mb-3">

                    }                                <label for="total_pig_price" class="form-label">ราคารวม (บาท)</label>

                                <div class="alert alert-light border border-info mb-0">

                    html += `                                    <h5 class="mb-0 text-info" id="total_pig_price_display">

                        <tr>                                        <i class="fas fa-tag me-2"></i>0.00 บาท

                            <td>${barn.code}</td>                                    </h5>

                            <td class="text-center">${penCount}</td>                                </div>

                            <td class="text-center">${pigsPerPen}${remainingPigs > 0 ? '+' : ''}</td>                                <small class="text-muted d-block mt-1">คำนวณจากจำนวนหมู × ราคาเฉลี่ย</small>

                            <td class="text-center"><strong>${barnTotal}</strong></td>                            </div>

                        </tr>

                    `;                            <!-- Payment Method -->

                    pigCounter += pigsPerPen * penCount;                            <div class="mb-3">

                });                                <label for="payment_method" class="form-label">

                                    วิธีชำระเงิน <span class="text-danger">*</span>

                allocationTableBody.innerHTML = html;                                </label>

                allocationPreview.style.display = 'block';                                <select class="form-select @error('payment_method') is-invalid @enderror"

            }                                    id="payment_method" name="payment_method" required>

                                    <option value="">-- เลือกวิธีชำระเงิน --</option>

            // Event listeners                                    <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>

            barnCheckboxes.forEach(cb => {                                        สด (Cash)

                cb.addEventListener('change', updateAllocationPreview);                                    </option>

            });                                    <option value="bank_transfer"

                                        {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>

            totalPigAmount.addEventListener('input', updateAllocationPreview);                                        โอนธนาคาร (Bank Transfer)

                                    </option>

            // Form submission                                    <option value="check" {{ old('payment_method') === 'check' ? 'selected' : '' }}>

            document.getElementById('batchEntryForm').addEventListener('submit', function(e) {                                        เช็ค (Check)

                const selectedBarns = Array.from(barnCheckboxes).filter(cb => cb.checked);                                    </option>

                const totalPigs = parseInt(totalPigAmount.value) || 0;                                    <option value="other" {{ old('payment_method') === 'other' ? 'selected' : '' }}>

                                        อื่นๆ (Other)

                if (selectedBarns.length === 0) {                                    </option>

                    e.preventDefault();                                </select>

                    errorAlert.classList.remove('d-none');                                @error('payment_method')

                    errorMessage.textContent = 'กรุณาเลือกเล้าอย่างน้อย 1 เล้า';                                    <small class="invalid-feedback d-block">{{ $message }}</small>

                    return;                                @enderror

                }                            </div>



                if (totalPigs === 0) {                            <!-- Payment Term -->

                    e.preventDefault();                            <div class="mb-0">

                    errorAlert.classList.remove('d-none');                                <label for="payment_term" class="form-label">

                    errorMessage.textContent = 'กรุณากรอกจำนวนหมู';                                    เงื่อนไขการชำระ <span class="text-danger">*</span>

                    return;                                </label>

                }                                <select class="form-select @error('payment_term') is-invalid @enderror" id="payment_term"

                                    name="payment_term" required>

                // Calculate total capacity                                    <option value="">-- เลือกเงื่อนไขการชำระ --</option>

                const totalCapacity = selectedBarns.reduce((sum, cb) => sum + parseInt(cb.dataset.capacity), 0);                                    <option value="cash" {{ old('payment_term') === 'cash' ? 'selected' : '' }}>

                if (totalPigs > totalCapacity) {                                        ชำระเต็มจำนวน (Cash)

                    e.preventDefault();                                    </option>

                    errorAlert.classList.remove('d-none');                                    <option value="credit" {{ old('payment_term') === 'credit' ? 'selected' : '' }}>

                    errorMessage.textContent = `จำนวนหมู (${totalPigs}) เกินความจุรวม (${totalCapacity})`;                                        ชำระเป็นงวด (Credit)

                    return;                                    </option>

                }                                </select>

            });                                @error('payment_term')

        });                                    <small class="invalid-feedback d-block">{{ $message }}</small>

    </script>                                @enderror

                            </div>

    <style>                        </div>

        .barn-checkbox {                    </div>

            cursor: pointer;                </div>

            width: 1.25rem;

            height: 1.25rem;                <!-- Column 4: Document & Note -->

        }                <div class="col-lg-6 mb-4">

                    <div class="card border-0 shadow-sm">

        .card.border-primary {                        <div class="card-header bg-warning text-dark">

            transition: all 0.3s ease;                            <h5 class="mb-0">

        }                                <i class="fas fa-file-upload me-2"></i>

                                เอกสารและหมายเหตุ

        .card.border-primary:hover {                            </h5>

            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);                        </div>

        }                        <div class="card-body">

                            <!-- Receipt File -->

        .card-header.bg-primary {                            <div class="mb-3">

            background-color: #0d6efd !important;                                <label for="receipt_file" class="form-label">ใบเสร็จ/เอกสารอื่นๆ</label>

        }                                <input type="file" class="form-control @error('receipt_file') is-invalid @enderror"

    </style>                                    id="receipt_file" name="receipt_file" accept=".pdf,.jpg,.jpeg,.png"

@endsection                                    @change="updateFileInfo()">

                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-info-circle me-1"></i>
                                    PDF, JPG, PNG เท่านั้น (ขนาดสูงสุด 5 MB)
                                </small>
                                <div id="fileInfo" class="mt-2"></div>
                                @error('receipt_file')
                                    <small class="invalid-feedback d-block">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Entry Note -->
                            <div class="mb-0">
                                <label for="entry_note" class="form-label">หมายเหตุเข้าหมู</label>
                                <textarea class="form-control @error('entry_note') is-invalid @enderror" id="entry_note" name="entry_note"
                                    rows="5" placeholder="หมายเหตุเพิ่มเติมเกี่ยวกับการเข้าหมู">{{ old('entry_note') }}</textarea>
                                @error('entry_note')
                                    <small class="invalid-feedback d-block">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm bg-light">
                        <div class="card-body">
                            <h6 class="card-title mb-3">
                                <i class="fas fa-list-check me-2"></i>
                                สรุปข้อมูล
                            </h6>
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="p-2">
                                        <small class="text-muted d-block">รหัสรุ่น</small>
                                        <h6 id="summaryBatchCode" class="mb-0">-</h6>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-2 border-start">
                                        <small class="text-muted d-block">จำนวนหมู</small>
                                        <h6 id="summaryPigAmount" class="mb-0">0 ตัว</h6>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-2 border-start">
                                        <small class="text-muted d-block">น้ำหนักรวม</small>
                                        <h6 id="summaryWeight" class="mb-0">0 kg</h6>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-2 border-start">
                                        <small class="text-muted d-block">ราคารวม</small>
                                        <h6 id="summaryPrice" class="mb-0">0 บาท</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="row mb-4">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-check-circle me-2"></i>
                        สร้างรุ่น + บันทึกเข้าหมู
                    </button>
                    <a href="{{ route('batch.index') }}" class="btn btn-secondary btn-lg ms-2">
                        <i class="fas fa-times-circle me-2"></i>
                        ยกเลิก
                    </a>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            const form = document.getElementById('batchEntryForm');

            // Calculate average weight
            document.getElementById('calcWeightBtn').addEventListener('click', calculateAverageWeight);

            function calculateAverageWeight() {
                const amount = parseInt(document.getElementById('total_pig_amount').value) || 0;
                const weight = parseFloat(document.getElementById('total_pig_weight').value) || 0;

                if (amount > 0 && weight > 0) {
                    const average = (weight / amount).toFixed(2);
                    document.getElementById('average_weight_per_pig').value = average;
                    calculateTotalPrice();
                }
            }

            // Auto-calculate when inputs change
            document.getElementById('total_pig_amount').addEventListener('change', calculateTotalPrice);
            document.getElementById('total_pig_weight').addEventListener('change', calculateAverageWeight);
            document.getElementById('average_price_per_pig').addEventListener('change', calculateTotalPrice);
            document.getElementById('batch_code').addEventListener('change', updateSummary);

            function calculateTotalPrice() {
                const amount = parseInt(document.getElementById('total_pig_amount').value) || 0;
                const price = parseFloat(document.getElementById('average_price_per_pig').value) || 0;
                const total = (amount * price).toFixed(2);

                document.getElementById('total_pig_price_display').textContent =
                    `💰 ${new Intl.NumberFormat('th-TH').format(total)} บาท`;

                updateSummary();
            }

            function updateSummary() {
                document.getElementById('summaryBatchCode').textContent =
                    document.getElementById('batch_code').value || '-';
                document.getElementById('summaryPigAmount').textContent =
                    (document.getElementById('total_pig_amount').value || 0) + ' ตัว';
                document.getElementById('summaryWeight').textContent =
                    (document.getElementById('total_pig_weight').value || 0) + ' kg';

                const price = parseFloat(document.getElementById('average_price_per_pig').value) *
                    (parseInt(document.getElementById('total_pig_amount').value) || 0);
                document.getElementById('summaryPrice').textContent =
                    new Intl.NumberFormat('th-TH').format(price.toFixed(2)) + ' บาท';
            }

            function updateFileInfo() {
                const fileInput = document.getElementById('receipt_file');
                const fileInfo = document.getElementById('fileInfo');

                if (fileInput.files.length > 0) {
                    const file = fileInput.files[0];
                    const size = (file.size / 1024).toFixed(2);
                    fileInfo.innerHTML = `
                <div class="alert alert-success py-2 mb-0">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>${file.name}</strong> (${size} KB)
                </div>
            `;
                } else {
                    fileInfo.innerHTML = '';
                }
            }

            // Initialize summary on page load
            updateSummary();
        </script>
    @endpush

@endsection
