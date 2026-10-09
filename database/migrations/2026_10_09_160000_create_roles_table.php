<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lookup table for users.role. Keeps all 4 admin-equivalent role
     * strings (o_admin/admin/view/sales_admin) as distinct rows per
     * product decision — they're still treated identically in code today,
     * Phase 2 is where any real permission gradient gets built.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->timestamps();
        });

        $now = now();
        DB::table('roles')->insert([
            ['slug' => 's_admin', 'name' => 'Super Admin', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'o_admin', 'name' => 'Order Admin', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'admin', 'name' => 'Admin', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'view', 'name' => 'View', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'sales_admin', 'name' => 'Sales Admin', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'rep', 'name' => 'Rep', 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'shop', 'name' => 'Shop', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
