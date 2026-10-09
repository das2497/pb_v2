<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Admin | Rep Assign Shop</title>
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

    @if (Auth::user()->role === 'sales_admin')
    <!-- menu -->
    @include('order-admin.components.menu-sales-admin')
    <!-- /menu -->
    @else
    <!-- menu -->
    @include('order-admin.components.menu')
    <!-- /menu -->
    @endif

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
                            <h2 class="pageheader-title"> Shop Assign </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/order-admin/dashboard" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Shop Assign</li>
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

                        <!-- ============================================================== -->

                        <!-- recent orders  -->
                        <!-- ============================================================== -->

                        @if (Auth::user()->role === 'view')
                        <div class="col-12">
                            <div class="card">
                                <h5 class="card-header">Assigned Shops</h5>
                                <form action="/order-admin/rep-assign" method="GET">
                                    @csrf
                                    <div class="input-group flex-nowrap p-2">
                                        <input type="text" class="form-control" id="ordering_admin_all_items_srch"
                                            placeholder="Search" aria-describedby="addon-wrapping" name="search">
                                        <button type="submit" class="input-group-text btn" id="addon-wrapping">Search</button>
                                    </div>
                                </form>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr class="border-0">
                                                    <th class="border-0">#</th>
                                                    <th class="border-0">Shop Name</th>
                                                    <th class="border-0">Rep Name</th>
                                                    @if (Auth::user()->role != 'view')
                                                    
                                                    @endif
                                                    <th class="border-0">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($datas as $key => $data)
                                                <tr>
                                                    <td>{{$loop->iteration}}</td>
                                                    <td>{{$data->shop_name}}</td>
                                                    <td>{{$data->rep_name}}</td>
                                                </tr>
                                                @endforeach
                                                <tr>
                                                    <td colspan="3">{{$datas->links()}}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="col-xl-3 col-lg-12 col-md-6 col-sm-12 col-12">
                            <!-- ============================================================== -->
                            <!-- top perfomimg  -->
                            <!-- ============================================================== -->
                            <div class="card">
                                <h5 class="card-header">Assign Shops To Representative</h5>
                                <div class="card-body p-0">
                                    <form action="/order-admin/rep-assign" method="POST" class="p-2">
                                        @csrf
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
                                        <div class="form-group">
                                            <label for="assign_rep">Select Rep</label>
                                            <select class="form-control" id="assign_rep" name="rep_id">
                                                <option value="">Select Rep</option>
                                                @foreach ($reps as $rep)
                                                <option value="{{$rep->id}}">{{$rep->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="assign_shop">Select Shop</label>
                                            <select class="form-control" id="assign_shop" name="shop_id">
                                                <option value="">Select Shop</option>
                                                @foreach ($shops as $shop)
                                                <option value="{{$shop->id}}">{{$shop->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <button class="btn btn-primary" type="submit">Assign</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- ============================================================== -->
                            <!-- end top perfomimg  -->
                            <!-- ============================================================== -->
                        </div>

                        <div class="col-xl-9 col-lg-12 col-md-6 col-sm-12 col-12">
                            <div class="card">
                                <h5 class="card-header">Assigned Shops</h5>
                                <form action="/order-admin/rep-assign" method="GET">
                                    @csrf
                                    <div class="input-group flex-nowrap p-2">
                                        <input type="text" class="form-control" id="ordering_admin_all_items_srch"
                                            placeholder="Search" aria-describedby="addon-wrapping" name="search">
                                        <button type="submit" class="input-group-text btn" id="addon-wrapping">Search</button>
                                    </div>
                                </form>
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
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr class="border-0">
                                                    <th class="border-0">#</th>
                                                    <th class="border-0">Shop Name</th>
                                                    <th class="border-0">Rep Name</th>
                                                    <th class="border-0">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($datas as $key => $data)
                                                <tr>
                                                    <td>{{$loop->iteration}}</td>
                                                    <td>{{$data->shop_name}}</td>
                                                    <form action="/order-admin/rep-assign-update" method="POST">
                                                        @csrf
                                                        <td>
                                                            <select class="form-control" id="assign_rep" name="rep_id">
                                                                @foreach ($reps as $rep)
                                                                <option value="{{$rep->id}}" @selected($rep->name == $data->rep_name)>{{$rep->name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="hidden" value="{{$data->shop_id}}" name="shop_id">
                                                            <button class="btn btn-outline-info">Update</button>
                                                        </td>
                                                    </form>
                                                </tr>
                                                @endforeach
                                                <tr>
                                                    <td colspan="3">{{$datas->links()}}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
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