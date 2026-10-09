<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * products.unit_price/mrp/direct_sale_price and carts.qty/price are
     * `string` columns today purely by omission. Converting them fails
     * (or silently corrupts, depending on sql_mode) the moment one
     * existing row isn't actually numeric, so this refuses to run at all
     * if it finds one — run `php artisan phase1:validate-numeric-columns`
     * first on any environment with real data.
     */
    public function up(): void
    {
        $this->assertAllNumeric('products', ['unit_price', 'mrp', 'direct_sale_price']);
        $this->assertAllNumeric('carts', ['qty', 'price']);

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('unit_price', 10, 2)->change();
            $table->decimal('mrp', 10, 2)->change();
            $table->decimal('direct_sale_price', 10, 2)->change();
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->unsignedInteger('qty')->change();
            $table->decimal('price', 10, 2)->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('unit_price')->change();
            $table->string('mrp')->change();
            $table->string('direct_sale_price')->change();
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->string('qty')->change();
            $table->string('price')->change();
        });
    }

    private function assertAllNumeric(string $table, array $columns): void
    {
        foreach ($columns as $column) {
            // Chunked, not pluck()-ed all at once — these tables can hold
            // millions of rows.
            $bad = collect();

            DB::table($table)
                ->whereNotNull($column)
                ->orderBy('id')
                ->select('id', $column)
                ->chunkById(5000, function ($rows) use ($column, &$bad) {
                    foreach ($rows as $row) {
                        if (!is_numeric($row->{$column})) {
                            $bad->put($row->id, $row->{$column});
                            if ($bad->count() >= 5) {
                                return false;
                            }
                        }
                    }
                });

            if ($bad->isNotEmpty()) {
                throw new \RuntimeException(
                    "Refusing to convert {$table}.{$column} to a numeric type: non-numeric values exist, e.g. " .
                    $bad->map(fn ($value, $id) => "#{$id}='{$value}'")->implode(', ') .
                    ". Fix the data (or run `php artisan phase1:validate-numeric-columns` for the full list) before re-running this migration."
                );
            }
        }
    }
};
