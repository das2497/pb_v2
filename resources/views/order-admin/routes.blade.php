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
                            <h2 class="pageheader-title"> Add Routes </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/order-admin/dashboard" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Add Routes</li>
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
                                <h5 class="card-header">All Routs</h5>
                                <form action="/order-admin/routes" method="GET">
                                    @csrf
                                    <div class="input-group flex-nowrap p-2">
                                        <input type="text" class="form-control" id="ordering_admin_all_items_srch"
                                            placeholder="Search Routes" aria-describedby="addon-wrapping" name="search">
                                        <button type="submit" class="input-group-text btn" id="addon-wrapping">Search</button>
                                    </div>
                                </form>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr class="border-0">
                                                    <th class="border-0">#</th>
                                                    <th class="border-0">Route Index</th>
                                                    <th class="border-0">Route Name</th>
                                                    <th class="border-0">Route Type</th>
                                                    <th class="border-0">Route Time</th>
                                                    <th class="border-0">Shops</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($routes as $route)
                                                <tr>
                                                    <td>{{$loop->iteration}}</td>
                                                    <td>{{$route->index}}</td>
                                                    <td>{{$route->name}}</td>
                                                    <td>{{$route->type}}</td>
                                                    <td>{{$route->time}}</td>
                                                    <td>
                                                        @php
                                                        $shops = DB::table('shops')->where('morning_route', $route->name)->orWhere('evening_route',$route->name)->get();
                                                        @endphp
                                                        @foreach($shops as $shop)
                                                        <span>{{ $shop->branch_code .' '. $shop->name }} | </span>
                                                        @endforeach
                                                    </td>
                                                </tr>
                                                @endforeach
                                                <tr>
                                                    <td></td>
                                                    <td colspan="2">{{ $routes->links() }}</td>
                                                    <td></td>
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
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <h5 class="card-header">Add Route</h5>
                                        <div class="card-body p-0">
                                            <form action="/order-admin/add-route" method="POST" class="p-2">
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
                                                    <label for="inputText3" class="col-form-label">Route Index</label>
                                                    <input name="index" id="route_index" type="text" class="form-control" placeholder="Route Index" value="{{old('index')}}">
                                                </div>
                                                <div class="form-group">
                                                    <label for="inputEmail">Route Name</label>
                                                    <input name="name" id="route_name" type="text" placeholder="Route Name" class="form-control" value="{{old('name')}}">
                                                </div>
                                                <div class="form-group">
                                                    <label for="input-select">Route Type</label>
                                                    <select class="form-control" id="route_type" name="type">
                                                        <option value="">Select Route Type</option>
                                                        <option value="Normal">Normal</option>
                                                        <option value="Special">Special</option>
                                                        <option value="PBD">PBD</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="input-select">Route Time</label>
                                                    <select class="form-control" name="time">
                                                        <option value="">Select Route Time</option>
                                                        <option value="Morning">Morning</option>
                                                        <option value="Evening">Evening</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <button type="submit" class="btn btn-primary">Add</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- ============================================================== -->
                            <!-- end top perfomimg  -->
                            <!-- ============================================================== -->
                        </div>

                        <div class="col-xl-9 col-lg-12 col-md-6 col-sm-12 col-12">
                            <div class="card">
                                <h5 class="card-header">All Routs</h5>
                                <form action="/order-admin/routes" method="GET">
                                    @csrf
                                    <div class="input-group flex-nowrap p-2">
                                        <input type="text" class="form-control" id="ordering_admin_all_items_srch"
                                            placeholder="Search Routes" aria-describedby="addon-wrapping" name="search">
                                        <button type="submit" class="input-group-text btn" id="addon-wrapping">Search</button>
                                    </div>
                                </form>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr class="border-0">
                                                    <th class="border-0">#</th>
                                                    <th class="border-0">Route Index</th>
                                                    <th class="border-0">Route Name</th>
                                                    <th class="border-0">Route Type</th>
                                                    <th class="border-0">Route Time</th>
                                                    <th class="border-0">Shops</th>
                                                    <th class="border-0"></th>
                                                    <th class="border-0"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($routes as $route)
                                                <tr>
                                                    <td>{{$loop->iteration}}</td>
                                                    <td>{{$route->index}}</td>
                                                    <td>{{$route->name}}</td>
                                                    <td>{{$route->type}}</td>
                                                    <td>{{$route->time}}</td>
                                                    <td>
                                                        @php
                                                        $shops = DB::table('shops')->where('morning_route', $route->name)->orWhere('evening_route',$route->name)->get();
                                                        @endphp
                                                        @foreach($shops as $shop)
                                                        <span>{{ $shop->branch_code .' '. $shop->name }} | </span>
                                                        @endforeach
                                                    </td>
                                                    <td>
                                                        <!-- Update Button -->
                                                        <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#updateRouteModal{{ $route->id }}">
                                                            Update
                                                        </button>
                                                    </td>
                                                    <form method="POST" action="{{route('order-admin-delete-route', $route->id)}}">
                                                        @csrf
                                                        <td><button type="submit" onclick="return confirmDelete();"
                                                                class="btn btn-outline-danger">Delete</button></td>
                                                    </form>
                                                </tr>
                                                <!-- Update Modal -->
                                                <div class="modal fade" id="updateRouteModal{{ $route->id }}" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel{{ $route->id }}" aria-hidden="true">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            <form action="{{ route('order-admin-update-route', $route->id) }}" method="POST" enctype="multipart/form-data">
                                                                @csrf
                                                                @method('PUT')
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="updateModalLabel{{ $route->id }}">Update Category</h5>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="form-group">
                                                                        <label for="category">Route Index</label>
                                                                        <input type="text" class="form-control" name="index" value="{{ $route->index }}" required>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="category">Route Name</label>
                                                                        <input type="text" class="form-control" name="name" value="{{ $route->name }}" required>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="input-select">Route Type</label>
                                                                        <select class="form-control" id="route_type" name="type">
                                                                            <option value="">Select Route Type</option>
                                                                            <option value="Normal" @selected($route->type === 'Normal')>Normal</option>
                                                                            <option value="Special" @selected($route->type === 'Special')>Special</option>
                                                                            <option value="PBD" @selected($route->type === 'PBD')>PBD</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="input-select">Route Time</label>
                                                                        <select class="form-control" name="time">
                                                                            <option value="">Select Route Time</option>
                                                                            <option value="Morning" @selected($route->time === 'Morning')>Morning</option>
                                                                            <option value="Evening" @selected($route->time === 'Evening')>Evening</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Close</button>
                                                                    <button type="submit" class="btn btn-outline-primary">Save changes</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                                <tr>
                                                    <td></td>
                                                    <td colspan="4">{{ $routes->links() }}</td>
                                                    <td></td>
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
    <script>
        function confirmDelete() {
            return confirm('Are you sure you want to delete this item?');
        }
    </script>
</body>

</html>