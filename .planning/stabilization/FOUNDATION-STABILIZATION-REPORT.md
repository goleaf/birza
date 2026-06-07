# Foundation Stabilization Report

Date: 2026-06-07

Scope: Laravel marketplace foundation audit before adding new major features.

This report captures the current project state only. No application refactor has been started from this stabilization prompt yet.

## Baseline

Observed stack:

- Laravel 12.61.1
- PHP 8.5
- Livewire 4.3.1
- Sanctum 4.3.2
- Laravel Boost 2.4.9
- PHPUnit 11.5
- Tailwind CSS 3.4
- SQLite local database
- Custom Livewire/Blade admin area. No Filament resources are currently present.

Verification already run:

- `composer validate --no-check-publish`: passes.
- `npm run build`: passes.
- `php artisan migrate:fresh --seed --no-interaction`: passes.
- `php artisan test --compact`: fails.
- Second full test run with JUnit output: 353 passed, 24 failed.

Build warnings:

- Browserslist/caniuse-lite is stale.
- Tailwind reports an unnecessary `length` typehint in `h-[length:var(--border)]`.

Important worktree note:

- The repository is already heavily dirty with many modified, added, and untracked files across application code, migrations, docs, and tests.
- Stabilization work must use scoped staging and small commits. Do not revert unrelated user work.

## Executive Summary

The project is a promising Laravel marketplace base. It already has separated route files for admin, buyer, and seller areas, a Livewire page structure, normalized marketplace tables in progress, a database cart and checkout action, order status history work, product image pipeline work, documentation, factories, and a growing test suite.

The foundation is not yet stable enough for new large features. The biggest blockers are:

- Full test suite is red.
- Several tests are stale relative to the current cart/order/image schema.
- Role and permission architecture is inconsistent.
- Many policy files exist but are default-deny scaffolds and are not mapped.
- Authorization is still often enforced inline in Livewire instead of through policies.
- UI stack is mixed across Mary UI, WireUI, daisyUI, Flowbite, Tailwind, and custom wrappers.
- Migrations and runtime schema show drift, especially around notifications.
- Buyer, seller, and admin logic is mostly separated by route files, but not yet consistently separated in layout, authorization, actions, and tests.
- Demo seeders exist but are not fully wired, idempotent, or verified.
- Product/order/cart/image behavior has improved, but docs and tests still describe older LaraCart/session behavior in places.
- Performance hotspots remain in buyer/seller dashboards and order lists, where full collections are loaded and aggregated in memory.

## What Is Already Good

Routes and areas:

- `routes/admin.php`, `routes/buyer.php`, and `routes/seller.php` exist.
- Admin, buyer, and seller private routes are grouped by prefix, route name, guard middleware, and active-account middleware.
- `bootstrap/app.php` centralizes routing and middleware aliases in the Laravel 12 style.

Architecture:

- Most browser pages are route-bound Livewire components instead of controller-heavy pages.
- Checkout has moved toward an action-based design with `App\Actions\Cart\CreateOrdersFromCartAction`.
- Product image handling has dedicated action classes.
- Several marketplace policies, enums, models, factories, and feature tests have been introduced.

Database:

- `users` now exists as a base user table, with buyer and seller profile tables linked by `user_id`.
- `carts` and `cart_items` exist as normalized tables.
- `orders` and `order_items` include buyer/seller/product snapshots.
- `product_images` exists as a normalized image table.
- Useful indexes exist on several common marketplace filters, including products, carts, orders, and order items.
- Money-like order columns use decimal columns in current migrations.

Security:

- Admin, buyer, and seller guards exist.
- `active.account` middleware is applied to private route groups.
- `OrderPolicy` contains real actor-aware logic and uses shared actor helpers.
- Seller product mutation paths often scope by the authenticated seller id, which is better than trusting request ids.

Testing:

- The test suite is substantial and already covers many areas.
- Current full suite output shows hundreds of passing tests, which is a good starting point.
- New feature/security/seed/image/cart/order tests are being introduced, even though several are not yet stable.

Docs:

- `README.md`, `CHANGELOG.md`, and `docs/` exist.
- The docs already acknowledge some incomplete areas and include release-oriented material.

Dependencies:

- Laravel Debugbar is dev-only and excluded from auto-discovery.
- Laravel Boost is available and should remain the preferred app-aware inspection tool.

## What Is Risky

Test baseline:

