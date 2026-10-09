<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Logs extends Model
{
    use HasFactory;

    protected $table = 'logs';

    protected $fillable = [
        'type',
        'message',
        'user',
        'user_id',
    ];

    public function userRecord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
