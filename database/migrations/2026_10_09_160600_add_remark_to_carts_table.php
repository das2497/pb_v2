<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * carts.remarke is a longstanding typo propagated through dozens of
     * controller/view references. This adds the correctly-spelled column;
     * `remarke` stays (and stays authoritative for the app) until Phase
     * 2/3 update every call site to `remark` and drop it.
     */
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->string('remark')->nullable()->after('remarke');
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn('remark');
        });
    }
};
