<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'unique_id',
        'shop',
        'total_price',
        'note',
        'time_period',
        'status',
        'default_name',
        'default_period',
    ];
}
