<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Orders extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'unique_id',
        'shop',
        'shop_id',
        'total_price',
        'note',
        'time_period',
        'status',
        'default_name',
        'default_period',
        'order_date',
    ];

    public function shopRecord(): BelongsTo
    {
        return $this->belongsTo(Shops::class, 'shop_id');
    }

    /**
     * Matched on `unique_id` -> carts.order_number, not the primary key —
     * see Cart::order() for why.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(Cart::class, 'order_number', 'unique_id');
    }
}
