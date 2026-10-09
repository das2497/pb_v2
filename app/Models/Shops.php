<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shops extends Model
{
    use HasFactory;
    protected $table = 'shops';

    protected $fillable = [
        'name',
        'sinhala_name',
        'branch_code',
        'email',
        'contact',
        'price_range',
        'order_time',
        'morning_route',
        'morning_route_id',
        'evening_route',
        'evening_route_id',
        'type',
    ];

    public function allowedOrderTimes(): array
    {
        if (is_string($this->order_time) && $this->order_time !== '' && $this->order_time !== 'All') {
            return [$this->order_time];
        }

        return OrderTime::orderBy('id')->pluck('name')->all();
    }

}