- The suite fails with 24 failures after a fresh migration/seed and full test run.
- Failures include stale model expectations, route/auth expectation drift, schema/table drift, readonly SQLite test database state, image fixture failures, idempotency failures, and marketplace authorization failures.
- Until the suite is green, new feature work will be hard to trust.

Authorization:

- `AuthServiceProvider` currently maps `OrderPolicy`, but many policy files are not registered and are not usable by the framework by convention because their signatures target `App\Models\User` while the app uses multiple authenticatable models.
- Many policy files return `false` for every operation. This is safe as a deny default but risky as a fake sense of completion.
- Some important Livewire actions rely on inline guard checks instead of policy calls.
- Admin protection is mostly guard/middleware based; there is no clear `accessAdminPanel` gate equivalent.

Area separation:

- Public, buyer, seller, and admin areas have route files, but the separation is not consistently expressed through layouts, navigation, policies, actions, tests, and documentation.
- Buyer catalog and cart behavior needs a product decision: private-only buyer routes, public catalog, or guest cart support. Current code and tests disagree.
- Seller account verification middleware exists but seller routes do not clearly enforce a verified seller requirement.

Database drift:

- The current migration file `database/migrations/2026_06_07_171238_create_notifications_table.php` creates `notifications`, but the runtime database after `migrate:fresh --seed` contains `user_notifications` instead.
- Migration files do not appear to explain the `user_notifications` table observed in SQLite. This must be resolved before notification work grows.
- `Order` still contains stale relationships such as `product()` and `country()` that do not match the current order schema.

UI/design:

- The UI stack currently mixes Mary UI, WireUI, daisyUI, Flowbite, Tailwind utilities, and custom `x-ui.*` wrappers.
- Flash messages use WireUI-style components while other wrappers use Mary-style components.
- Button/card/table/modal patterns are not yet governed by one design system.

Performance:

- Buyer dashboard loads all buyer orders and aggregates in PHP.
- Seller dashboard loads all seller order items and aggregates in PHP.
- Buyer and seller order index pages load full histories and aggregate in PHP instead of paginating and using query-level aggregates.
- Some Blade views still compute values or access authentication directly.

Documentation drift:

- README still references LaraCart/session cart behavior, while current code is moving to database-backed carts.
- CHANGELOG claims several foundation improvements that are not fully verified because the full suite is red.
- Existing `.planning/codebase` audits include useful history but contain stale facts after recent schema changes.

## What Is Inconsistent

Roles:

- The app has `Admin`, `Buyer`, `Seller`, and base `User` concepts.
- Policies use `App\Models\User` in many files, while route guards authenticate admins, buyers, and sellers directly.
- Some ownership rules are in policies, some in Livewire components, and some in query constraints.

Cart:

- `Cart` is now a header model with `user_id`, `guest_token`, and `status`.
- Existing unit tests still expect older fields like `product_id` and `quantity` on `Cart`.
- Route expectations around cart access are inconsistent with tests.

Notifications:

- Laravel notification-style files exist.
- Marketplace notification models/actions/components also exist.
- The schema table name is inconsistent between migrations and runtime database.
- Notification routes are not clearly integrated into buyer/seller/admin areas.

Admin:

- Project instructions mention Filament, but the app currently uses custom Livewire admin pages.
- Stabilization must either document custom Livewire admin as the current direction or create a future migration plan. It should not mix Filament into the current codebase accidentally.

Translations:

- `lang/en.json` and `lang/lt.json` contain scanner-generated code-like keys and junk strings.
- Translation parity tests exist but the key set is noisy and not trustworthy yet.

UI:

- Multiple component systems define the same primitives: buttons, alerts, modals, tables, dropdowns, badges, cards.
- Some layout/navigation code uses direct guard checks in Blade.

## What Is Duplicated

Authentication and role logic:

- Admin, buyer, and seller route groups are separate, but authorization logic is duplicated across middleware, Livewire components, layout visibility, and ad hoc guards.

Marketplace calculations:

- Dashboard/order totals are calculated in multiple Livewire components using collections.
- Seller order item summaries appear in seller dashboard and seller order index logic.

Image fields:

- `products` still has legacy image columns and JSON/library fields while normalized `product_images` exists.

UI primitives:

- Buttons, alerts, cards, tables, and modals are represented by several libraries and custom classes.

Documentation:

- README, changelog, and planning docs describe overlapping states of the project, and some are stale.

