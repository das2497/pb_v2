<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepAssignShop extends Model
{
    use HasFactory;

    protected $table = 'rep_assign_shops';

    protected $fillable = [
        'rep_id',
        'rep_id_int',
        'shop_id',
        'shop_id_int',
    ];

    public function rep(): BelongsTo
    {
        return $this->belongsTo(Rep::class, 'rep_id_int');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shops::class, 'shop_id_int');
    }
}
