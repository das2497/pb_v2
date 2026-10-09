<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Rep extends Model
{
    use HasFactory;

    protected $table='reps';

    protected $fillable=[
        'name',
        'email',
        'contact',
        'type',
        'access',
        'password',
    ];

    /**
     * Via the typed, FK-constrained pivot columns added in Phase 1 — see
     * Shops::reps() for why not the legacy string columns.
     */
    public function shops(): BelongsToMany
    {
        return $this->belongsToMany(Shops::class, 'rep_assign_shops', 'rep_id_int', 'shop_id_int');
    }

    /**
     * Matched on `email`, not a FK — reps has no user_id column.
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'email', 'email');
    }
}
