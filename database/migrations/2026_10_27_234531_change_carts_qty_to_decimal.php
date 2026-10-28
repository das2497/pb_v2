<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * carts.qty has been an integer column, but several order-edit pages
     * (pending/processing/under-review views and their "add item" forms,
     * across all three roles) already accept a decimal qty in their HTML
     * (step="0.01" pattern="[0-9]+(\.[0-9]+)?") - any fractional value
     * entered there was silently rounded/truncated on save because the
     * column itself can't store it. This makes the column match what the
     * UI has already been promising.
     */
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->decimal('qty', 10, 2)->unsigned()->change();
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->unsignedInteger('qty')->change();
        });
    }
};
