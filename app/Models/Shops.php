<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function morningRoute(): BelongsTo
    {
        return $this->belongsTo(Routes::class, 'morning_route_id');
    }

    public function eveningRoute(): BelongsTo
    {
        return $this->belongsTo(Routes::class, 'evening_route_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Orders::class, 'shop_id');
    }

    /**
     * Via the typed, FK-constrained pivot columns added in Phase 1
     * (rep_id_int/shop_id_int) — not the legacy unconstrained string
     * columns, which the Phase 1 backfill found to be substantially stale.
     */
    public function reps(): BelongsToMany
    {
        return $this->belongsToMany(Rep::class, 'rep_assign_shops', 'shop_id_int', 'rep_id_int');
    }

    /**
     * Matched on `email`, not a FK — shops has no user_id column.
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'email', 'email');
    }
}
