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
| Dashboard stats widgets + charts | — | 🔲 manual QA (visual) |
| Logs listing/search | — | 🔲 manual QA |
| Pending orders: list/search | — | 🔲 manual QA |
| Pending orders: accept → Processing | `OrderLifecycleTest.php`, `Services/OrderServiceTest.php` | ✅ |
| Pending/Processing/Under-Review: cancel + period reassignment, incl. the conflicting-order divergence between them | `OrderAdmin/OrderUpdateTest.php` | ✅ |
| Pending/Processing/Under-Review: item qty update + delete | `OrderAdmin/ItemEditTest.php` | ✅ |
| Pending/Processing/Under-Review: add item to order | `OrderAdmin/AddItemTest.php` | ✅ |
| Under-Review: delete whole order | `OrderAdmin/ItemEditTest.php` | ✅ |
| Complete orders: list/view | — | 🔲 manual QA |
| Products: add/update/delete | `OrderAdmin/AdminCrudFormRequestTest.php` | ✅ |
| Order times: add/delete (incl. reserved-name rejection) | `OrderAdmin/AdminCrudFormRequestTest.php` | ✅ |
| Product categories: add/update/delete | `OrderAdmin/AdminCrudFormRequestTest.php` | ✅ |
| Shops: add/update/delete | `OrderAdmin/AdminCrudFormRequestTest.php` | 🟡 add/update covered; delete (`shops.delete`) not directly tested |
| Routes: add/update/delete | `OrderAdmin/AdminCrudFormRequestTest.php` | 🟡 add/update covered; delete not directly tested |
| Reps: add, assign/update shop assignments, update access, delete | `OrderAdmin/AdminCrudFormRequestTest.php` | 🟡 add + assign covered; access-update and delete not directly tested |
| Create order on a shop's behalf (cart, add items, submit) | `OrderAdmin/CartAndOrderTest.php` | ✅ |
| Export report (Excel download) | — | 🔲 manual QA (file generation/formatting) |
| Shop report: morning/evening, normal/full-screen | — | 🔲 manual QA (visual report layout) |
| Final report: morning/evening, full-screen | — | 🔲 manual QA (visual report layout) |
| Google Sheets refresh ×4 (morning/evening summary, morning/evening shop report) | `OrderAdmin/GoogleSheetWiringTest.php` | 🟡 wiring (correct spreadsheet target) covered; the actual Google API write is 🔲 manual QA (no network access in tests) |
| Processing → Complete bulk transfer | `OrderLifecycleTest.php`, `Services/OrderServiceTest.php` | ✅ |
| Profile: edit details, change password | `ProfileFormRequestTest.php` | ✅ |

## Rep

| Feature | Test coverage | Status |
|---|---|---|
| Dashboard stats (shop-scoped revenue query) | `RepDashboardTest.php` | 🟡 the shop-scoping query logic is covered; the rest of the dashboard view is 🔲 manual QA |
| My shops listing | — | 🔲 manual QA |
| Create order (cart, add items, submit; no time-window restriction unlike Shop) | `Rep/CartAndOrderTest.php` | ✅ |
| Pending order: accept → Processing (access-gated) | `OrderLifecycleTest.php` | ✅ |
| Pending/Processing/Under-Review: item qty update + delete (access-gated, message varies by controller) | `Rep/ItemEditTest.php` | ✅ |
| Pending/Processing/Under-Review: add item to order (access-gated) | `Rep/AddItemTest.php` | ✅ |
| Under-Review: delete whole order | `Rep/ItemEditTest.php` | ✅ |
| Complete order: list/view | — | 🔲 manual QA |
| Export report | — | N/A — dead code; controller has always rendered `order-admin.export-report` instead of its own view (documented in Phase 5, not fixed) |
| Shop report: morning/evening, full-screen | — | 🔲 manual QA (visual report layout) |
| Final report: morning/evening, full-screen | `Rep/CompleteOrdersFormRequestTest.php` | 🟡 date-filter search covered; visual layout is 🔲 manual QA |
| Profile: edit details | `ProfileFormRequestTest.php` | ✅ |

## Shop

| Feature | Test coverage | Status |
|---|---|---|
| Dashboard | — | 🔲 manual QA |
| Profile: edit details | `ProfileFormRequestTest.php` | ✅ |
| All orders: list/view | — | 🔲 manual QA |
| Pending orders: item qty update + delete | `Shop/ItemEditTest.php` | ✅ |
| Pending orders: add item | `Shop/AddItemTest.php` | ✅ |
| Processing orders: list/view | — | 🔲 manual QA |
| Under-Review orders: list/view | — | 🔲 manual QA |
| Complete orders: list/view | — | 🔲 manual QA |
| Default (standing) orders: list/view/delete, update item, delete item, add items | `OrderLifecycleTest.php` (creation only) | 🟡 creating a default order is covered; managing an existing one (view/delete/update-item/delete-item/add-items) is 🔲 manual QA |
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

- **Visual/report rendering** (dashboards, Shop Report, Final Report, Export
  Report) has no automated coverage anywhere — these need either a browser
  screenshot-based test or a standing manual QA pass before each release.
- **Google Sheets actual writes** can't run in CI without live credentials and
  network access; only the wiring (correct spreadsheet target) is automated.
- Several admin CRUD **delete** actions (shops, routes, reps) and rep
  **access-update** aren't directly tested, only add/update.
- Shop's **default-order management** (view/delete/update-item/delete-item/
  add-items on an *existing* default order, as opposed to creating one) has
  no automated coverage.
- **Load/performance testing** of the dashboard and report queries is done -
  see [PERFORMANCE_NOTES.md](PERFORMANCE_NOTES.md). Found several dashboard
  queries taking seconds-to-minutes at real production data volume (~74k
  orders, ~1.9M cart rows); all have since been fixed (missing indexes, a
  `whereDate()` that defeated an existing one, caching for the genuinely
  unbounded aggregates) and re-verified against the same data.
