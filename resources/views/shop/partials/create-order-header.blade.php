All Products
@if ($period && $products->count())
<span class="bakery-cart-count">{{ $products->count() }}</span>
@endif
<a href="/shop/cart?shop={{ $my_shop }}" class="btn btn-outline-warning m-l-10 float-right cart-btn"><i class="fa fa-shopping-cart"></i> Cart</a>