## What Is Missing

Architecture:

- A current architecture decision record for Livewire admin vs Filament.
- A clear area map for public, buyer, seller, and admin pages.
- A consistent action/service boundary for business operations.

Authorization:

- Complete mapped policies for products, carts, reviews, images, notifications, profiles, and admin actions.
- Livewire authorization checks for every private action.
- Tests for dangerous role/ownership attempts.

Database:

- Resolved notification table naming.
- Cleanup plan for legacy product image fields.
- Review of stale relationships and public identifiers/slugs.
- Confirmed indexes for actual list/filter/order queries.

Test data:

- Fully wired and idempotent demo seeders.
- Fresh database with realistic admin, buyer, seller, products, categories, images, carts, orders, reviews, favorites, notifications, addresses, and edge cases.

Tests:

- Green baseline.
- Role access tests for every private area.
- Ownership tests for seller products and buyer orders.
- Cart/checkout tests aligned with database cart behavior.
- Image upload and replacement tests that do not depend on missing real files.
- Notification tests after schema decision.

Docs:

- Roles guide.
- Testing guide.
- Installation and environment guide.
- Demo accounts guide.
- Release workflow.
- Honest feature status and known gaps.

## Area Audit

### Public Area

Current state:

- Public routes are limited compared with buyer/seller/admin routes.
- Catalog and cart behavior needs a product decision: public marketplace browsing with optional guest cart, or authenticated buyer-only shopping.

Risks:

- Tests and route behavior disagree about cart access.
- Public pages may not yet have a complete public layout/navigation standard.

Needs:

- Public route group with prefix/name/middleware decisions.
- Public layout and navigation separate from private buyer/seller/admin navigation.
- Explicit tests for public homepage, catalog, product detail, locale switching, and forbidden private URLs.

### Buyer Area

Current state:

- Buyer routes are grouped under `buyer.` and protected by `auth:buyer` and `active.account:buyer`.
- Buyer dashboard, product catalog, cart, checkout, orders, profile, and notifications are present or in progress.

Risks:

- Buyer dashboard and orders load full histories and aggregate in PHP.
- Buyer order ownership must be consistently policy-protected.
- Cart behavior is still changing and tests are stale.

Needs:

- Buyer layout, navigation, and dashboard contract.
- Buyer authorization rules for orders, cart, reviews, addresses, notifications, and profile.
- Query-level aggregates and pagination.

### Seller Area

Current state:

- Seller routes are grouped under `seller.` and protected by `auth:seller` and `active.account:seller`.
- Seller product management and orders exist.
- Inline seller ownership checks are present in places.

Risks:

- Seller verification is not clearly enforced across the private area.
- Product edit/delete authorization is not centralized in `ProductPolicy`.
- Seller dashboard/order pages load and aggregate large collections in memory.

Needs:

- Seller layout, navigation, and dashboard contract.
- Verified seller middleware decision.
- Policy-backed product, image, order item, notification, and profile actions.
- Tests for seller attempting to manage another seller's products/orders.

### Admin Area

Current state:

- Admin routes are grouped under `admin.` and protected by `auth:admin` and `active.account:admin`.
- Admin pages are custom Livewire pages, not Filament resources.

Risks:

- Admin authorization is mostly guard-based.
- Admin destructive actions need consistent policy/gate authorization and audit logging.
- There is no current Filament integration despite project instructions mentioning Filament.

Needs:

- Current admin architecture decision documented.
- `accessAdminArea` gate or equivalent.
- Admin action authorization and audit logging.
- Tests for non-admin access and dangerous admin operations.

## Technical Audit By Area

### Routes

Good:

- Area-specific route files exist.
- Route groups use prefixes, route names, guards, and active account middleware.
- Laravel 12 route wiring lives in `bootstrap/app.php`.

Risk:

- `php artisan route:list --except-vendor` hides route-bound Livewire pages because they resolve through vendor Livewire internals. Full route listing must be used for area counts.
- API search route is public and should have an explicit throttle decision.
- Area routes do not consistently express policy/gate authorization.

Files:

- `bootstrap/app.php`
- `routes/web.php`
- `routes/admin.php`
- `routes/buyer.php`
- `routes/seller.php`
- `routes/api.php`

### Controllers

Good:

- Most page work is in Livewire components instead of large controllers.
- Admin product controller-style actions are limited.

Risk:

