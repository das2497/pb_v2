<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
            ->whereNotIn('products.order_time', [$period, 'All'])
            ->pluck('products.name_english');
    }

    public function scopeForPeriod($query, $period)
    {
        if (is_string($period) && $period !== '' && $period !== 'All' && OrderTime::where('name', $period)->exists()) {
            $query->whereIn('order_time', [$period, 'All']);
        }

        return $query;
    }
}
