# Codebase Concerns

**Analysis Date:** 2026-04-01

## Tech Debt

**Database structure needs cleanup before new marketplace features:**
- Files: `.planning/codebase/DATABASE-STRUCTURE-AUDIT.md`, `database/migrations/*`, `app/Models/*`, `app/Models/Users/*`, `database/factories/*`, `database/seeders/*`
- Issue: the live schema has separate role-specific user tables, no physical roles/permissions tables, overlapping product-attribute pivots, split cart storage, split product image storage, missing domain tables for favorites/reviews/messages/addresses/notifications, and several weak constraints.
- Why: marketplace behavior was added incrementally around Livewire screens and separate auth guards without a consolidated domain model pass.
- Impact: new features can land on the wrong persistence path, duplicate data, or rely on unconstrained status/relationship state.
- Fix approach: use the database audit as the pre-feature checklist; decide user/role strategy, normalize addresses/images/cart/order status, consolidate attribute pivots, and add missing constraints/indexes with additive migrations.

**Order domain mixes checkout, payment, fulfillment, and seller settlement in one record:**
- Files: `app/Livewire/Frontend/Buyer/Cart/Index.php`, `app/Livewire/Frontend/Seller/Orders/Show.php`, `app/Livewire/Frontend/Seller/Orders/Index.php`, `app/Livewire/Frontend/Seller/Dashboard.php`, `app/Models/Order.php`, `database/migrations/2024_03_20_000009_create_orders_table.php`, `database/migrations/2024_12_18_074617_create_order_item_table.php`
- Issue: one `orders` row can contain `order_items` from multiple sellers, but `status` and `payment_status` live only on the parent order and seller-facing screens mutate them globally.
- Why: checkout and seller dashboards were built around a simplified single-order workflow instead of seller-level fulfillment records.
- Impact: multi-seller orders have no correct source of truth for who has accepted, cancelled, shipped, or been paid; finance and UI states can diverge.
- Fix approach: either enforce one seller per order at checkout or introduce seller-scoped fulfillment/payment records and derive seller dashboards from `order_items` + `seller_transactions`.

**Schema and model layer contain parallel legacy structures:**
- Files: `app/Models/Admin.php`, `app/Models/Users/Admin.php`, `app/Models/AttributeProduct.php`, `app/Models/ProductAttributeValue.php`, `database/migrations/2024_03_20_000013_create_product_attribute_table.php`, `database/migrations/2024_03_20_000014_create_attribute_product_table.php`, `database/migrations/2024_03_21_000000_create_product_attribute_value_table.php`, `database/migrations/2025_01_09_115725_add_field_balance_to_users_buyers_table.php`
- Issue: the codebase keeps two admin model classes, three product/attribute pivot tables, and a `users_buyers.balance` column that is not used by the runtime code.
- Why: schema changes were layered on without removing abandoned paths.
- Impact: migrations are harder to reason about, relation bugs are easier to introduce, and developers can update the wrong table or model.
- Fix approach: audit live data, remove dead models/tables/columns, and standardize on one admin model and one product-attribute storage strategy.

**Cross-cutting concerns stay embedded in Livewire components and providers:**
- Files: `app/Livewire/Backend/Buyers/Credit.php`, `app/Livewire/Frontend/Buyer/Cart/Index.php`, `app/Providers/AuthServiceProvider.php`, `app/Providers/GlobalSettingsServiceProvider.php`, `app/Providers/UserGuardServiceProvider.php`
- Files: `app/Policies` not present, `app/Observers` not present, `app/Listeners` not present, `app/Events` not present
- Issue: authorization, balance mutation, stock mutation, cache lookup, and view sharing are handled inline instead of via policies, observers, events, or action classes.
- Why: Livewire screens were allowed to grow into the primary business-logic layer.
- Impact: behavior is duplicated, hard to test in isolation, and easy to break when changing UI components.
- Fix approach: move state transitions into dedicated actions/services, add policies for resource-level authorization, and move cache invalidation into observers/events.

