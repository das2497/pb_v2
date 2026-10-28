All Products
@if ($products->count())
<span class="bakery-cart-count">{{ $products->count() }}</span>
@endif
&mdash; for {{ \Carbon\Carbon::parse($order->order_date)->format('Y-m-d') }} ({{ $order->time_period }})
<a href="{{ route('shop.scheduled-orders-view', ['id' => $order->unique_id]) }}" class="btn btn-outline-danger m-l-10 float-right">Go Back</a>