- Controllers/components still contain business rules that belong in policies or actions.
- Admin product destructive actions need explicit authorization and audit logs.

Files:

- `app/Http/Controllers/**`
- `app/Livewire/**`

### Livewire Components

Good:

- Route-bound Livewire pages create a clear SSR-compatible structure.
- Cart checkout delegates to an action.
- Marketplace components are already split by frontend/backend and buyer/seller/admin areas.

Risk:

- Private Livewire actions need policy checks, not only hidden UI.
- Some components perform collection-heavy calculations.
- Some components mix validation, authorization, persistence, notification, and UI state.

Files:

- `app/Livewire/Frontend/Buyer/**`
- `app/Livewire/Frontend/Seller/**`
- `app/Livewire/Backend/**`

### Models

Good:

- Core marketplace models exist.
- Order status enum casting is in progress.
- Relationships are improving.

Risk:

- Some model relationships are stale relative to the current schema.
- Several models need consistent casts, scopes, fillables, public identifiers, and ownership helpers.
- Product image data is split between legacy product columns and normalized `product_images`.

Files:

- `app/Models/Product.php`
- `app/Models/Order.php`
- `app/Models/OrderItem.php`
- `app/Models/Cart.php`
- `app/Models/CartItem.php`
- `app/Models/ProductImage.php`
- `app/Models/Buyer.php`
- `app/Models/Seller.php`
- `app/Models/User.php`

### Migrations And Schema

Good:

- Fresh migration and seed currently pass.
- Several marketplace indexes are present.
- Normalized cart, order item, product image, and status history tables exist.

Risk:

- Notification migration and runtime schema disagree.
- Some older migrations/schema patterns still reflect the previous app shape.
- There may be missing indexes for real list queries after query cleanup.

Files:

- `database/migrations/*orders*`
- `database/migrations/*order_items*`
- `database/migrations/*order_status_histories*`
- `database/migrations/*products*`
- `database/migrations/*product_images*`
- `database/migrations/*carts*`
- `database/migrations/*cart_items*`
- `database/migrations/*notifications*`
- `database/migrations/*reviews*`
- `database/migrations/*addresses*`

### Policies, Gates, Middleware

Good:

- `OrderPolicy` contains real actor-aware authorization.
- `ResolvesPolicyActors` is a useful shared helper.
- Route groups use guard and active-account middleware.

Risk:

- Most generated policy files are default-deny scaffolds.
- Many policy methods type-hint the wrong actor model for a multi-guard app.
- Policy registration/mapping is incomplete.
- Seller and admin authorization often happens inline.

Files:

- `app/Providers/AuthServiceProvider.php`
- `app/Policies/**`
- `app/Policies/Concerns/ResolvesPolicyActors.php`
- `app/Http/Middleware/EnsureActiveAccount.php`
- `app/Http/Middleware/EnsureVerifiedAccount.php`
- `bootstrap/app.php`

### Blade Views, Layouts, UI Components

Good:

- Blade SSR is consistent with project constraints.
- Reusable components and wrappers exist.
- Tailwind build passes.

Risk:

- UI primitives are split across Mary UI, WireUI, daisyUI, Flowbite, and custom components.
- Some Blade layouts directly access guards or perform date/storage calculations.
- Design system decisions are not documented.

Files:

- `resources/views/layouts/**`
- `resources/views/components/**`
- `resources/views/frontend/**`
- `resources/views/backend/**`
- `tailwind.config.js`
- `resources/css/app.css`
- `resources/js/app.js`

### Translations

Good:

- Translation files exist for English and Lithuanian.
- Translation parity tests exist.

Risk:

- JSON translation files contain code-like scanner artifacts.
- Some user-facing strings are likely still hardcoded.
- Translation tests need cleanup before they become reliable guardrails.

Files:

- `lang/en.json`
- `lang/lt.json`
- `tests/Feature/Translations/TranslationFilesTest.php`
- `resources/views/**`
- `app/Livewire/**`

### Factories And Seeders

Good:

- Multiple factories and seeders exist.
- Fresh migration and base seeding pass.
- Demo seeders are being introduced.

Risk:

- Some seeder tests fail because seeders are not idempotent.
- Demo scenario seeder is not fully wired.
- Image seeding depends on a source that may not be readable.
- Seeders do not yet prove the app is operational from a fresh database.

Files:

- `database/seeders/DatabaseSeeder.php`
- `database/seeders/Demo/**`
- `database/factories/**`
- `tests/Feature/Seeders/**`

### Tests

Good:

- The suite is broad and growing.
- There are tests for marketplace flows, security, seeders, translations, images, cart, checkout, and orders.

Risk:

- Full suite is currently red.
- Some tests encode stale behavior.
- Some tests depend on environment state or non-idempotent seeders.
- Several `test_example` placeholder tests still hit `/` and fail because test schema is incomplete.

Files:

- `tests/Feature/Marketplace/**`
- `tests/Feature/Images/**`
- `tests/Feature/Security/**`
- `tests/Feature/Seeders/**`
- `tests/Feature/Controllers/**`
- `tests/Unit/Models/**`

### Notifications

Good:

- Notification classes and marketplace notification work are present.

Risk:

- Schema naming is inconsistent.
- Routes and policies are not clearly integrated.
- Actor ownership and read/unread behavior need tests.

Files:

- `app/Notifications/**`
- `app/Models/Notification.php` or `app/Models/UserNotification.php` if present
- `app/Livewire/**Notification**`
- `database/migrations/*notification*`
- `routes/**`

### Image Handling

Good:

- A normalized `product_images` pipeline is in progress.
- Image upload/replacement tests exist.
- Product images can be associated with products with primary/sort metadata.

Risk:

- Legacy product image fields remain.
- Tests fail when expected image files are not readable.
- Product delete/force-delete behavior must be verified against storage cleanup.

Files:

- `app/Actions/Images/**`
- `app/Models/ProductImage.php`
- `app/Models/Product.php`
- `app/Livewire/Frontend/Seller/Products/**`
- `resources/views/frontend/seller/products/**`
- `tests/Feature/Images/**`

### Cart And Checkout

Good:

- Database-backed cart tables exist.
- Checkout uses a dedicated action.
- Order item snapshots preserve product title/price/seller name.
- Stock is decremented during checkout.

Risk:

- Tests and docs still reference older cart behavior.
- Guest cart vs buyer-only cart must be decided.
- Checkout needs authorization, audit, notification, and address integration tests.

Files:

- `app/Actions/Cart/CreateOrdersFromCartAction.php`
- `app/Livewire/Frontend/Buyer/Cart/Index.php`
- `app/Models/Cart.php`
- `app/Models/CartItem.php`
- `app/Models/Order.php`
- `app/Models/OrderItem.php`
- `tests/Feature/Marketplace/CartCheckoutFeatureTest.php`

### Orders

Good:

- Order status enums and status history are in progress.
- Order item seller/product snapshots exist.
- `OrderPolicy` is more mature than other policies.

Risk:

- Some old relationships remain.
- Buyer/seller order lists are not fully optimized.
- Status changes need consistent audit logging, notifications, and policy enforcement.

Files:

- `app/Models/Order.php`
- `app/Models/OrderItem.php`
- `app/Models/OrderStatusHistory.php`
- `app/Enums/OrderStatus.php`
- `app/Enums/OrderPaymentStatus.php`
- `app/Policies/OrderPolicy.php`
- `app/Livewire/Frontend/Buyer/Orders/**`
- `app/Livewire/Frontend/Seller/Orders/**`
- `app/Livewire/Backend/Orders/**`

### Documentation

Good:

- README, changelog, docs index, release notes template, and several guides exist.

Risk:

- Documentation is ahead of verified behavior in places.
- Cart/checkout docs are stale.
- Release workflow exists but should be tied to passing verification.

Files:

- `README.md`
- `CHANGELOG.md`
- `docs/README.md`
- `docs/release-notes-template.md`
- `.planning/codebase/**`
- `.planning/quick/**`

### Composer Dependencies

Good:

- Core Laravel dependencies are current.
- Debugbar is dev-only.

Risk:

- `darryldecode/cart` remains in dependencies while checkout has moved toward database carts.
- UI packages overlap.
- Dependency cleanup should wait until replacement code and tests are green.

Files:

- `composer.json`
- `composer.lock`

### Frontend Dependencies

Good:

- Frontend build passes.

Risk:

- daisyUI, Flowbite, Mary UI, WireUI, and custom UI wrappers overlap.
- `resources/js/app.js` contains a stale Livewire v3 comment while the app uses Livewire 4.

Files:

- `package.json`
- `package-lock.json`
- `tailwind.config.js`
- `resources/js/app.js`
- `resources/css/app.css`

