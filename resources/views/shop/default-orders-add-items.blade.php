@extends('layouts.shop-dashboard')

@section('title', 'Shop')

@section('content')
    <div class="dashboard-wrapper">
        <div class="dashboard-ecommerce">
            <div class="container-fluid dashboard-content ">
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="page-header">
                            <h2 class="pageheader-title"> Default Orders Add Items </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/shop/dashboard" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="/rep/pending-order" class="breadcrumb-link">Default Orders</a></li>
                                        <li class="breadcrumb-item"><a href="/rep/pending-orders-view/{{$order_number}}" class="breadcrumb-link">Default Orders View</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Default Orders Add Items</li>
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
                                <h5 class="card-header">Default Orders Add Items
                                    <a href="{{ route('shop.default-orders-view', ['id' => $order_number]) }}" class="btn btn-outline-danger m-l-10 float-right">Go Back</a>
                                </h5>
                                @if (session('success'))
                                <div class="alert alert-success">
                                    <strong>Success!</strong>
                                    <pre>{{ print_r(session('success'), true) }}</pre>
                                </div>
                                @endif
                                @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                                <div class="alert alert-danger" style="display: none;" id="warning">
                                    <p class="text-center">Pleace select a shop and search or this dosn't have any item in the cart!</p>
                                </div>
                                <div class="input-group flex-nowrap p-2">
                                    <form action="/shop/default-orders-add-items" method="GET" class="input-group flex-nowrap p-2">
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
                                                    <td><img src="{{asset('assets/images/item-images/'.$product->img)}}" alt=""  class="img-thumbnail" style="width: 80px; height: 80px;" /></td>
                                                    <td>{{$product->item_number}}</td>
                                                    <td>{{$product->name_english}}</td>
                                                    <td>
                                                        @if (isset($cart_items) && $cart_items->contains('item_number', $product->item_number))
                                                        <input class="form-control" type="number" name="qty" value="{{ $cart_items->firstWhere('item_number', $product->item_number)->qty }}" disabled style="min-width: 100px;">
                                                    </td>
                                                    <td>
                                                    <td><button class="btn btn-outline-success disabled">Add item to pending orders</button></td>
                                                    </td>
                                                    @else
                                                    <form action="{{route('shop.default-orders-add-items-process')}}" method="POST">
                                                        @csrf
                                                        <input class="form-control" type="number" min="0" step="0.01" required pattern="[0-9]+(\.[0-9]+)?" name="qty" style="min-width: 100px;">
                                                        <input type="hidden" name="order_number" value="{{ $order_number }}">
                                                        <input type="hidden" name="item_number" value="{{ $product->item_number }}">
                                                        <input type="hidden" name="shop" value="{{ $shop }}">
                                                        <td>
                                                        <td><button type="submit" class="btn btn-outline-success">Add item to pending orders</button></td>
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
    
@endsection
