<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepAssignShop extends Model
{
    use HasFactory;

    protected $table = 'rep_assign_shops';

    protected $fillable = [
        'rep_id',
        'shop_id',
    ];    
}
