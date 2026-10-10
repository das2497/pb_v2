# Rollback & Decommissioning Plan (Phase 7)

Ground truth for this doc is `git log --oneline` (every phase/part is its
own commit — see the table below) and the actual migration files in
`database/migrations/` (checked: every one has a working `down()`, and
none of them drop the old columns the new ones sit next to).

## How rollback actually works here

**There are no feature flags in this codebase** - the Phase 7 plan's
"behind feature flags or route-level gating" line is aspirational, not
built. What *is* true, and is the real reason each phase is low-risk to
roll back:

- Every phase/part is a **separate, bisectable commit** - rolling back
  code is `git revert <sha>` (or resetting a release branch to the prior
  phase's commit), not a flag flip.
- Every migration has a `down()`. Rolling back schema is
  `php artisan migrate:rollback --step=N` for whatever that phase added.
- Phase 1's FK columns (`orders.shop_id`, `rep_assign_shops.rep_id_int`/
  `shop_id_int`, etc.) were **added alongside** the old string columns,
  never replacing them - and the old columns are still what production
  code actually joins/queries on today (confirmed directly: Phase 6's
  dashboard work found the live queries still use `orders.shop`,
  `rep_assign_shops.rep_id`/`shop_id`, not the new FK columns). So
  reverting Phase 1's migration drops columns nothing downstream
  exclusively depends on yet - safe.

## Per-phase rollback table

| Phase | Code revert point(s) | DB rollback needed? | Risk if reverted |
|---|---|---|---|
| 0 | `3a68527` | No (test-only) | None - test suite only |
| 1 | `513469a` | Yes - `migrate:rollback`, drops the new FK/index columns | Low - nothing else depends on them exclusively yet (see above) |
| 2 | `f684ec9`, `bfff5d6`, `85a8093`, `c685834`, `46ec2ad`, `080a260` | No | Low-medium - `85a8093` changed which middleware class routes use (4 lines in `routes/web.php`); reverting restores the 5 old duplicate classes, which still exist in git history and behave identically (proven by `RoleMiddlewareTest` passing unchanged against both) |
| 3 | `00c4727` … `c48e0e5` (10 parts) | No | Medium - these wire controllers onto the Phase 2 services; reverting any part reverts that controller to its pre-service inline logic. Each part's own tests (Phase 0/6 coverage) must pass post-revert before trusting it |
| 4 | `3394923` | No (config only) | Low - env-var extraction, no logic change |
| 5 | `9a7b239` … `c279705` (views/vendor/Breeze removal) | No | Medium-high - Breeze/Tailwind/Alpine files are **deleted**, not flagged off; reverting means `git revert` (not just redeploy) to bring `resources/views/auth/`, `tailwind.config.js`, etc. back. No DB involvement. |
| 6 | `be1f0e8` … `3cc9414` | `3cc9414` adds an index migration - reversible via `migrate:rollback` | Low - tests + the dashboard query fixes; reverting loses the performance fix but changes nothing else |
| 7 | `50afd00` (monitoring) | No | None - purely additive (logging/alerting), already verified not to change app behavior |

## Decommissioning status: nothing is eligible yet

The Phase 7 plan is explicit that decommissioning (actually dropping the
superseded legacy columns/files/routes) happens **after** a phase has run
stable in production for an agreed stabilization window. Since this
project is still on `master` in local dev - **nothing has shipped to
production yet** - that window hasn't started for any phase, so nothing
listed below should be touched now. Listed here so it's not forgotten
once a real deployment happens:

- `shops.branch_code`, `rep_assign_shops.rep_id`/`shop_id` (legacy string
  columns) - **still actively read by the dashboard and reports today**
  (confirmed in Phase 6's performance work). Do not drop these until the
  app is fully switched to the Phase 1 FK columns *and* that backfill is
  verified 100% complete (it currently is not - see
  `tests/PERFORMANCE_NOTES.md`'s root-cause #1).
- The 5 original role-gating middleware classes superseded in Phase 2
  part 3 - already deleted from the working tree; only "decommissioning"
  left is accepting they're gone (nothing to do).
- Breeze/Tailwind/Alpine and its views/config - already deleted in
  Phase 5; same as above.

## What's still open from Phase 7's scope

- **Final full-system QA pass across all three roles** - this is exactly
  the 🔲 manual-QA-only rows already tracked in
  `tests/REGRESSION_CHECKLIST.md` (dashboards, report rendering, several
  listing pages, default-order management, admin delete actions). Not
  done here - it needs either a browser walkthrough of each role or the
  user's own manual pass before any real cutover.
- **Incremental production cutover itself** - deploying each phase and
  watching it stabilize is an operational step outside this repo; nothing
  to build, just to schedule once there's a production target.
