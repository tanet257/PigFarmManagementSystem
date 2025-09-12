<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <title>Report Batch</title>
    <style>
      body {
          font-family: 'sarabun';
          font-size: 14px;
      }
      h2 {
          text-align: center;
          margin-bottom: 20px;
      }
      table {
          width: 100%;
          border-collapse: collapse;
          font-size: 12px;
      }
      th, td {
          border: 1px solid #000;
          padding: 5px;
          text-align: center;
          word-wrap: break-word;
      }
      th {
          background-color: #464646;
          color: #fff;
      }
      </style>

</head>
<body>
    <h2>รายงานรุ่นหมู</h2>
    <table>
        <thead>
            <tr>
                <th>ชื่อฟาร์ม</th>
                <th>รหัสเล้า</th>
                <th>จำนวนเล้า</th>
                <th>จำนวนสุกร</th>
                <th>จำนวนคอก</th>
                <th>รหัสคอก</th>
                <th>รหัสรุ่น</th>
                <th>น้ำหนักรวม (กก.)</th>
                <th>จำนวนรวม (ตัว)</th>
                <th>ราคารวม (บาท)</th>
                <th>สถานะ</th>
                <th>หมายเหตุ</th>
                <th>วันที่เริ่มต้น</th>
                <th>วันที่สิ้นสุด</th>
            </tr>
        </thead>
        <tbody>
            @foreach($batches as $batch)
            <tr>
                <td>{{ $batch->farm->farm_name ?? '-' }}</td>
                <td>{{ $batch->barn->barn_code ?? '-' }}</td>
                <td>{{ $batch->farm->barn_capacity ?? '-' }}</td>
                <td>{{ $batch->barn->pig_capacity ?? '-' }}</td>
                <td>{{ $batch->barn->pen_capacity ?? '-' }}</td>
                <td>{{ $batch->pen->pen_code ?? '-' }}</td>
                <td>{{ $batch->batch_code ?? '-' }}</td>
                <td>{{ number_format($batch->total_pig_weight, 2) ?? '-' }}</td>
                <td>{{ $batch->total_pig_amount ?? '-' }}</td>
                <td>{{ number_format($batch->total_pig_price, 2) ?? '-' }}</td>
                <td>{{ $batch->status ?? '-' }}</td>
                <td>{{ $batch->note ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($batch->start_date)->format('d/m/Y H:i') ?? '-' }}</td>
                <td>{{ $batch->end_date ? \Carbon\Carbon::parse($batch->end_date)->format('d/m/Y H:i') : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