## Files That Need Refactoring First

Highest priority:

- `app/Providers/AuthServiceProvider.php`
- `app/Policies/ProductPolicy.php`
- `app/Policies/CartPolicy.php`
- `app/Policies/ReviewPolicy.php`
- `app/Policies/NotificationPolicy.php`
- `app/Policies/ProductImagePolicy.php`
- `app/Policies/UserPolicy.php`
- `app/Policies/SellerProfilePolicy.php` or equivalent profile policy files
- `app/Livewire/Frontend/Seller/Products/Edit.php`
- `app/Livewire/Frontend/Seller/Products/Index.php`
- `app/Livewire/Frontend/Buyer/Cart/Index.php`
- `app/Livewire/Frontend/Buyer/Dashboard.php`
- `app/Livewire/Frontend/Seller/Dashboard.php`
- `app/Livewire/Frontend/Buyer/Orders/Index.php`
- `app/Livewire/Frontend/Seller/Orders/Index.php`
- `app/Models/Order.php`
- `app/Models/Product.php`
- `database/migrations/2026_06_07_171238_create_notifications_table.php`
- `database/seeders/DatabaseSeeder.php`
- `database/seeders/Demo/**`
- `resources/views/components/ui/**`
- `resources/views/layouts/**`
- `lang/en.json`
- `lang/lt.json`

## Tests That Need Attention First

Fix stale or failing baseline tests:

- `tests/Unit/Models/CartTest.php`
- `tests/Feature/AuditLogTest.php`
- `tests/Feature/Factories/ModelFactoriesTest.php` or equivalent factory tests
- `tests/Feature/Controllers/Frontend/Buyer/CartControllerTest.php`
- `tests/Feature/Controllers/Frontend/Seller/ProductControllerTest.php`
- `tests/Feature/Controllers/Backend/ProductControllerTest.php`
- `tests/Feature/DatabaseSeederTest.php`
- `tests/Feature/Images/ProductImagePipelineTest.php`
- `tests/Feature/Marketplace/CartCheckoutFeatureTest.php`
- `tests/Feature/Marketplace/OrderStatusWorkflowFeatureTest.php`
- `tests/Feature/Marketplace/ProductCatalogFeatureTest.php`
- `tests/Feature/Marketplace/RoleAccessFeatureTest.php`
- `tests/Feature/Security/AuthorizationSecurityTest.php`
- `tests/Feature/Seeders/DemoScenarioSeederTest.php`
- `tests/Feature/Seeders/ProductSeederTest.php`
- `tests/Feature/Support/SpotlightTest.php`
- `tests/Feature/Translations/TranslationFilesTest.php`

Add or strengthen tests:

- Guest forbidden checks for private routes.
- Buyer forbidden checks against another buyer's orders.
- Seller forbidden checks against another seller's products and order items.
- Admin-only route and action checks.
- Livewire action authorization checks.
- Cart add/update/remove/checkout edge cases.
- Product image upload, replace, delete, primary image, and cleanup behavior.
- Notification read/unread and ownership behavior.
- Demo seeder idempotency.
- Multilingual page smoke tests.

## Documentation That Needs Attention First

Update or create:

- `README.md`
- `CHANGELOG.md`
- `docs/installation.md`
- `docs/env.md`
- `docs/roles-and-permissions.md`
- `docs/testing.md`
- `docs/release-workflow.md`
- `docs/architecture.md`
- `docs/design-system.md`
- `docs/demo-data.md`
- `docs/known-gaps.md`

Documentation must be honest about incomplete or unverified behavior. Do not claim marketplace blocks are finished until fresh migration, seeders, full suite, frontend build, and manual page checks pass.

## Stabilization Blockers Before New Features

Do these before adding large marketplace modules:

1. Make the full test suite green or explicitly isolate known legacy failures with a documented owner.
2. Resolve cart behavior and update tests/docs to match.
3. Resolve notification schema/table naming.
4. Register and implement core policies for multi-guard actors.
5. Move private Livewire actions to policy-backed authorization.
6. Decide and document the current admin architecture: custom Livewire admin now, Filament only as a future migration if desired.
7. Choose one UI standard and migrate wrappers/components toward it.
8. Wire idempotent demo seeders.
9. Fix high-risk N+1 and full-history collection loading.
10. Update README/changelog/docs only with verified behavior.

