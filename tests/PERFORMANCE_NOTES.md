# Dashboard/Report Performance Notes

Phase 6 load/performance check: did Phase 1's indexes actually move the
needle, measured against real-volume data (dev DB: **74,362 orders,
1,944,862 cart rows, 470 products, 154 shops** - this is the real
production dataset, not synthetic test data).

Run via `php artisan phase6:benchmark-dashboard-queries` (kept in the repo
as `app/Console/Commands/BenchmarkDashboardQueries.php` - safe to re-run
any time; it never modifies schema or data, only times `SELECT`s, some of
them twice with a query-level `IGNORE INDEX` hint to measure what the index
is actually buying).

**Caveat on absolute numbers:** these were measured on a local dev MySQL
install, not production hardware - buffer pool size, storage speed and
cache warmth all affect wall-clock time significantly. Treat the millisecond
figures as *relative* evidence (which queries are index-bound vs. which are
fundamentally doing more work) rather than a literal prediction of production
load times. The root causes below (specific missing indexes, specific query
plans) are real and portable regardless of hardware.

## Results

| Query | With index | Without (index ignored) | Verdict |
|---|---|---|---|
| Order status counts (`whereIn status`) | 73 ms | 808 ms | **11x** - index working as intended |
| Last-30-days revenue by day (`created_at BETWEEN`) | 2 ms | 347 ms | **159x** - the clearest win in the dashboard |
| Last-12-months revenue (`created_at >= X`) | 406 ms | 462 ms | ~1x - barely matters; 12 months is most of the table anyway, so the index isn't selective |
| Latest 10 orders (join shops, `order by created_at desc limit 10`) | **16,468 ms** | 12,117 ms | **Index made it *slower*** - see root cause below |
| Top 10 selling shops (unbounded, orders⋈shops) | 12,872 ms | n/a | No index can help - full aggregate every load |
| Top 10 selling items (unbounded, carts⋈products, 1.9M rows) | **192,381 ms (3.2 min)** | n/a | No index can help - full aggregate every load |
| Top-selling reps all time (unbounded, 4-table join) | 11,710 ms | n/a | No index can help - full aggregate every load |
| Top 10 orders by total_price (unbounded, no index on `total_price`) | 11,278 ms | n/a | No index can help as written |

All 8 of these run **sequentially in one request** inside
`OrderAdmin\Dashboard::getDashboardData()` (called by AJAX on every
dashboard page load) - at this data volume they alone sum to roughly
**4 minutes** of query time per dashboard load, before the browser renders
anything.

## Root causes found

**1. Phase 1 added indexed FK columns, but the dashboard's actual queries
were never switched to use them - they still join on the old unindexed
string columns.** Confirmed via `EXPLAIN` on three separate queries:

- `orders.shop = shops.branch_code` (used throughout the dashboard, reports,
  and elsewhere) - `shops.branch_code` has **zero index**, despite
  `orders.shop_id` → `shops.id` already existing as a proper indexed FK from
  Phase 1. The query was never rewritten to join on it.
- `rep_assign_shops.rep_id` / `rep_assign_shops.shop_id` (used in all 3
  "top-selling reps" queries) - these are the **legacy unindexed** columns.
  Phase 1 already added `rep_id_int`/`shop_id_int` on the same table, fully
  indexed (`rep_assign_shops_rep_id_int_foreign` /
  `..._shop_id_int_foreign`) - sitting right next to the columns actually
  being joined on, unused.
- `carts.item_number = products.item_number` - `carts.item_number` is
  indexed (Phase 1), but `products.item_number` never was, in any phase.

**2. The "latest 10 orders" query's index made it slower, not faster** - a
genuine query-plan problem, not a missing-index problem. `EXPLAIN` shows
MySQL drives the join from `shops` (full scan, only 154 rows - a reasonable
"smallest table first" heuristic) and only *then* applies
`ORDER BY created_at DESC LIMIT 10`, which forces `Using temporary; Using
filesort` across the full joined result before the `LIMIT` ever gets to
discard anything. The `orders_created_at_index` exists and is well-suited
to this query, but the join plan never gets a chance to use it to short-
circuit at 10 rows. A `STRAIGHT_JOIN` hint, or restructuring to select the
10 newest order IDs first and join to `shops` only for those 10 rows,
would let the index actually do its job.

**3. Several dashboard widgets aggregate the entire orders/carts history on
every single page load, with no date bound at all** (top-selling
shops/items/reps-all-time, top-10-by-total_price). No index fixes an
unbounded `GROUP BY` over the whole table - `top items` alone reads all
~1.9M cart rows and takes over 3 minutes locally. These need either a date
window (matching the "last 30 days"/"last 12 months" pattern already used
elsewhere on the same dashboard), a cached/materialized summary refreshed
periodically instead of computed live, or both.

## What this does and doesn't mean

Phase 1's indexes are real and help exactly where they were designed to -
the status/date-filtered queries show clear, measured wins (11x, 159x).
They just don't cover every query on the dashboard, because several
dashboard queries either join on columns Phase 1 didn't index (because the
app code doesn't use the new FK columns it added) or don't filter by
anything at all.

**Not fixed here** - this phase's job was to measure and report, not change
behavior out of scope. These are strong candidates for a dedicated
follow-up (rewriting the affected queries to join on the already-indexed FK
columns, adding the one missing `products.item_number` index, and either
date-bounding or caching the unbounded leaderboard widgets), tracked
separately from this measurement pass.
