<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop</title>
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
    @include('shop.components.header')
    <!-- /header  -->

    <!-- menu -->
    @include('shop.components.menu')
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
                            <h2 class="pageheader-title"> {{Auth::user()->name}} Dashboard </h2>
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
                                    <h5 class="text-muted">Today my estimated Total Revenue</h5>
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
                                    <h5 class="text-muted">This week estimated Total Revenue <small> ({{date('Y-m-d', strtotime($startOfThisWeek))}} to {{date('Y-m-d', strtotime($endOfThisWeek))}})</small></h5>
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
                                    <h5 class="text-muted">This Month estimated Total Revenue <small> ({{date('Y-m-d', strtotime($startOfThisMonth))}} to {{date('Y-m-d', strtotime($endOfThisMonth))}})</small></h5>
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

                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="card">
                                <h2 class="card-header">Last 12 Months Revenue</h2>
                                <div class="card-body">
                                    <canvas id="barChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="card">
                                <h2 class="card-header">Last 30 Days Revenue</h2>
                                <div class="card-body">
                                    <canvas id="past30DaysRevenueChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="card">
                                <h2 class="card-header">My Best Selling Product Categories</h2>
                                <div class="card-body">
                                    <canvas id="bestSellingProductCategories"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="card">
                                <h2 class="card-header">My Best Selling Products</h2>
                                <div class="card-body">
                                    <canvas id="bestSellingProducts"></canvas>
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
                                <h5 class="card-header">Last 30 Days Top 10 Orders</h5>
                                <div class="card-body">
                                    <div class="table-responsive ">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr class="border-0">
                                                    <th class="border-0">#</th>
                                                    <th class="border-0">Order Id</th>
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
        const months = {!! json_encode($last12montsrevenue -> pluck('month')) !!};
        const amounts = {!!json_encode($last12montsrevenue -> pluck('amount')) !!};

        // Bar Chart
        const barCtx = document.getElementById('barChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'Orders Amount (Rs)',
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
        const barCtx30 = document.getElementById('past30DaysRevenueChart').getContext('2d');
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

        // Extract data from PHP to JavaScript
        const shops = {!!json_encode($bestSellingCategories -> pluck('category')) !!};
        const totalSales = {!!json_encode($bestSellingCategories -> pluck('total_quantity_sold')) !!};

        // Create the pie chart
        const ctx = document.getElementById('bestSellingProductCategories').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: shops,
                datasets: [{
                    label: 'Total Sales',
                    data: totalSales,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                        '#FF9F40', '#E7E9ED', '#8C9EFF', '#00CC99', '#FF99CC'
                    ],
                    hoverOffset: 4
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
                        text: 'Best Selling Products Categories'
                    },
                }
            }
        });
        
        // Extract data from PHP to JavaScript
        const name_sinhala = {!!json_encode($topSellingProducts -> pluck('name_sinhala')) !!};
        const totalSalesP = {!!json_encode($topSellingProducts -> pluck('total_quantity_sold')) !!};

        // Create the pie chart
        const ctxP = document.getElementById('bestSellingProducts').getContext('2d');
        new Chart(ctxP, {
            type: 'pie',
            data: {
                labels: name_sinhala,
                datasets: [{
                    label: 'Total Sales',
                    data: totalSalesP,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                        '#FF9F40', '#E7E9ED', '#8C9EFF', '#00CC99', '#FF99CC'
                    ],
                    hoverOffset: 4
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
                        text: 'Best Selling Products Categories'
                    },
                }
            }
        });
    </script>
</body>

</html>