<!DOCTYPE html>
<html lang="ar">

    <head>
        <title>لوحة التحكم - @yield('title')</title>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Portal - Bootstrap 5 Admin Dashboard Template For Developers">
        <meta name="author" content="Xiaoying Riley at 3rd Wave Media">
        <link rel="shortcut icon" href="{{ asset('favicon.png') }}" type="image/x-icon">
        <link rel="icon" href="{{ asset('favicon.png') }}" type="image/x-icon">
        <script defer src="{{ asset('assets/plugins/fontawesome/js/all.min.js') }}"></script>
        <link rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/bootstrap-multiselect@1.1.1/dist/css/bootstrap-multiselect.css" />
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-multiselect@1.1.1/dist/js/bootstrap-multiselect.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
        <link id="theme-style" rel="stylesheet" href="{{ asset('assets/css/portal.css') }}">
    </head>

    <body class="app">
        @include('dashboard.layouts.header')
        <div class="app-wrapper">
            <div class="app-content pt-3 p-md-3 p-lg-4">
                <div class="container-xl">
                    @yield('content')
                </div>
            </div>
        </div>
        <script src="{{ asset('assets/plugins/popper.min.js') }}"></script>
        <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('assets/plugins/chart.js/chart.min.js') }}"></script>
        <script src="{{ asset('assets/js/index-charts.js') }}"></script>
        <script src="{{ asset('assets/js/app.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
            integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8=" crossorigin="anonymous"></script>
        {{-- <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script> --}}

        @yield('script')
    </body>

</html>
