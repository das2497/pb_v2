@extends('layouts.dashboard', ['headerView' => 'rep.components.header', 'menuView' => 'rep.components.menu'])

@section('title', 'Rep | Dashboard')

@section('content')
    <div class="dashboard-wrapper">
        <div class="dashboard-ecommerce">
            <div class="container-fluid dashboard-content ">
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="page-header">
                            <h2 class="pageheader-title"> Complete Orders </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/rep/dashboard" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Complete Orders</li>
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
                                <h5 class="card-header">Complete Orders</h5>
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
                                <form action="{{ route('rep.complete-order') }}" method="GET" class="input-group flex-nowrap p-2">
                                    @csrf
                                    <input type="date" class="form-control mr-2" name="date">
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
                                                    <th class="border-0">Estimate&nbsp;full&nbsp;amount</th>
                                                    <th class="border-0"></th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                @foreach ($Orders as $order)
                                                <tr>
                                                    <td>{{ $loop->iteration}}</td>
                                                    <td>{{ $order->unique_id }}</td>
                                                    <td>{{ $order->name }}</td>
                                                    <td>{{ $order->time_period }}</td>
                                                    <td>{{ $order->order_time }}</td>
                                                    <td>{{ $order->note }}</td>
                                                    <td>රු. {{ number_format($order->total_price,2) }}</td>
                                                    <td><a href="{{ route('rep.complete-orders-view', ['id' => $order->unique_id]) }}" class="btn btn-outline-success">View</a></td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div>
                                        {{$Orders->links()}}
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
