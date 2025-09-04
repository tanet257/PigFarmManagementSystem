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
        }, 4000); // 4 วิ แล้วหาย
    }
};

function closeSnackbar() {
    let sb = document.getElementById("snackbar");
    if (sb) {
        sb.classList.remove("show");
    }
}
</script>


                <form action="{{url('upload_batch')}}" method="post" enctype="multipart/form-data">
                    @csrf

                <div class="mb-3 row">
                  <label class="col-sm-3 col-form-label">ชื่อฟาร์ม</label>
                  <div class="col-sm-9">

                    <select name="farm_id" class="form-select" required>
                      <option value="">-- เลือกฟาร์ม --</option>
                      @foreach($farms as $farm)
                        <option value="{{ $farm->id }}">{{ $farm->farm_name ?? 'ฟาร์ม '.$farm->id }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>

                <div class="mb-3 row">
                  <label class="col-sm-3 col-form-label">รหัสรุ่น</label>
                  <div class="col-sm-9">
                    <input type="text" name="batch_code" class="form-control" required>
                  </div>
                </div>

                <div class="mb-3 row">
                  <label class="col-sm-3 col-form-label">เล้า</label>
                  <div class="col-sm-9">
                    <select name="barn_id" class="form-select" required>
                      <option value="">-- เลือกเล้า --</option>
                      @foreach($barns as $barn)
                        <option value="{{ $barn->id }}">{{ $barn->barn_code ?? 'เล้า '.$barn->id }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>

                <div class="mb-3 row">
                  <label class="col-sm-3 col-form-label">คอก</label>
                  <div class="col-sm-9">
                    <select name="pen_id" class="form-select" required>
                      <option value="">-- เลือกคอก --</option>
                      @foreach($pens as $pen)
                        <option value="{{ $pen->id }}">{{ $pen->pen_code ?? 'คอก '.$pen->id }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>

                <div class="mb-3 row">
                  <label class="col-sm-3 col-form-label">จำนวนหมูทั้งหมด</label>
                  <div class="col-sm-9">
                    <input type="text" name="total_pig_amount" class="form-control" required>
                  </div>
                </div>

                <div class="mb-3 row">
                  <label class="col-sm-3 col-form-label">จำนวนหมูเข้า</label>
                  <div class="col-sm-9">
                    <input type="text" name="initial_pig_amount" class="form-control" required>
                  </div>
                </div>

                <div class="mb-3 row">
                  <label class="col-sm-3 col-form-label">น้ำหนักหมูรวม</label>
                  <div class="col-sm-9">
                    <input type="text" name="total_pig_weight" class="form-control" required>
                  </div>
                </div>

                <div class="mb-3 row">
                  <label class="col-sm-3 col-form-label">น้ำหนักหมูเฉลี่ย</label>
                  <div class="col-sm-9">
                    <input type="text" name="average_pig_weight" class="form-control" required>
                  </div>
                </div>

                <div class="mb-3 row">
                  <label class="col-sm-3 col-form-label">ราคาหมูรวม</label>
                  <div class="col-sm-9">
                    <input type="text" name="total_pig_price" class="form-control" required>
                  </div>
                </div>

                <div class="mb-3 row">
                  <label class="col-sm-3 col-form-label">ราคาหมูเฉลี่ย</label>
                  <div class="col-sm-9">
                    <input type="text" name="average_pig_price" class="form-control" required>
                  </div>
                </div>

                <div class="mb-3 row">
                  <label class="col-sm-3 col-form-label">หมายเหตุ</label>
                  <div class="col-sm-9">
                    <textarea name="note" rows="4" class="form-control" ></textarea>
                  </div>
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
