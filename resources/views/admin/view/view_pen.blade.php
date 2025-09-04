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
                        <th>Barn Id</th>
                        <th>Pen Id</th>
                        
                        <th>Pig Capacity</th>
                        <th>Status</th>
                        <th>Note</th>
                        <th>Date</th>
                    </tr>

                    @foreach($pens as $pen)

                    <tr>
                        <td>{{ $pen->barn_id}}</td>
                        <td>{{ $pen->pen_id}}</td>
                        <td>{{ $pen->pig_capacity}}</td>
                        <td>{{ $pen->status}}</td>
                        <td>{{ $pen->note}}</td>
                        <td>{{ $pen->date}}</td>
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
