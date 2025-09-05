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

            <h1>All Barn</h1>

            <div>

                <table>

                    <tr>
                        <th>Farm Id</th>
                        <th>Batch Id</th>

                        <th>Feed Type</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                        <th>Price Per Unit</th>
                        <th>Total</th>
                        <th>Note</th>
                        <th>Date</th>
                    </tr>

                    @foreach($feedings as $feeding)

                    <tr>
                        <td>{{ $feeding->farm_id}}</td>
                        <td>{{ $feeding->batch_id}}</td>
                        <td>{{ $feeding->feed_type}}</td>
                        <td>{{ $feeding->quantity}}</td>
                        <td>{{ $feeding->unit}}</td>
                        <td>{{ $feeding->price_per_unit}}</td>
                        <td>{{ $feeding->total}}</td>
                        <td>{{ $feeding->date}}</td>
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
