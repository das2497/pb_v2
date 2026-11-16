<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Generic key/value settings store, so things that used to be
 * .env-only (first user: the Google Sheets export's spreadsheet IDs and
 * service-account credentials path) can be configured from the Order
 * Admin panel without a redeploy. Values stay nullable: an unset key
 * means "fall back to config/env", which keeps the existing
 * config/services.php defaults working untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
