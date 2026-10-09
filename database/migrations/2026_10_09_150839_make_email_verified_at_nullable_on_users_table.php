<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * users.email_verified_at was created NOT NULL, but ProfileController::update()
     * sets it to null whenever a user changes their email (standard Breeze
     * behavior). Any shop/rep/admin changing their email today hits a DB
     * constraint violation instead of a normal profile update.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable(false)->change();
        });
    }
};
