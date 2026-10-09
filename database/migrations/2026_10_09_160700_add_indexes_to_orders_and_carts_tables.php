<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Indexes on the columns every order-listing/report query filters or
     * joins on, found via the audit's read of every controller.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index('unique_id');
            $table->index('shop');
            $table->index('status');
            $table->index('time_period');
            $table->index('created_at');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->index('order_number');
            $table->index('shop_bc_number');
            $table->index('item_number');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['unique_id']);
            $table->dropIndex(['shop']);
            $table->dropIndex(['status']);
            $table->dropIndex(['time_period']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropIndex(['order_number']);
            $table->dropIndex(['shop_bc_number']);
            $table->dropIndex(['item_number']);
        });
    }
};
