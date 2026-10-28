@extends('layouts.dashboard', ['headerView' => 'order-admin.components.header', 'menuView' => 'order-admin.components.menu'])

@section('title', 'Order Admin | Rep Assign Shop')

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
                            <h2 class="pageheader-title"> Shop Assign </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/order-admin/dashboard" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Shop Assign</li>
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
                                <h5 class="card-header">Assigned Shops</h5>
                                <form action="/order-admin/rep-assign" method="GET">
                                    @csrf
                                    <div class="input-group flex-nowrap p-2">
                                        <input type="text" class="form-control" id="ordering_admin_all_items_srch"
                                            placeholder="Search" aria-describedby="addon-wrapping" name="search">
                                        <button type="submit" class="input-group-text btn" id="addon-wrapping">Search</button>
                                    </div>
                                </form>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr class="border-0">
                                                    <th class="border-0">#</th>
                                                    <th class="border-0">Shop Name</th>
                                                    <th class="border-0">Rep Name</th>
                                                    @if (Auth::user()->role != 'view')
                                                    
                                                    @endif
                                                    <th class="border-0">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($datas as $key => $data)
                                                <tr>
                                                    <td>{{$loop->iteration}}</td>
                                                    <td>{{$data->shop_name}}</td>
                                                    <td>{{$data->rep_name}}</td>
                                                </tr>
                                                @endforeach
                                                <tr>
                                                    <td colspan="3">{{$datas->links()}}</td>
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
                                <h5 class="card-header">Assign Shops To Representative</h5>
                                <div class="card-body p-0">
                                    <form action="/order-admin/rep-assign" method="POST" class="p-2">
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
                                            <label for="assign_rep">Select Rep</label>
                                            <select class="form-control" id="assign_rep" name="rep_id">
                                                <option value="">Select Rep</option>
                                                @foreach ($reps as $rep)
                                                <option value="{{$rep->id}}">{{$rep->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="assign_shop">Select Shop</label>
                                            <select class="form-control" id="assign_shop" name="shop_id">
                                                <option value="">Select Shop</option>
                                                @foreach ($shops as $shop)
                                                <option value="{{$shop->id}}">{{$shop->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <button class="btn btn-primary" type="submit">Assign</button>
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
                                <h5 class="card-header">Assigned Shops</h5>
                                <form action="/order-admin/rep-assign" method="GET">
                                    @csrf
                                    <div class="input-group flex-nowrap p-2">
                                        <input type="text" class="form-control" id="ordering_admin_all_items_srch"
                                            placeholder="Search" aria-describedby="addon-wrapping" name="search">
                                        <button type="submit" class="input-group-text btn" id="addon-wrapping">Search</button>
                                    </div>
                                </form>
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
                                <div class="card-body p-0">
                                    {{-- A <form> can't legally live inside a <tr>/<tbody> (browsers
                                         foster-parent it out of the table, which silently orphaned the
                                         select/Update button below from any form at all). Each row's form
                                         is declared here instead and linked to its row's select/button via
                                         the HTML5 form="" attribute - same fix as
                                         rep/pending-orders-view.blade.php. --}}
                                    @foreach ($datas as $data)
                                    <form id="assignForm{{ $loop->index }}" action="/order-admin/rep-assign-update" method="POST" class="d-none">
                                        @csrf
                                        <input type="hidden" value="{{ $data->shop_id }}" name="shop_id">
                                    </form>
                                    @endforeach
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr class="border-0">
                                                    <th class="border-0">#</th>
                                                    <th class="border-0">Shop Name</th>
                                                    <th class="border-0">Rep Name</th>
                                                    <th class="border-0">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($datas as $key => $data)
                                                <tr>
                                                    <td>{{$loop->iteration}}</td>
                                                    <td>{{$data->shop_name}}</td>
                                                    <td>
                                                        <select form="assignForm{{ $loop->index }}" class="form-control" id="assign_rep" name="rep_id">
                                                            @foreach ($reps as $rep)
                                                            <option value="{{$rep->id}}" @selected($rep->name == $data->rep_name)>{{$rep->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <button form="assignForm{{ $loop->index }}" class="btn btn-outline-info">Update</button>
                                                    </td>
                                                </tr>
                                                @endforeach
                                                <tr>
                                                    <td colspan="3">{{$datas->links()}}</td>
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
