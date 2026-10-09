<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('shops')->where('order_time', 'Both')->update(['order_time' => 'All']);
    }

    public function down(): void
    {
        DB::table('shops')->where('order_time', 'All')->update(['order_time' => 'Both']);
    }
};
