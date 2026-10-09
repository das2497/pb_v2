@extends('layouts.dashboard', ['headerView' => 'rep.components.header', 'menuView' => 'rep.components.menu'])

@section('title', 'Rep | Dashboard')

@section('content')
    <div class="dashboard-wrapper">
        <div class="dashboard-ecommerce">
            <div class="container-fluid dashboard-content ">
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="page-header">
                            <h2 class="pageheader-title"> Pending Orders </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/order-admin/dashboard" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Pending Orders</li>
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
                                <h5 class="card-header">Pending Orders</h5>
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
                                <form action="/rep/pending-order" method="GET">
                                    @csrf
                                    <div class="input-group flex-nowrap p-2">
                                        <input type="text" class="form-control" id="ordering_admin_all_items_srch"
                                            placeholder="Type shop name" aria-describedby="addon-wrapping" name="search">
                                        <button type="submit" class="input-group-text btn" id="addon-wrapping">Search</button>
                                    </div>
                                </form>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr class="border-0">
                                                    <th class="border-0">#</th>
                                                    <th class="border-0">Order Id</th>
                                                    <th class="border-0">Outlet</th>
                                                    <th class="border-0">Delivery time</th>
                                                    <th class="border-0">Order create time</th>
                                                    <th class="border-0">Special Note</th>
                                                    <th class="border-0">Estimate full amount</th>
                                                    <th class="border-0"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($Orders as $order)
                                                <tr>
                                                    <td>{{ $loop->iteration}}</td>
                                                    <td>{{ $order->unique_id }}</td>
                                                    <td>{{ $order->shop_name }}</td>
                                                    <td>{{ $order->time_period }}</td>
                                                    <td>{{ $order->created_at }}</td>
                                                    <td>
                                                        <form action="/rep/pending-order-note-update" method="POST" class="input-group">
                                                            @csrf
                                                            <input class="form-control" type="text" value="{{ $order->note }}" name="note">
                                                            <input type="hidden" name="order_number" value="{{ $order->unique_id }}">
                                                            <button type="submit" class="btn btn-link"><i class="fa fa-refresh" aria-hidden="true"></i></button>
                                                        </form>
                                                    </td>
                                                    <td>රු. {{ number_format($order->total_price,2) }}</td>
                                                    <td>
                                                        <a href="{{ route('rep.pending-orders-view', [
                                                    'id' => $order->unique_id,
                                                    'note'=>$order->note,
                                                    'shop_name'=>$order->shop_name
                                                    ]) }}" class="btn btn-outline-success">View</a>
                                                    </td>
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
