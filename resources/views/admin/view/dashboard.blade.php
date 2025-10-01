<!DOCTYPE html>
<html>

<head>
    @include('admin.css')
</head>

<body>

    @include('admin.header')

    @include('admin.sidebar')


    <div class="page-content">
        <div class="page-header">
            <div class="container-fluid">
                <link rel="stylesheet"
                    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
                <h3>จำนวนหมูทั้งหมด: {{ $totalPigs }}</h3>
                <h3>ต้นทุนรวม: {{ number_format($totalCosts, 2) }}</h3>
                <h3>รายได้รวม: {{ number_format($totalSales, 2) }}</h3>


            </div>
        </div>
    </div>
    <!-- JavaScript files-->
    @include('admin.js')
</body>

</html>
