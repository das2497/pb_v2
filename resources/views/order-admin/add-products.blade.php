@extends('layouts.dashboard', ['headerView' => 'order-admin.components.header', 'menuView' => 'order-admin.components.menu'])

@section('title', 'Order Admin | Add Products')

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
                            <h2 class="pageheader-title"> Add Products </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/order-admin/dashboard" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Add Products</li>
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

                        @if (Auth::user()->role === 'view')
                        <div class="col-12">
                            <div class="card">
                                @if(session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                                @endif
                                <h5 class="card-header">All Products</h5>
                                <form action="/order-admin/add-products" method="GET">
                                    @csrf
                                    <div class="input-group flex-nowrap p-2">
                                        <input type="text" class="form-control" id="ordering_admin_all_items_srch"
                                            placeholder="Search Products" aria-describedby="addon-wrapping" name="search">
                                        <button type="submit" class="input-group-text btn" id="addon-wrapping">Search</button>
                                    </div>
                                </form>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr class="border-0">
                                                    <th class="border-0">#</th>
                                                    <th class="border-0">Item Image</th>
                                                    <th class="border-0">Item ID</th>
                                                    <th class="border-0">Item Name English</th>
                                                    <th class="border-0">Item Name Sinhala</th>
                                                    <th class="border-0">Category</th>
                                                    <th class="border-0">Visibility</th>
                                                    <th class="border-0">Order Time</th>
                                                    <th class="border-0">PB Unit Price</th>
                                                    <th class="border-0">PB MRP</th>
                                                    <th class="border-0">PB Direct Sale Price</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($products as $product)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        <img
                                                            class="border shadow rounded"
                                                            src="{{ asset('assets/images/item-images/'.$product->img) }}"
                                                            alt="item image"
                                                            style="height: 100px; width: auto;" />
                                                    </td>
                                                    <td>{{ $product->item_number }}</td>
                                                    <td>{{ $product->name_english }}</td>
                                                    <td>{{ $product->name_sinhala }}</td>
                                                    <td>{{ $product->category }}</td>
                                                    <td>{{ $product->visibility }}</td>
                                                    <td>{{ $product->order_time }}</td>
                                                    <td>{{ $product->unit_price }}</td>
                                                    <td>{{ $product->mrp }}</td>
                                                    <td>{{ $product->direct_sale_price }}</td>
                                                </tr>
                                                @endforeach
                                                <tr>
                                                    <td></td>
                                                    <td colspan="9">{{ $products->links() }}</td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                @if(session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                                @endif
                                <h5 class="card-header">All Categories</h5>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr class="border-0">
                                                    <th class="border-0">#</th>
                                                    <th class="border-0">Category</th>
                                                    <th class="border-0">Main Category</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($categories as $category)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $category->category }}</td>
                                                    <td>{{ $category->main_category }}</td>
                                                </tr>
                                                @endforeach
                                                <tr>
                                                    <td></td>
                                                    <td colspan="9">{{ $categories->links() }}</td>
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
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <h5 class="card-header">Add Products</h5>
                                        <div class="card-body p-0">
                                            <form action="{{route('order-admin-add-products')}}" method="POST" enctype="multipart/form-data" class="p-2">
                                                @csrf
                                                <div class="form-group">
                                                    <label for="file" class="col-form-label">Item image</label>
                                                    <input name="file" id="file" type="file" class="form-control" value="{{old('file')}}">
                                                </div>
                                                <div class="form-group">
                                                    <label for="inputText3" class="col-form-label">Item Number</label>
                                                    <input name="item_number" id="item_num" type="text" class="form-control" placeholder="Item Number" value="{{old('item_number')}}">
                                                </div>
                                                <div class="form-group">
                                                    <label for="inputEmail">Item Name English</label>
                                                    <input name="item_name_e" id="item_name_e" type="text" placeholder="Item Name" class="form-control" value="{{old('item_name_e')}}">
                                                </div>
                                                <div class="form-group">
                                                    <label for="inputEmail">Item Name Sinhala</label>
                                                    <input name="item_name_s" id="item_name_s" type="text" placeholder="Item Name" class="form-control" value="{{old('item_name_s')}}">
                                                </div>
                                                <div class="form-group">
                                                    <label for="input-select">Product Category</label>
                                                    <select class="form-control" id="category" name="category">
                                                        <option value="">Select Category</option>
                                                        @foreach ($product_categories_drop_down as $category)
                                                        <option value="{{$category->category}}">{{$category->category}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="input-select">Select Visibility</label>
                                                    <select class="form-control" id="visibility" name="visibility">
                                                        <option value="">Select Visibility</option>
                                                        <option value="All">all</option>
                                                        <option value="Rep">Rep</option>
                                                        <option value="Shop">Shop</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="order_time">Select Order Time</label>
                                                    <select class="form-control" id="order_time" name="order_time" required>
                                                        <option value="">Select Order Time</option>
                                                        @foreach ($order_times as $ot)
                                                        <option value="{{ $ot->name }}" @selected(old('order_time')===$ot->name)>{{ $ot->name }}</option>
                                                        @endforeach
                                                        <option value="All" @selected(old('order_time')==='All' )>All</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="inputEmail">PB Unit Price</label>
                                                    <input name="pb_unit_price" id="pb_unit_price" type="text" placeholder="PB Unit Price" class="form-control" value="{{old('pb_unit_price')}}">
                                                </div>
                                                <div class="form-group">
                                                    <label for="inputEmail">PB MRP</label>
                                                    <input name="pb_mrp" id="pb_mrp" type="text" placeholder="PB MRP" class="form-control" value="{{old('pb_mrp')}}">
                                                </div>
                                                <div class="form-group">
                                                    <label for="inputEmail">PB Direct Sale price</label>
                                                    <input name="pb_direct_sale_price" id="pb_direct_sale_price" type="text" placeholder="PB Direct Sale price" class="form-control" value="{{old('pb_direct_sale_price')}}">
                                                </div>
                                                <div>
                                                    <button type="submit" class="btn btn-primary">Add</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
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
                                        @endif
                                        <h5 class="card-header">Add Category</h5>
                                        <div class="card-body p-0">
                                            <form action="{{ route('order-admin-add-product-category') }}" method="POST" class="p-2">
                                                @csrf
                                                <div class="form-group">
                                                    <label for="main-category">Main Category</label>
                                                    <select class="form-control" name="main_category" id="main-category">
                                                        <option value="">Select Main Category</option>
                                                        <option value="PB">PB</option>
                                                        <option value="NPB">NPB</option>
                                                        <option value="PB Premium">PB Premium</option>                                                        
                                                        <option value="PBI">PBI</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="item_catgry">Category Name</label>
                                                    <input id="item_catgry" name="category" type="text" placeholder="Type Category Name" class="form-control">
                                                </div>
                                                <div>
                                                    <button class="btn btn-primary" type="submit">Add</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="card">
                                        @if (session('error'))
                                        <div class="alert alert-danger">
                                            {{ session('error') }}
                                        </div>
                                        @endif
                                        <h5 class="card-header">Order Times</h5>
                                        <div class="card-body p-2">
                                            @foreach ($order_times as $ot)
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span>{{ $ot->name }}</span>
                                                @unless (in_array($ot->name, ['Morning', 'Evening'], true))
                                                <form method="POST" action="{{ route('order-admin-delete-order-time', $ot->id) }}">
                                                    @csrf
                                                    <button type="submit" onclick="return confirm('Delete this order time?');" class="btn btn-sm btn-outline-danger">Delete</button>
                                                </form>
                                                @endunless
                                            </div>
                                            @endforeach
                                            <form action="{{ route('order-admin-add-order-time') }}" method="POST" class="mt-3">
                                                @csrf
                                                <div class="form-group">
                                                    <label for="new_order_time">New Order Time</label>
                                                    <input id="new_order_time" name="name" type="text" placeholder="e.g. Night" class="form-control" maxlength="50" required>
                                                </div>
                                                <button class="btn btn-primary" type="submit">Add Order Time</button>
                                            </form>
                                        </div>
                                    </div>
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
                                <h5 class="card-header">All Products</h5>
                                <form action="/order-admin/add-products" method="GET">
                                    @csrf
                                    <div class="input-group flex-nowrap p-2">
                                        <input type="text" class="form-control" id="ordering_admin_all_items_srch"
                                            placeholder="Search Products" aria-describedby="addon-wrapping" name="search">
                                        <button type="submit" class="input-group-text btn" id="addon-wrapping">Search</button>
                                    </div>
                                </form>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr class="border-0">
                                                    <th class="border-0">#</th>
                                                    <th class="border-0">Item Image</th>
                                                    <th class="border-0">Item ID</th>
                                                    <th class="border-0">Item Name English</th>
                                                    <th class="border-0">Item Name Sinhala</th>
                                                    <th class="border-0">Category</th>
                                                    <th class="border-0">Visibility</th>
                                                    <th class="border-0">Order Time</th>
                                                    <th class="border-0">PB Unit Price</th>
                                                    <th class="border-0">PB MRP</th>
                                                    <th class="border-0">PB Direct Sale Price</th>
                                                    <th class="border-0">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($products as $product)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td><img src="{{ asset('assets/images/item-images/'.$product->img) }}" alt="item image" class="img-thumbnail" style="height: 80px;" /></td>
                                                    <td>{{ $product->item_number }}</td>
                                                    <td>{{ $product->name_english }}</td>
                                                    <td>{{ $product->name_sinhala }}</td>
                                                    <td>{{ $product->category }}</td>
                                                    <td>{{ $product->visibility }}</td>
                                                    <td>{{ $product->order_time }}</td>
                                                    <td>{{ $product->unit_price }}</td>
                                                    <td>{{ $product->mrp }}</td>
                                                    <td>{{ $product->direct_sale_price }}</td>
                                                    <td>
                                                        <!-- Update Button -->
                                                        <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#updateModal{{ $product->id }}">
                                                            Update
                                                        </button>
                                                    </td>
                                                    <form method="POST" action="{{route('order-admin-products.delete', $product->id)}}">
                                                        @csrf
                                                        <td><button type="submit" onclick="return confirmDelete();" class="btn btn-outline-danger">Delete</button></td>
                                                    </form>
                                                </tr>

                                                <!-- Update Modal -->
                                                <div class="modal fade" id="updateModal{{ $product->id }}" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel{{ $product->id }}" aria-hidden="true">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            <form action="{{ route('order-admin-products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                                                                @csrf
                                                                @method('PUT')
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="updateModalLabel{{ $product->id }}">Update Product</h5>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="form-group">
                                                                        <label for="item_number">Item ID</label>
                                                                        <input type="text" class="form-control" name="item_number" value="{{ $product->item_number }}" required>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="name_english">Item Name English</label>
                                                                        <input type="text" class="form-control" name="name_english" value="{{ $product->name_english }}" required>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="name_sinhala">Item Name Sinhala</label>
                                                                        <input type="text" class="form-control" name="name_sinhala" value="{{ $product->name_sinhala }}" required>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="category">Category</label>
                                                                        <select class="form-control" id="category" name="category">
                                                                            <option value="">Select Category</option>
                                                                            @foreach ($product_categories_drop_down as $category)
                                                                            <option value="{{ $category->category }}" @selected($product->category==$category->category)>
                                                                                {{ $category->category }}
                                                                            </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <div class="form-group">
                                                                            <label for="input-select">Select Visibility</label>
                                                                            <select class="form-control" id="visibility" name="visibility">
                                                                                <option value="">Select Visibility</option>
                                                                                @if ($product->visibility === "All" )
                                                                                <option value="All" selected>All</option>
                                                                                @else
                                                                                <option value="All">All</option>
                                                                                @endif
                                                                                @if ($product->visibility === "Rep" )
                                                                                <option value="Rep" selected>Rep</option>
                                                                                @else
                                                                                <option value="Rep">Rep</option>
                                                                                @endif
                                                                                @if ($product->visibility === "Shop" )
                                                                                <option value="Shop" selected>Shop</option>
                                                                                @else
                                                                                <option value="Shop">Shop</option>
                                                                                @endif
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="order_time">Select Order Time</label>
                                                                        <select class="form-control" name="order_time" required>
                                                                            @foreach ($order_times as $ot)
                                                                            <option value="{{ $ot->name }}" @selected($product->order_time === $ot->name)>{{ $ot->name }}</option>
                                                                            @endforeach
                                                                            <option value="All" @selected(!$order_times->contains('name', $product->order_time))>All</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="unit_price">PB Unit Price</label>
                                                                        <input type="text" class="form-control" name="unit_price" value="{{ $product->unit_price }}" required>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="mrp">PB MRP</label>
                                                                        <input type="text" class="form-control" name="mrp" value="{{ $product->mrp }}" required>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="direct_sale_price">PB Direct Sale Price</label>
                                                                        <input type="text" class="form-control" name="direct_sale_price" value="{{ $product->direct_sale_price }}" required>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="img">Item Image</label>
                                                                        <input type="file" class="form-control" name="img">
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Close</button>
                                                                    <button type="submit" class="btn btn-outline-primary">Save changes</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                                <tr>
                                                    <td></td>
                                                    <td colspan="9">{{ $products->links() }}</td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                @if(session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                                @endif
                                <h5 class="card-header">All Categories</h5>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr class="border-0">
                                                    <th class="border-0">#</th>
                                                    <th class="border-0">Category</th>
                                                    <th class="border-0">Main Category</th>
                                                    <th class="border-0">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($categories as $category)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $category->category }}</td>
                                                    <td>{{ $category->main_category }}</td>
                                                    <td>
                                                        <!-- Update Button -->
                                                        <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#updateCatModal{{ $category->id }}">
                                                            Update
                                                        </button>
                                                    </td>
                                                    <form method="POST" action="{{route('order-admin-delete-product-category', $product->id)}}">
                                                        @csrf
                                                        <td><button type="submit" onclick="return confirmDelete();" class="btn btn-outline-danger">Delete</button></td>
                                                    </form>
                                                </tr>
                                                <!-- Update Modal -->
                                                <div class="modal fade" id="updateCatModal{{ $category->id }}" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel{{ $category->id }}" aria-hidden="true">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            <form action="{{ route('order-admin-update-product-category', $category->id) }}" method="POST" enctype="multipart/form-data">
                                                                @csrf
                                                                @method('PUT')
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="updateModalLabel{{ $category->id }}">Update Category</h5>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="form-group">
                                                                        <label for="category">Category</label>
                                                                        <input type="text" class="form-control" name="category" value="{{ $category->category }}" required>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="main_category">Main Category</label>
                                                                        <select class="form-control" name="main_category" id="main-category">
                                                                            <option value="">Select Main Category</option>
                                                                            <option value="PB" @selected('PB'==$category->category)>PB</option>
                                                                            <option value="NPB" @selected('NPB'==$category->category)>NPB</option>
                                                                            <option value="PB Premium" @selected('PB Premium'==$category->category)>PB Premium</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Close</button>
                                                                    <button type="submit" class="btn btn-outline-primary">Save changes</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                                <tr>
                                                    <td></td>
                                                    <td colspan="9">{{ $categories->links() }}</td>
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
        function confirmDelete() {
            return confirm('Are you sure you want to delete this item?');
        }
    </script>
@endpush
