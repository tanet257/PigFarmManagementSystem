<!DOCTYPE html>
<html>
  <head>
    @include('admin.css')

    <style>

        table
        {
            border:1px solid skyblue;
            margin: auto;
            width: 800px;
        }

        th
        {
            background-color: skyblue;
            color: white;
            padding: 10px;
            margin: 10px;
        }

        td
        {
            color: white;
            padding: 10px;
        }

    </style>
  </head>
  <body>

    @include('admin.header')

    @include('admin.sidebar')


    <div class="page-content">
        <div class="page-header">
            <div class="container-fluid">

            <h1>All Batch</h1>

            <div>

                <table>

                    <tr>
                        <th>Barn Id</th>
                        <th>Pen Id</th>
                        <th>Farm Id</th>

                        <th>Batch Code</th>
                        <th>Total Pig Weight</th>
                        <th>Total Pig Amount</th>
                        <th>Total Pig Price</th>
                        <th>Status</th>
                        <th>Note</th>

                        <th>Start Date</th>
                        <th>End Date</th>
                    </tr>

                    @foreach($batches as $batch)

                    <tr>
                        <td>{{ $batch->barn_id}}</td>
                        <td>{{ $batch->pen_id}}</td>
                        <td>{{ $batch->farm_id}}</td>
                        <td>{{ $batch->batch_code}}</td>
                        <td>{{ $batch->total_pig_weight}}</td>
                        <td>{{ $batch->total_pig_amount}}</td>
                        <td>{{ $batch->total_pig_price}}</td>
                        <td>{{ $batch->status}}</td>
                        <td>{{ $batch->note}}</td>
                        <td>{{ $batch->start_date}}</td>
                        <td>{{ $batch->end_date}}</td>

                    </tr>
                    @endforeach
                </table>
            </div>
      </div>
    </div>
    <!-- JavaScript files-->
   @include('admin.js')
  </body>
</html>
