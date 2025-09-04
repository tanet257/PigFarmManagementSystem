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
                        <th>Pen Id</th>

                        <th>Amount</th>
                        <th>Cause</th>
                        <th>Note</th>
                        <th>Date</th>
                    </tr>

                    @foreach($pig_deaths as $pig_death)

                    <tr>
                        <td>{{ $pig_death->barn_code}}</td>
                        <td>{{ $pig_death->batch_id}}</td>
                        <td>{{ $pig_death->pen_id}}</td>
                        <td>{{ $pig_death->amount}}</td>
                        <td>{{ $pig_death->cause}}</td>
                        <td>{{ $pig_death->note}}</td>
                        <td>{{ $pig_death->date}}</td>
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
