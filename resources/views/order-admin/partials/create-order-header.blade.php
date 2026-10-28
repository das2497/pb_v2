All Products
@if ($period && $products->count())
<span class="oa-order-cart-count">{{ $products->count() }}</span>
@endif
<a href="{{ route('order-admin.cart', ['shop' => $my_shop]) }}"
    class="btn btn-outline-warning m-l-10 float-right cart-btn {{ $my_shop ? '' : 'disabled' }}"
    @if (!$my_shop) tabindex="-1" aria-disabled="true" @endif>
    <i class="fa fa-shopping-cart"></i> Cart
</a>
