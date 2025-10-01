<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายงานการจัดสรรหมู (Batch Pen Allocations)</title>
    <style>
        body {
            font-family: 'TH Sarabun New', sans-serif;
            font-size: 14pt;
            color: #000;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 6px;
            text-align: center;
            vertical-align: middle;
        }

        th {
            background-color: #ddd;
        }

        .inner-table th,
        .inner-table td {
            font-size: 12pt;
            padding: 4px;
        }

        .inner-table th {
            background-color: #eee;
        }
    </style>
</head>

<body>
    <h1>รายงานการจัดสรรหมู (Batch Pen Allocations)</h1>

    @foreach($barnSummaries as $barn)
        <h3>ฟาร์ม: {{ $barn['farm_name'] ?? '-' }} | เล้า: {{ $barn['barn_code'] }}</h3>
        <p>ความจุเล้า: {{ $barn['capacity'] }} | จำนวนที่จัดสรรแล้ว: {{ $barn['total_allocated'] }}</p>

        <table class="inner-table">
            <thead>
                <tr>
                    <th>คอก (Pen)</th>
                    <th>Capacity</th>
                    <th>Allocated</th>
                    <th>Batch</th>
                </tr>
            </thead>
            <tbody>
                @foreach($barn['pens'] as $pen)
                    <tr>
                        <td>{{ $pen['pen_code'] }}</td>
                        <td>{{ $pen['capacity'] }}</td>
                        <td>{{ $pen['allocated'] }}</td>
                        <td>{{ implode(', ', $pen['batches']->toArray()) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <hr>
    @endforeach

</body>

</html>
