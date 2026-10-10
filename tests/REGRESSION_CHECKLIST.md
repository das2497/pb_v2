# Regression Checklist

Maps every feature in `pb_new` (as found in `routes/web.php`) to its automated
test coverage or, where automation isn't practical, a manual QA step. Built as
part of Phase 6 (Testing & QA Buildout) of the 2.0 rebuild, so "same features
preserved" is verifiable against this list rather than just claimed.

**How to use this file:** before a release, confirm `php artisan test` is
green (covers every ✅ row) and manually walk through every 🔲 row. When you
add or change a route/controller, add or update its row here in the same PR.

Legend: ✅ automated · 🟡 partially automated (note explains the gap) · 🔲 manual
QA only (automation isn't practical, e.g. real Google Sheets/email I/O or
visual report layout).

## Shared / Auth

| Feature | Test coverage | Status |
|---|---|---|
| Login (all roles) | `Auth/AuthenticationTest.php` | ✅ |
| Register (creates a user with a role) | `Auth/RegistrationTest.php` | ✅ |
| Forgot password (request reset link) | `Auth/PasswordResetTest.php` | ✅ |
| Reset password (consume token) | `Auth/PasswordResetTest.php` | ✅ |
| Confirm password (re-auth for sensitive actions) | `Auth/PasswordConfirmationTest.php` | ✅ |
| Email verification prompt/link | `Auth/EmailVerificationTest.php` | ✅ |
| Generic profile: edit name/email, change password, delete account | `ProfileTest.php` | ✅ |
| Role-based dashboard routing + cross-role bounce | `RoleMiddlewareTest.php` | ✅ |
| AI chat (OrderAdmin): access gate, SQL-injection rejection, safe-query execution | `AIChatSecurityTest.php` | ✅ |
| AI chat: actual LLM response quality/relevance | — | 🔲 manual QA (not something to assert on) |

## OrderAdmin

| Feature | Test coverage | Status |
|---|---|---|
| Dashboard stats widgets + charts | — | ✅ manually verified 2026-10-10 in a live browser against real-volume data - all widgets/charts render correctly and match direct DB queries, including the cold-cache (~110s) → warm-cache (fast) behavior from the Phase 6 fix |
| Logs listing/search | — | ✅ manually verified 2026-10-10 - renders real log entries correctly |
| Pending orders: list/search | — | ✅ manually verified 2026-10-10 |
| Pending orders: accept → Processing | `OrderLifecycleTest.php`, `Services/OrderServiceTest.php` | ✅ |
| Pending/Processing/Under-Review: cancel + period reassignment, incl. the conflicting-order divergence between them | `OrderAdmin/OrderUpdateTest.php` | ✅ |
| Pending/Processing/Under-Review: item qty update + delete | `OrderAdmin/ItemEditTest.php` | ✅ |
| Pending/Processing/Under-Review: add item to order | `OrderAdmin/AddItemTest.php` | ✅ |
| Under-Review: delete whole order | `OrderAdmin/ItemEditTest.php` | ✅ |
| Complete orders: list/view | — | ✅ manually verified 2026-10-10 - paginated (20/page), loads fast |
| Products: add/update/delete | `OrderAdmin/AdminCrudFormRequestTest.php` | ✅ |
| Order times: add/delete (incl. reserved-name rejection) | `OrderAdmin/AdminCrudFormRequestTest.php` | ✅ |
| Product categories: add/update/delete | `OrderAdmin/AdminCrudFormRequestTest.php` | ✅ |
| Shops: add/update/delete | `OrderAdmin/AdminCrudFormRequestTest.php` | 🟡 add/update covered; delete (`shops.delete`) not directly tested |
| Routes: add/update/delete | `OrderAdmin/AdminCrudFormRequestTest.php` | 🟡 add/update covered; delete not directly tested |
| Reps: add, assign/update shop assignments, update access, delete | `OrderAdmin/AdminCrudFormRequestTest.php` | 🟡 add + assign covered; access-update and delete not directly tested |
| Create order on a shop's behalf (cart, add items, submit) | `OrderAdmin/CartAndOrderTest.php` | ✅ |
| Export report (Excel download) | — | 🟡 manually verified 2026-10-10 the route/category selection form renders correctly; actually generating and downloading the Excel file wasn't exercised |
| Shop report: morning/evening, normal/full-screen | — | 🟡 manually verified 2026-10-10 the morning/normal variant renders correctly with real shop data; evening and full-screen variants not separately checked |
| Final report: morning/evening, full-screen | — | 🟡 manually verified 2026-10-10 the morning variant renders correctly (route×item matrix); evening and full-screen variants not separately checked |
| Google Sheets refresh ×4 (morning/evening summary, morning/evening shop report) | `OrderAdmin/GoogleSheetWiringTest.php` | 🟡 wiring covered by tests; the actual live write was manually verified 2026-10-10 for Morning Summary against the real production spreadsheet (`storage/credentials.json`, real spreadsheet ID) - confirmed via the Logs table entry (only written after `updateRow()` returns) plus no exception in that minute's log output. Evening Summary and the 2 Shop Report refreshes share the identical `GoogleSheetExportService` call pattern and weren't separately triggered, to avoid writing to those 3 live sheets beyond what was needed to confirm the mechanism works |
| Processing → Complete bulk transfer | `OrderLifecycleTest.php`, `Services/OrderServiceTest.php` | ✅ |
| Profile: edit details, change password | `ProfileFormRequestTest.php` | ✅ |

