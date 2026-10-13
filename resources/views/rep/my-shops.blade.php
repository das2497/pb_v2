@extends('layouts.dashboard', ['headerView' => 'rep.components.header', 'menuView' => 'rep.components.menu'])

@section('title', 'Rep | Dashboard')

@section('content')
    <div class="dashboard-wrapper">
        <div class="dashboard-ecommerce">
            <div class="container-fluid dashboard-content ">
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="page-header">
                            <h2 class="pageheader-title"> My Shops </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/rep/dashboard" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">My Shops</li>
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
                                <h5 class="card-header">My Shops</h5>
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
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr class="border-0">
                                                    <th class="border-0">#</th>
                                                    <th class="border-0">Branch Code</th>
                                                    <th class="border-0">Name</th>
                                                    <th class="border-0">Contact</th>
                                                    <th class="border-0">Email</th>
                                                    <th class="border-0">Morning Route</th>
                                                    <th class="border-0">Evening Route</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($shops as $shop)
                                                @php
                                                $orders = DB::table('orders')
                                                ->where('shop', '=', $shop->branch_code)
                                                ->where('status', '!=', 'Cancelled')
                                                ->where('status', '!=', 'Default')
                                                ->whereDate('created_at', '=', $date)
                                                ->count();
                                                @endphp
                                                @if ($shop->order_time == 'All' && $orders == 2)
                                                <tr class="table-success">
                                                    @elseif($shop->order_time == 'All' && $orders == 1)
                                                <tr class="table-warning">
                                                    @elseif(($shop->order_time == 'Morning' || $shop->order_time == 'Evening') && $orders == 1)
                                                <tr class="table-success">
                                                    @else
                                                <tr>
                                                    @endif
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $shop->branch_code }}</td>
                                                    <td>{{ $shop->name }}</td>
                                                    <td>{{ $shop->contact }}</td>
                                                    <td>{{ $shop->email }}</td>
                                                    <td>{{ $shop->morning_route }}</td>
                                                    <td>{{ $shop->evening_route }}</td>
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
