<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Shop dashboard's "best selling categories" widget
     * (products ⋈ product_categories on `category`) was measured at
     * ~27 seconds against real-volume data (1.9M cart rows): neither side
     * of that join column had an index, so MySQL drove the join from the
     * tiny product_categories table and, for each of its ~19 rows,
     * re-scanned every matching cart row via carts.shop_bc_number and
     * filtered by category with no index to narrow it - effectively
     * (category count) x (shop's cart rows) work instead of an indexed
     * lookup. Confirmed via EXPLAIN: `products` and `product_categories`
     * both showed no usable key for this join.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('category');
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['category']);
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropIndex(['category']);
        });
    }
};
