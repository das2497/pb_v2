@extends('layouts.dashboard', ['headerView' => 'order-admin.components.header', 'menuView' => 'order-admin.components.menu'])

@section('title', 'Order Admin | Pending Orders')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

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
@endpush

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
                                <form action="/order-admin/pending-orders" method="GET" class="input-group flex-nowrap p-2">
                                    @csrf
                                    <input type="text" class="form-control" id="ordering_admin_all_items_srch" placeholder="Search Orders" aria-describedby="addon-wrapping" name="search">
                                    <button type="submit" class="input-group-text btn" id="addon-wrapping">Search</button>
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
                                                    <th class="border-0">Canceleration</th>
                                                </tr>
                                            </thead>
                                            @if (Auth::user()->role === 'view')
                                            <tbody>
                                                @foreach ($Orders as $order)
                                                <tr>
                                                    <td>{{ $loop->iteration}}</td>
                                                    <td>{{ $order->unique_id }}</td>
                                                    <td>{{ $order->name }}</td>
                                                    <td>{{$order->time_period}}</td>
                                                    <td>{{ $order->order_created }}</td>
                                                    <td>{{ $order->note }}</td>
                                                    <td>රු. {{ number_format($order->total_price,2) }}</td>
                                                    <td>
                                                        <a href="{{ route('order-admin-pending-orders-view', ['id' => $order->unique_id ,'note'=>$order->note ]) }}" class="btn btn-outline-success">View</a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            @else
                                            <tbody>
                                                @foreach ($Orders as $order)
                                                <tr>
                                                    <form action="/order-admin/pending-orders-update-order" method="POST">
                                                        @csrf
                                                        <td>{{ $loop->iteration}}</td>
                                                        <td>{{ $order->unique_id }}</td>
                                                        <td>{{ $order->name }}</td>
                                                        <td>
                                                            <select name="period" class="form-control">
                                                                @php
                                                                $shop_times = ($order->order_time && $order->order_time !== 'All') ? [$order->order_time] : $order_times->all();
                                                                if (!in_array($order->time_period, $shop_times, true)) { $shop_times[] = $order->time_period; }
                                                                @endphp
                                                                @foreach ($shop_times as $time)
                                                                <option value="{{ $time }}" @selected($order->time_period === $time)>{{ $time }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>{{ $order->order_created }}</td>
                                                        <td>
                                                            <input class="form-control" type="text" value="{{ $order->note }}" name="note">
                                                        </td>
                                                        <td>රු. {{ number_format($order->total_price,2) }}</td>
                                                        <td>
                                                            <label class="switch">
                                                                <input type="checkbox" name="order_cancel">
                                                                <span class="slider"></span>
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('order-admin-pending-orders-view', ['id' => $order->unique_id ,'note'=>$order->note ]) }}" class="btn btn-outline-success">View</a>
                                                        </td>
                                                        <td>
                                                            <input type="hidden" value="{{ $order->unique_id }}" name="order_id">
                                                            <input type="hidden" value="{{ $order->status }}" name="state">
                                                            <input type="hidden" value="{{ $order->shop }}" name="shop">
                                                            <button type="submit" class="btn btn-outline-primary">Update</button>
                                                        </td>
                                                    </form>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            @endif
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
    
@endsection