**Migration quality is patch-style and rollback coverage is incomplete:**
- Files: `database/migrations/2025_01_06_121751_make_field_min_order_count_required_products_table.php`, `database/migrations/2025_01_07_125555_add_field_is_active_to_users_buyers_and_users_sellers_tables.php`, `database/migrations/2025_01_08_074308_add_password_reset_at_field_to_buyers_sellers_table.php`, `database/migrations/2025_01_15_143614_add_field_bank_account_to_buyers_and_sellers_users_table.php`, `database/migrations/2025_01_21_061404_add_field_veterinary_sertificate_to_seller.php`
- Issue: multiple migrations have no `down()` method, and one migration duplicates `password_reset_at` that was already added earlier.
- Why: schema fixes were applied incrementally without normalizing the migration history.
- Impact: rollback paths are unreliable and environment drift becomes more likely over time.
- Fix approach: add missing rollback steps, consolidate duplicate column-history migrations, and verify the current schema before any new migration work.

## Known Bugs

**Admin product creation screen submits to a missing Livewire action:**
- Files: `app/Livewire/Backend/Products/Create.php`, `resources/views/backend/products/form.blade.php`, `tests/Feature/Controllers/Backend/ProductControllerTest.php`
- Symptoms: the admin create page renders, but submitting the form targets `wire:submit.prevent="save"` even though the component has no `save()` method.
- Trigger: visit `route('backend.products.create')` and submit the form.
- Workaround: create products through seller-facing screens or direct database seeding.
- Root cause: `app/Livewire/Backend/Products/Create.php` is only a read-only form scaffold; creation logic, validation, and uploads were never implemented.
- Blocked by: no behavior test exercises form submission, only page rendering.

**Normal checkout makes pending-order workflows unreachable:**
- Files: `app/Livewire/Frontend/Buyer/Cart/Index.php`, `app/Livewire/Frontend/Buyer/Orders/Show.php`, `app/Livewire/Frontend/Seller/Orders/Show.php`
- Symptoms: buyer and seller screens are built around pending orders, but checkout marks every new order as paid inside the same transaction.
- Trigger: complete a normal cart checkout.
- Workaround: manually edit the order in the database or create special seed data with `payment_status = pending`.
- Root cause: `checkout()` creates the order as pending and then immediately updates it to `Order::STATUS['PAID']` with the inline comment "Payment simulation: always succeed for now."
- Blocked by: no real payment state machine and no integration test covering post-checkout seller/buyer actions.

**A seller can change the status of a shared order for every seller attached to it:**
- Files: `app/Livewire/Frontend/Buyer/Cart/Index.php`, `app/Livewire/Frontend/Seller/Orders/Show.php`, `app/Livewire/Frontend/Seller/Transactions/Index.php`, `app/Models/SellerTransaction.php`
- Symptoms: a seller with one line item can change the whole order to `paid` or `cancelled`; later sellers lose the ability to settle their own portion and may never receive a transaction record.
- Trigger: place one order containing products from multiple sellers, then open it as one seller in `seller.orders.show`.
- Workaround: manual finance correction and status repair by an admin.
- Root cause: seller authorization checks only prove the seller owns at least one `order_items` row, but the mutation updates the parent `orders` row and not a seller-scoped fulfillment record.

**Buyer dashboard data is not trustworthy:**
- Files: `app/Livewire/Frontend/Buyer/Dashboard.php`, `resources/views/frontend/buyer/dashboard/index.blade.php`, `resources/views/frontend/buyer/dashboard/orders.blade.php`
- Symptoms: the top dashboard cards show fixed placeholder amounts (`1 000 €`, `2 000 €`, `3 000 €`) and the component computes metrics with `total_price` on `Order`, even though the persisted order column is `order_total`.
- Trigger: visit `route('buyer.dashboard')`.
- Workaround: use the order list directly instead of dashboard summary numbers.
- Root cause: the view still contains prototype markup and the component aggregates against a field that does not exist on the `orders` table.

## Security Considerations

