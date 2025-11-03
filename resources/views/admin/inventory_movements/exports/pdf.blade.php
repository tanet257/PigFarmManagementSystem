<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายงาน Inventory Movement</title>
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
    <h1>รายงานคลังสินค้า (Inventory Movement)</h1>

    <table>
        <thead>
            <tr>
                <th>วันที่</th>
                <th>ชื่อฟาร์ม</th>
                <th>รหัสรุ่น</th>
                <th>ประเภทสินค้า</th>
                <th>รหัสสินค้า</th>
                <th>ชื่อสินค้า</th>
                <th>ประเภทการเปลี่ยนแปลง</th>
                <th>จำนวน</th>
                <th>หน่วย</th>
                <th>โน้ต</th>
                <th>บันทึกเมื่อ</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($movements as $movement)
                <tr>
                    <td>{{ $movement->date }}</td>
                    <td>{{ $movement->storehouse->farm->farm_name ?? '-' }}</td>
                    <td>{{ $movement->batch->batch_code ?? '-' }}</td>
                    <td>{{ $movement->storehouse->item_type ?? '- ' }}</td>
                    <td>{{ $movement->storehouse->item_code ?? '-' }}</td>
                    <td>{{ $movement->storehouse->item_name ?? '-' }}</td>
                    <td>{{ $movement->change_type === 'in' ? 'เข้า' : ($movement->change_type === 'out' ? 'ออก' : '-') }}</td>
                    <td>{{ number_format($movement->quantity, 2) }}</td>
                    <td>{{ $movement->quantity_unit ?? $movement->storehouse->unit ?? '-' }}</td>
                    <td>{{ $movement->note ?? '-' }}</td>
                    <td>{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
