<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Admin | Add Shop</title>
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
                            <h2 class="pageheader-title"> Add Shops </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/order-admin/dashboard" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Add Shops</li>
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
                        <div class="col-xl-3 col-lg-12 col-md-6 col-sm-12 col-12">
                            <!-- ============================================================== -->
                            <!-- top perfomimg  -->
                            <!-- ============================================================== -->
                            <div class="card">
                                <h5 class="card-header">Add Shop</h5>
                                <div class="card-body p-0">
                                    <form action="" method="POST" enctype="multipart/form-data" class="p-2">
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
                                            <label for="sp_uname" class="col-form-label">Shop Name</label>
                                            <input name="name" id="name" type="text" class="form-control" placeholder="Shop Name" value="{{old('name')}}">
                                        </div>
                                        <div class="form-group">
                                            <label for="sp_branch_code" class="col-form-label">Branch Code Number</label>
                                            <input name="branch_code" id="sp_branch_code" type="text" class="form-control" placeholder="Branch Code Number" value="{{old('branch_code')}}">
                                        </div>
                                        <div class="form-group">
                                            <label for="sp_email">Email</label>
                                            <input name="email" id="sp_email" type="text" placeholder="Email" class="form-control" value="{{old('email')}}">
                                        </div>
                                        <div class="form-group">
                                            <label for="sp_contact">Contact No</label>
                                            <input name="contact" id="sp_contact" type="text" placeholder="Contact No" class="form-control" value="{{old('contact')}}">
                                        </div>
                                        <div class="form-group">
                                            <label for="sp_price_range">Select Price Range</label>
                                            <select class="form-control" id="sp_price_range" name="price_range">
                                                <option value="">Select Price Range</option>
                                                <option value="Unit Price">Unit Price</option>
                                                <option value="PB MRP">PB MRP</option>
                                                <option value="PB Direct Sale Price">PB Direct Sale Price</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="sp_route">Select Morning Route</label>
                                            <select class="form-control" id="sp_route" name="morning_route">
                                                <option value="">Select Morning Route</option>
                                                @foreach ($routes as $route)
                                                <option value="{{$route->name}}">{{$route->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="sp_route">Select Evening Route</label>
                                            <select class="form-control" id="sp_route" name="evening_route">
                                                <option value="">Select Evening Route</option>
                                                @foreach ($routes as $route)
                                                <option value="{{$route->name}}">{{$route->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="sp_shop_type">Select Shop Type</label>
                                            <select class="form-control" id="sp_shop_type" name="type">
                                                <option value="">Select Shop Type</option>
                                                <option value="Outlet">Outlet</option>
                                                <option value="Route Rep">Route Rep</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="sp_route">Order Time</label>
                                            <select class="form-control" id="sp_order_time" name="order_time">
                                                <option value="">Select Order Time</option>
                                                <option value="Morning">Morning</option>
                                                <option value="Evening">Evening</option>
                                                <option value="All">All</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="password">Password</label>
                                            <input name="password" id="password" type="text" placeholder="Password" class="form-control" value="{{old('password')}}">
                                        </div>
                                        <div>
                                            <button type="submit" class="btn btn-primary">Add</button>
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
                                <h5 class="card-header">Pending Orders</h5>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr class="border-0">
                                                    <th class="border-0">#</th>
                                                    <th class="border-0">Branch Code</th>
                                                    <th class="border-0">Email</th>
                                                    <th class="border-0">Price Range</th>
                                                    <th class="border-0">Morning Route</th>
                                                    <th class="border-0">Evening Route</th>
                                                    <th class="border-0">Order Time</th>
                                                    <th class="border-0">Type</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($shops as $shop)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $shop->branch_code }}</td>
                                                    <td>{{ $shop->email }}</td>
                                                    <td>{{ $shop->price_range }}</td>
                                                    <td>{{ $shop->morning_route }}</td>
                                                    <td>{{ $shop->evening_route }}</td>
                                                    <td>{{ $shop->order_time }}</td>
                                                    <td>{{ $shop->type }}</td>
                                                </tr>
                                                @endforeach
                                                <tr>
                                                    <td></td>
                                                    <td colspan="6">{{$shops->links()}}</td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
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