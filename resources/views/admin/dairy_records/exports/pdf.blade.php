<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Dairy Records Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: center; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h2 style="text-align:center;">บันทึกประจำวัน (Dairy Records)</h2>
    <table>
        <thead>
            <tr>
                <th>ฟาร์ม</th>
                <th>รุ่น</th>
                <th>เล้า/คอก</th>
                <th>ประเภท</th>
                <th>รายละเอียด</th>
                <th>จำนวน</th>
                <th>วันที่</th>
                <th>โน๊ต</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dairyRecords as $record)
                {{-- Feed --}}
                @foreach($record->dairy_storehouse_uses as $use)
                    <tr>
                        <td>{{ $record->batch?->farm?->farm_name ?? '-' }}</td>
                        <td>{{ $record->batch?->batch_code ?? '-' }}</td>
                        <td>{{ $use->barn?->barn_code ?? '-' }}</td>
                        <td>อาหาร</td>
                        <td>รหัส: {{ $use->storehouse?->item_code }}, หน่วย: {{ $use->storehouse?->unit }}</td>
                        <td>{{ $use->quantity }}</td>
                        <td>{{ $record->updated_at }}</td>
                        <td>{{ $record->note ?? '-' }}</td>
                    </tr>
                @endforeach

                {{-- Treatment --}}
                @foreach($record->batch_treatments as $bt)
                    <tr>
                        <td>{{ $record->batch?->farm?->farm_name ?? '-' }}</td>
                        <td>{{ $record->batch?->batch_code ?? '-' }}</td>
                        <td>{{ $record->barn?->barn_code ?? '-' }}/{{ $bt->pen?->pen_code ?? '-' }}</td>
                        <td>การรักษา</td>
                        <td>ยา: {{ $bt->medicine_code }}, หน่วย: {{ $bt->unit }}, สถานะ: {{ $bt->status }}</td>
                        <td>{{ $bt->quantity }}</td>
                        <td>{{ $record->updated_at }}</td>
                        <td>{{ $record->note ?? '-' }}</td>
                    </tr>
                @endforeach

                {{-- Death --}}
                @foreach($record->pig_deaths as $pd)
                    <tr>
                        <td>{{ $record->batch?->farm?->farm_name ?? '-' }}</td>
                        <td>{{ $record->batch?->batch_code ?? '-' }}</td>
                        <td>{{ $record->barn?->barn_code ?? '-' }}/{{ $pd->pen?->pen_code ?? '-' }}</td>
                        <td>หมูตาย</td>
                        <td>คอก: {{ $pd->pen?->pen_code ?? '-' }}</td>
                        <td>{{ $pd->quantity }}</td>
                        <td>{{ $record->updated_at }}</td>
                        <td>{{ $record->note ?? '-' }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</body>
</html>
