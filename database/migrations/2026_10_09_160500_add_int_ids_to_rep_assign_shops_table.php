<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * rep_assign_shops.rep_id/shop_id already hold the right values but as
     * unconstrained strings. Rather than changing those columns in place
     * against data we haven't validated, this adds typed, constrained
     * staging columns to backfill into and report against first; the
     * actual column swap is a later, explicitly-staged migration once a
     * backfill report comes back clean.
     */
    public function up(): void
    {
        Schema::table('rep_assign_shops', function (Blueprint $table) {
            $table->foreignId('rep_id_int')->nullable()->after('rep_id')->constrained('reps')->cascadeOnDelete();
            $table->foreignId('shop_id_int')->nullable()->after('shop_id')->constrained('shops')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rep_assign_shops', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rep_id_int');
            $table->dropConstrainedForeignId('shop_id_int');
        });
    }
};
