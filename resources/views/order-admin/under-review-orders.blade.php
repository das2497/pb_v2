@extends('layouts.dashboard', ['headerView' => 'order-admin.components.header', 'menuView' => 'order-admin.components.menu'])

@section('title', 'Order Admin | Under Review Orders')

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
@endpush

@section('content')
    <div class="dashboard-wrapper">
        <div class="dashboard-ecommerce">
            <div class="container-fluid dashboard-content ">
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="page-header">
                            <h2 class="pageheader-title"> Under Review Orders </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/order-admin/dashboard" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Under Review Orders</li>
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
                                <h5 class="card-header">Under Review Orders</h5>
                                @if (session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                                @endif
                                <div class="input-group flex-nowrap p-2">
                                    <input type="text" class="form-control" id="ordering_admin_all_items_srch" placeholder="Search Orders" aria-describedby="addon-wrapping">
                                    <span class="input-group-text btn" id="addon-wrapping">Search</span>
                                </div>
                                <div class="card-body p-0">
                                    @if (Auth::user()->role != 'view')
                                    {{-- A <form> can't legally live inside a <tr>/<tbody> (browsers
                                         foster-parent it out of the table, which silently orphaned the
                                         period/cancel/Update controls below from any form at all). Each
                                         row's form is declared here instead and linked to its row's
                                         inputs/button via the HTML5 form="" attribute - same fix as
                                         rep/pending-orders-view.blade.php. --}}
                                    @foreach ($Orders as $order)
                                    <form id="orderForm{{ $loop->index }}" action="/order-admin/under-review-orders-update-order" method="POST" class="d-none">
                                        @csrf
                                        <input type="hidden" value="{{ $order->unique_id }}" name="order_id">
                                        <input type="hidden" value="{{ $order->status }}" name="state">
                                    </form>
                                    @endforeach
                                    @endif
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
                                                    @if (Auth::user()->role != 'view')
                                                    <th class="border-0">Canceleration</th>
                                                    @endif
                                                    <th class="border-0"></th>
                                                    <th class="border-0"></th>
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
                                                        <a href="{{ route('order-admin-under-review-orders-view', ['id' => $order->unique_id ,'note'=>$order->note ]) }}" class="btn btn-outline-success">View</a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            @else
                                            <tbody>
                                                @foreach ($Orders as $order)
                                                <tr>
                                                    <td>{{ $loop->iteration}}</td>
                                                    <td>{{ $order->unique_id }}</td>
                                                    <td>{{ $order->name }}</td>
                                                    <td>
                                                        <select form="orderForm{{ $loop->index }}" name="period" class="form-control">
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
                                                    <td>{{ $order->note }}</td>
                                                    <td>රු. {{ number_format($order->total_price,2) }}</td>
                                                    <td>
                                                        <label class="switch">
                                                            <input form="orderForm{{ $loop->index }}" type="checkbox" name="order_cancel">
                                                            <span class="slider"></span>
                                                        </label>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('order-admin-under-review-orders-view', ['id' => $order->unique_id ,'note'=>$order->note ]) }}" class="btn btn-outline-success">View</a>
                                                    </td>
                                                    <td>
                                                        <button form="orderForm{{ $loop->index }}" type="submit" class="btn btn-outline-primary">Update</button>
                                                    </td>
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

@push('scripts')
<script>
        function confirmDelete() {
            return confirm('Are you sure you want to delete this order?');
        }
    </script>
@endpush
