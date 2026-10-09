<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'carts';

    protected $fillable = [
        'item_number',
        'qty',
        'price',
        'remarke',
        'remark',
        'shop_bc_number',
        'order_number',
        'rep',
        'default_name'
    ];

    /**
     * Matched on `item_number`, not a numeric FK — see
     * Products::cartItems() for why.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Products::class, 'item_number', 'item_number');
    }

    /**
     * Matched on `order_number` -> orders.unique_id, not a numeric FK —
     * both are app-generated identifiers, not primary keys.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Orders::class, 'order_number', 'unique_id');
    }
}
