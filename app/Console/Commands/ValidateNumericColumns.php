<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Pre-flight check for the Phase 1 migration that converts
 * products.unit_price/mrp/direct_sale_price and carts.qty/price from
 * string to decimal/integer. Those columns are typed as `string` purely
 * because no one enforced otherwise — but converting a string column to a
 * numeric one fails (or silently corrupts, depending on the DB's strict
 * mode) the moment a single existing row isn't actually numeric. Run this
 * against an environment before running that migration there.
 */
class ValidateNumericColumns extends Command
{
    protected $signature = 'phase1:validate-numeric-columns';

    protected $description = 'Check products/carts price and quantity columns for non-numeric values before converting their types';

    public function handle(): int
    {
        $problems = 0;
        $problems += $this->checkColumn('products', 'unit_price');
        $problems += $this->checkColumn('products', 'mrp');
        $problems += $this->checkColumn('products', 'direct_sale_price');
        $problems += $this->checkColumn('carts', 'qty');
        $problems += $this->checkColumn('carts', 'price');

        if ($problems === 0) {
            $this->info('All checked columns are safe to convert to numeric types.');
            return self::SUCCESS;
        }

        $this->error("{$problems} column(s) have non-numeric values — do not run the type-conversion migration until these are fixed.");
        return self::FAILURE;
    }

    private function checkColumn(string $table, string $column): int
    {
        // Done in PHP, not SQL (e.g. REGEXP isn't available on SQLite,
        // which is what the test suite and CI run against) — and chunked,
        // since this table can hold millions of rows and pluck()-ing the
        // whole column into memory at once is not safe.
        $badRows = collect();

        DB::table($table)
            ->whereNotNull($column)
            ->orderBy('id')
            ->select('id', $column)
            ->chunkById(5000, function ($rows) use ($column, &$badRows) {
                foreach ($rows as $row) {
                    if (!is_numeric($row->{$column})) {
                        $badRows->put($row->id, $row->{$column});
                        if ($badRows->count() >= 10) {
                            return false; // stop chunking, we have enough examples
                        }
                    }
                }
            });

        if ($badRows->isEmpty()) {
            $this->line("{$table}.{$column}: ok");
            return 0;
        }

        $this->warn("{$table}.{$column}: non-numeric values found, e.g.:");
        foreach ($badRows as $id => $value) {
            $this->line("  - {$table} #{$id}: '{$value}'");
        }

        return 1;
    }
}
