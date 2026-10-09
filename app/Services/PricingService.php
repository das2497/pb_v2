<?php

namespace App\Services;

use App\Models\Products;
use App\Models\Shops;

/**
 * Centralizes the price_range -> unit/MRP/direct-sale price lookup that
 * was previously copy-pasted (identically) across ~15 methods in
 * OrderAdmin/Rep/Shop controllers.
 */
class PricingService
{
    public static function priceFor(Products $product, Shops $shop): float
    {
        return match ($shop->price_range) {
            'Unit Price' => (float) $product->unit_price,
            'PB MRP' => (float) $product->mrp,
            'PB Direct Sale Price' => (float) $product->direct_sale_price,
            default => 0.0,
        };
    }
}
