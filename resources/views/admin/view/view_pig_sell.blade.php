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
                        <th>Pig Death Id</th>

                        <th>Sell Type</th>
                        <th>Quantity</th>
                        <th>Total Weight</th>
                        <th>Price Per Kg</th>
                        <th>Total Price</th>
                        <th>Buyer</th>

                        <th>Note</th>
                        <th>Date</th>
                    </tr>

                    @foreach($pig_sells as $pig_sell)

                    <tr>
                        <td>{{ $pig_sell->farm_id}}</td>
                        <td>{{ $pig_sell->batch_id}}</td>
                        <td>{{ $pig_sell->pig_death_id}}</td>
                        <td>{{ $pig_sell->sell_type}}</td>
                        <td>{{ $pig_sell->quantity}}</td>
                        <td>{{ $pig_sell->total_weight}}</td>
                        <td>{{ $pig_sell->price_per_kg}}</td>
                        <td>{{ $pig_sell->total_price}}</td>
                        <td>{{ $pig_sell->buyer}}</td>
                        <td>{{ $pig_sell->date}}</td>
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