**Verification and reset flows overload `remember_token` and store tokens in plaintext columns:**
- Files: `app/Livewire/Frontend/Auth/Register.php`, `app/Livewire/Frontend/Auth/ForgotPassword.php`, `app/Livewire/Frontend/Auth/ResetPassword.php`, `app/Livewire/Frontend/Auth/VerifyEmail.php`, `app/Livewire/Frontend/Auth/VerificationNotice.php`, `app/Livewire/Frontend/Auth/RegisterSuccess.php`, `app/Models/Users/Buyer.php`, `app/Models/Users/Seller.php`, `config/auth.php`
- Risk: password reset and email verification share the same column and lifecycle; tokens are stored directly on user rows; `ForgotPassword` and resend flows disclose whether an email exists.
- Current mitigation: password reset expiry is checked with `password_reset_at`, and verification resend has a small rate limit.
- Recommendations: use Laravel password brokers instead of custom token storage, introduce a dedicated email-verification token column or signed URLs, return generic success responses for unknown emails, and throttle forgot-password/admin-login endpoints.

**Admin authorization stops at the guard boundary:**
- Files: `routes/admin.php`, `app/Providers/AuthServiceProvider.php`, `app/Livewire/Backend/Buyers/Index.php`, `app/Livewire/Backend/Sellers/Index.php`, `app/Livewire/Backend/Products/Index.php`, `app/Livewire/Backend/Categories/Index.php`, `app/Livewire/Backend/Attributes/Index.php`
- Risk: destructive admin actions only require `auth:admin`; there are no policies, no per-resource `authorize()` calls, and no role/permission separation inside the admin panel.
- Current mitigation: admin routes are behind the admin guard.
- Recommendations: add policies for each mutable resource, enforce authorization inside Livewire actions, and separate super-admin privileges from ordinary admin access.

**Buyer credit attachments live on the public disk:**
- Files: `app/Livewire/Backend/Buyers/Credit.php`, `app/Livewire/Backend/Buyers/CreditHistory.php`, `database/migrations/2025_02_04_000000_create_credit_attachments_table.php`
- Risk: credit evidence files are stored under the `public` disk, so any exposed or guessable storage path bypasses the guarded download method.
- Current mitigation: `downloadAttachment()` checks that the attachment belongs to the current buyer history before serving it.
- Recommendations: move attachments to a private disk, serve them only through authorized streamed downloads, and keep opaque file identifiers separate from user-visible filenames.

## Performance Bottlenecks

**Seller order analytics hydrate full history into memory on every request:**
- Files: `app/Livewire/Frontend/Seller/Orders/Index.php`, `app/Livewire/Frontend/Seller/Dashboard.php`, `resources/views/frontend/seller/orders/index.blade.php`
- Problem: seller order pages load every matching `order_items` row with `->get()`, then derive status counts and totals in collections.
- Measurement: `app/Livewire/Frontend/Seller/Orders/Index.php` performs one full-history `get()`, four extra revenue `sum()` queries, one top-products aggregate, and multiple collection passes per request.
- Cause: analytics are computed in PHP instead of grouped SQL or cached reporting tables.
- Improvement path: paginate visible orders, move counters into grouped queries/scopes, cache dashboard summaries, and precompute seller reporting data.

**Buyer order and dashboard pages do full-history aggregation in PHP:**
- Files: `app/Livewire/Frontend/Buyer/Orders/Index.php`, `app/Livewire/Frontend/Buyer/Dashboard.php`
- Problem: buyer history pages fetch all orders for the buyer and compute counts/totals in memory.
- Measurement: both components call `->get()` for the full matching order history; `Dashboard` then flattens all order items and performs several collection scans.
- Cause: no paginated/reporting split between "recent activity" and "all-time stats."
- Improvement path: keep recent orders paginated, calculate summaries in SQL, and stop rehydrating the full order graph for dashboard cards.

**Blade rendering still triggers database work and hidden lazy loads:**
- Files: `resources/views/backend/products/form.blade.php`, `resources/views/backend/buyers/orders.blade.php`, `resources/views/backend/attributes/index.blade.php`, `app/Livewire/Frontend/Buyer/Products/Show.php`, `resources/views/frontend/buyer/products/show.blade.php`
- Problem: the backend product form calls `$cat->subcategories()->orderBy(...)->get()` inside the template, buyer orders sum related items in Blade, attributes fall back to `$attribute->values->count()`, and the buyer product page touches several relations that the component never eager-loads.
- Measurement: `resources/views/backend/products/form.blade.php` creates an `1 + N` query pattern where `N` is the number of root categories rendered.
- Cause: query composition leaked into templates instead of staying in Livewire components or model scopes.
- Improvement path: eager-load subcategories/relations in the component, pass prebuilt option trees, and remove relation queries and aggregates from Blade entirely.

