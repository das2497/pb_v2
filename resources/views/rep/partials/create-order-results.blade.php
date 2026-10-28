@if (!$my_shop)
<div class="rep-order-empty-state">
    <div class="rep-order-empty-state-icon">🏬</div>
    <h4>Pick a shop to get started</h4>
    <p class="text-muted mb-0">Choose a shop above to see what's available to order for them.</p>
</div>
@elseif (!$period)
<div class="rep-order-empty-state">
    <div class="rep-order-empty-state-icon">⏰</div>
    <h4>Pick an order time to get started</h4>
    <p class="text-muted mb-0">Choose a time above to see what's available to order.</p>
</div>
@else
<div class="card-body p-0">
    <form action="{{ route('rep.add-to-cart-all') }}" method="POST" id="rep-order-form">
        @csrf
        <input type="hidden" name="branch_code" value="{{ $my_shop }}">
        <input type="hidden" name="form_token" value="{{ $form_token }}">

        @if ($products->isEmpty())
        <div class="rep-order-empty-state">
            <div class="rep-order-empty-state-icon">🔍</div>
            <h4>No products found</h4>
            <p class="text-muted mb-0">Try a different search term, or switch to "All category".</p>
        </div>
        @else
        <div class="rep-order-label-list">
            @foreach($products as $product)
            @php
                $inCart = isset($cart_item) && $cart_item->contains('item_number', $product->item_number);
                $cartQty = $inCart ? $cart_item->firstWhere('item_number', $product->item_number)->qty : 0;
                $price = $selected_shop ? $product->priceFor($selected_shop) : $product->unit_price;
            @endphp
            <div class="rep-order-label-chip {{ $inCart ? 'in-cart' : '' }}" title="{{ $product->name_english }}">
                <div class="rep-order-label-top">
                    <img class="rep-order-label-img" src="{{ asset('assets/images/item-images/'.$product->img) }}" alt="" loading="lazy">
                    <span class="rep-order-label-name">{{ $product->name_english }}</span>
                </div>
                <div class="rep-order-label-bottom">
                <span class="rep-order-label-price">රු. {{ number_format($price, 2) }}</span>
                @if ($inCart)
                <span class="rep-order-label-qty-value">x{{ rtrim(rtrim(number_format($cartQty, 2), '0'), '.') }}</span>
                <span class="rep-order-badge-chip-cart"><i class="fa fa-check"></i> In cart</span>
                @else
                <div class="rep-order-qty-stepper">
                    <button type="button" class="rep-order-qty-btn rep-order-qty-minus" aria-label="Decrease quantity">&minus;</button>
                    <input class="rep-order-label-qty" type="number" name="qty[{{ $product->item_number }}]" min="0" step="0.01" required pattern="[0-9]+(\.[0-9]+)?" value="0">
                    <button type="button" class="rep-order-qty-btn rep-order-qty-plus" aria-label="Increase quantity">+</button>
                </div>
                <label class="rep-order-switch">
                    <input type="checkbox" class="rep-order-select-checkbox" name="selected_items[]" value="{{ $product->item_number }}">
                    <span class="slider"></span>
                </label>
                @endif
                </div>
            </div>
            @endforeach
        </div>

        <div class="p-3 border-top">
            <button type="submit" class="btn btn-outline-success" id="rep_order_form_submit"><i class="fa fa-cart-plus"></i> Add selected items to cart</button>
        </div>
        @endif
    </form>
</div>
@endif
