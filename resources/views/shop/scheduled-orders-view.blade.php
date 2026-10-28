@extends('layouts.shop-dashboard')

@section('title', 'Shop')

@section('content')
    <div class="dashboard-wrapper">
        <div class="dashboard-ecommerce">
            <div class="container-fluid dashboard-content ">
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="page-header">
                            <h2 class="pageheader-title"> Scheduled Order View </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/shop/dashboard" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="{{ route('shop.scheduled-orders') }}" class="breadcrumb-link">Scheduled Orders</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Scheduled Order View</li>
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
                                <h5 class="card-header">
                                    Scheduled Order View
                                    &mdash; {{ \Carbon\Carbon::parse($order->order_date)->format('Y-m-d (D)') }} / {{ $order->time_period }}
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
                                @elseif (session('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                                @endif
                                <div class="alert alert-info m-2 mb-0">
                                    These products will be added to your cart automatically on {{ \Carbon\Carbon::parse($order->order_date)->format('Y-m-d') }} when you open the cart for a {{ $order->time_period }} order.
                                </div>
                                <div class="input-group flex-nowrap p-2">
                                    <a class="btn btn-outline-success m-l-10" href="{{ route('shop.scheduled-orders-add-items', ['order_number' => $order->unique_id]) }}">
                                        <span class="bi bi-plus-square fs-4"></span> Add product
                                    </a>
                                    <a href="{{ route('shop.scheduled-orders') }}" class="btn btn-outline-danger m-l-10">Go Back</a>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="border-0">#</th>
                                                    <th class="border-0">Item image</th>
                                                    <th class="border-0">Item Id</th>
                                                    <th class="border-0">Item Name English</th>
                                                    <th class="border-0">quantity</th>
                                                    <th class="border-0">Remarke</th>
                                                    <th class="border-0">Price</th>
                                                    <th class="border-0">Update</th>
                                                    <th class="border-0">Delete</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                $total = 0;
                                                @endphp
                                                @foreach ($items as $item)
                                                <tr>
                                                    <td>{{$loop->iteration}}</td>
                                                    <td><img src="{{asset('assets/images/item-images/'.$item->img)}}" alt="" class="img-thumbnail" style="width: 80px; height: 80px;" /></td>
                                                    <td>{{$item->item_number}}</td>
                                                    <td>{{$item->name_english}}</td>
                                                    <form action="{{ route('shop.scheduled-orders-update-item') }}" method="POST">
                                                        <td><input type="number" style="width: 200px;" name="qty" class="form-control"
                                                                value="{{$item->qty}}" min="0" step="0.01" required pattern="[0-9]+(\.[0-9]+)?"></td>
                                                        <td><input type="text" class="form-control" style="width: 200px;"
                                                                name="remarke" value="{{$item->remarke}}" /></td>
                                                        @csrf
                                                        <td>රු. {{number_format($item->price*$item->qty,2)}}</td>
                                                        <input type="hidden" name="item_number" value="{{$item->item_number}}" />
                                                        <input type="hidden" name="shop" value="{{$item->shop_bc_number}}">
                                                        <input type="hidden" name="order_number" value="{{$item->order_number}}">
                                                        <td><button type="submit" class="btn btn-outline-primary">Update</button></td>
                                                        <td><button type="submit" formaction="{{ route('shop.scheduled-orders-delete-item') }}" onclick="return confirmDelete();"
                                                                class="btn btn-outline-danger">Delete</button></td>
                                                    </form>
                                                </tr>
                                                @php
                                                $total += $item->price*$item->qty;
                                                @endphp
                                                @endforeach
                                                @if (count($items) === 0)
                                                <tr>
                                                    <td colspan="9" class="text-center">No products selected yet. Use "Add product" above.</td>
                                                </tr>
                                                @endif
                                                <tr>
                                                    <td colspan="6"></td>
                                                    <th class="text-right">Total : </th>
                                                    <th colspan="2">රු. {{number_format($total,2)}}</th>
                                                </tr>
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
            return confirm('Are you sure you want to delete this item?');
        }
    </script>
@endpush