**Search endpoint uses non-sargable raw JSON and wildcard filtering:**
- Files: `app/Http/Controllers/Api/ProductSearchController.php`
- Problem: product and category search rely on `LOWER(...) LIKE '%query%'` and `JSON_EXTRACT(...)` raw clauses.
- Measurement: the endpoint does two uncached queries per request, both using function-wrapped columns that normal B-tree indexes cannot serve effectively for leading-wildcard search.
- Cause: search was implemented directly in SQL expressions rather than via a dedicated search index or normalized searchable columns.
- Improvement path: add normalized searchable columns or full-text search, validate/trim query length, and remove raw JSON search from the request path.

## Fragile Areas

**Custom multi-guard auth stack is spread across shared Livewire screens and providers:**
- Files: `config/auth.php`, `app/Providers/AuthServiceProvider.php`, `app/Providers/UserGuardServiceProvider.php`, `app/Livewire/Frontend/Auth/Login.php`, `app/Livewire/Frontend/Auth/Register.php`, `app/Livewire/Frontend/Auth/ForgotPassword.php`, `app/Livewire/Frontend/Auth/ResetPassword.php`, `app/Livewire/Frontend/Auth/VerifyEmail.php`
- Why fragile: buyer and seller flows share components that infer behavior from the URL segment while also relying on custom providers, manual mail flows, and overloaded token fields.
- Common failures: redirects point to the wrong guard, token lifecycles collide, and changes to one user type can unintentionally break the other.
- Safe modification: change one flow at a time, add end-to-end tests for both buyer and seller paths, and avoid reusing columns for unrelated auth concerns.
- Test coverage: route-level status-code tests exist, but there are no interaction tests for login throttling, reset flow, verification flow, or cross-guard edge cases.

**Global settings behavior is split between a provider, direct queries, and a cached shared view variable:**
- Files: `app/Providers/GlobalSettingsServiceProvider.php`, `app/Livewire/Backend/Settings/Index.php`, `app/Livewire/Frontend/Buyer/Cart/Index.php`, `app/Models/GlobalSettings.php`
- Why fragile: settings are loaded in multiple places, one path caches them, another reads directly from the database, and cache invalidation is not tied to updates.
- Common failures: stale pricing in cart totals, inconsistent values between screens, and hidden fallback to `0` if provider boot catches an exception.
- Safe modification: centralize access behind one settings service/action, invalidate cache on update, and stop swallowing all exceptions in the provider.
- Test coverage: `tests/Unit/Providers/GlobalSettingsServiceProviderTest.php` only checks method existence, not actual cache behavior or stale-value invalidation.

**Schema history requires manual caution before any migration or relation change:**
- Files: `database/migrations/2025_01_07_125555_add_field_is_active_to_users_buyers_and_users_sellers_tables.php`, `database/migrations/2025_01_08_074308_add_password_reset_at_field_to_buyers_sellers_table.php`, `database/migrations/2025_01_09_115725_add_field_balance_to_users_buyers_table.php`, `database/migrations/2026_03_27_000000_optimize_core_indexes.php`
- Why fragile: duplicate column history, dead columns, late index additions, and missing rollback methods mean the migration chain does not document one obvious canonical state.
- Common failures: local/test/prod schemas drift, new migrations assume columns exist in a particular shape, and rollbacks become destructive.
- Safe modification: inspect the actual schema first, write additive migrations only, and explicitly document any cleanup migration that removes dead structures.
- Test coverage: there are seeder and model tests, but no migration-integrity test proving a clean migrate/rollback/migrate cycle.

**UI delete/confirm flows depend on package-specific notification wiring:**
- Files: `app/Livewire/Concerns/InteractsWithWireUi.php`, `app/Providers/AppServiceProvider.php`, `app/Livewire/Backend/Products/Index.php`, `app/Livewire/Backend/Buyers/Index.php`, `app/Livewire/Frontend/Seller/Orders/Show.php`
- Why fragile: confirmation modals and notifications depend on `WireUi\Traits\WireUiActions` plus custom script ordering in `AppServiceProvider`.
- Common failures: if script boot order changes, destructive actions appear clickable but modal confirmation/feedback stops working.
- Safe modification: keep UI interactions behind the trait, regression-test destructive actions after JS or package upgrades, and avoid bypassing the shared confirm helpers.
- Test coverage: no browser or Livewire interaction tests assert that confirm/delete flows still execute after UI library changes.

