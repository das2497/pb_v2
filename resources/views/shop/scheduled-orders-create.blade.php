@extends('layouts.shop-dashboard')

@section('title', 'Shop')

@section('content')
    <div class="dashboard-wrapper">
        <div class="dashboard-ecommerce">
            <div class="container-fluid dashboard-content ">
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="page-header">
                            <h2 class="pageheader-title"> Schedule a New Order </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/shop/dashboard" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="{{ route('shop.scheduled-orders') }}" class="breadcrumb-link">Scheduled Orders</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Schedule a New Order</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ecommerce-widget">
                    <div class="row">
                        <div class="col-12 col-lg-6">
                            <div class="card">
                                <h5 class="card-header">Schedule a New Order</h5>
                                @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                                @if (session('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                                @endif
                                <div class="card-body">
                                    <form action="{{ route('shop.scheduled-orders-store') }}" method="POST">
                                        @csrf
                                        <div class="form-group mb-3">
                                            <label for="order_date">Order Date</label>
                                            <input type="date" class="form-control" id="order_date" name="order_date"
                                                min="{{ now()->toDateString() }}" value="{{ old('order_date') }}" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="order_time">Order Time</label>
                                            <select class="form-control" id="order_time" name="order_time" required>
                                                <option value="">Select order time</option>
                                                @foreach ($allowed_times as $time)
                                                <option value="{{ $time }}" @selected(old('order_time') == $time)>{{ $time }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-outline-success">Next: Select Products</button>
                                        <a href="{{ route('shop.scheduled-orders') }}" class="btn btn-outline-danger">Cancel</a>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
