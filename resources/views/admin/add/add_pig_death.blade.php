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

        /* ปิด scroll/arrow ของ number input */
        input[type=number]::-webkit-outer-spin-button,
        input[type=number]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type=number] {
            -moz-appearance: textfield; /* Firefox */
        }

        /* เพิ่ม class no-scroll เฉพาะ input number ก็ได้ */
        .no-scroll::-webkit-outer-spin-button,
        .no-scroll::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .no-scroll {
            -moz-appearance: textfield;
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
              <h4 class="mb-0">เพิ่มการให้อาหาร (Add Feeding)</h4>
            </div>
            <div class="card-body">
                <link rel="stylesheet"
                    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
                    
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
                        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                    }

                    .snackbar.show {
                        visibility: visible;
                        animation: fadein 0.5s, fadeout 0.5s 10s;
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
                        from {
                            bottom: 0;
                            opacity: 0;
                        }

                        to {
                            bottom: 30px;
                            opacity: 1;
                        }
                    }

                    @keyframes fadeout {
                        from {
                            bottom: 30px;
                            opacity: 1;
                        }

                        to {
                            bottom: 0;
                            opacity: 0;
                        }
                    }
                </style>

                @if (session('success'))
                    <div id="snackbar" class="snackbar" style="background-color:#28a745">
                        {{ session('success') }}
                        <button onclick="closeSnackbar()">✖</button>
                    </div>
                @endif

                @if (session('error'))
                    <div id="snackbar" class="snackbar" style="background-color:#dc3545">
                        <span id="snackbarMessage">{{ session('error') }}</span>
                        <button id="copyBtn" onclick="copySnackbar()"><i class="bi bi-copy"></i></button>
                        <button onclick="closeSnackbar()">✖</button>
                    </div>
                @endif

                <script>
                    window.onload = function() {
                        let sb = document.getElementById("snackbar");
                        if (sb) {
                            sb.classList.add("show");
                            setTimeout(function() {
                                if (sb) sb.classList.remove("show");
                            }, 10500);
                        }
                    };

                    function copySnackbar() {
                        let text = document.getElementById("snackbarMessage").innerText;
                        navigator.clipboard.writeText(text).then(() => {
                            let btn = document.getElementById("copyBtn");
                            btn.innerHTML = '<i class="bi bi-check2"></i> Copied';
                            btn.disabled = true; // ป้องกันกดซ้ำ
                            setTimeout(() => {
                                btn.innerHTML = '<i class="bi bi-copy"></i> Copy';
                                btn.disabled = false;
                            }, 2000); // 2 วิแล้วกลับมาเหมือนเดิม
                        });
                    }

                    function closeSnackbar() {
                        let sb = document.getElementById("snackbar");
                        if (sb) {
                            sb.classList.remove("show");
                        }
                    }
                </script>


            <form action="{{url('upload_pig_death')}}" method="post" enctype="multipart/form-data">
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
                  <label for="pen_code" class="form-label">เลือกคอก</label>
                    <select name="pen_id" class="form-select" required>
                      <option value="">-- เลือกคอก --</option>
                      @foreach($pens as $pen)
                        <option value="{{ $pen->id }}">{{ $pen->pen_code ?? 'คอก '.$pen->id }}</option>
                      @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="amount" class="form-label">จำนวนหมูที่ตาย</label>
                    <input type="number" class="form-control no-scroll" id="amount" name="amount" value="{{ old('amount') }}" required>
                </div>

                <div class="mb-3">
                    <label for="cause" class="form-label">สาเหตุการตาย</label>
                    <input type="text" class="form-control" id="cause" name="cause" value="{{ old('cause') }}" required>
                </div>

                <div class="mb-3">
                    <label for="note" class="form-label">หมายเหตุ</label>
                    <textarea class="form-control" id="note" name="note">{{ old('note') }}</textarea>
                </div>

                <button type="submit" value="Add Feeding" class="btn btn-primary">บันทึก</button>


                </form>
            </div>
          </div>

        </div>
        </div>
    </div>

    @include('admin.js')
  </body>
</html>
