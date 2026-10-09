<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rep | Dashboard</title>
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>

    <!-- header  -->
    @include('rep.components.header')
    <!-- /header  -->

    <!-- menu -->
    @include('rep.components.menu')
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
                            <h2 class="pageheader-title"> Rep Dashboard </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link">Dashboard</a></li>
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

                    <div class="row">
                        <!-- ============================================================== -->
                        <!-- sales  -->
                        <!-- ============================================================== -->
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="card border-3 border-top border-top-primary">
                                <div class="card-body">
                                    <h5 class="text-muted">Pending Orders Count</h5>
                                    <div class="metric-value d-inline-block">
                                        <h1 class="mb-1">
                                            {{$pending_orders_count}}
                                        </h1>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- ============================================================== -->
                        <!-- end sales  -->
                        <!-- ============================================================== -->
                        <!-- ============================================================== -->
                        <!-- new customer  -->
                        <!-- ============================================================== -->
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="card border-3 border-top border-top-primary">
                                <div class="card-body">
                                    <h5 class="text-muted">Processing Orders Count</h5>
                                    <div class="metric-value d-inline-block">
                                        <h1 class="mb-1">
                                            {{$processing_orders_count}}
                                        </h1>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- ============================================================== -->
                        <!-- end new customer  -->
                        <!-- ============================================================== -->
                        <!-- ============================================================== -->
                        <!-- visitor  -->
                        <!-- ============================================================== -->
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="card border-3 border-top border-top-primary">
                                <div class="card-body">
                                    <h5 class="text-muted">Total Completed Orders Count</h5>
                                    <div class="metric-value d-inline-block">
                                        <h1 class="mb-1">
                                            {{$complete_orders_count}}
                                        </h1>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- ============================================================== -->
                        <!-- end visitor  -->
                        <!-- ============================================================== -->
                        <!-- ============================================================== -->
                        <!-- total orders  -->
                        <!-- ============================================================== -->
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="card border-3 border-top border-top-primary">
                                <div class="card-body">
                                    <h5 class="text-muted">Under Review Orders Count</h5>
                                    <div class="metric-value d-inline-block">
                                        <h1 class="mb-1">
                                            {{$under_review_orders_count}}
                                        </h1>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- ============================================================== -->
                        <!-- end total orders  -->
                        <!-- ============================================================== -->
                    </div>

                    <div class="row">
                        <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="text-muted">Today estimated Total Revenue</h5>
                                    <div class="metric-value d-inline-block">
                                        <h1 class="mb-1">රු.
                                        {{number_format($today_total_revenue,2)}}
                                        </h1>
                                    </div>
                                    <div class="metric-label d-inline-block float-right font-weight-bold">

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="text-muted">This week estimated Total Revenue <small class="alert-warning p-1"> ({{date('Y-m-d', strtotime($startOfThisWeek))}} to {{date('Y-m-d', strtotime($endOfThisWeek))}})</small></h5>
                                    <div class="metric-value d-inline-block">
                                        <h1 class="mb-1">රු.
                                        {{number_format($thisWeek_total_revenue,2)}}
                                        </h1>
                                    </div>
                                    <div class="metric-label d-inline-block float-right text-success font-weight-bold">

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="text-muted">This Month estimated Total Revenue <small class="alert-warning p-1"> ({{date('Y-m-d', strtotime($startOfThisMonth))}} to {{date('Y-m-d', strtotime($endOfThisMonth))}})</small></h5>
                                    <div class="metric-value d-inline-block">
                                        <h1 class="mb-1">රු.
                                        {{number_format($lastMonth_total_revenue,2)}}
                                        </h1>
                                    </div>
                                    <div class="metric-label d-inline-block float-right text-primary font-weight-bold">

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row d-none">
                        <!-- ============================================================== -->

                        <!-- ============================================================== -->

                        <!-- recent orders  -->
                        <!-- ============================================================== -->
                        <div class="col-xl-6 col-lg-12 col-md-6 col-sm-12 col-12">
                            <div class="card">
                                <h5 class="card-header">Last 7 days morning estimated value</h5>
                                <div class="card-body">
                                    <canvas id="chartjs_bar_m"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6 col-lg-12 col-md-6 col-sm-12 col-12">
                            <div class="card">
                                <h5 class="card-header">Last 7 days evining estimated value</h5>
                                <div class="card-body">
                                    <canvas id="chartjs_bar_e"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="card">
                                <h2 class="card-header">My Last 30 days Revenues</h2>
                                <div class="card-body">
                                    <canvas id="barChart2"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="card">
                                <h2 class="card-header">My Last 12 Months Revenues</h2>
                                <div class="card-body">
                                    <canvas id="barChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="card">
                                <h2 class="card-header">My Shops Revenues</h2>
                                <div class="card-body">
                                <canvas id="revenuePieChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                            <div class="card">
                                <h5 class="card-header">Resent Orders</h5>
                                <div class="card-body">
                                    <div class="table-responsive ">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr class="border-0">
                                                    <th class="border-0">#</th>
                                                    <th class="border-0">Order Id</th>
                                                    <th class="border-0">Shop</th>
                                                    <th class="border-0">Delivery time</th>
                                                    <th class="border-0">Order create time</th>
                                                    <th class="border-0">Estimate&nbsp;full&nbsp;amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($latest_orders as $latest_order)
                                                <tr>
                                                    <td>{{$loop->iteration}}</td>
                                                    <td>{{$latest_order->unique_id}}</td>
                                                    <td>{{$latest_order->shop_name}}</td>
                                                    <td>{{$latest_order->time_period}}</td>
                                                    <td>{{$latest_order->order_date}}</td>
                                                    <td>රු. {{$latest_order->total_price}}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                            <div class="card">
                                <h5 class="card-header">Top 10 Orders</h5>
                                <div class="card-body">
                                    <div class="table-responsive ">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr class="border-0">
                                                    <th class="border-0">#</th>
                                                    <th class="border-0">Order Id</th>
                                                    <th class="border-0">Shop</th>
                                                    <th class="border-0">Delivery time</th>
                                                    <th class="border-0">Order create time</th>
                                                    <th class="border-0">Estimate&nbsp;full&nbsp;amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($top_orders as $top_order)
                                                <tr>
                                                    <td>{{$loop->iteration}}</td>
                                                    <td>{{$top_order->unique_id}}</td>
                                                    <td>{{$top_order->shop_name}}</td>
                                                    <td>{{$top_order->time_period}}</td>
                                                    <td>{{$top_order->order_date}}</td>
                                                    <td>රු. {{$top_order->total_price}}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
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
        // Data from the backend
        const months = {!! json_encode($last12monthsRevenue -> pluck('month')) !!};
        const amounts = {!! json_encode($last12monthsRevenue -> pluck('amount')) !!};

        // Bar Chart
        const barCtx = document.getElementById('barChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'Revenue (Rs)',
                    data: amounts,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Data from the backend
        const dates = {!! json_encode($last30DaysRevenue->pluck('date')) !!};
        const amounts30 = {!! json_encode($last30DaysRevenue->pluck('amount')) !!};

        // Bar Chart
        const barCtx30 = document.getElementById('barChart2').getContext('2d');
        new Chart(barCtx30, {
            type: 'bar',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Revenue (Rs)',
                    data: amounts30,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        const revenues = @json($revenues);

        const labels = revenues.map(shop => shop.shop);
        const data = revenues.map(shop => shop.revenue);

        const ctx = document.getElementById('revenuePieChart').getContext('2d');
        const revenuePieChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue',
                    data: data,
                    backgroundColor: [
                       'rgba(255, 99, 132, 0.6)',    'rgba(54, 162, 235, 0.6)',    'rgba(255, 206, 86, 0.6)',     'rgba(75, 192, 192, 0.6)',    'rgba(153, 102, 255, 0.6)',
    'rgba(255, 159, 64, 0.6)',    'rgba(255, 99, 71, 0.6)',     'rgba(0, 128, 128, 0.6)',      'rgba(128, 0, 128, 0.6)',      'rgba(0, 255, 127, 0.6)',
    'rgba(70, 130, 180, 0.6)',    'rgba(210, 105, 30, 0.6)',    'rgba(255, 140, 0, 0.6)',      'rgba(0, 139, 139, 0.6)',      'rgba(139, 0, 139, 0.6)',
    'rgba(0, 255, 255, 0.6)',     'rgba(255, 0, 255, 0.6)',     'rgba(255, 215, 0, 0.6)',      'rgba(218, 112, 214, 0.6)',    'rgba(50, 205, 50, 0.6)',
    'rgba(72, 209, 204, 0.6)',    'rgba(199, 21, 133, 0.6)',    'rgba(25, 25, 112, 0.6)',      'rgba(245, 222, 179, 0.6)',    'rgba(255, 228, 196, 0.6)',
    'rgba(139, 69, 19, 0.6)',     'rgba(0, 128, 0, 0.6)',       'rgba(255, 105, 180, 0.6)',    'rgba(128, 128, 0, 0.6)',      'rgba(173, 216, 230, 0.6)',
    'rgba(240, 128, 128, 0.6)',   'rgba(224, 255, 255, 0.6)',   'rgba(250, 235, 215, 0.6)',    'rgba(127, 255, 212, 0.6)',    'rgba(255, 182, 193, 0.6)',
    'rgba(255, 250, 205, 0.6)',   'rgba(240, 248, 255, 0.6)',   'rgba(220, 20, 60, 0.6)',      'rgba(0, 255, 127, 0.6)',      'rgba(255, 69, 0, 0.6)',
    'rgba(0, 191, 255, 0.6)',     'rgba(255, 20, 147, 0.6)',    'rgba(123, 104, 238, 0.6)',    'rgba(106, 90, 205, 0.6)',     'rgba(112, 128, 144, 0.6)',
    'rgba(176, 196, 222, 0.6)',   'rgba(255, 127, 80, 0.6)',    'rgba(147, 112, 219, 0.6)',    'rgba(60, 179, 113, 0.6)',     'rgba(46, 139, 87, 0.6)',
    'rgba(102, 205, 170, 0.6)',   'rgba(143, 188, 143, 0.6)',   'rgba(32, 178, 170, 0.6)',     'rgba(0, 206, 209, 0.6)',      'rgba(95, 158, 160, 0.6)',
    'rgba(72, 61, 139, 0.6)',     'rgba(139, 0, 0, 0.6)',       'rgba(233, 150, 122, 0.6)',    'rgba(255, 160, 122, 0.6)',    'rgba(255, 192, 203, 0.6)',
    'rgba(221, 160, 221, 0.6)',   'rgba(176, 224, 230, 0.6)',   'rgba(152, 251, 152, 0.6)',    'rgba(175, 238, 238, 0.6)',    'rgba(219, 112, 147, 0.6)',
    'rgba(238, 232, 170, 0.6)',   'rgba(250, 250, 210, 0.6)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Shop Revenues'
                    }
                }
            }
        });
    </script>
</body>

</html>