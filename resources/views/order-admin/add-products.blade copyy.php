<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Admin | Add Products</title>
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
                            <h2 class="pageheader-title"> Add Products </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/order-admin/dashboard" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Add Products</li>
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
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <h5 class="card-header">Add Products</h5>
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
                                                    <label for="file" class="col-form-label">Item image</label>
                                                    <input name="file" id="file" type="file" class="form-control" value="{{old('file')}}">
                                                </div>
                                                <div class="form-group">
                                                    <label for="inputText3" class="col-form-label">Item Number</label>
                                                    <input name="item_number" id="item_num" type="text" class="form-control" placeholder="Item Number" value="{{old('item_number')}}">
                                                </div>
                                                <div class="form-group">
                                                    <label for="inputEmail">Item Name English</label>
                                                    <input name="item_name_e" id="item_name_e" type="text" placeholder="Item Name" class="form-control" value="{{old('item_name_e')}}">
                                                </div>
                                                <div class="form-group">
                                                    <label for="inputEmail">Item Name Sinhala</label>
                                                    <input name="item_name_s" id="item_name_s" type="text" placeholder="Item Name" class="form-control" value="{{old('item_name_s')}}">
                                                </div>
                                                <div class="form-group">
                                                    <label for="input-select">Product Category</label>
                                                    <select class="form-control" id="category" name="category">
                                                        <option value="">Select Category</option>
                                                        @foreach ($categories as $category)
                                                        <option value="{{$category->category}}">{{$category->category}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="input-select">Select Visibility</label>
                                                    <select class="form-control" id="visibility" name="visibility">
                                                        <option value="">Select Visibility</option>
                                                        <option value="All">all</option>
                                                        <option value="Rep">Rep</option>
                                                        <option value="Shop">Shop</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="inputEmail">PB Unit Price</label>
                                                    <input name="pb_unit_price" id="pb_unit_price" type="text" placeholder="PB Unit Price" class="form-control" value="{{old('pb_unit_price')}}">
                                                </div>
                                                <div class="form-group">
                                                    <label for="inputEmail">PB MRP</label>
                                                    <input name="pb_mrp" id="pb_mrp" type="text" placeholder="PB MRP" class="form-control" value="{{old('pb_mrp')}}">
                                                </div>
                                                <div class="form-group">
                                                    <label for="inputEmail">PB Direct Sale price</label>
                                                    <input name="pb_direct_sale_price" id="pb_direct_sale_price" type="text" placeholder="PB Direct Sale price" class="form-control" value="{{old('pb_direct_sale_price')}}">
                                                </div>
                                                <div>
                                                    <button type="submit" class="btn btn-primary">Add</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="card">
                                        <h5 class="card-header">Add Category</h5>
                                        <div class="card-body p-0">
                                            <form action="{{ route('order-admin-add-products') }}" method="POST" class="p-2">
                                                @csrf
                                                <div class="form-group">
                                                    <label for="item_catgry">Category Name</label>
                                                    <input id="item_catgry" name="category" type="text" placeholder="Item Category" class="form-control">
                                                </div>
                                                <div>
                                                    <button class="btn btn-primary" type="submit">Add</button>
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
                                <h5 class="card-header">All Products</h5>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr class="border-0">
                                                    <th class="border-0">#</th>
                                                    <th class="border-0">Item image</th>
                                                    <th class="border-0">Item Id</th>
                                                    <th class="border-0">Item Name English</th>
                                                    <th class="border-0">Item Name Sinhala</th>
                                                    <th class="border-0">Category</th>
                                                    <th class="border-0">Visibility</th>
                                                    <th class="border-0">PB Unit Price</th>
                                                    <th class="border-0">PB MRP</th>
                                                    <th class="border-0">PB Direct Sale price</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($products as $product)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td><img src="{{ asset('assets/images/item-images/'.$product->img) }}" alt="item image" class="img-thumbnail" style="height: 80px;" /></td>
                                                    <td>{{ $product->item_number }}</td>
                                                    <td>{{ $product->name_english }}</td>
                                                    <td>{{ $product->name_sinhala }}</td>
                                                    <td>{{ $product->category }}</td>
                                                    <td>{{ $product->visibility }}</td>
                                                    <td>{{ $product->unit_price }}</td>
                                                    <td>{{ $product->mrp }}</td>
                                                    <td>{{ $product->direct_sale_price }}</td>
                                                    <td><button class="btn btn-outline-success">Update</button></td>
                                                </tr>
                                                @endforeach
                                                <tr>
                                                    <td></td>
                                                    <td colspan="8">{{ $products->links() }}</td>
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