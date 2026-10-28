@extends('layouts.shop-dashboard')

@section('title', 'Shop')

@section('content')
    <div class="dashboard-wrapper">
        <div class="dashboard-ecommerce">
            <div class="container-fluid dashboard-content ">
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="page-header">
                            <h2 class="pageheader-title"> Default Orders </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/shop/dashboard" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Default Orders</li>
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
                                <h5 class="card-header">Default Orders</h5>
                                <div class="input-group flex-nowrap p-2">
                                    <input type="text" class="form-control" id="ordering_admin_all_items_srch" placeholder="Search Orders" aria-describedby="addon-wrapping">
                                    <span class="input-group-text btn" id="addon-wrapping">Search</span>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr class="border-0">
                                                    <th class="border-0">#</th>
                                                    <th class="border-0">Order Id</th>
                                                    <th class="border-0">Order Name</th>
                                                    <th class="border-0">Outlet</th>
                                                    <th class="border-0">Order Time</th>
                                                    <th class="border-0">Estimate full amount</th>
                                                    <th class="border-0"></th>
                                                    <th class="border-0"></th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                @foreach ($Orders as $order)
                                                <tr>
                                                    <td>{{ $loop->iteration}}</td>
                                                    <td>{{ $order->unique_id }}</td>
                                                    <td>{{ $order->default_name }}</td>
                                                    <td>{{ $order->name }}</td>
                                                    <td>{{ $order->default_period ?? '-' }}</td>
                                                    <td>රු. {{ number_format($order->total_price,2) }}</td>
                                                    <td><a href="{{ route('shop.default-orders-view', ['id' => $order->unique_id]) }}" class="btn btn-outline-success">View</a></td>
                                                    <td>
                                                        <form action="{{ route('shop.default-orders-delete', ['id' => $order->unique_id]) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline-danger" onclick="return confirmDelete();">Delete</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                @endforeach


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
    
@endsection

@push('scripts')
<script>
        function confirmDelete() {
            return confirm('Are you sure you want to delete this item?');
        }
    </script>
@endpush
