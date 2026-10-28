<div class="card-body p-0">
    <form action="{{ route('shop.scheduled-orders-add-items-process') }}" method="POST" id="bakery-order-form">
        @csrf
        <input type="hidden" name="order_number" value="{{ $order_number }}">

        @if ($products->isEmpty())
        <div class="bakery-empty-cart">
            <div class="bakery-empty-cart-icon">🔍</div>
            <h4>No products found</h4>
            <p class="text-muted mb-0">Try a different search term, or switch to "All category".</p>
        </div>
        @else
        <div class="bakery-label-list">
            @foreach($products as $product)
            @php
                $inOrder = isset($cart_items) && $cart_items->contains('item_number', $product->item_number);
                $orderQty = $inOrder ? $cart_items->firstWhere('item_number', $product->item_number)->qty : 0;
            @endphp
            <div class="bakery-label-chip {{ $inOrder ? 'in-cart' : '' }}" title="{{ $product->name_english }}">
                <div class="bakery-label-top">
                    <img class="bakery-label-img" src="{{ asset('assets/images/item-images/'.$product->img) }}" alt="" loading="lazy">
                    <span class="bakery-label-name">{{ $product->name_english }}</span>
                </div>
                <div class="bakery-label-bottom">
                <span class="bakery-label-price">රු. {{ number_format($product->unit_price, 2) }}</span>
                @if ($inOrder)
                <span class="bakery-label-qty-value">x{{ rtrim(rtrim(number_format($orderQty, 2), '0'), '.') }}</span>
                <span class="bakery-badge-chip-cart"><i class="fa fa-check"></i> Added</span>
                @else
                <div class="bakery-qty-stepper">
                    <button type="button" class="bakery-qty-btn bakery-qty-minus" aria-label="Decrease quantity">&minus;</button>
                    <input class="bakery-label-qty" type="number" name="qty[{{ $product->item_number }}]" min="0" step="0.01" required pattern="[0-9]+(\.[0-9]+)?" value="0">
                    <button type="button" class="bakery-qty-btn bakery-qty-plus" aria-label="Increase quantity">+</button>
                </div>
                <label class="switch">
                    <input type="checkbox" class="bakery-select-checkbox" name="selected_items[]" value="{{ $product->item_number }}">
                    <span class="slider"></span>
                </label>
                @endif
                </div>
            </div>
            @endforeach
        </div>

        <div class="p-3 border-top">
            <button type="submit" class="btn btn-outline-success"><i class="fa fa-cart-plus"></i> Add selected items to scheduled order</button>
        </div>
        @endif
    </form>
</div>
