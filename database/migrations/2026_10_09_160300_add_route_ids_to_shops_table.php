<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * shops.morning_route/evening_route match routes.name by free-text
     * string today. These are the real FKs; the string columns stay until
     * Phase 2/3 switch over.
     */
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->foreignId('morning_route_id')->nullable()->after('morning_route')->constrained('routes')->nullOnDelete();
            $table->foreignId('evening_route_id')->nullable()->after('evening_route')->constrained('routes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropConstrainedForeignId('morning_route_id');
            $table->dropConstrainedForeignId('evening_route_id');
        });
    }
};
