@extends('layouts.dashboard', ['headerView' => 'order-admin.components.header', 'menuView' => 'order-admin.components.menu'])

@section('title', 'Order Admin | Add Shop')

@section('content')
    <div class="dashboard-wrapper">
        <div class="dashboard-ecommerce">
            <div class="container-fluid dashboard-content ">
                <!-- ============================================================== -->
                <!-- pageheader  -->
                <!-- ============================================================== -->
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="page-header">
                            <h2 class="pageheader-title"> Add Shops </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/order-admin/dashboard" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Add Shops</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- end pageheader  -->
                <!-- ============================================================== -->
                <div class="ecommerce-widget">

                    <div class="row">
                        <!-- ============================================================== -->

                        <!-- ============================================================== -->

                        <!-- recent orders  -->
                        <!-- ============================================================== -->
                        @if (Auth::user()->role === 'view')
                        <div class="col-12">
                            <div class="card">
                                @if(session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                                @endif
                                <h5 class="card-header">All Shops</h5>
                                <form action="/order-admin/add-shop" method="GET">
                                    @csrf
                                    <div class="input-group flex-nowrap p-2">
                                        <input type="text" class="form-control" id="ordering_admin_all_items_srch"
                                            placeholder="Search Shops" aria-describedby="addon-wrapping" name="search">
                                        <button type="submit" class="input-group-text btn" id="addon-wrapping">Search</button>
                                    </div>
                                </form>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr class="border-0">
                                                    <th class="border-0">#</th>
                                                    <th class="border-0">Branch Code</th>
                                                    <th class="border-0">Name</th>
                                                    <th class="border-0">Email</th>
                                                    <th class="border-0">Contact</th>
                                                    <th class="border-0">Assigned Rep</th>
                                                    <th class="border-0">Price Range</th>
                                                    <th class="border-0">Morning Route</th>
                                                    <th class="border-0">Evening Route</th>
                                                    <th class="border-0">Order Time</th>
                                                    <th class="border-0">Type</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($shops as $shop)
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
                                                    <td>{{ $shop->email }}</td>
                                                    <td>{{ $shop->contact }}</td>
                                                    <td>{{ $shop->rep_name }}</td>
                                                    <td>{{ $shop->price_range }}</td>
                                                    <td>{{ $shop->morning_route }}</td>
                                                    <td>{{ $shop->evening_route }}</td>
                                                    <td>{{ $shop->order_time }}</td>
                                                    <td>{{ $shop->type }}</td>
                                                </tr>
                                                @endforeach
                                                <tr>
                                                    <td></td>
                                                    <td colspan="7">{{ $shops->links() }}</td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="col-xl-3 col-lg-12 col-md-6 col-sm-12 col-12">
                            <!-- ============================================================== -->
                            <!-- top perfomimg  -->
                            <!-- ============================================================== -->
                            <div class="card">
                                <h5 class="card-header">Add Shop</h5>
                                <div class="card-body p-0">
                                    <form action="" method="POST" enctype="multipart/form-data" class="p-2">
                                        @csrf
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
                                        <div class="form-group">
                                            <label for="sp_uname" class="col-form-label">Shop Name</label>
                                            <input name="name" id="name" type="text" class="form-control" placeholder="Shop Name" value="{{old('name')}}">
                                        </div>
                                        <div class="form-group">
                                            <label for="sp_uname" class="col-form-label">Shop Name Sinhala</label>
                                            <input name="name_sinhala" id="name" type="text" class="form-control" placeholder="Shop Name" value="{{old('name_sinhala')}}">
                                        </div>
                                        <div class="form-group">
                                            <label for="sp_branch_code" class="col-form-label">Branch Code Number</label>
                                            <input name="branch_code" id="sp_branch_code" type="text" class="form-control" placeholder="Branch Code Number" value="{{old('branch_code')}}">
                                        </div>
                                        <div class="form-group">
                                            <label for="sp_email">Email</label>
                                            <input name="email" id="sp_email" type="text" placeholder="Email" class="form-control" value="{{old('email')}}">
                                        </div>
                                        <div class="form-group">
                                            <label for="sp_contact">Contact No</label>
                                            <input name="contact" id="sp_contact" type="text" placeholder="Contact No" class="form-control" value="{{old('contact')}}">
                                        </div>
                                        <div class="form-group">
                                            <label for="sp_price_range">Select Price Range</label>
                                            <select class="form-control" id="sp_price_range" name="price_range">
                                                <option value="">Select Price Range</option>
                                                <option value="Unit Price">Unit Price</option>
                                                <option value="PB MRP">PB MRP</option>
                                                <option value="PB Direct Sale Price">PB Direct Sale Price</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="sp_route">Select Morning Route</label>
                                            <select class="form-control" id="sp_route" name="morning_route">
                                                <option value="">Select Morning Route</option>
                                                @foreach ($morning_routes as $morning_route)
                                                <option value="{{$morning_route->name}}">{{$morning_route->name}}</option>
                                                @endforeach
                                                <option value="none">None</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="sp_route">Select Evening Route</label>
                                            <select class="form-control" id="sp_route" name="evening_route">
                                                <option value="">Select Evening Route</option>
                                                @foreach ($evening_routes as $evening_route)
                                                <option value="{{$evening_route->name}}">{{$evening_route->name}}</option>
                                                @endforeach
                                                <option value="none">None</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="sp_shop_type">Select Shop Type</label>
                                            <select class="form-control" id="sp_shop_type" name="type">
                                                <option value="">Select Shop Type</option>
                                                <option value="Outlet">Outlet</option>
                                                <option value="Route Rep">Route Rep</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="sp_route">Order Time</label>
                                            <select class="form-control" id="sp_order_time" name="order_time">
                                                <option value="">Select Order Time</option>
                                                <option value="Morning">Morning</option>
                                                <option value="Evening">Evening</option>
                                                <option value="All">All</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="password">Password</label>
                                            <input name="password" id="password" type="text" placeholder="Password" class="form-control" value="{{old('password')}}">
                                        </div>
                                        <div>
                                            <button type="submit" class="btn btn-primary">Add</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- ============================================================== -->
                            <!-- end top perfomimg  -->
                            <!-- ============================================================== -->
                        </div>
                        <div class="col-xl-9 col-lg-12 col-md-6 col-sm-12 col-12">
                            <div class="card">
                                @if(session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                                @endif
                                <h5 class="card-header">All Shops</h5>
                                <form action="/order-admin/add-shop" method="GET">
                                    @csrf
                                    <div class="input-group flex-nowrap p-2">
                                        <input type="text" class="form-control" id="ordering_admin_all_items_srch"
                                            placeholder="Search Shops" aria-describedby="addon-wrapping" name="search">
                                        <button type="submit" class="input-group-text btn" id="addon-wrapping">Search</button>
                                    </div>
                                </form>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr class="border-0">
                                                    <th class="border-0">#</th>
                                                    <th class="border-0">Branch Code</th>
                                                    <th class="border-0">Name</th>
                                                    <th class="border-0">Email</th>
                                                    <th class="border-0">Contact</th>
                                                    <th class="border-0">Assigned Rep</th>
                                                    <th class="border-0">Price Range</th>
                                                    <th class="border-0">Morning Route</th>
                                                    <th class="border-0">Evening Route</th>
                                                    <th class="border-0">Order Time</th>
                                                    <th class="border-0">Type</th>
                                                    <th class="border-0">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($shops as $shop)
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
                                                    <td>{{ $shop->email }}</td>
                                                    <td>{{ $shop->contact }}</td>
                                                    <td>{{ $shop->rep_name }}</td>
                                                    <td>{{ $shop->price_range }}</td>
                                                    <td>{{ $shop->morning_route }}</td>
                                                    <td>{{ $shop->evening_route }}</td>
                                                    <td>{{ $shop->order_time }}</td>
                                                    <td>{{ $shop->type }}</td>
                                                    <td class="d-inline-flex">
                                                        <!-- Update Button -->
                                                        <button type="button" class="btn btn-sm btn-outline-primary mr-2" data-toggle="modal" data-target="#updateModal{{ $shop->id }}">
                                                            Update
                                                        </button>
                                                        <!-- Delete Button -->
                                                        <a onclick="return confirmDelete();" href="{{ route('shops.delete',['id' => $shop->id]) }}" class="btn btn-sm btn-outline-danger">Delete</a>
                                                    </td>
                                                </tr>

                                                <!-- Update Modal -->
                                                <div class="modal fade" id="updateModal{{ $shop->id }}" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel{{ $shop->id }}" aria-hidden="true">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            <form action="{{ route('shops.update', $shop->id) }}" method="POST">
                                                                @csrf
                                                                @method('PUT')
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="updateModalLabel{{ $shop->id }}">Update Shop</h5>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="form-group">
                                                                        <label for="branch_code">Branch Code</label>
                                                                        <input type="text" class="form-control" name="branch_code" value="{{ $shop->branch_code }}" required>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="email">Email</label>
                                                                        <input type="email" class="form-control" name="email" value="{{ $shop->email }}" required>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="price_range">Price Range</label>
                                                                        <select class="form-control" id="sp_price_range" name="price_range">
                                                                            <option value="" @selected($shop->price_range =='' )>Select Price Range</option>
                                                                            <option value="Unit Price" @selected($shop->price_range =='Unit Price' )>Unit Price</option>
                                                                            <option value="PB MRP" @selected($shop->price_range =='PB MRP' )>PB MRP</option>
                                                                            <option value="PB Direct Sale Price" @selected($shop->price_range =='PB Direct Sale Price' )>PB Direct Sale Price</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="morning_route">Morning Route</label>
                                                                        <select class="form-control" id="sp_route" name="morning_route">
                                                                            <option value="" @selected($shop->morning_route =='' )>Select Morning Route</option>
                                                                            @foreach ($morning_routes as $morning_route)
                                                                            <option value="{{$morning_route->name}}" @selected($shop->morning_route == $morning_route->name )>{{$morning_route->name}}</option>
                                                                            @endforeach
                                                                            <option value="none" @selected($shop->morning_route == 'none' )>None</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="evening_route">Evening Route</label>
                                                                        <select class="form-control" id="sp_route" name="evening_route">
                                                                            <option value="" @selected($shop->evening_route == '' )>Select Evening Route</option>
                                                                            @foreach ($evening_routes as $evening_route)
                                                                            <option value="{{$evening_route->name}}" @selected($shop->evening_route == $evening_route->name )>{{$evening_route->name}}</option>
                                                                            @endforeach
                                                                            <option value="none" @selected($shop->evening_route == 'none' )>None</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="order_time">Order Time</label>
                                                                        <select class="form-control" id="sp_order_time" name="order_time">
                                                                            <option value="" @selected($shop->order_time == '' )>Select Order Time</option>
                                                                            <option value="Morning" @selected($shop->order_time == 'Morning' )>Morning</option>
                                                                            <option value="Evening" @selected($shop->order_time == 'Evening' )>Evening</option>
                                                                            <option value="All" @selected($shop->order_time == 'All' )>All</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="type">Shop Type</label>
                                                                        <select class="form-control" id="sp_shop_type" name="type">
                                                                            <option value="" @selected($shop->type == '' )>Select Shop Type</option>
                                                                            <option value="Outlet" @selected($shop->type == 'Outlet' )>Outlet</option>
                                                                            <option value="Route Rep" @selected($shop->type == 'Route Rep' )>Route Rep</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <input type="hidden" name="name" value="{{ $shop->name }}">
                                                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal">Close</button>
                                                                    <button type="submit" class="btn btn-sm btn-outline-primary">Save changes</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                                <tr>
                                                    <td></td>
                                                    <td colspan="7">{{ $shops->links() }}</td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

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
        // Store the scroll position before the page unloads
        window.addEventListener('beforeunload', function() {
            sessionStorage.setItem('scrollPosition', window.scrollY);
        });

        // Restore the scroll position when the page loads
        window.addEventListener('load', function() {
            const scrollPosition = sessionStorage.getItem('scrollPosition');
            if (scrollPosition) {
                window.scrollTo(0, parseInt(scrollPosition, 10));
                sessionStorage.removeItem('scrollPosition'); // Optionally clear the stored position
            }
        });

        function confirmDelete() {
            return confirm('Are you sure you want to delete this item?');
        }
    </script>
@endpush
