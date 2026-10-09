<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Admin | Pending Orders</title>
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
    @include('rep.components.header')
    <!-- /header  -->

    <!-- menu -->
    @include('rep.components.menu')
    <!-- /menu -->

    <!-- content -->
    <div class="dashboard-wrapper">
        <div class="dashboard-ecommerce">
            <div class="container-fluid dashboard-content ">
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="page-header">
                            <h2 class="pageheader-title"> Pending Orders Add Items </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/order-admin/dashboard" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Pending Orders Add Items</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ecommerce-widget">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <h5 class="card-header">Pending Orders Add Items <a href="/rep/pending-orders-view/{{$order_number}}" class="btn btn-outline-danger m-l-10 float-right">Go Back</a></h5>
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
                                <div class="alert alert-danger" style="display: none;" id="warning">
                                    <p class="text-center">Pleace select a shop and search or this dosn't have any item in the cart!</p>
                                </div>
                                <div class="input-group flex-nowrap p-2">
                                    <form action="/rep/pending-orders-add-item" method="GET" class="input-group flex-nowrap p-2">
                                        @csrf
                                        <input type="text" class="form-control mr-2" id="" placeholder="Search Orders" aria-describedby="addon-wrapping" name="search">
                                        <select class="form-control m-l-10" id="category" name="category">
                                            <option value="" @selected(session('category')=='' )>All category</option>
                                            @foreach ( $categories as $category )
                                            <option value="{{$category->category}}" @selected(session('category')==$category->category)>{{$category->category}}</option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" value="{{ $order_number }}" name="order_number">
                                        <button type="submit" class="btn btn-outline-primary m-l-10">Search</button>
                                    </form>
                                </div>

                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr class="border-0">
                                                    <th class="border-0">#</th>
                                                    <th class="border-0 d-none d-lg-block">Item image</th>
                                                    <th class="border-0">Item Id</th>
                                                    <th class="border-0">Item Name English</th>
                                                    <th class="border-0">Quantity</th>
                                                    <th class="border-0"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($products as $product)
                                                <tr @if(isset($cart_items) && $cart_items->contains('item_number', $product->item_number)) class="table-success" @endif>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td><img src="{{asset('assets/images/item-images/'.$product->img)}}" alt="" width="50" /></td>
                                                    <td>{{$product->item_number}}</td>
                                                    <td>{{$product->name_english}}</td>
                                                    <td>
                                                        @if (isset($cart_items) && $cart_items->contains('item_number', $product->item_number))
                                                        <input class="form-control" type="number" name="qty" value="{{ $cart_items->firstWhere('item_number', $product->item_number)->qty }}" disabled>
                                                    </td>
                                                    <td>
                                                    <td><button class="btn btn-outline-success disabled">Add item to processing orders</button></td>
                                                    </td>
                                                    @else
                                                    <form action="{{route('rep.processing-orders-add-items-process')}}" method="POST">
                                                        @csrf
                                                        <input class="form-control" type="number" min="0" step="0.01" required pattern="[0-9]+(\.[0-9]+)?" name="qty">
                                                        <input type="hidden" name="order_number" value="{{ $order_number }}">
                                                        <input type="hidden" name="item_number" value="{{ $product->item_number }}">
                                                        <input type="hidden" name="shop" value="{{ $shop }}">
                                                        <td>
                                                        <td><button type="submit" class="btn btn-outline-success">Add item to processing orders</button></td>
                                                        </td>
                                                    </form>
                                                    @endif
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