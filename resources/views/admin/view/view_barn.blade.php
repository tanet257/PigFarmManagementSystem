@extends('layouts.admin')

@section('title', 'All Barn')

@section('content')
    <div class="container-fluid">
        <h1>All Barn</h1>

        <div>
            <table>
                <tr>
                    <th>Barn Code</th>
                    <th>Pig Capacity</th>
                    <th>Pen Capacity</th>
                    <th>Note</th>
                </tr>

                @foreach ($barns as $barn)
                    <tr>
                        <td>{{ $barn->barn_code }}</td>
                        <td>{{ $barn->pig_capacity }}</td>
                        <td>{{ $barn->pen_capacity }}</td>
                        <td>{{ $barn->note }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection
