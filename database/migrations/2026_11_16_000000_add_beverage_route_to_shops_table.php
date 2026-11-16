<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Beverage is a first-class order period (order_times) and routes can
     * already be tagged time = 'Beverage', but shops had nowhere to record
     * which beverage route they belong to - only morning_route and
     * evening_route. Without this column the Beverage shop report has no
     * way to show beverage routes only.
     *
     * Nullable, matching how morning_route_id/evening_route_id were added:
     * existing shops simply have no beverage route until one is assigned,
     * and shops with none stay out of the beverage report.
     */
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('beverage_route')->nullable()->after('evening_route_id');
            $table->foreignId('beverage_route_id')->nullable()->after('beverage_route')->constrained('routes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropConstrainedForeignId('beverage_route_id');
            $table->dropColumn('beverage_route');
        });
    }
};
