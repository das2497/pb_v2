<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Peoples Bakers')</title>
    <link rel="icon" href="{{ asset('assets/images/logo.png') }}">

    <!-- Bootstrap CSS -->
    @vite(['resources/css/vendor/base-1.css'])
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/circular-std/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/fontawesome/css/fontawesome-all.css') }}">
    @vite(['resources/css/vendor/base-2.css'])
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/material-design-iconic-font/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/flag-icon-css/flag-icon.min.css') }}">
    @stack('styles')
</head>

<body class="table-dark">

    @yield('content')

    <!-- jQuery 3.3.1 -->
    <script src="{{ \Illuminate\Support\Facades\Vite::asset('resources/vendor-src/jquery-3.3.1.min.js') }}"></script>
    <!-- Bootstrap bundle JS -->
    <script src="{{ \Illuminate\Support\Facades\Vite::asset('resources/vendor-src/bootstrap/bootstrap.bundle.js') }}"></script>
    <!-- SlimScroll JS -->
    <script src="{{ \Illuminate\Support\Facades\Vite::asset('resources/vendor-src/jquery.slimscroll.js') }}"></script>
    <!-- Main JS -->
    <script src="{{ asset('assets/libs/js/main-js.js') }}"></script>
    <!-- Chartist JS -->
    <script src="{{ \Illuminate\Support\Facades\Vite::asset('resources/vendor-src/charts/chartist.min.js') }}"></script>
    <!-- Sparkline JS -->
    <script src="{{ \Illuminate\Support\Facades\Vite::asset('resources/vendor-src/jquery.sparkline.js') }}"></script>
    <!-- Morris JS -->
    <script src="{{ \Illuminate\Support\Facades\Vite::asset('resources/vendor-src/charts/raphael.min.js') }}"></script>
    <script src="{{ \Illuminate\Support\Facades\Vite::asset('resources/vendor-src/charts/morris.js') }}"></script>
    <!-- C3 Charts JS -->
    <script src="{{ \Illuminate\Support\Facades\Vite::asset('resources/vendor-src/charts/c3.min.js') }}"></script>
    <script src="{{ \Illuminate\Support\Facades\Vite::asset('resources/vendor-src/charts/d3-5.4.0.min.js') }}"></script>
    <script src="{{ \Illuminate\Support\Facades\Vite::asset('resources/vendor-src/charts/C3chartjs.js') }}"></script>
    <!-- Dashboard E-commerce JS -->
    <script src="{{ asset('assets/libs/js/dashboard-ecommerce.js') }}"></script>
    <!-- Chart Bundle JS -->
    <script src="{{ \Illuminate\Support\Facades\Vite::asset('resources/vendor-src/charts/Chart.bundle.js') }}"></script>
    <script src="{{ \Illuminate\Support\Facades\Vite::asset('resources/vendor-src/charts/chartjs-demo.js') }}"></script>
    @stack('scripts')

</body>

</html>
