<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Routes extends Model
{
    use HasFactory;

    protected $table = 'routes';

    protected $fillable = [
        'name',
        'index',
        'type',
        'time'
    ];

    public function morningShops(): HasMany
    {
        return $this->hasMany(Shops::class, 'morning_route_id');
    }

    public function eveningShops(): HasMany
    {
        return $this->hasMany(Shops::class, 'evening_route_id');
    }
}
