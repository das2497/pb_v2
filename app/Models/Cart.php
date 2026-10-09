<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'carts';

    protected $fillable = [
        'item_number',
        'qty',
        'price',
        'remarke',
        'shop_bc_number',
        'order_number',
        'rep',
        'default_name'
    ];
}
