@extends('layouts.dashboard', ['headerView' => 'order-admin.components.header', 'menuView' => 'order-admin.components.menu'])

@section('title', 'Order Admin | Create Order')

@push('styles')
<style>
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
        }

        input:checked+.slider {
            background-color: #2196F3;
        }

        input:focus+.slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked+.slider:before {
            -webkit-transform: translateX(26px);
            -ms-transform: translateX(26px);
            transform: translateX(26px);
        }
    </style>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
@endpush

@section('content')
    <div class="dashboard-wrapper">
        <div class="dashboard-ecommerce">
            <div class="container-fluid dashboard-content ">
                <!-- ============================================================== -->
                <!-- pageheader  -->
                <!-- ============================================================== -->
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="page-header">
                            <h2 class="pageheader-title"> Create Order </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/order-admin/dashboard" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Create Order</li>
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
                        <div class="col-12">
                            <div class="card">
                                @if (!empty($my_shop) && $cart_item->contains('shop_bc_number', $my_shop))
                                <h5 class="card-header">All Products <a href="/order-admin/cart?shop={{$my_shop}}" class="btn btn-outline-warning m-l-10 float-right cart-btn"><i class="bi bi-cart4"></i> Cart</a></h5>
                                @else
                                <h5 class="card-header">All Products <button class="btn btn-outline-warning m-l-10 float-right" onclick="cart_btn();"><i class="bi bi-cart4"></i> Cart</button></h5>
                                @endif
                                <div class="alert alert-danger" style="display: none;" id="warning">
                                    <p class="text-center">Pleace select a shop and search or this dosn't have any item in the cart!</p>
                                </div>
                                <form action="/order-admin/create-order" method="GET" class="row g-2 p-2">
                                    @csrf
                                    <div class="col-12 col-lg-3">
                                        <input type="text" class="form-control" placeholder="Type To Search Products...." aria-describedby="addon-wrapping"
                                            name="search" value="{{old('search', session('input.search'))}}">
                                    </div>
                                    <div class="col-6 col-lg-2">
                                        <select class="form-control" id="category" name="category">
                                            <option value="" @selected(session('category')=='' )>All category</option>
                                            @foreach ( $categories as $category )
                                            <option value="{{$category->category}}" @selected(session('category')==$category->category)>{{$category->category}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6 col-lg-3">
                                        <select class="form-control select2" id="shop" name="shop">
                                            <option value="">Select Shop <small>(Type the first letter of the shop)</small></option>
                                            @foreach($shops as $shop)
                                            <option value="{{ $shop->branch_code }}" {{ (request()->input('shop') == $shop->branch_code || old('shop') == $shop->branch_code) ? 'selected' : '' }}>
                                                {{ $shop->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6 col-lg-2">
                                        <select class="form-control" id="order_time" name="order_time">
                                            @if (!$period)
                                            <option value="" selected>Select Order Time</option>
                                            @endif
                                            @foreach ($allowed_times as $time)
                                            <option value="{{ $time }}" @selected($period===$time)>{{ $time }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6 col-lg-2">
                                        <button type="submit" class="btn btn-outline-primary w-100">Search</button>
                                    </div>
                                </form>

                                @if (!$period)
                                <div class="card-body">
                                    <p class="text-center text-muted mb-0">Select a shop and an order time, then press Search to view products.</p>
                                </div>
                                @else
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr class="border-0">
                                                    <th class="border-0">#</th>
                                                    <th class="border-0">Item image</th>
                                                    <th class="border-0">Item Id</th>
                                                    <th class="border-0">Item Name English</th>
                                                    <th class="border-0">Quantity</th>
                                                    <th class="border-0"></th>
                                                </tr>
                                            </thead>
                                            <!-- <tbody>
                                                @if ($errors->any())
                                                <tr>
                                                    <td colspan="6">
                                                        <div class="alert alert-danger">
                                                            <ul>
                                                                @foreach ($errors->all() as $error)
                                                                <li>{{ $error }}</li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endif

                                                @foreach($products as $product)
                                                <tr @if(isset($cart_item) && $cart_item->contains('item_number', $product->item_number)) class="table-success" @endif>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td><img src="{{ asset('assets/images/item-images/'.$product->img) }}" width="60" height="60" /></td>
                                                    <td>{{ $product->item_number }}</td>
                                                    <td>{{ $product->name_english }}</td>
                                                    <td>
                                                        @if (isset($cart_item) && $cart_item->contains('item_number', $product->item_number))
                                                        <input class="form-control" type="number" name="qty" value="{{ $cart_item->firstWhere('item_number', $product->item_number)->qty }}" disabled>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-outline-success" disabled>Add to cart</button>
                                                    </td>
                                                    @else
                                                    <form action="{{ route('add-to-cart') }}" method="POST">
                                                        @csrf
                                                        <input class="form-control" style="min-width: 100px;" type="number" name="qty" required>
                                                        <input type="hidden" name="branch_code" value="{{ session('selected_shop') }}">
                                                        <input type="hidden" name="item_number" value="{{ $product->item_number }}">
                                                        <td>
                                                            <button type="submit" class="btn btn-outline-success add-to-cart-btn">Add to cart</button>
                                                        </td>
                                                    </form>
                                                    @endif
                                                    </td>

                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody> -->

                                            <form action="{{ route('order-admin.add-to-cart-all') }}" method="POST">
                                                @csrf
                                                <tbody>
                                                    @if ($errors->any())
                                                    <tr>
                                                        <td colspan="7">
                                                            <div class="alert alert-danger">
                                                                <ul>
                                                                    @foreach ($errors->all() as $error)
                                                                    <li>{{ $error }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endif
                                                    @foreach($products as $product)
                                                    <tr @if(isset($cart_item) && $cart_item->contains('item_number', $product->item_number)) class="table-success" @endif>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td><img src="{{ asset('assets/images/item-images/'.$product->img) }}" width="60" height="60" /></td>
                                                        <td>{{ $product->item_number }}</td>
                                                        <td>{{ $product->name_english }}</td>
                                                        <td>
                                                            @if (isset($cart_item) && $cart_item->contains('item_number', $product->item_number))
                                                            <input class="form-control" type="number" name="qty[{{ $product->item_number }}]" value="{{ $cart_item->firstWhere('item_number', $product->item_number)->qty }}" disabled>
                                                            @else
                                                            <input class="form-control" style="min-width: 100px;" type="number" name="qty[{{ $product->item_number }}]" min="0" step="0.01" required pattern="[0-9]+(\.[0-9]+)?" value="0">
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if (isset($cart_item) && $cart_item->contains('item_number', $product->item_number))
                                                            <!-- <button class="btn btn-outline-success" disabled>Add to cart</button> -->
                                                            <label class="switch">
                                                                <input type="checkbox" name="selected_items[]" value="{{ $product->item_number }}" disabled checked>
                                                                <span class="slider"></span>
                                                            </label>
                                                            @else
                                                            <input type="hidden" name="item_numbers[]" value="{{ $product->item_number }}">
                                                            <!-- <button type="submit" class="btn btn-outline-success add-to-cart-btn">Add to cart</button> -->
                                                            <label class="switch">
                                                                <input type="checkbox" name="selected_items[]" value="{{ $product->item_number }}">
                                                                <span class="slider"></span>
                                                            </label>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <!-- <input type="checkbox" data-toggle="switchbutton" data-size="sm" data-offlabel="Select" data-onlabel="Selected" name="selected_items[]" value="{{ $product->item_number }}" id="default"> -->
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                    <tr>
                                                        <td colspan="5"></td>
                                                        <td>
                                                            @if ($my_shop)
                                                            <input type="hidden" name="branch_code" value="{{ $my_shop }}">
                                                            <button type="submit" class="btn btn-outline-success">Add all to cart</button>
                                                            @else
                                                            <button disabled class="btn btn-outline-success">Add all to cart</button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </form>
                                        </table>

                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
    
@endsection

@push('scripts')
<script>
        $(document).ready(function() {
            $('#shop').change(function() {
                let shop = $(this).val(); // Retrieve the selected value of the select element
                let add_to_cart = $('.add-to-cart-btn'); // Select the Add to cart button
                let cart = $('.cart-btn'); // Select the cart button

                if (shop == '') {
                    add_to_cart.prop('disabled', true); // Disable the button if no shop is selected
                    cart.addClass('disabled');
                } else {
                    add_to_cart.prop('disabled', false); // Enable the button if a shop is selected
                    cart.removeClass('disabled');
                }
            });

            $('#shop').trigger('change');
        });

        function cart_btn() {
            document.getElementById('warning').style.display = 'block';
        }
    </script>


    <script>
        $(document).ready(function() {
            $('#shop').select2({
                placeholder: "Select Shop",
                allowClear: true
            });
        });
    </script>
@endpush
