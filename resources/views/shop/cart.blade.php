@extends('layouts.shop-dashboard')

@section('title', 'Shop | Cart')

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
                                🛒 Cart
                                @if ($carts->isNotEmpty())
                                <span class="bakery-cart-count">{{ $carts->count() }} item{{ $carts->count() === 1 ? '' : 's' }}</span>
                                @endif
                            </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/shop/dashboard" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="/shop/create-order" class="breadcrumb-link">Create Order</a></li>
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
                                @elseif (session('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                                @endif
                                <h5 class="card-header">{{$shop->name}}'s Cart <a href="/shop/create-order" class="btn btn-outline-danger float-right"><i class="fa fa-arrow-left"></i> Add More Items</a></h5>

                                @if ($carts->isEmpty())
                                <div class="bakery-empty-cart">
                                    <div class="bakery-empty-cart-icon">🧁</div>
                                    <h4>Your cart is empty</h4>
                                    <p class="text-muted">Browse the menu and add some bakery items to get started.</p>
                                    <a href="/shop/create-order" class="btn btn-outline-success"><i class="fa fa-cart-plus"></i> Start an Order</a>
                                </div>
                                @else
                                <div class="card-body p-0">
                                    @if ($carts->first()->price_range)
                                    <div class="bakery-price-tier-note"><i class="fa fa-tag"></i> Pricing tier: <strong>{{ $carts->first()->price_range }}</strong></div>
                                    @endif

                                    <div class="bakery-cart-list">
                                        @foreach ($carts as $cart)
                                        @php $total += $cart->price * $cart->qty; @endphp
                                        <form action="/shop/cart/update-qty" method="POST" class="bakery-cart-item">
                                            @csrf
                                            <img class="bakery-cart-item-img" src="{{ asset('assets/images/item-images/' . $cart->img) }}" alt="" loading="lazy">
                                            <div class="bakery-cart-item-info">
                                                <div class="bakery-cart-item-name">{{ $cart->name_english }}</div>
                                                <div class="bakery-cart-item-id">#{{ $cart->item_number }} &middot; රු. {{ number_format($cart->price, 2) }} each</div>
                                            </div>
                                            <div class="bakery-qty-stepper bakery-qty-stepper-lg">
                                                <button type="button" class="bakery-qty-btn bakery-qty-minus" aria-label="Decrease quantity">&minus;</button>
                                                <input type="number" name="qty" value="{{$cart->qty}}" min="0" step="0.01" required pattern="[0-9]+(\.[0-9]+)?">
                                                <button type="button" class="bakery-qty-btn bakery-qty-plus" aria-label="Increase quantity">+</button>
                                            </div>
                                            <input type="text" class="form-control bakery-cart-item-note" name="remarke" value="{{$cart->remarke}}" placeholder="Note (optional)">
                                            <div class="bakery-cart-item-total">රු. {{ number_format($cart->price*$cart->qty, 2) }}</div>
                                            <input type="hidden" name="item_number" value="{{$cart->item_number}}" />
                                            <input type="hidden" name="branch_code" value="{{$cart->shop_bc_number}}">
                                            <div class="bakery-cart-item-actions">
                                                <button type="submit" class="btn btn-outline-primary btn-sm" title="Update this item"><i class="fa fa-check"></i> Update</button>
                                                <button type="submit" formaction="/shop/cart/delete-item" onclick="return confirmDelete();" class="btn btn-outline-danger btn-sm" title="Remove this item"><i class="fa fa-trash"></i></button>
                                            </div>
                                        </form>
                                        @endforeach
                                    </div>

                                    <div class="bakery-cart-total-row">
                                        <span>Total Price</span>
                                        <strong>රු. {{ number_format($total, 2) }}</strong>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        @if ($carts->isNotEmpty())
                        <div class="col-12 cart-actions-card">
                            <div class="card">
                                <h5 class="card-header">Order Summary</h5>
                                <div class="card-body">
                                    <form action="/shop/cart/order-process" method="POST">
                                        @csrf
                                        <div class="row">
                                            <div class="col-12 col-md-6 m-b-10">
                                                <label for="order_note_field" class="bakery-form-label" id="note">Special Note</label>
                                                <textarea id="order_note_field" class="form-control" name="note" rows="2" placeholder="Anything we should know about this order? (optional)"></textarea>
                                            </div>

                                            @if ($period)
                                            <input type="hidden" name="order_time" value="{{ $period }}">
                                            @else
                                            <div class="col-12 m-b-10">
                                                <div class="alert alert-warning mb-0">Select an order time on the <a href="/shop/create-order">Create Order</a> page first (not needed for Default Orders).</div>
                                            </div>
                                            @endif

                                            <div class="col-12 col-md-6 m-b-10">
                                                <div class="bakery-default-order-toggle">
                                                    <label class="switch">
                                                        <input type="checkbox" onchange="default_name();" name="default" id="default">
                                                        <span class="slider"></span>
                                                    </label>
                                                    <div>
                                                        <div class="bakery-form-label">Save as Default Order</div>
                                                        <small class="text-muted">Repeats automatically every time - skips picking an order time.</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="bakery-cart-total-row mt-3">
                                            <span>Total Price</span>
                                            <strong>රු. {{ number_format($total, 2) }}</strong>
                                        </div>

                                        <input type="hidden" name="total" value="{{$total}}">
                                        <input type="hidden" name="shop" value="{{$shop->branch_code}}">
                                        <button id="proceedBtn" class="btn btn-outline-success px-4 w-100 mt-3"><i class="fa fa-check-circle"></i> Proceed</button>
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
                <strong>රු. {{number_format($total,2)}}</strong>
            </div>
            <button type="button" class="btn btn-outline-success px-4" onclick="document.getElementById('proceedBtn').click();">Proceed</button>
        </div>
        @endif
    </div>

@endsection

@push('scripts')
<script>
        function confirmDelete() {
            return confirm('Are you sure you want to remove this item from your cart?');
        }

        function default_name() {
            let isCheck = document.getElementById('default').checked;
            let note = document.getElementById('note');
            note.textContent = isCheck ? 'Default Order Name' : 'Special Note';
        }
    </script>
@endpush
