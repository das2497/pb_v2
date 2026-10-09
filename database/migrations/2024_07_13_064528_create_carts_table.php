<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->string('item_number');
            $table->string('qty');
            $table->string('price');
            $table->string('remarke')->nullable();
            $table->string('shop_bc_number');
            $table->string('order_number')->nullable();
            $table->string('rep')->nullable();
            $table->string('default_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
