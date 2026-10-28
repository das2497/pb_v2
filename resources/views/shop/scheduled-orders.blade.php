@extends('layouts.shop-dashboard')

@section('title', 'Shop')

@section('content')
    <div class="dashboard-wrapper">
        <div class="dashboard-ecommerce">
            <div class="container-fluid dashboard-content ">
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="page-header">
                            <h2 class="pageheader-title"> Scheduled Orders </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/shop/dashboard" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Scheduled Orders</li>
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
                                <h5 class="card-header">
                                    Scheduled Orders
                                    <a href="{{ route('shop.scheduled-orders-create') }}" class="btn btn-outline-success m-l-10 float-right">
                                        <span class="bi bi-plus-square fs-4"></span> Schedule a new order
                                    </a>
                                </h5>
                                <div class="alert alert-info m-2 mb-0">
                                    Pick a future date and an order time, then select the products you want. On that date, those products will already be in your cart when you come to place the order.
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr class="border-0">
                                                    <th class="border-0">#</th>
                                                    <th class="border-0">Order Date</th>
                                                    <th class="border-0">Order Time</th>
                                                    <th class="border-0">Estimate full amount</th>
                                                    <th class="border-0"></th>
                                                    <th class="border-0"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($orders as $order)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($order->order_date)->format('Y-m-d (D)') }}</td>
                                                    <td>{{ $order->time_period }}</td>
                                                    <td>රු. {{ number_format($order->total_price, 2) }}</td>
                                                    <td><a href="{{ route('shop.scheduled-orders-view', ['id' => $order->unique_id]) }}" class="btn btn-outline-success">View</a></td>
                                                    <td>
                                                        <form action="{{ route('shop.scheduled-orders-delete', ['id' => $order->unique_id]) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline-danger" onclick="return confirmDelete();">Delete</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">No scheduled orders yet.</td>
                                                </tr>
                                                @endforelse
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

@push('scripts')
<script>
        function confirmDelete() {
            return confirm('Are you sure you want to delete this scheduled order?');
        }
    </script>
@endpush
