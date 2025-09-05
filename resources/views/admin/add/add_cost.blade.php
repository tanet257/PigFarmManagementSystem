<!DOCTYPE html>
<html>
  <head>
    @include('admin.css')
    <style>
      label {
        display:inline-block;
        width: 200px;
        font-weight: bold;
      }
    </style>
  </head>
  <body>

    @include('admin.header')
    @include('admin.sidebar')

    <div class="page-content">
        <div class="page-header">
        <div class="container-fluid">

          <div class="card shadow-lg border-0 rounded-3">
            <div class="card-header bg-primary text-white">
              <h4 class="mb-0">เพิ่มค่าใช้จ่าย (Add Cost)</h4>
            </div>
            <div class="card-body">

                <style>
.snackbar {
    visibility: hidden;
    min-width: 250px;
    margin-left: -125px;
    background-color: #333;
    color: #fff;
    text-align: center;
    border-radius: 8px;
    padding: 16px;
    position: fixed;
    z-index: 9999;
    right: 20px;
    bottom: 30px;
    font-size: 16px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.3);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.snackbar.show {
    visibility: visible;
    animation: fadein 0.5s, fadeout 0.5s 3s; /* 3 วิ แล้วหายไป */
}
.snackbar button {
    background: none;
    border: none;
    color: #fff;
    font-weight: bold;
    margin-left: 10px;
    cursor: pointer;
}
@keyframes fadein {
    from {bottom: 0; opacity: 0;}
    to {bottom: 30px; opacity: 1;}
}
@keyframes fadeout {
    from {bottom: 30px; opacity: 1;}
    to {bottom: 0; opacity: 0;}
}
</style>

@if(session('success'))
<div id="snackbar" class="snackbar" style="background-color:#28a745">
  {{ session('success') }}
  <button onclick="closeSnackbar()">✖</button>
</div>
@endif

@if(session('error'))
<div id="snackbar" class="snackbar" style="background-color:#dc3545">
  {{ session('error') }}
  <button onclick="closeSnackbar()">✖</button>
</div>
@endif

<script>
window.onload = function() {
    let sb = document.getElementById("snackbar");
    if (sb) {
        sb.classList.add("show");
        setTimeout(function(){
            if (sb) sb.classList.remove("show");
        }, 10000); // 10 วิ แล้วหาย
    }
};

function closeSnackbar() {
    let sb = document.getElementById("snackbar");
    if (sb) {
        sb.classList.remove("show");
    }
}
</script>


                <form action="{{url('upload_cost')}}" method="post" enctype="multipart/form-data">
                    @csrf

            <div class="mb-3">
                  <label class="">เลือกฟาร์ม</label>
                    <div class="mb-3 row">
            <select name="farm_id" class="form-select" required>
                      <option value="">-- เลือกฟาร์ม --</option>
                      @foreach($farms as $farm)
                        <option value="{{ $farm->id }}">{{ $farm->farm_name ?? 'ฟาร์ม '.$farm->id }}</option>
                      @endforeach
                    </select>
                    </div>
            </div>

            <div class="mb-3">
                  <label class="">เลือกรุ่น</label>
                    <div class="mb-3 row">
            <select name="batch_id" class="form-select" required>
                      <option value="">-- เลือกรุ่น --</option>
                      @foreach($batches as $batch)
                        <option value="{{ $batch->id }}">{{ $batch->batch_code ?? 'รุ่น '.$batch->id }}</option>
                      @endforeach
                    </select>
                    </div>
            </div>

            <div class="mb-3">
                  <label class="">ประเภทค่าใช้จ่าย</label>
            <select name="cost_type" class="form-select" required>
                      <option value="">-- เลือกประเภทค่าใช้จ่าย --</option>
                      <option value="piglet"> piglet </option>
                      <option value="feed"> ค่าอาหาร </option>
                      <option value="medicine"> ค่ายา </option>
                      <option value="vaccine"> ค่าวัคซีน </option>
                      <option value="bran"> ค่ารำ </option>
                      <option value="labor"> ค่าแรงงาน </option>
                      <option value="transport"> ค่ารถ/ขนส่ง </option>
                      <option value="repair"> ค่าซ่อมแซม </option>
                      <option value="dead_pig"> ขาดทุนจากหมูตาย </option>
                      <option value="other"> อื่น ๆ </option>
            </select>
            </div>

            <div class="mb-3">
                <label for="quantity" class="form-label">จำนวน</label>
                <input type="number" class="form-control" id="quantity" name="quantity" value="{{ old('quantity') }}" required min="0">
            </div>

            <div class="mb-3">
                <label for="price_per_unit" class="form-label">ราคา/หน่วย</label>
                <input type="text" class="form-control" id="price_per_unit" name="price_per_unit" value="{{ old('price_per_unit') }}" required>
            </div>

            <div class="mb-3">
                <label for="total_price" class="form-label">ราคารวม</label>
                <input type="number" class="form-control" id="total_price" name="total_price" value="{{ old('total_price') }}" required min="0">
            </div>

            <div class="mb-3">
                <label for="note" class="form-label">หมายเหตุ</label>
                <textarea class="form-control" id="note" name="note">{{ old('note') }}</textarea>
            </div>

            <button type="submit" value="Add Cost" class="btn btn-primary">บันทึก</button>

                </form>

            </div>
          </div>

        </div>
        </div>
    </div>

    @include('admin.js')
  </body>
</html>