## Scaling Limits

**Seller and buyer dashboards scale with history size, not viewport size:**
- Files: `app/Livewire/Frontend/Seller/Dashboard.php`, `app/Livewire/Frontend/Seller/Orders/Index.php`, `app/Livewire/Frontend/Buyer/Dashboard.php`, `app/Livewire/Frontend/Buyer/Orders/Index.php`
- Current capacity: acceptable only while each account has a relatively small order history because each request hydrates the full matching set.
- Limit: once individual sellers or buyers accumulate thousands of orders/items, every dashboard/filter request becomes a large in-memory aggregation job.
- Symptoms at limit: slow Livewire responses, high PHP memory use, and timeouts on account dashboards.
- Scaling path: separate "list" and "analytics" queries, add summarized reporting tables, and cache seller/buyer KPI cards.

**Search is bounded by catalog size because it does not use a search-oriented index:**
- Files: `app/Http/Controllers/Api/ProductSearchController.php`
- Current capacity: small catalogs with short search volumes.
- Limit: large category trees and product catalogs make `LOWER(...) LIKE '%term%'` + `JSON_EXTRACT(...)` progressively slower.
- Symptoms at limit: slow autocomplete responses and growing database CPU for every keystroke.
- Scaling path: normalize searchable text into indexed columns or move autocomplete to a dedicated full-text/search service.

**Credit history export runs synchronously in the request cycle:**
- Files: `app/Livewire/Backend/Buyers/CreditHistory.php`
- Current capacity: moderate credit histories because `exportCsv()` streams in-process with `chunk(1000)`.
- Limit: large histories still hold a Livewire request open for the full export duration.
- Symptoms at limit: slow downloads, HTTP timeouts, and repeated export attempts from admins.
- Scaling path: queue exports to storage, notify the admin when the file is ready, and keep streamed export only for small datasets.

## Dependencies at Risk

**`lukepolo/laracart` is deeply embedded in UI and checkout logic:**
- Files: `app/Livewire/Frontend/Buyer/Cart/Index.php`, `app/Livewire/Frontend/Buyer/Products/Show.php`, `resources/views/layouts/frontend/header.blade.php`, `resources/views/frontend/buyer/cart/index.blade.php`, `composer.json`
- Risk: cart behavior, item hashes, totals, and header rendering are all coupled directly to the package API.
- Impact: replacing or hardening cart behavior requires touching buyer product pages, checkout, header UI, and tests at the same time.
- Migration plan: wrap cart access in an application service and move package-specific calls out of Blade and Livewire screens.

**`wireui/wireui` and `intervention/image` upgrades will be expensive because usage is duplicated:**
- Files: `app/Livewire/Concerns/InteractsWithWireUi.php`, `app/Providers/AppServiceProvider.php`, `app/Livewire/Backend/Products/Edit.php`, `app/Livewire/Frontend/Seller/Products/Create.php`, `app/Livewire/Frontend/Seller/Products/Edit.php`, `composer.json`
- Risk: notification/modal behavior and image-processing code are duplicated across several components and depend on package-specific APIs.
- Impact: UI-library or image-library upgrades require synchronized edits across multiple flows, increasing regression risk.
- Migration plan: centralize image storage/transforms behind one reusable action/service and keep all WireUI interactions routed through the shared trait.

## Missing Critical Features

**No real payment workflow exists:**
- Files: `app/Livewire/Frontend/Buyer/Cart/Index.php`
- Problem: checkout uses an inline "always succeed" payment simulation and immediately marks orders as paid.
- Current workaround: none in the product code; manual data correction is the only fallback.
- Blocks: real payment reconciliation, realistic pending/cancel flows, refunds, and reliable seller settlement.
- Implementation complexity: High, because the order domain needs clearer seller/payment boundaries before a gateway can be added safely.

