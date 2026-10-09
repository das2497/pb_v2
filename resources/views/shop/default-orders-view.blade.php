@extends('layouts.dashboard', ['headerView' => 'shop.components.header', 'menuView' => 'shop.components.menu'])

@section('title', 'Shop')

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
                            <h2 class="pageheader-title"> Default Orders View </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/order-admin/dashboard" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Default Orders View</li>
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
                                <h5 class="card-header">Default Orders View</h5>
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
                                    <!-- {{ $items }} -->
                                </div>
                                @elseif (session('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                                @endif
                                <div class="input-group flex-nowrap p-2">
                                    <input type="text" class="form-control" id="" placeholder="Search Orders" aria-describedby="addon-wrapping">
                                    <span class="input-group-text btn" id="addon-wrapping" onclick="">Search</span>
                                    <a href="/shop/cart?shop={{$branch_code}}" class="btn btn-outline-warning m-l-10">Go To Cart</a>
                                    <a class="btn btn-outline-success m-l-10" href="{{route('shop.default-orders-add-items',['order_number'=>$order_number])}}">
                                        <span class="bi bi-plus-square fs-4"></span> Add product
                                    </a>
                                    <a href="/shop/default-orders" class="btn btn-outline-danger m-l-10">Go Back</a>
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
                                                    <form action="/shop/default-orders-update-item" method="POST">
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
                                                        <td><button type="submit" formaction="/shop/default-orders-delete-item" onclick="return confirmDelete();"
                                                                class="btn btn-outline-danger">Delete</button></td>
                                                    </form>
                                                </tr>
                                                @php
                                                $total += $item->price*$item->qty;
                                                @endphp
                                                @endforeach
                                                <tr>
                                                    <td colspan="7"></td>
                                                    <th class="text-right">Total : </th>
                                                    <th>රු. {{number_format($total,2)}}</th>
                                                </tr>
                                                <tr>
                                                    <td colspan="8"></td>
                                                    <td>
                                                        <form action="{{ route('shop.default-orders-add-to-cart') }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" value="{{$order_number}}" name="id">
                                                            <button type="submit" class="btn btn-outline-primary" onclick="return confirmAddToCart();">Add To Cart</button>
                                                        </form>

                                                    </td>
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

        function confirmAddToCart() {
            return confirm('Are you sure you want to add this items to cart?');
        }
    </script>
@endpush
