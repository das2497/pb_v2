@extends('layouts.dashboard', ['headerView' => 'rep.components.header', 'menuView' => 'rep.components.menu'])

@section('title', 'Order Admin | Cart')

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
                            <h2 class="pageheader-title"> Under Review Orders View </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/rep/dashboard" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Under Review Orders View</li>
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
                        <div class="col-12">
                            <div class="card">
                                <h5 class="card-header">Under Review Orders View</h5>
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
                                <div class="input-group flex-nowrap p-2">
                                    <input type="text" class="form-control" id="" placeholder="Search Orders" aria-describedby="addon-wrapping">
                                    <span class="input-group-text btn" id="addon-wrapping" onclick="">Search</span>
                                    <a class="btn btn-outline-success m-l-10" href="{{route('rep.under-review-orders-add-items',['order_number'=>$order_number])}}">
                                        <span class="bi bi-plus-square fs-4"></span> Add product
                                    </a>
                                    <a href="/rep/under-review-order" class="btn btn-outline-danger m-l-10">Go Back</a>
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
                                                    <th class="border-0">Quantity</th>
                                                    <th class="border-0">Remarke</th>
                                                    <th class="border-0">Price</th>
                                                    <th class="border-0"></th>
                                                    <th class="border-0"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                $total = 0;
                                                @endphp
                                                @foreach ($items as $item)
                                                <tr>
                                                    <td>{{$loop->iteration}}</td>
                                                    <td><img src="{{asset('assets/images/item-images/'.$item->img)}}" alt="" width="50" /></td>
                                                    <td>{{$item->item_number}}</td>
                                                    <td>{{$item->name_english}}</td>
                                                    <form action="/rep/under-review-orders-update" method="POST">
                                                        @csrf
                                                        <td><input type="number" class="form-control" min="0" step="0.01" required pattern="[0-9]+(\.[0-9]+)?" value="{{$item->qty}}" name="qty"></td>
                                                        <td><input type="text" class="form-control" value="{{$item->remarke}}" name="remarke"></td>
                                                        <td>රු. {{number_format($item->price*$item->qty,2)}}</td>
                                                        <input type="hidden" name="item_number" value="{{$item->item_number}}" />
                                                        <input type="hidden" name="order_number" value="{{$item->order_number}}" />
                                                        <input type="hidden" name="shop" value="{{$shop}}" />
                                                        <td><button type="submit" class="btn btn-outline-warning m-l-10">Update</button></td>
                                                        <td><button type="submit" formaction="/rep/under-review-orders-delete" onclick="return confirmDelete();" class="btn btn-outline-danger m-l-10">Delete</button></td>
                                                    </form>
                                                </tr>
                                                @php
                                                $total += $item->price*$item->qty;
                                                @endphp
                                                @endforeach
                                                <tr>
                                                    <td colspan="4"></td>
                                                    <th class="text-right">Total : </th>
                                                    <th>රු. {{number_format($total,2)}}</th>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
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