## Rep

| Feature | Test coverage | Status |
|---|---|---|
| Dashboard stats (shop-scoped revenue query) | `RepDashboardTest.php` | ✅ shop-scoping query logic covered by tests; the rest of the view manually verified 2026-10-10 |
| My shops listing | — | ✅ manually verified 2026-10-10 |
| Create order (cart, add items, submit; no time-window restriction unlike Shop) | `Rep/CartAndOrderTest.php` | ✅ |
| Pending order: accept → Processing (access-gated) | `OrderLifecycleTest.php` | ✅ |
| Pending/Processing/Under-Review: item qty update + delete (access-gated, message varies by controller) | `Rep/ItemEditTest.php` | ✅ |
| Pending/Processing/Under-Review: add item to order (access-gated) | `Rep/AddItemTest.php` | ✅ |
| Under-Review: delete whole order | `Rep/ItemEditTest.php` | ✅ |
| Complete order: list/view | — | ✅ manually verified 2026-10-10 - correctly scoped to the rep's own shops |
| Export report | — | N/A — dead code; controller has always rendered `order-admin.export-report` instead of its own view (documented in Phase 5, not fixed) |
| Shop report: morning/evening, full-screen | — | 🔲 manual QA (visual report layout) |
| Final report: morning/evening, full-screen | `Rep/CompleteOrdersFormRequestTest.php` | 🟡 date-filter search covered; visual layout is 🔲 manual QA |
| Profile: edit details | `ProfileFormRequestTest.php` | ✅ |

## Shop

| Feature | Test coverage | Status |
|---|---|---|
| Dashboard | — | ✅ manually verified 2026-10-10 - matches DB counts exactly |
| Profile: edit details | `ProfileFormRequestTest.php` | ✅ |
| All orders: list/view | — | ✅ manually verified 2026-10-10 |
| Pending orders: item qty update + delete | `Shop/ItemEditTest.php` | ✅ |
| Pending orders: add item | `Shop/AddItemTest.php` | ✅ |
| Processing orders: list/view | — | ✅ manually verified 2026-10-10 |
| Under-Review orders: list/view | — | 🔲 manual QA - and currently *can't* be exercised even manually: zero Under Review orders exist anywhere in the dev dataset right now |
| Complete orders: list/view | — | ✅ manually verified 2026-10-10 |
| Default (standing) orders: list/view/delete, update item, delete item, add items | `OrderLifecycleTest.php` (creation only) | 🟡 creating a default order is covered; the empty-state list page was manually verified 2026-10-10 to render without error, but managing an *existing* default order (view/delete/update-item/delete-item/add-items) is still 🔲 manual QA - no test account with an existing default order was available this session |
| Create order: price-tier selection, duplicate-default-item guard | `Shop/PricingTierTest.php`, `Services/CartServiceTest.php`, `Services/PricingServiceTest.php` | ✅ |
| Create order: time window, zero-total rejection, same-period same-day rejection | `OrderLifecycleTest.php`, `Services/OrderServiceTest.php` | ✅ |
| Create order: save as default standing order | `OrderLifecycleTest.php`, `Services/OrderServiceTest.php` | ✅ |
| Cart: update qty, delete item | `Shop/ItemEditTest.php` | 🟡 covered via the pending-order item-edit path; the pre-order cart's own update/delete isn't separately asserted |

