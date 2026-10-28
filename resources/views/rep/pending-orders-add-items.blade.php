@extends('layouts.dashboard', ['headerView' => 'rep.components.header', 'menuView' => 'rep.components.menu'])

@section('title', 'Order Admin | Pending Orders')

@section('content')
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
                                        <li class="breadcrumb-item"><a href="/rep/dashboard" class="breadcrumb-link">Dashboard</a></li>
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
                                <h5 class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    Pending Orders Add Items
                                    <a href="/rep/pending-orders-view/{{$order_number}}" class="btn btn-outline-danger">Go Back</a>
                                </h5>
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
                                <div class="p-2">
                                    <form action="/rep/pending-orders-add-item" method="GET" class="row g-2">
                                        @csrf
                                        <div class="col-12 col-sm-5">
                                            <input type="text" class="form-control" placeholder="Search item name or code" name="search" value="{{ request('search') }}">
                                        </div>
                                        <div class="col-12 col-sm-4">
                                            <select class="form-control" id="category" name="category">
                                                <option value="" @selected(session('category')=='' )>All category</option>
                                                @foreach ( $categories as $category )
                                                <option value="{{$category->category}}" @selected(session('category')==$category->category)>{{$category->category}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <input type="hidden" value="{{ $order_number }}" name="order_number">
                                        <div class="col-12 col-sm-3">
                                            <button type="submit" class="btn btn-outline-primary w-100">Search</button>
                                        </div>
                                    </form>
                                </div>

                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr class="border-0">
                                                    <th class="border-0">#</th>
                                                    <th class="border-0 d-none d-lg-table-cell">Item image</th>
                                                    <th class="border-0">Item Id</th>
                                                    <th class="border-0">Item Name English</th>
                                                    <th class="border-0">Quantity</th>
                                                    <th class="border-0"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($products as $product)
                                                @php
                                                $inCart = isset($cart_items) && $cart_items->contains('item_number', $product->item_number);
                                                @endphp
                                                <tr @if($inCart) class="table-success" @endif>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td class="d-none d-lg-table-cell"><img src="{{asset('assets/images/item-images/'.$product->img)}}" alt="" width="50" /></td>
                                                    <td>{{$product->item_number}}</td>
                                                    <td>{{$product->name_english}}</td>
                                                    @if ($inCart)
                                                    <td>
                                                        <input class="form-control" type="number" value="{{ $cart_items->firstWhere('item_number', $product->item_number)->qty }}" disabled>
                                                    </td>
                                                    <td><button class="btn btn-outline-success disabled" disabled>Already in order</button></td>
                                                    @else
                                                    <td>
                                                        <input form="addItem{{ $loop->index }}" class="form-control" type="number" min="0" step="0.01" required pattern="[0-9]+(\.[0-9]+)?" name="qty">
                                                    </td>
                                                    <td>
                                                        <form id="addItem{{ $loop->index }}" action="{{route('rep.pending-orders-add-items-process')}}" method="POST" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="order_number" value="{{ $order_number }}">
                                                            <input type="hidden" name="item_number" value="{{ $product->item_number }}">
                                                            <input type="hidden" name="shop" value="{{ $shop }}">
                                                            <button type="submit" class="btn btn-outline-success">Add to order</button>
                                                        </form>
                                                    </td>
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
