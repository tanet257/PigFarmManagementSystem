<!DOCTYPE html>
<html>
  <head>
    @include('admin.css')
    <style>
        table {
            border: 1px solid skyblue;
            margin: auto;
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background-color: skyblue;
            color: white;
            padding: 10px;
            text-align: center;
        }
        td {
            color: black;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .no-records {
            text-align: center;
            color: red;
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

          <h1>รายการบันทึกสุกรรับเข้า (Pig Entry Records)</h1>

          <div>
            <table>
              <thead>
                <tr>
                  <th>Farm Id</th>
                  <th>Entry Id</th>
                  <th>Barn Id</th>
                  <th>Pen Id</th>
                  <th>วันที่รับเข้า</th>
                  <th>จำนวน (ตัว)</th>
                  <th>น้ำหนักรวม (กก.)</th>
                  <th>จำนวนรวม</th>
                  <th>ราคารวม</th>
                  <th>ราคา/ตัว</th>
                  <th>หมายเหตุ</th>
                  <th>Start Date</th>
                  <th>End Date</th>
                </tr>
              </thead>
              <tbody>
                @if($pig_entry_records->isEmpty())
                  <tr>
                    <td colspan="13" class="no-records">No records found</td>
                  </tr>
                @else
                  @foreach($pig_entry_records as $pig_entry_record)
                    <tr>
                      <td>{{ $pig_entry_record->farm_id }}</td>
                      <td>{{ $pig_entry_record->id }}</td>
                      <td>{{ $pig_entry_record->barn_id }}</td>
                      <td>{{ $pig_entry_record->pen_id }}</td>

                      <td>{{ $pig_entry_record->pig_entry_date }}</td>
                      <td>{{ $pig_entry_record->total_pig_weight }}</td>
                      <td>{{ $pig_entry_record->total_pig_amount ?? '-' }}</td>
                      <td>{{ $pig_entry_record->total_pig_price ?? '-' }}</td>
                      <td>{{ $pig_entry_record->price_per_pig ?? '-' }}</td>
                      <td>{{ $pig_entry_record->note ?? '-' }}</td>
                      <td>{{ $pig_entry_record->start_date ?? '-' }}</td>
                      <td>{{ $pig_entry_record->end_date ?? '-' }}</td>
                    </tr>
                  @endforeach
                @endif
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </div>

    @include('admin.js')
  </body>
</html>
