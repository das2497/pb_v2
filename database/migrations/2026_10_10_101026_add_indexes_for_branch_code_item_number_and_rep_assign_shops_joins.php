<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 6 load testing (see tests/PERFORMANCE_NOTES.md) found the
     * dashboard/report queries join on these exact columns, but only one
     * side of each join was ever indexed: orders.shop already has an index
     * (from the orders/carts migration), but shops.branch_code - the other
     * side of `orders.shop = shops.branch_code` - never did. Same pattern
     * for products.item_number (carts.item_number is indexed, the products
     * side isn't) and rep_assign_shops.rep_id/shop_id (the plain string
     * columns the dashboard actually joins on - not the already-indexed
     * rep_id_int/shop_id_int Phase 1 added, which can't be swapped in here
     * since their backfill isn't 100% complete and doing so would silently
     * drop unmatched rows from the results).
     */
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->index('branch_code');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index('item_number');
        });

        Schema::table('rep_assign_shops', function (Blueprint $table) {
            $table->index('rep_id');
            $table->index('shop_id');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropIndex(['branch_code']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['item_number']);
        });

        Schema::table('rep_assign_shops', function (Blueprint $table) {
            $table->dropIndex(['rep_id']);
            $table->dropIndex(['shop_id']);
        });
    }
};
