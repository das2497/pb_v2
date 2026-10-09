<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product_category extends Model
{
    use HasFactory;

    protected $table = 'product_categories';

    protected $fillable = [
        'category',
        'main_category',
    ];

    /**
     * Matched on the `category` string, not a numeric FK — products.category
     * has no FK column to this table (a gap the audit found; not yet closed).
     */
    public function products(): HasMany
    {
        return $this->hasMany(Products::class, 'category', 'category');
    }
}
