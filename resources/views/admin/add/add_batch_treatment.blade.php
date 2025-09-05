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
              <h4 class="mb-0">เพิ่มรุ่นหมู (Add Batch)</h4>
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


                <form action="{{url('upload_batch_treatment')}}" method="post" enctype="multipart/form-data">
                    @csrf

                <div class="mb-3 row">
                  <label for="farm_id" class="">เลือกฟาร์ม</label>
                    <select name="farm_id" class="form-select" required>
                      <option value="">-- เลือกฟาร์ม --</option>
                      @foreach($farms as $farm)
                        <option value="{{ $farm->id }}">{{ $farm->farm_name ?? 'ฟาร์ม '.$farm->id }}</option>
                      @endforeach
                    </select>
                </div>

                <div class="mb-3 row">
                  <label for="batch_code" class="form-label">เลือกรหัสรุ่น</label>
                    <select name="batch_id" class="form-select" required>
                      <option value="">-- เลือกรหัสรุ่น --</option>
                      @foreach($batches as $batch)
                        <option value="{{ $batch->id }}">{{ $batch->batch_code ?? 'รุ่น '.$batch->id }}</option>
                      @endforeach
                    </select>
                </div>

                <div class="mb-3 row">
                  <label for="barn_code" class="form-label">เลือกเล้า</label>
                    <select name="barn_id" class="form-select" required>
                      <option value="">-- เลือกเล้า --</option>
                      @foreach($barns as $barn)
                        <option value="{{ $barn->id }}">
                            {{ $barn->barn_code ?? 'เล้า '.$barn->id }}
                        </option>
                      @endforeach
                    </select>
                </div>

                <div class="mb-3 row">
                  <label for="pen_code" class="form-label">เลือกคอก</label>
                    <select name="pen_id" class="form-select" required>
                      <option value="">-- เลือกคอก --</option>
                      @foreach($pens as $pen)
                        <option value="{{ $pen->id }}">{{ $pen->pen_code ?? 'คอก '.$pen->id }}</option>
                      @endforeach
                    </select>
                </div>

                <div class="mb-3 row">
                  <label class="">ชื่อยา</label>
                    <input type="text" name="medicine_name" class="form-control" required>
                </div>

                <div class="mb-3 row">
                  <label class="">ขนาดยา</label>
                    <input type="text" name="dosage" class="form-control" required>
                </div>

                <div class="mb-3 row">
                  <label class="">หน่วย</label>
                    <input type="text" name="unit" class="form-control" required>
                </div>

                <div class="mb-3 row">
                  <label class="">หมายเหตุ</label>
                    <textarea name="note" rows="4" class="form-control" ></textarea>
                </div>

                <div class="text-end">
                  <button type="submit" class="btn btn-primary">บันทึกข้อมูล</button>
                </div>

              </form>

            </div>
          </div>

        </div>
        </div>
    </div>

    @include('admin.js')
  </body>
</html>
