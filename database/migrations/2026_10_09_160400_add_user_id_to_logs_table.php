<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * logs.user stores a display-name string today (not unique, so a
     * backfill match can be ambiguous — see Phase1BackfillForeignKeys).
     * The string column stays until Phase 2/3 switch over.
     */
    public function up(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('user')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
