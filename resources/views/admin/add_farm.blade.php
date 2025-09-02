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
              <h4 class="mb-0">เพิ่มฟาร์ม (Add Farm)</h4>
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


                <form action="{{url('upload_farm')}}" method="post" enctype="multipart/form-data">
                    @csrf

            <div class="mb-3">
                <label for="farm_name" class="form-label">ชื่อฟาร์ม</label>
                <input type="text" class="form-control" id="farm_name" name="farm_name" value="{{ old('farm_name') }}" required>
            </div>

            <button type="submit" value="Add Farm" class="btn btn-primary">บันทึก</button>


                </form>
            </div>
          </div>

        </div>
        </div>
    </div>

    @include('admin.js')
  </body>
</html>
