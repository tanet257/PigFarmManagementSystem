<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', 'Admin Dashboard') - Pig Farm Management</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all,follow">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('admin.css')

    {{-- Additional page-specific styles --}}
    @stack('styles')
</head>

<body>
    @include('admin.header')
    @include('admin.sidebar')

    <div class="page-content">
        <div class="page-header">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>
    </div>

    @include('admin.js')

    <!-- Script ป้องกันการ back button หลัง logout -->
    <script src="{{ asset('admin/js/prevent-back-button.js') }}"></script>

    {{-- Additional page-specific scripts --}}
    @stack('scripts')
</body>

</html>
