@extends('layouts.dashboard', ['headerView' => 'order-admin.components.header', 'menuView' => 'order-admin.components.menu'])

@section('title', 'Order Admin | Cart')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/order-admin-order-theme.css') }}">
@endpush

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
                            <h2 class="pageheader-title">
                                Cart
                                @if ($carts->isNotEmpty())
                                <span class="oa-order-cart-count">{{ $carts->count() }} item{{ $carts->count() === 1 ? '' : 's' }}</span>
                                @endif
                            </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/order-admin/dashboard" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="/order-admin/create-order?shop={{ $shop->branch_code }}" class="breadcrumb-link">Create Order</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Cart</li>
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
                    @php $total = 0; @endphp

                    <div class="row">
                        <div class="col-12 col-md-12">
                            <div class="card oa-order-card">
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
                                    {{ $shop->name }}'s Cart
                                    <a href="/order-admin/create-order?shop={{ $shop->branch_code }}" class="btn btn-outline-primary float-right"><i class="fa fa-arrow-left"></i> Add More Items</a>
                                </h5>

                                @if ($carts->isEmpty())
                                <div class="oa-order-empty-state">
                                    <div class="oa-order-empty-state-icon">🛒</div>
                                    <h4>This cart is empty</h4>
                                    <p class="text-muted">Browse the menu and add some items to get started.</p>
                                    <a href="/order-admin/create-order?shop={{ $shop->branch_code }}" class="btn btn-outline-success"><i class="fa fa-cart-plus"></i> Start an Order</a>
                                </div>
                                @else
                                <div class="card-body p-0">
                                    @if ($carts->first()->price_range)
                                    <div class="oa-order-price-tier-note"><i class="fa fa-tag"></i> Pricing tier: <strong>{{ $carts->first()->price_range }}</strong></div>
                                    @endif

                                    <div class="oa-order-cart-list">
                                        @foreach ($carts as $cart)
                                        @php $total += $cart->price * $cart->qty; @endphp
                                        <form action="/order-admin/cart/update-qty" method="POST" class="oa-order-cart-item">
                                            @csrf
                                            <img class="oa-order-cart-item-img" src="{{ asset('assets/images/item-images/' . $cart->img) }}" alt="" loading="lazy">
                                            <div class="oa-order-cart-item-info">
                                                <div class="oa-order-cart-item-name">{{ $cart->name_english }}</div>
                                                <div class="oa-order-cart-item-id">#{{ $cart->item_number }} &middot; රු. {{ number_format($cart->price, 2) }} each</div>
                                            </div>
                                            <div class="oa-order-qty-stepper oa-order-qty-stepper-lg">
                                                <button type="button" class="oa-order-qty-btn oa-order-qty-minus" aria-label="Decrease quantity">&minus;</button>
                                                <input type="number" name="qty" value="{{ $cart->qty }}" min="0" step="0.01" required pattern="[0-9]+(\.[0-9]+)?">
                                                <button type="button" class="oa-order-qty-btn oa-order-qty-plus" aria-label="Increase quantity">+</button>
                                            </div>
                                            <input type="text" class="form-control oa-order-cart-item-note" name="remarke" value="{{ $cart->remarke }}" placeholder="Note (optional)">
                                            <div class="oa-order-cart-item-total">රු. {{ number_format($cart->price * $cart->qty, 2) }}</div>
                                            <input type="hidden" name="item_number" value="{{ $cart->item_number }}" />
                                            <input type="hidden" name="branch_code" value="{{ $cart->shop_bc_number }}">
                                            <div class="oa-order-cart-item-actions">
                                                <button type="submit" class="btn btn-outline-primary btn-sm" title="Update this item"><i class="fa fa-check"></i> Update</button>
                                                <button type="submit" formaction="/order-admin/cart/delete-item" onclick="return confirmDelete();" class="btn btn-outline-danger btn-sm" title="Remove this item"><i class="fa fa-trash"></i></button>
                                            </div>
                                        </form>
                                        @endforeach
                                    </div>

                                    <div class="oa-order-cart-total-row">
                                        <span>Total Price</span>
                                        <strong>රු. {{ number_format($total, 2) }}</strong>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        @if ($carts->isNotEmpty())
                        <div class="col-12 cart-actions-card">
                            <div class="card oa-order-card">
                                <h5 class="card-header">Order Summary</h5>
                                <div class="card-body">
                                    <form action="/order-admin/cart/order-process" method="POST">
                                        @csrf
                                        <div class="row">
                                            <div class="col-12 m-b-10">
                                                <label for="order_note_field" class="oa-order-form-label">Special Note</label>
                                                <textarea id="order_note_field" class="form-control" name="note" rows="2" placeholder="Anything we should know about this order? (optional)"></textarea>
                                            </div>

                                            @if ($period)
                                            <input type="hidden" name="order_time" value="{{ $period }}">
                                            @else
                                            <div class="col-12 m-b-10">
                                                <div class="alert alert-warning mb-0">Select an order time on the <a href="/order-admin/create-order?shop={{ $shop->branch_code }}">Create Order</a> page first.</div>
                                            </div>
                                            @endif
                                        </div>

                                        <div class="oa-order-cart-total-row mt-3">
                                            <span>Total Price</span>
                                            <strong>රු. {{ number_format($total, 2) }}</strong>
                                        </div>

                                        <input type="hidden" name="total" value="{{ $total }}">
                                        <input type="hidden" name="shop" value="{{ $shop->branch_code }}">
                                        <button id="proceedBtn" type="submit" class="btn btn-outline-success px-4 w-100 mt-3" @disabled(!$period)>
                                            <i class="fa fa-check-circle"></i> Proceed
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>

        @if ($carts->isNotEmpty())
        <div class="cart-sticky-bar">
            <div class="cart-sticky-bar-total">
                <span class="cart-sticky-bar-label">Total</span>
                <strong>රු. {{ number_format($total, 2) }}</strong>
            </div>
            <button type="button" class="btn btn-outline-success px-4" onclick="document.getElementById('proceedBtn').click();" @disabled(!$period)>Proceed</button>
        </div>
        @endif
    </div>

@endsection

@push('scripts')
<script src="{{ asset('assets/js/order-admin-order-theme.js') }}"></script>
<script>
    function confirmDelete() {
        return confirm('Are you sure you want to remove this item from the cart?');
    }
</script>
@endpush
