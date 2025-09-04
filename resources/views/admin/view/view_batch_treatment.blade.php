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

            <h1>All Batch Treatment</h1>

            <div>

                <table>

                    <tr>
                        <th>Barn Id</th>
                        <th>Pen Id</th>
                        <th>Batch Id</th>
                        <th>Farm Id</th>

                        <th>Medicine Name</th>
                        <th>Dosage</th>
                        <th>Unit</th>
                        <th>Status</th>
                        <th>Note</th>
                        <th>Date</th>
                    </tr>

                    @foreach($batch_treatments as $batch_treatment)

                    <tr>
                        <td>{{ $batch_treatment->barn_id}}</td>
                        <td>{{ $batch_treatment->pen_id}}</td>
                        <td>{{ $batch_treatment->batch_id}}</td>
                        <td>{{ $batch_treatment->farm_id}}</td>
                        <td>{{ $batch_treatment->medicine_name}}</td>
                        <td>{{ $batch_treatment->dosage}}</td>
                        <td>{{ $batch_treatment->unit}}</td>
                        <td>{{ $batch_treatment->status}}</td>
                        <td>{{ $batch_treatment->note}}</td>
                        <td>{{ $batch_treatment->barn_code}}</td>
                        <td>{{ $batch_treatment->pig_capacity}}</td>
                        <td>{{ $batch_treatment->pen_capacity}}</td>
                        <td>{{ $batch_treatment->note}}</td>
                        <td>{{ $batch_treatment->barn_code}}</td>
                        <td>{{ $batch_treatment->pig_capacity}}</td>
                        <td>{{ $batch_treatment->pen_capacity}}</td>
                        <td>{{ $batch_treatment->note}}</td>
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
