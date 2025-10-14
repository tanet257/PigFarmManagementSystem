<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานการขายสุกร</title>
    <style>
        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: normal;
            src: url("{{ storage_path('fonts/THSarabunNew.ttf') }}") format('truetype');
        }

        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: bold;
            src: url("{{ storage_path('fonts/THSarabunNew Bold.ttf') }}") format('truetype');
        }

        * {
            font-family: 'THSarabunNew', sans-serif;
        }

        body {
            font-size: 16px;
            margin: 20px;
        }

        h1 {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .header-info {
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table,
        th,
        td {
            border: 1px solid #000;
        }

        th {
            background-color: #f0f0f0;
            padding: 8px;
            text-align: center;
            font-weight: bold;
        }

        td {
            padding: 6px;
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .summary {
            margin-top: 20px;
            float: right;
            width: 40%;
        }

        .summary table {
            width: 100%;
        }

        .summary td {
            padding: 5px;
        }

        .footer {
            margin-top: 40px;
            clear: both;
            text-align: center;
            font-size: 12px;
        }

        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .badge-secondary {
            background-color: #e2e3e5;
            color: #383d41;
        }
    </style>
</head>

<body>
    <h1>รายงานการขายสุกร</h1>

    <div class="header-info">
        <strong>ระบบจัดการฟาร์มสุกร (Pig Farm Management System)</strong><br>
        พิมพ์วันที่: {{ now()->format('d/m/Y H:i:s') }}
        @if (isset($filters))
            <br>
            @if (!empty($filters['farm']))
                ฟาร์ม: {{ $filters['farm'] }}
            @endif
            @if (!empty($filters['batch']))
                | รุ่น: {{ $filters['batch'] }}
            @endif
            @if (!empty($filters['date_range']))
                | ช่วงเวลา: {{ $filters['date_range'] }}
            @endif
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">ลำดับ</th>
                <th style="width: 10%;">เลขที่ขาย</th>
                <th style="width: 10%;">วันที่ขาย</th>
                <th style="width: 12%;">ฟาร์ม</th>
                <th style="width: 10%;">รุ่น</th>
                <th style="width: 8%;">จำนวน (ตัว)</th>
                <th style="width: 10%;">น้ำหนักรวม (กก.)</th>
                <th style="width: 10%;">ราคา/กก.</th>
                <th style="width: 12%;">ราคารวม</th>
                <th style="width: 13%;">สถานะ</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalQuantity = 0;
                $totalWeight = 0;
                $totalPrice = 0;
            @endphp
            @forelse($pigSales as $index => $sale)
                @php
                    $totalQuantity += $sale->quantity ?? 0;
                    $totalWeight += $sale->total_weight ?? 0;
                    $totalPrice += $sale->net_total ?? ($sale->total_price ?? 0);
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $sale->sale_number ?? '-' }}</td>
                    <td>{{ $sale->sell_date ? $sale->sell_date->format('d/m/Y') : '-' }}</td>
                    <td class="text-left">{{ $sale->farm->farm_name ?? '-' }}</td>
                    <td>{{ $sale->batch->batch_code ?? '-' }}</td>
                    <td>{{ number_format($sale->quantity ?? 0) }}</td>
                    <td class="text-right">{{ number_format($sale->total_weight ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($sale->price_per_kg ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($sale->net_total ?? ($sale->total_price ?? 0), 2) }}</td>
                    <td>
                        @if ($sale->status === 'approved')
                            <span class="badge badge-success">อนุมัติแล้ว</span>
                        @elseif($sale->status === 'rejected')
                            <span class="badge badge-danger">ปฏิเสธ</span>
                        @else
                            <span class="badge badge-warning">รออนุมัติ</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center; color: #999;">ไม่มีข้อมูลการขาย</td>
                </tr>
            @endforelse
        </tbody>
        @if ($pigSales->count() > 0)
            <tfoot>
                <tr style="background-color: #f8f9fa; font-weight: bold;">
                    <td colspan="5" class="text-right">รวมทั้งหมด:</td>
                    <td>{{ number_format($totalQuantity) }}</td>
                    <td class="text-right">{{ number_format($totalWeight, 2) }}</td>
                    <td colspan="1"></td>
                    <td class="text-right">{{ number_format($totalPrice, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="summary">
        <table>
            <tr>
                <td class="text-left"><strong>จำนวนรายการ:</strong></td>
                <td class="text-right">{{ $pigSales->count() }} รายการ</td>
            </tr>
            <tr>
                <td class="text-left"><strong>จำนวนสุกรทั้งหมด:</strong></td>
                <td class="text-right">{{ number_format($totalQuantity) }} ตัว</td>
            </tr>
            <tr>
                <td class="text-left"><strong>น้ำหนักรวม:</strong></td>
                <td class="text-right">{{ number_format($totalWeight, 2) }} กก.</td>
            </tr>
            <tr>
                <td class="text-left"><strong>ราคารวมทั้งหมด:</strong></td>
                <td class="text-right">{{ number_format($totalPrice, 2) }} บาท</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>*** สิ้นสุดรายงาน ***</p>
        <p style="font-size: 10px;">ระบบจัดการฟาร์มสุกร - สร้างโดย Pig Farm Management System</p>
    </div>
</body>

</html>