## Cross-cutting

| Feature | Test coverage | Status |
|---|---|---|
| Eloquent model relationships (Order↔Shop, Shop↔Routes, Rep↔Shop pivot, User↔Role, Product↔Category, PricingService wiring) | `ModelRelationshipsTest.php` | ✅ |
| Phase 1 FK backfill migrations | `Phase1BackfillTest.php` | ✅ |
| Form Request validation (spot checks across cart/item-edit endpoints) | `FormRequestValidationTest.php` | ✅ |
| `GoogleSheetExportService` construction + config-driven spreadsheet IDs | `GoogleSheetExportServiceTest.php` | ✅ |

## Known gaps (not yet closed)

- **Google Sheets actual writes** can't run in CI without live credentials and
  network access; only the wiring (correct spreadsheet target) is automated.
- Several admin CRUD **delete** actions (shops, routes, reps) and rep
  **access-update** aren't directly tested, only add/update.
- Shop's **default-order management** on an *existing* default order
  (view/delete/update-item/delete-item/add-items, as opposed to creating one)
  still has no automated or manual coverage - no test account with existing
  default-order data was available during the 2026-10-10 manual QA pass.
- **Export Report's actual Excel generation** (the download itself, not the
  selection form) wasn't exercised in the 2026-10-10 manual QA pass.
- Evening and full-screen variants of Shop Report / Final Report weren't
  separately checked in the 2026-10-10 pass (only the morning/normal variant
  was); same underlying query/view pattern as the checked variant, but not
  independently confirmed.
- **Under Review orders list/view** can't be manually verified right now -
  zero orders with that status exist anywhere in the current dev dataset.
- **Load/performance testing** of the dashboard and report queries is done -
  see [PERFORMANCE_NOTES.md](PERFORMANCE_NOTES.md). Found several dashboard
  queries taking seconds-to-minutes at real production data volume (~74k
  orders, ~1.9M cart rows); all have since been fixed (missing indexes, a
  `whereDate()` that defeated an existing one, caching for the genuinely
  unbounded aggregates) and re-verified against the same data.

## Manual QA pass, 2026-10-10

Walked through the remaining 🔲 rows above in a live browser (OrderAdmin,
Rep, and Shop accounts) against the real dev dataset. Everything checked
rendered correctly and matched direct DB query results - see the updated
rows above for exactly what was and wasn't covered. Three things initially
looked like regressions from this rebuild and were each traced back to the
original baseline commit (`15546ca`) to confirm they're pre-existing, not
introduced here - preserved as-is, not fixed, per this project's mandate:

- Every non-dashboard page throws Morris/Chartist console errors
  ("Graph container element not found" / "querySelectorAll on null") -
  `dashboard-ecommerce.js` and the `dashboard-ecommerce` wrapper div are
  copy-pasted onto every page's original Blade file, not just the actual
  dashboard, in the pre-rebuild baseline too.
- Shop's and Rep's sidebar "Dashboard" link points to `/order-admin/dashboard`
  instead of their own role's dashboard - present verbatim in the baseline's
  `shop/components/menu.blade.php` / `rep/components/menu.blade.php`.
- The Shop Report page's browser tab title reads "Order Admin | Cart" -
  already wrong in the baseline's `shop-report-morning.blade.php`.
