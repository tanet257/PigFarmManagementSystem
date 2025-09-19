<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายงาน Storehouses</title>
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
        th, td {
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
    <h1>รายงานคลังสินค้า (Storehouses)</h1>

    <table>
        <thead>
            <tr>
                <th>วันที่</th>
                <th>ชื่อฟาร์ม</th>
                <th>รหัสรุ่น</th>
                <th>ประเภทรายการ</th>
                <th>รหัสรายการ</th>
                <th>ชื่อรายการ</th>
                <th>จำนวนสต็อก</th>
                <th>ราคาต่อหน่วย</th>
                <th>ค่าส่ง</th>
                <th>ราคารวม</th>
                <th>หน่วย</th>
                <th>สถานะ</th>
                <th>โน๊ต</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($storehouses as $storehouse)
                <tr>
                    <td>{{ $storehouse->latestCost->date ?? '-' }}</td>
                    <td>{{ $storehouse->farm->farm_name ?? '-' }}</td>
                    <td>{{ $storehouse->batch_code ?? '-' }}</td>
                    <td>{{ $storehouse->item_type }}</td>
                    <td>{{ $storehouse->item_code }}</td>
                    <td>{{ $storehouse->item_name }}</td>
                    <td>{{ number_format($storehouse->stock, 2) }}</td>
                    <td>{{ number_format($storehouse->price_per_unit ?? 0, 2) }}</td>
                    <td>{{ number_format($storehouse->latestCost->transport_cost ?? 0, 2) }}</td>
                    <td>{{ number_format($storehouse->latestCost->total_price ?? 0, 2) }}</td>
                    <td>{{ $storehouse->unit }}</td>
                    <td>{{ $storehouse->status }}</td>
                    <td>{{ $storehouse->note ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