**Admin-side product creation is not implemented end to end:**
- Files: `app/Livewire/Backend/Products/Create.php`, `resources/views/backend/products/form.blade.php`
- Problem: the route and form exist, but validation, persistence, upload handling, and attribute syncing are missing.
- Current workaround: create products through seller-facing flows or seed data.
- Blocks: admin product onboarding and any backend-only catalog management workflow.
- Implementation complexity: Medium, because `Edit` already contains most of the needed logic and can be extracted/shared.

**Buyer dashboard is still partly prototype UI instead of production analytics:**
- Files: `resources/views/frontend/buyer/dashboard/index.blade.php`, `app/Livewire/Frontend/Buyer/Dashboard.php`
- Problem: placeholder cards, commented-out metrics, and inconsistent computed fields mean the dashboard is not a reliable operational screen.
- Current workaround: use order detail/list pages instead of dashboard summaries.
- Blocks: buyer-facing reporting, KPI trust, and any product decisions based on dashboard engagement.
- Implementation complexity: Medium, because the page needs both view cleanup and data-model correction.

## Test Coverage Gaps

**Livewire action coverage is effectively absent:**
- Files: `tests/Feature/Controllers/Backend/ProductControllerTest.php`, `tests/Feature/Controllers/Frontend/Seller/ProductControllerTest.php`, `tests/Feature/Controllers/Frontend/Buyer/CartControllerTest.php`
- Files: `app/Livewire/Backend/Products/Create.php`, `app/Livewire/Backend/Buyers/Credit.php`, `app/Livewire/Backend/Buyers/CreditHistory.php`, `app/Livewire/Frontend/Buyer/Cart/Index.php`, `app/Livewire/Frontend/Seller/Orders/Show.php`
- What's not tested: component methods such as `save`, `submitCredit`, `exportCsv`, `checkout`, `cancelOrder`, and `updateStatus`.
- Risk: broken mutations ship unnoticed; the missing admin product `save()` method is the clearest example.
- Priority: High
- Difficulty to test: Medium, because `Livewire::test()` coverage is missing across the suite rather than blocked by infrastructure.

**Authentication edge cases are not behavior-tested:**
- Files: `app/Livewire/Frontend/Auth/Login.php`, `app/Livewire/Frontend/Auth/ForgotPassword.php`, `app/Livewire/Frontend/Auth/ResetPassword.php`, `app/Livewire/Frontend/Auth/VerifyEmail.php`, `app/Livewire/Backend/Auth/Login.php`
- What's not tested: login throttling, admin-login brute-force protection, password-reset enumeration behavior, token expiry, and resend-verification flow.
- Risk: security regressions or broken auth flows will surface only in manual testing or production.
- Priority: High
- Difficulty to test: Medium, because the flows already exist and mostly need focused feature tests around mail, rate limiting, and redirects.

**Multi-seller order settlement has no regression coverage:**
- Files: `app/Livewire/Frontend/Buyer/Cart/Index.php`, `app/Livewire/Frontend/Seller/Orders/Show.php`, `app/Models/SellerTransaction.php`, `app/Livewire/Frontend/Seller/Transactions/Index.php`
- What's not tested: one checkout containing multiple sellers, subsequent seller status changes, and transaction/balance side effects.
- Risk: silent finance bugs and stuck seller balances.
- Priority: High
- Difficulty to test: High, because the current model conflates order-level and seller-level state and will require scenario-heavy tests.

**Performance-sensitive search and reporting paths are only smoke-tested:**
- Files: `tests/Feature/Controllers/Api/ProductSearchControllerTest.php`, `app/Http/Controllers/Api/ProductSearchController.php`, `app/Livewire/Frontend/Seller/Orders/Index.php`, `app/Livewire/Frontend/Buyer/Dashboard.php`
- What's not tested: locale-specific search behavior, large-result filtering, invalid `locale`/query combinations, and dashboard/reporting correctness.
- Risk: raw query changes or reporting fixes can break silently while status-code-only tests still pass.
- Priority: Medium
- Difficulty to test: Medium, because current tests already reach these routes but do not assert the underlying behavior in detail.

---

*Concerns audit: 2026-04-01*
*Update as issues are fixed or new ones discovered*
