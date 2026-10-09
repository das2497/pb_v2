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
    <style>
        .div {
            height: 400px;
            /* max-width: 100vw; */
            overflow-x: auto;
            overflow-y: auto;
            position: relative;
            /* margin-top: 100px; */
        }

        table {
            border-collapse: collapse;
            table-layout: fixed;

        }

        thead {
            th {
                position: sticky;
                top: 0;
                left: 0;


                &:first-child {
                    z-index: 3;
                }
            }
        }

        th,
        td {
            /* padding: 10px 100px; */
            text-transform: capitalize;
            border: 1px solid black;
            padding-inline: 20px;
        }

        th {
            background: white;
            /* color: white; */
            white-space: nowrap;

            &:first-child,
            &:nth-child(2)

            /* &:nth-child(3)  */
                {
                position: sticky;
                left: 0px;
                z-index: 3;
                background-color: #feebe9;
                color: #000;
            }
        }

        td {

            &:first-child,
            &:nth-child(2)

            /* &:nth-child(3)  */
                {
                position: sticky;
                left: 0px;
                z-index: 2;
                background-color: #feebe9;
                color: #000;
            }
        }
    </style>
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
                            <h2 class="pageheader-title"> Morning Shop Report </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Morning Shop Report</li>
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
                        <div class="col-12 col-lg-4 d-grid">
                            <a class="btn btn-outline-primary mb-4" href="/rep/shop-report-evening">Evining Report</a>
                        </div>
                        <!-- ============================================================== -->

                        <!-- ============================================================== -->

                        <!-- recent orders  -->
                        <!-- ============================================================== -->
                        <div class="col-12">
                            <div class="card">
                                <div class="row">
                                    <div class="col-4">
                                        <h5 class="card-header">Today Morning Shop Report &nbsp;&nbsp;
                                            <a href="/rep/shop-report-full-screen" type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#morning_shop_wise_report">
                                                Full Screen
                                            </a>
                                        </h5>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="div">
                                        <table class="table-bordered border-dark">
                                            <thead class="bg-light ">
                                                <tr>
                                                    <th>#</th>
                                                    <th style="min-width: 280px;">Item Code | Item</th>
                                                    @foreach ($header_normal as $head)
                                                    <th style="min-width: 200px;" colspan="2">
                                                        <h6>{{$head->shop_name}}</h6>
                                                        <h6>{{$head->branch_code}}</h6>
                                                    </th>
                                                    @endforeach
                                                    <th class="table-primary">
                                                        <h6>Normal Route Total</h6>
                                                    </th>

                                                    @foreach ($header_special as $head)
                                                    <th style="min-width: 200px;" colspan="2">
                                                        <h6>{{$head->shop_name}}</h6>
                                                        <h6>{{$head->branch_code}}</h6>
                                                    </th>
                                                    @endforeach
                                                    <th class="table-danger">
                                                        <h6>Special Route Total</h6>
                                                    </th>

                                                    @foreach ($header_pbd as $head)
                                                    <th style="min-width: 200px;" colspan="2">
                                                        <h6>{{$head->shop_name}}</h6>
                                                        <h6>{{$head->branch_code}}</h6>
                                                    </th>
                                                    @endforeach
                                                    <th class="table-danger">
                                                        <h6>PBD Route Total</h6>
                                                    </th>

                                                    <th class="table-warning">
                                                        <h6>Total</h6>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody style="max-height: 400px;">
                                                @php
                                                $total_normal = 0;
                                                $total_special = 0;
                                                $total_pbd = 0;
                                                $index = 0;
                                                $timeZone = 'Asia/Colombo';
                                                $currentDate = new DateTime();
                                                @endphp
                                                @foreach ($products as $product)
                                                @php
                                                // Reset the totals for each product
                                                $total_normal = 0;
                                                $total_special = 0;
                                                $total_pbd = 0;
                                                @endphp
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $product->item_number }} | {{ $product->name_english }}</td>

                                                    {{-- Normal Route --}}
                                                    @foreach ($header_normal as $head)
                                                    @php
                                                    $order = DB::table('orders')
                                                    ->join('carts', 'orders.unique_id', '=', 'carts.order_number')
                                                    ->where('orders.shop', '=', $head->branch_code)
                                                    ->where('carts.item_number', '=', $product->item_number)
                                                    ->where('orders.time_period', '=', 'Morning')
                                                    ->where('orders.status','=','Processing')
                                                    ->whereDate('orders.created_at', $currentDate)
                                                    ->select('carts.*', 'orders.*')
                                                    ->first();

                                                    $orderQty = $order ? $order->qty : 0; // Check if $order exists
                                                    if ($order) {
                                                    $total_normal += $orderQty;
                                                    }
                                                    @endphp
                                                    <th class="{{ $orderQty != 0 ? 'table-success' : '' }}">
                                                        {{ $orderQty }}
                                                    </th>
                                                    <th style="max-width: 180px; overflow-x: scroll;">
                                                        <span>{{ $order ? $order->remarke : '' }}</span>
                                                    </th>
                                                    @endforeach

                                                    <td class="table-primary">{{ $total_normal }}</td>

                                                    {{-- Special Route --}}
                                                    @foreach ($header_special as $head)
                                                    @php
                                                    $order = DB::table('orders')
                                                    ->join('carts', 'orders.unique_id', '=', 'carts.order_number')
                                                    ->where('orders.shop', '=', $head->branch_code)
                                                    ->where('carts.item_number', '=', $product->item_number)
                                                    ->where('orders.time_period', '=', 'Morning')
                                                    ->where('orders.status','=','Processing')
                                                    ->whereDate('orders.created_at', $currentDate)
                                                    ->select('carts.*', 'orders.*')
                                                    ->first();

                                                    $orderQty = $order ? $order->qty : 0; // Check if $order exists
                                                    if ($order) {
                                                    $total_special += $orderQty;
                                                    }
                                                    @endphp
                                                    <th class="{{ $orderQty != 0 ? 'table-success' : '' }}">
                                                        {{ $orderQty }}
                                                    </th>
                                                    <th style="max-width: 180px; overflow-x: scroll;">
                                                        <span>{{ $order ? $order->remarke : '' }}</span>
                                                    </th>
                                                    @endforeach

                                                    <td class="table-danger">{{ $total_special }}</td>

                                                    {{-- PBD Route --}}
                                                    @foreach ($header_pbd as $head)
                                                    @php
                                                    $order = DB::table('orders')
                                                    ->join('carts', 'orders.unique_id', '=', 'carts.order_number')
                                                    ->where('orders.shop', '=', $head->branch_code)
                                                    ->where('carts.item_number', '=', $product->item_number)
                                                    ->where('orders.time_period', '=', 'Morning')
                                                    ->where('orders.status','=','Processing')
                                                    ->whereDate('orders.created_at', $currentDate)
                                                    ->select('carts.*', 'orders.*')
                                                    ->first();

                                                    $orderQty = $order ? $order->qty : 0; // Check if $order exists
                                                    if ($order) {
                                                    $total_pbd += $orderQty;
                                                    }
                                                    @endphp
                                                    <th class="{{ $orderQty != 0 ? 'table-success' : '' }}">
                                                        {{ $orderQty }}
                                                    </th>
                                                    <th style="max-width: 180px; overflow-x: scroll;">
                                                        <span>{{ $order ? $order->remarke : '' }}</span>
                                                    </th>
                                                    @endforeach

                                                    <td class="table-danger">{{ $total_pbd }}</td>

                                                    {{-- Total for all routes --}}
                                                    <td class="table-warning">{{ $total_normal + $total_special + $total_pbd }}</td>
                                                </tr>
                                                @php
                                                $index++;
                                                @endphp
                                                @endforeach
                                            </tbody>

                                        </table>
                                    </div>
                                    <div class="p-2">
                                        {{ $products->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ============================================================== -->
                        <!-- end recent orders  -->

                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- /content -->

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

</body>

</html>