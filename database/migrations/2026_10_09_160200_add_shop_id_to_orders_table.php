<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * orders.shop is a shop branch_code string today (fragile — editing a
     * shop's branch code orphans its historical orders). This adds the
     * real FK alongside it; orders.shop stays until Phase 2/3 switch over.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('shop_id')->nullable()->after('shop')->constrained('shops')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shop_id');
        });
    }
};
