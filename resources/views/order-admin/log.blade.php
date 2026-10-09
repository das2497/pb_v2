<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Admin | Logs</title>
    <link rel="icon" href="{{ asset('assets/images/logo.png') }}">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/circular-std/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/fontawesome/css/fontawesome-all.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/charts/chartist-bundle/chartist.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/charts/morris-bundle/morris.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/material-design-iconic-font/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/charts/c3charts/c3.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/flag-icon-css/flag-icon.min.css') }}">

</head>

<body>

    <!-- header  -->
    @include('order-admin.components.header')
    <!-- /header  -->

    <!-- menu -->
    @include('order-admin.components.menu')
    <!-- /menu -->

    <!-- content -->
    <div class="dashboard-wrapper">
        <div class="dashboard-ecommerce">
            <div class="container-fluid dashboard-content ">
                <!-- ============================================================== -->
                <!-- pageheader  -->
                <!-- ============================================================== -->
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="page-header">
                            <h2 class="pageheader-title"> Ordering Admin Logs </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link">Logs</a></li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- end pageheader  -->
                <!-- ============================================================== -->
                <div class="ecommerce-widget">

                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    @endif
                    @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <form action="" method="GET" class="row mb-2">
                                        <div class="col-2">
                                            <input type="date" class="form-control" name="date" id="">
                                        </div>
                                        <div class="col-4">
                                            <input type="text" class="form-control" placeholder="Search..." name="search" id="">
                                        </div>
                                        <div class="col-2">
                                            <button class="btn btn-primary" id="search">Search</button>
                                        </div>
                                    </form>
                                    <div class="row">
                                        <div class="col-12">
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <th style="width: 20%;">DateTime</th>
                                                        <th style="width: 20%;">Type</th>
                                                        <th style="width: 20%;">User</th>
                                                        <th style="width: 40%;">Messege</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($logs as $log)
                                                    <tr>
                                                        <td>[{{$log->created_at}}]</td>
                                                        <td>{{$log->type}}</td>
                                                        <td>{{$log->user}}</td>
                                                        <td>
                                                            <p class="overflow-x-scroll">{{$log->message}}</p>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div>
                                        {{$logs->links()}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ============================================================== -->
            <!-- end footer -->
            <!-- ============================================================== -->
        </div>

        <!-- jQuery 3.3.1 -->
        <script src="{{ asset('assets/vendor/jquery/jquery-3.3.1.min.js') }}"></script>
        <!-- Bootstrap bundle JS -->
        <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.js') }}"></script>
        <!-- SlimScroll JS -->
        <script src="{{ asset('assets/vendor/slimscroll/jquery.slimscroll.js') }}"></script>
        <!-- Main JS -->
        <script src="{{ asset('assets/libs/js/main-js.js') }}"></script>
        <!-- Chartist JS -->
        <script src="{{ asset('assets/vendor/charts/chartist-bundle/chartist.min.js') }}"></script>
        <!-- Sparkline JS -->
        <script src="{{ asset('assets/vendor/charts/sparkline/jquery.sparkline.js') }}"></script>
        <!-- Morris JS -->
        <script src="{{ asset('assets/vendor/charts/morris-bundle/raphael.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/charts/morris-bundle/morris.js') }}"></script>
        <!-- C3 Charts JS -->
        <script src="{{ asset('assets/vendor/charts/c3charts/c3.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/charts/c3charts/d3-5.4.0.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/charts/c3charts/C3chartjs.js') }}"></script>
        <!-- Dashboard E-commerce JS -->
        <script src="{{ asset('assets/libs/js/dashboard-ecommerce.js') }}"></script>
        <!-- Chart Bundle JS -->
        <script src="{{ asset('assets/vendor/charts/charts-bundle/Chart.bundle.js') }}"></script>
        <script src="{{ asset('assets/vendor/charts/charts-bundle/chartjs.js') }}"></script>

        <script>
            document.getElementById('refresh').addEventListener('click', function() {
                // Disable the button
                this.disabled = true;

                // You can also perform other actions here
                // For example, refreshing the content or triggering an API call
            });
        </script>
</body>

</html>