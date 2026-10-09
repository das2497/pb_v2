<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Admin | Final Report</title>
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

        .div2 {
            height: 800px;
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
            color: black;
            /* min-width: 160px; */
        }

        th {
            /* background: white; */
            color: black;
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

<body class="table-dark">

    <div class="container-fluid">
        <div class="row">
            <div class="col-12 p-2">
                <div class="card p-2">
                    <div class="row">
                        <div class="col-4">
                            <h5 class="card-header">Today Evening Final Report &nbsp;&nbsp;
                            </h5>
                        </div>
                        <div class="col-2 offset-6">
                            <a href="/rep/final-report-evening" class="btn btn-outline-dark">Go Back</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="div">
                            <table class="table-bordered border-dark">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th style="min-width: 280px;">Item Code | Item</th>
                                        @foreach ($routes_normal as $route)
                                        <th style="min-width: 200px;">
                                            {{$route->name}}
                                        </th>
                                        @endforeach

                                        <th class="table-primary">Normal Rout Total</th>

                                        @foreach ($routes_special as $route)
                                        <th style="min-width: 200px;">
                                            {{$route->name}}
                                        </th>
                                        @endforeach

                                        <th class="table-danger">Special Rout Total</th>

                                        @foreach ($routes_pbd as $route)
                                        <th style="min-width: 200px;">
                                            {{$route->name}}
                                        </th>
                                        @endforeach

                                        <th class="table-info">PBD Total</th>
                                        <th class="table-warning">Total</th>
                                    </tr>
                                </thead>
                                <tbody style="max-height: 400px;">
                                    @php
                                    $currentDate = new DateTime();
                                    @endphp
                                    @foreach ($products as $product)
                                    @php
                                    $normal_total = 0;
                                    $special_total = 0;
                                    $pbd_total = 0;
                                    @endphp
                                    <tr>
                                        <td>{{$loop->iteration}}</td>
                                        <td>{{$product->item_number}} | {{$product->name_english}}</td>

                                        @foreach ($routes_normal as $route)
                                        @php
                                        $qty = DB::table('orders')
                                        ->join('carts', 'orders.unique_id', '=', 'carts.order_number')
                                        ->join('shops','orders.shop','=','shops.branch_code')
                                        ->where('carts.item_number', $product->item_number)
                                        ->whereDate('orders.created_at', $currentDate)
                                        ->where('shops.evening_route', $route->name)
                                        ->where('orders.status', '=', 'Processing')
                                        ->where('orders.time_period', '=', 'Evening')
                                        ->sum('carts.qty');
                                        $normal_total += $qty;
                                        @endphp
                                        <td>{{$qty}}</td>
                                        @endforeach

                                        <td class="table-primary">{{$normal_total}}</td>

                                        @foreach ($routes_special as $route)
                                        @php
                                        $qty = DB::table('orders')
                                        ->join('carts', 'orders.unique_id', '=', 'carts.order_number')
                                        ->join('shops','orders.shop','=','shops.branch_code')
                                        ->where('carts.item_number', $product->item_number)
                                        ->whereDate('orders.created_at', $currentDate)
                                        ->where('shops.evening_route', $route->name)
                                        ->where('orders.status', '=', 'Processing')
                                        ->where('orders.time_period', '=', 'Evening')
                                        ->sum('carts.qty');
                                        $special_total += $qty;
                                        @endphp
                                        <td>{{$qty}}</td>
                                        @endforeach

                                        <td class="table-danger">{{$special_total}}</td>

                                        @foreach ($routes_pbd as $route)
                                        @php
                                        $qty = DB::table('orders')
                                        ->join('carts', 'orders.unique_id', '=', 'carts.order_number')
                                        ->join('shops','orders.shop','=','shops.branch_code')
                                        ->where('carts.item_number', $product->item_number)
                                        ->whereDate('orders.created_at', $currentDate)
                                        ->where('shops.evening_route', $route->name)
                                        ->where('orders.status', '=', 'Processing')
                                        ->where('orders.time_period', '=', 'Evening')
                                        ->sum('carts.qty');
                                        $special_total += $qty;
                                        @endphp
                                        <td>{{$qty}}</td>
                                        @endforeach

                                        <td class="table-info">{{$pbd_total}}</td>
                                        <td class="table-warning">{{$normal_total+$special_total}}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="text-center">{{$products->links()}}</p>
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
    <script>
        function confirmDelete() {
            return confirm('Are you sure you want to delete this item?');
        }
    </script>
</body>

</html>