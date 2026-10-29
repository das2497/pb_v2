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

**4. `topSellingRepsToday` uses `whereDate('orders.created_at', $today)`,**
found while verifying the fixes below (not in the original benchmark run -
this query wasn't one of the 8 originally timed). `whereDate()` compiles to
`DATE(orders.created_at) = '...'`, and wrapping an indexed column in a
function makes `orders_created_at_index` unusable - confirmed via `EXPLAIN`
(full join-driven scan instead of an index seek). 13 seconds at real data
volume, next to `topSellingRepsLast30Days` right below it in the same
controller, which uses a plain `whereBetween` range on the same column and
runs in under 5ms.

## Fixes applied

All behavior-preserving (verified below) - same query results, different
execution path:

- **Added indexes** on `shops.branch_code`, `products.item_number`,
  `rep_assign_shops.rep_id`, `rep_assign_shops.shop_id` (migration
  `2026_10_10_101026_...`) - the actual columns the dashboard's queries join
  on today. Deliberately *not* switching the queries to the Phase 1 FK
  columns (`orders.shop_id`, `rep_assign_shops.rep_id_int`/`shop_id_int`)
  instead, because that backfill isn't 100% complete (3,261 of 74,362 orders
  have a null `shop_id`; over half of `rep_assign_shops` rows have null
  `rep_id_int`/`shop_id_int`) - switching the join would silently drop those
  rows from every result. Indexing the columns actually in use avoids that
  risk entirely while fixing the same bottleneck.
- **Rewrote `topSellingRepsToday`'s `whereDate()` to a `whereBetween()`**
  range over the same calendar day (`OrderAdmin\Dashboard.php`) - confirmed
  identical results before/after on real data (one float-formatting
  difference, `156.86200000000002` vs `156.862` - same value, a FP
  summation-order artifact, not a behavior change).
- **Cached the 4 genuinely unbounded aggregates** (top-selling shops/items/
  reps-all-time, top-10-orders-by-value) for 5 minutes via `Cache::remember`
  - verified identical results between a cold and a warm read. This is the
  one deliberate behavior change: these widgets are now "as of up to 5
  minutes ago" instead of always perfectly live, which is the standard,
  expected trade-off for a leaderboard-style aggregate at this data volume
  and doesn't change what any individual number means, only how fresh it is
  between page loads.

### Before / after (same dev DB, same data)

| Query | Before | After |
|---|---|---|
| Latest 10 orders | 16,468 ms | **4.7 ms** (index fix alone - no caching needed) |
| Top 10 selling shops | 12,872 ms | **1,083 ms** uncached / ~1-5 ms cached |
| Top 10 orders by total_price | 11,278 ms | **372 ms** uncached / ~2 ms cached |
| `topSellingRepsToday` (`whereDate` → `whereBetween`) | 13,068 ms | **37.7 ms** |
| Top-selling reps all time | 11,710 ms | 11,343 ms uncached (index didn't help much here - `rep_assign_shops` is only 341 rows, not the bottleneck) / **~1 ms cached** |
| Top 10 selling items | 192,381 ms | 198,416 ms uncached (genuinely needs to read all ~1.9M cart rows - no index changes that) / **~1 ms cached** |

The index fix alone (no caching) already resolved 3 of the original 4
"nothing can help" queries. Only the two that must read the full `carts`/
`rep_assign_shops`-driven history every time still need the cache to be
fast - and now are.

## What this does and doesn't mean

Phase 1's indexes are real and helped exactly where they were designed to -
the status/date-filtered queries showed clear, measured wins (11x, 159x) and
that work is left as-is. The dashboard-specific issues found here were
different problems in the same family: joins on unindexed columns one hop
away from the ones Phase 1 did index, a `whereDate()` that accidentally
defeated an index that already existed, a lost join/sort plan, and a few
widgets with no bound on the data they aggregate. All are now fixed.

## Phase 7: Rep and Shop dashboards

The work above only covered `OrderAdmin\Dashboard`. A follow-up pass timed
`Rep\Dashboard` and `Shop\Dashboard` the same way (real dev data, worst-case
shop/rep by order volume: shop `00093` with 1,410 orders, rep with 10,640
orders across 18 assigned shops) and found three more issues:

**1. Shop dashboard's "best selling categories" widget:** ~27.4 **seconds**
uncached. Root cause (confirmed via `EXPLAIN`): neither `products.category`
nor `product_categories.category` - the actual join column - had an index
on either side, so MySQL drove the join from the 19-row `product_categories`
table and, for each category, re-scanned every one of the shop's ~43k cart
rows filtering by category with no index to narrow it. Fixed with a
migration adding both indexes - **27,440 ms → 1,459 ms** (19x), confirmed via
`EXPLAIN ANALYZE` to now be a clean indexed nested-loop plan (the remaining
~1.5s is genuinely reading ~43k cart rows for one shop; already behind the
existing 10-minute `Cache::remember`, same as the admin-side unbounded
aggregates above).

**2. Shop dashboard's `firstLoading()` status-count query unnecessarily
joined `rep_assign_shops`/`reps`** - copy-paste from the rep version; the
query only ever filtered on the shop's own `branch_code` (already resolved
earlier in the same method) and never read anything rep-related. Removing
the two dead joins: **~190-220 ms → ~7 ms** (verified identical results
before/after).

**3. Rep dashboard's `firstLoading()`/`getDashboardData()` re-ran the full
`orders ⋈ shops ⋈ rep_assign_shops ⋈ reps` join from scratch in every one of
7 separate queries**, just to find which shops belong to the logged-in rep -
at real volume that join alone cost ~370-490 ms, confirmed via
`EXPLAIN ANALYZE` to already be using its indexes correctly (the cost is
genuinely proportional to the rep's real order volume, not a bad plan).
Resolved the rep's shop list **once** (branch codes + names, cached 10 min -
a rep's assignments change rarely), and rewrote every query to filter with
`whereIn('orders.shop', $shopCodes)` directly instead of repeating the join.
Also merged `firstLoading()`'s two queries (status counts + revenue sums)
into one, and added a short (30s) cache to `firstLoading()` and
`latest_orders`/`top_orders` - these read real per-rep order history so a
cache is the honest fix, same reasoning as the admin-side unbounded
aggregates, just scoped and much shorter-lived since it's not literally
unbounded. Verified byte-for-byte identical results against the original
queries for the busiest real rep (aside from ~1e-9-level floating point
`SUM()` summation-order noise, same category of non-change documented
above). End-to-end, via the real controller with real auth:
**firstLoading: 530 ms cold → 2.8 ms warm. getDashboardData: 812 ms cold →
3.85 ms warm.**

None of this touched `rep_assign_shops.rep_id`/`shop_id` (still the
unindexed-by-type `varchar` columns, not the indexed `_int` ones) -
confirmed via `SHOW COLUMNS` that 129/341 `rep_id_int` and 189/341
`shop_id_int` rows are still `NULL` (same incomplete backfill noted in
Phase 6), so switching the join would still silently drop rows. The
`shopsForRep()` cache only reads from this join once every 10 minutes per
rep now, rather than fixing the underlying type mismatch.
