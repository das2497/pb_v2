<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Phase 6 load/performance check: times the actual dashboard/report queries
 * from OrderAdmin\Dashboard against real-volume data (dev DB: ~74k orders,
 * ~1.9M cart rows), and for the ones a Phase 1 index directly applies to,
 * re-runs the exact same query with that index disabled (MySQL's
 * IGNORE INDEX hint on the query itself - no schema change, fully safe and
 * reversible) to measure the actual delta the index buys. For the
 * join-heavy aggregates that have no date or status bound at all
 * (top-selling shops/items/reps-all-time), no index can help regardless -
 * those do a genuine full scan every single dashboard load, which this
 * command times separately rather than against a meaningless "without
 * index" run.
 */
class BenchmarkDashboardQueries extends Command
{
    protected $signature = 'phase6:benchmark-dashboard-queries';

    protected $description = 'Time the OrderAdmin dashboard queries against real-volume data, with/without the Phase 1 indexes where applicable';

    public function handle(): int
    {
        $this->info('Row counts: orders=' . DB::table('orders')->count() . ', carts=' . DB::table('carts')->count());
        $this->newLine();

        $this->indexComparison(
            'Order status counts (whereIn status, groupBy status)',
            fn () => DB::table('orders')
                ->select('status', DB::raw('count(id) as count'))
                ->whereIn('status', ['Pending', 'Processing', 'Complete', 'Under Review'])
                ->groupBy('status'),
            'orders',
            'orders_status_index'
        );

        $this->indexComparison(
            'Last-12-months revenue (created_at >= X, groupBy month)',
            fn () => DB::table('orders')
                ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('SUM(total_price) as amount'))
                ->where('created_at', '>=', now()->subMonths(12))
                ->whereNot('status', 'Pending')
                ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
                ->orderBy('month'),
            'orders',
            'orders_created_at_index'
        );

        $this->indexComparison(
            'Last-30-days revenue by day (created_at BETWEEN)',
            fn () => DB::table('orders')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_price) as total_revenue'))
                ->whereBetween('created_at', [now()->subDays(30)->startOfDay(), now()->endOfDay()])
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date'),
            'orders',
            'orders_created_at_index'
        );

        $this->indexComparison(
            'Latest 10 orders (orderBy created_at desc, limit 10)',
            fn () => DB::table('orders')->join('shops', 'orders.shop', '=', 'shops.branch_code')
                ->select('orders.*', 'shops.*', 'orders.created_at as order_created_at')
                ->orderByDesc('orders.created_at')->limit(10),
            'orders',
            'orders_created_at_index'
        );

        $this->newLine();
        $this->warn('Unbounded full-scan aggregates (no index can help - every row is read on every dashboard load):');
        $this->timeOnly(
            'Top 10 selling shops (orders join shops, no date/status filter)',
            fn () => DB::table('orders')->join('shops', 'orders.shop', '=', 'shops.branch_code')
                ->select('shops.name', DB::raw('SUM(orders.total_price) as total_sales'))
                ->groupBy('shops.name', 'shops.branch_code')->orderByDesc('total_sales')->limit(10)
        );
        $this->timeOnly(
            'Top 10 selling items (carts join products, no filter - scans all ~1.9M cart rows)',
            fn () => DB::table('carts')->join('products', 'carts.item_number', '=', 'products.item_number')
                ->select('products.name_english', DB::raw('SUM(CAST(carts.qty AS DECIMAL(10,2))) as total_qty_sold'))
                ->groupBy('products.item_number', 'products.name_english')->orderByDesc('total_qty_sold')->limit(10)
        );
        $this->timeOnly(
            'Top-selling reps all time (4-table join: orders/shops/rep_assign_shops/reps, no filter)',
            fn () => DB::table('orders')
                ->join('shops', 'orders.shop', '=', 'shops.branch_code')
                ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
                ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
                ->select('reps.name', DB::raw('SUM(orders.total_price) as total_sales'))
                ->groupBy('reps.id', 'reps.name')->orderByDesc('total_sales')->limit(10)
        );
        $this->timeOnly(
            'Top 10 orders by total_price (no index on total_price - full sort every time)',
            fn () => DB::table('orders')->join('shops', 'orders.shop', '=', 'shops.branch_code')
                ->select('orders.unique_id', 'orders.total_price', 'shops.name')
                ->whereNot('orders.status', 'Cancelled')->orderByDesc('orders.total_price')->limit(10)
        );

        return self::SUCCESS;
    }

    /** @param \Closure(): Builder $builderFactory */
    private function indexComparison(string $label, \Closure $builderFactory, string $table, string $index): void
    {
        $withIndex = $this->time(fn () => $builderFactory()->get());
        $withoutIndexTime = $this->timeIgnoringIndex($builderFactory(), $table, $index);

        $speedup = $withIndex > 0 ? round($withoutIndexTime / $withIndex, 1) : 0;

        $this->line(sprintf(
            '%s%s  with index: %7.2f ms  |  without (%s ignored): %7.2f ms  |  %sx',
            $label,
            str_pad('', max(0, 65 - strlen($label))),
            $withIndex,
            $index,
            $withoutIndexTime,
            $speedup
        ));
    }

    /** @param \Closure(): Builder $builderFactory */
    private function timeOnly(string $label, \Closure $builderFactory): void
    {
        $ms = $this->time(fn () => $builderFactory()->get());
        $this->line(sprintf('%s%s  %7.2f ms', $label, str_pad('', max(0, 65 - strlen($label))), $ms));
    }

    private function time(\Closure $run): float
    {
        $start = microtime(true);
        $run();
        return (microtime(true) - $start) * 1000;
    }

    /**
     * Takes $builder's own compiled SQL and re-issues it with an
     * IGNORE INDEX hint spliced in right after the first reference to
     * $table, so MySQL's planner can't use $index for this one run.
     */
    private function timeIgnoringIndex(Builder $builder, string $table, string $index): float
    {
        $sql = $builder->toSql();
        $bindings = $builder->getBindings();

        $hinted = preg_replace(
            '/\bfrom `' . preg_quote($table, '/') . '`/',
            "from `{$table}` IGNORE INDEX (`{$index}`)",
            $sql,
            1
        );

        $start = microtime(true);
        DB::select($hinted, $bindings);
        return (microtime(true) - $start) * 1000;
    }
}
