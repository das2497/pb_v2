<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Admin | Cart</title>
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

    <link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap-switch-button@1.1.0/css/bootstrap-switch-button.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap-switch-button@1.1.0/dist/bootstrap-switch-button.min.js"></script>
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
                            <h2 class="pageheader-title"> Cart </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/rep/dashboard" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="/rep/create-order?shop={{session('selected_shop')}}" class="breadcrumb-link">Create Order</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Cart</li>
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

                        <div class="col-12 col-md-12">
                            <div class="card">
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
                                @elseif (session('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                                @endif
                                <h5 class="card-header">{{$shop->name}}'s Cart <a href="/shop/create-order?shop={{session('selected_shop')}}" class="btn btn-outline-danger float-right">Go Back</a></h5>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr class="border-0">
                                                    <th class="border-0">#</th>
                                                    <th class="border-0">Item image</th>
                                                    <th class="border-0">Item Id</th>
                                                    <th class="border-0">Item Name English</th>
                                                    <th class="border-0">Price Range</th>
                                                    <th class="border-0">Price</th>
                                                    <th class="border-0">quantity</th>
                                                    <th class="border-0">Remarke</th>
                                                    <th class="border-0">Total</th>
                                                    <th class="border-0">Update</th>
                                                    <th class="border-0">Delete</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                $total=0;
                                                @endphp
                                                @foreach ($carts as $cart)
                                                <tr>
                                                    <td>{{$loop->iteration}}</td>
                                                    <td>
                                                        <img 
                                                            class="img-thumbnail"
                                                            src="{{ asset('assets/images/item-images/' . $cart->img) }}"
                                                            alt="product image"
                                                            style="height: 100px; width: 100px;">
                                                    </td>
                                                    <td>{{$cart->item_number}}</td>
                                                    <td>{{$cart->name_english}}</td>
                                                    <td>{{$cart->price_range}}</td>
                                                    <td>රු. {{number_format($cart->price,2)}}</td>
                                                    <form action="/shop/cart/update-qty" method="POST">
                                                        @csrf
                                                        <td><input type="number" style="width: 100px;" name="qty" class="form-control" value="{{$cart->qty}}" min="0" step="0.01" required pattern="[0-9]+(\.[0-9]+)?"></td>
                                                        <td style="width: 100px;"><input type="text" class="form-control" style="width: 200px;" name="remarke" value="{{$cart->remarke}}" /></td>
                                                        <td>රු. {{number_format($cart->price*$cart->qty,2)}}</td>
                                                        <input type="hidden" name="item_number" value="{{$cart->item_number}}" />
                                                        <input type="hidden" name="branch_code" value="{{$cart->shop_bc_number}}">
                                                        <td><button type="submit" class="btn btn-outline-primary">Update</button></td>
                                                        <td><button type="submit" formaction="/shop/cart/delete-item" onclick="return confirmDelete();" class="btn btn-outline-danger">Delete</button></td>
                                                    </form>
                                                </tr>
                                                @php
                                                $total+=$cart->price*$cart->qty;
                                                @endphp
                                                @endforeach
                                                <tr>
                                                    <td colspan="7"></td>
                                                    <th class="text-right">Total Price : </th>
                                                    <th colspan="3">රු. {{number_format($total,2)}}</th>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card">
                                <div class="card-body p-2">
                                    <form action="/shop/cart/order-process" method="POST" class="row mt-2">
                                        @csrf
                                        <div class="col-12 col-md-6 m-b-10">
                                            <label for="order_note" id="note">Special Note</label>
                                            <textarea class="form-control" name="note" cols="30" rows="2"></textarea>
                                        </div>
                                        @if ($period)
                                        <input type="hidden" name="order_time" value="{{ $period }}">
                                        @else
                                        <div class="col-12 m-b-10">
                                            <div class="alert alert-warning mb-0">Select an order time on the <a href="/shop/create-order">Create Order</a> page first (not needed for Default Orders).</div>
                                        </div>
                                        @endif
                                        <div class="col-12 col-md-6 offset-md-0 m-b-10">
                                            <div class="form-check form-switch">
                                                <input type="checkbox" onchange="default_name();" data-toggle="switchbutton" data-size="sm" data-offlabel="Normal Order" data-onlabel="Default Order" name="default" id="default">
                                                <!-- <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault" name="default"> -->
                                                <!-- <label class="form-check-label" for="flexSwitchCheckDefault">Default Order</label> -->
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 offset-md-3 m-b-10 mt-4">
                                            <input type="hidden" name="total" value="{{$total}}">
                                            <input type="hidden" name="shop" value="{{$shop->branch_code}}">
                                            <button class="btn btn-outline-success px-4 w-100">Proceed</button>
                                        </div>
                                    </form>
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

    <script>
        function confirmDelete() {
            return confirm('Are you sure you want to delete this item?');
        }

        function default_name() {
            let isCheck = document.getElementById('default').checked;
            let note = document.getElementById('note');
            console.log(isCheck);
            if (isCheck == true) {
                console.log('true');
                note.innerHTML = 'Default Order Name';
            } else {
                console.log('false');
                note.innerHTML = 'Special Note';
            }
        }
    </script>
</body>

</html>