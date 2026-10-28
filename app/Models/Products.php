<?php

namespace App\Models;

use App\Services\PricingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Products extends Model
{
    use HasFactory;

    protected $table="products";

    protected $fillable = [
        'item_number',
        'name_english',
        'name_sinhala',
        'visibility',
        'order_time',
        'category',
        'unit_price',
        'mrp',
        'direct_sale_price',
        'img',
    ];

    /**
     * Matched on the `category` string, not a numeric FK — see
     * Product_category::products() for why. Named categoryRecord() (not
     * category()) since `category` is already a real string column here.
     */
    public function categoryRecord(): BelongsTo
    {
        return $this->belongsTo(Product_category::class, 'category', 'category');
    }

    /**
     * Matched on `item_number`, not a numeric FK — carts has no product_id
     * column.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(Cart::class, 'item_number', 'item_number');
    }

    public function priceFor(Shops $shop): float
    {
        return PricingService::priceFor($this, $shop);
    }

    public static function availableForOrder($itemNumber, $orderNumber): bool
    {
        $order = Orders::where('unique_id', $orderNumber)->first(['time_period', 'default_period']);
        $period = $order && $order->time_period === 'Default' ? $order->default_period : $order?->time_period;

        return static::forPeriod($period)->where('item_number', $itemNumber)->exists();
    }

    public static function incompatibleItemNames($orderNumber, $period)
    {
        if (!OrderTime::where('name', $period)->exists()) {
            return collect();
        }

        return Cart::join('products', 'products.item_number', '=', 'carts.item_number')
            ->where('carts.order_number', $orderNumber)
            ->where(function ($q) use ($period) {
                $q->where('products.order_time', '!=', 'All')
                    ->where('products.order_time', '!=', $period)
                    ->where('products.order_time', 'not like', $period . ',%')
                    ->where('products.order_time', 'not like', '%,' . $period)
                    ->where('products.order_time', 'not like', '%,' . $period . ',%');
            })
            ->pluck('products.name_english');
    }

    public function scopeForPeriod($query, $period)
    {
        if (is_string($period) && $period !== '' && $period !== 'All' && OrderTime::where('name', $period)->exists()) {
            $query->where(function ($q) use ($period) {
                $q->where('order_time', 'All')
                    ->orWhere('order_time', $period)
                    ->orWhere('order_time', 'like', $period . ',%')
                    ->orWhere('order_time', 'like', '%,' . $period)
                    ->orWhere('order_time', 'like', '%,' . $period . ',%');
            });
        }

        return $query;
    }

    /**
     * The specific order times this product is restricted to, as stored
     * (comma-separated, e.g. "Morning,Evening"). Empty when the product
     * is available for every period ('All') - callers should check
     * order_time === 'All' separately rather than relying on this being
     * non-empty.
     */
    public function orderTimeList(): array
    {
        if (!is_string($this->order_time) || $this->order_time === '' || $this->order_time === 'All') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $this->order_time))));
    }
}
