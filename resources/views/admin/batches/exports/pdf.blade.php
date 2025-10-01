<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายงาน batch</title>
    <style>
        body {
            font-family: 'TH Sarabun New', sans-serif;
            font-size: 14pt;
            background: #fff;
            color: #000;
        }

        h1 {
            text-align: center;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 6px;
            text-align: center;
        }

        th {
            background: #ddd;
        }
    </style>
</head>

<body>
    <h1>รายงานรุ่น (Batch)</h1>

    <table>
        <thead>
            <tr>
                <th>ชื่อฟาร์ม</th>
                <th>รหัสรุ่น</th>
                <th>จำนวนเล้า</th>
                <th>จำนวนสุกร</th>
                <th>จำนวนคอก</th>
                <th>น้ำหนักรวม</th>
                <th>จำนวนรวม</th>
                <th>ราคารวม</th>
                <th>สถานะ</th>
                <th>หมายเหตุ</th>
                <th>วันที่เริ่มต้น</th>
                <th>วันที่สิ้นสุด</th>
                <th>จัดการ</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($batches as $batch)
                <tr>
                    <td>{{ $batch->farm->farm_name ?? '-' }}</td>
                    <td>{{ $batch->batch_code }}</td>
                    <td>{{ $batch->farm->barns->count() ?? '-' }}</td>
                    <td>{{ $batch->farm->barns->first()->pig_capacity ?? '-' }}</td>
                    <td>{{ $batch->farm->barns->first()->pens->count() ?? '-' }}</td>
                    <td>{{ number_format($batch->total_pig_weight ?? 0, 2) }}</td>
                    <td>{{ number_format($batch->total_pig_amount ?? 0) }}</td>
                    <td>{{ number_format($batch->total_pig_price ?? 0, 2) }}</td>
                    <td>{{ $batch->status ?? '-' }}</td>
                    <td>{{ $batch->note ?? '-' }}</td>
                    <td>{{ $batch->start_date ?? '-' }}</td>
                    <td>{{ $batch->end_date ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
