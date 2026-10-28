@extends('layouts.shop-dashboard')

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
                            <h2 class="pageheader-title"> Pending Orders View </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/shop/dashboard" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Pending Orders View</li>
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
                                <h5 class="card-header">Pending Orders View </h5>
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
                                    <a class="btn btn-outline-success m-l-10" href="{{route('shop.pending-orders-add-items',['order_number'=>$order_number])}}">
                                        <span class="bi bi-plus-square fs-4"></span> Add product
                                    </a>
                                    <a href="/shop/pending-orders" class="btn btn-outline-danger m-l-10">Go Back</a>
                                </div>
                                <div class="card-body p-0">
                                    {{-- A <form> can't legally live inside a <tr>/<tbody> (browsers foster-parent
                                         it out of the table, which silently orphaned the Update/Delete buttons
                                         below from any form at all). Each row's Update/Delete form is declared
                                         here instead and linked to its row's inputs/buttons via the HTML5
                                         form="" attribute - same fix as rep/pending-orders-view.blade.php. --}}
                                    @foreach ($items as $item)
                                    <form id="itemForm{{ $loop->index }}" action="/shop/pending-orders-update" method="POST" class="d-none">
                                        @csrf
                                        <input type="hidden" name="item_number" value="{{ $item->item_number }}">
                                        <input type="hidden" name="order_number" value="{{ $item->order_number }}">
                                        <input type="hidden" name="shop" value="{{ $shop }}">
                                    </form>
                                    @endforeach
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
                                                    <td><input form="itemForm{{ $loop->index }}" type="number" class="form-control" value="{{$item->qty}}" name="qty" min="0" step="0.01" required pattern="[0-9]+(\.[0-9]+)?" style="min-width: 100px;"></td>
                                                    <td><input form="itemForm{{ $loop->index }}" type="text" class="form-control" value="{{$item->remarke}}" name="remarke" style="min-width: 200px;"></td>
                                                    <td>රු. {{number_format($item->price*$item->qty,2)}}</td>
                                                    <td><button form="itemForm{{ $loop->index }}" type="submit" class="btn btn-outline-warning m-l-10">Update</button></td>
                                                    <td><button form="itemForm{{ $loop->index }}" type="submit" formaction="/shop/pending-orders-delete" onclick="return confirmDelete();" class="btn btn-outline-danger m-l-10">Delete</button></td>
                                                </tr>
                                                @php
                                                $total += $item->price*$item->qty;
                                                @endphp
                                                @endforeach
                                                <tr>
                                                    <td colspan="5"></td>
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
