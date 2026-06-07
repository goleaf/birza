# Foundation Stabilization Plan

Date: 2026-06-07

Goal: make the existing marketplace clean, consistent, secure, testable, documented, and ready for future growth before adding new major features.

No large feature modules should be added until these phases are complete.

## Execution Rules

- Work in small commits.
- Each commit must have one clear purpose.
- Do not refactor the entire project in one uncontrolled change.
- Before each phase, inspect the latest dirty tree and avoid reverting unrelated user work.
- After each phase, run the minimum relevant tests.
- Run `vendor/bin/pint --dirty --format agent` after PHP changes.
- Run `npm run build` after UI or frontend asset changes.
- Update docs and changelog only for verified behavior.
- Use Laravel Boost/schema inspection before database or query changes.
- Keep all query logic Eloquent-based.
- Do not add raw SQL strings.

Recommended commit shape:

- `refactor: standardize marketplace area architecture`
- `refactor: standardize role architecture`
- `refactor: standardize marketplace UI system`
- `refactor: clean marketplace database structure`
- `test: add complete factories and demo seeders`
- `test: add feature coverage for core flows`
- `security: harden policies middleware and audit trail`
- `perf: fix marketplace N+1 queries`
- `docs: add marketplace foundation workflow`
- `refactor: stabilize marketplace foundation`

The final consolidation commit requested by the prompt is:

`refactor: stabilize marketplace foundation`

Use that only after the full stabilization block is verified.

## Phase 1 - Architecture Cleanup

What will be changed:

- Define the current marketplace area architecture explicitly: public, buyer, seller, admin.
- Keep route files grouped by area and make each route group consistent.
- Confirm which pages belong to each area.
- Document that the current admin area is custom Livewire/Blade, not Filament.
- Move repeated business logic from Livewire components into actions where the operation changes state.
- Remove stale route/controller assumptions from tests and docs.

Why it matters:

- Future features need a predictable home.
- Buyers, sellers, admins, and guests must not share accidental logic paths.
- The project instructions mention Filament, but the current codebase does not use it. The architecture needs one clear direction.

Files affected:

- `bootstrap/app.php`
- `routes/web.php`
- `routes/admin.php`
- `routes/buyer.php`
- `routes/seller.php`
- `routes/api.php`
- `app/Livewire/Frontend/Buyer/**`
- `app/Livewire/Frontend/Seller/**`
- `app/Livewire/Backend/**`
- `app/Actions/**`
- `README.md`
- `docs/architecture.md`
- `CHANGELOG.md`

How it will be tested:

- `php artisan route:list -v`
- Feature tests for public, buyer, seller, and admin route access.
- Livewire smoke tests for each dashboard.
- Forbidden URL checks for wrong roles.

What must be documented:

- Area map.
- Route group rules.
- Layout ownership.
- Admin architecture decision.
- Current public catalog/cart decision.

Suggested commits:

- `refactor: define marketplace area boundaries`
- `docs: document marketplace architecture`

## Phase 2 - Role And Permission Standardization

What will be changed:

- Decide the actor model contract for policies across `Admin`, `Buyer`, `Seller`, and base `User`.
- Register or convention-align all core policies.
- Replace default-deny policy scaffolds with real rules.
- Move inline private action checks into policy-backed authorization.
- Add gates for area access where useful, such as admin area access.
- Enforce seller ownership for products, images, and seller order views.
- Enforce buyer ownership for orders, cart, addresses, reviews, and notifications.
- Ensure API routes are authenticated or explicitly public and throttled.

Why it matters:

- Hidden menu items are not security.
- Every private action must be protected server-side.
- Multi-guard apps need consistent actor handling or policies silently fail.

Files affected:

- `app/Providers/AuthServiceProvider.php`
- `app/Policies/**`
- `app/Policies/Concerns/ResolvesPolicyActors.php`
- `app/Http/Middleware/EnsureActiveAccount.php`
- `app/Http/Middleware/EnsureVerifiedAccount.php`
- `app/Livewire/Frontend/Buyer/**`
- `app/Livewire/Frontend/Seller/**`
- `app/Livewire/Backend/**`
- `routes/api.php`
- `tests/Feature/Marketplace/RoleAccessFeatureTest.php`
- `tests/Feature/Security/AuthorizationSecurityTest.php`
- `docs/roles-and-permissions.md`

How it will be tested:

- Feature tests for guest, buyer, seller, admin access.
- Livewire action tests using forbidden assertions.
- Ownership tests for cross-buyer and cross-seller access.
- API access tests if API routes remain enabled.

What must be documented:

- Role definitions.
- Area access matrix.
- Policy ownership rules.
- Dangerous action authorization rules.

Suggested commits:

- `refactor: standardize role architecture`
- `security: enforce marketplace ownership policies`

## Phase 3 - UI And Design System Standardization

What will be changed:

- Choose one primary UI direction for Blade/Livewire.
- Keep one standard for buttons, forms, tables, cards, modals, dropdowns, badges, alerts, empty states, loading states, and mobile behavior.
- Consolidate duplicated UI wrappers.
- Stop mixing Mary UI, WireUI, daisyUI, Flowbite, and custom components for the same primitives.
- Remove direct UI library usage from feature views where a shared project component should be used.
- Move layout guard/navigation logic out of Blade where possible.

Why it matters:

- The app must look like one product.
- Shared UI primitives reduce future page inconsistency.
- UI changes become cheaper and safer.

Files affected:

- `resources/views/components/ui/**`
- `resources/views/layouts/**`
- `resources/views/frontend/**`
- `resources/views/backend/**`
- `resources/css/app.css`
- `resources/js/app.js`
- `tailwind.config.js`
- `package.json`
- `composer.json`
- `docs/design-system.md`

How it will be tested:

- Component rendering tests where practical.
- Livewire page smoke tests.
- Manual page checks for homepage, catalog, product page, cart, checkout, buyer dashboard, seller dashboard, admin dashboard, orders, and notifications.
- `npm run build`

What must be documented:

- Primary UI library/wrapper decision.
- Component usage rules.
- Button/form/table/modal/badge/alert conventions.
- Mobile behavior expectations.

Suggested commits:

- `refactor: standardize marketplace UI system`
- `docs: document marketplace design system`

## Phase 4 - Database And Model Cleanup

What will be changed:

- Resolve notification table naming and model ownership.
- Remove or deprecate stale model relationships that no longer match the schema.
- Review product image structure and plan migration away from legacy image columns.
- Verify foreign keys, indexes, nullability, money columns, status fields, soft deletes, slugs, and public identifiers.
- Add missing indexes only after confirming real query paths.
- Add or clean model scopes for common marketplace filters.
- Keep money as decimal fields.
- Preserve order snapshots.

Why it matters:

- Schema drift breaks tests, seeders, docs, and future features.
- Models must reflect real database truth.
- Stable scopes and relationships are the foundation for safe query reuse.

Files affected:

- `database/migrations/**`
- `app/Models/Product.php`
- `app/Models/ProductImage.php`
- `app/Models/Cart.php`
- `app/Models/CartItem.php`
- `app/Models/Order.php`
- `app/Models/OrderItem.php`
- `app/Models/OrderStatusHistory.php`
- `app/Models/Review.php`
- `app/Models/Address.php`
- `app/Models/**Notification**`
- `tests/Unit/Models/**`
- `tests/Feature/DatabaseSeederTest.php`
- `docs/database.md`

How it will be tested:

- `php artisan migrate:fresh --seed --no-interaction`
- Focused model unit tests.
- Seeder tests.
- Feature tests for catalog, cart, checkout, orders, notifications, images.
- Laravel Boost database schema inspection before and after migration changes.

What must be documented:

- Core table map.
- Order snapshot policy.
- Public identifier/slug policy.
- Notification storage decision.
- Product image storage decision.

Suggested commits:

- `refactor: clean marketplace database structure`
- `refactor: align marketplace models with schema`

## Phase 5 - Factories And Seeders

What will be changed:

- Make every marketplace model factory usable from tests.
- Wire complete demo data from `DatabaseSeeder` or a documented demo seeder command.
- Make seeders idempotent.
- Seed realistic admins, buyers, sellers, products, categories, images, carts, orders, statuses, reviews, favorites, notifications, addresses, and audit logs if implemented.
- Include edge cases: empty states, many records for pagination, blocked users, inactive sellers, draft products, published products, out-of-stock products, cancelled orders, completed orders, and disputed/refunded orders if supported.
- Avoid depending on unreadable image fixture files.

Why it matters:

- A fresh database must be operational.
- Tests need reliable factories and demo states.
- Manual QA needs realistic data.

Files affected:

- `database/factories/**`
- `database/seeders/DatabaseSeeder.php`
- `database/seeders/Demo/**`
- `database/seeders/ProductSeeder.php`
- `tests/Feature/Seeders/**`
- `tests/Feature/Factories/**`
- `docs/demo-data.md`
- `README.md`

How it will be tested:

- `php artisan migrate:fresh --seed --no-interaction`
- Seeder idempotency tests.
- Factory creation tests.
- Manual login checks with demo accounts.
- Pagination smoke tests using many seeded records.

What must be documented:

- Demo accounts.
- Demo seeder command.
- Seeded scenarios and edge cases.
- Which demo data is safe for local/testing only.

Suggested commits:

- `test: add complete factories and demo seeders`
- `docs: document demo marketplace data`

## Phase 6 - Feature Tests

What will be changed:

- First fix stale failing tests so the baseline is trustworthy.
- Add feature coverage for real marketplace flows.
- Cover happy paths, failure paths, and dangerous attempts.
- Align tests with the chosen cart/public catalog behavior.
- Use factories instead of manual database inserts.

Why it matters:

- Future feature work needs a safety net.
- Security and ownership failures must be caught automatically.
- Red tests from stale expectations hide real regressions.

Files affected:

- `tests/Feature/Auth/**`
- `tests/Feature/Marketplace/**`
- `tests/Feature/Security/**`
- `tests/Feature/Images/**`
- `tests/Feature/Seeders/**`
- `tests/Feature/Translations/**`
- `tests/Feature/Controllers/**`
- `tests/Unit/Models/**`

How it will be tested:

- Focused test files after each area.
- `php artisan test --compact` after the phase.
- JUnit or compact output captured if failures remain.

What must be documented:

- Testing guide.
- Core test matrix.
- Known gaps if any remain.
- How to run focused tests and the full suite.

Suggested commits:

- `test: fix marketplace baseline tests`
- `test: add feature coverage for core flows`

## Phase 7 - Security Hardening

What will be changed:

- Ensure every important entity has authorization rules.
- Protect Livewire actions with policies/gates.
- Add audit logs for important actions if not already complete.
- Verify dangerous actions require confirmation and authorization.
- Check upload validation and image storage safety.
- Check API protection and throttling.
- Confirm inactive/blocked users cannot access private areas.

Audit events to cover:

- Seller created product.
- Seller changed price.
- Buyer created order.
- Admin blocked user.
- Order status changed.
- Refund created if supported.
- Dispute opened if supported.
- Moderation decision made if supported.

Why it matters:

- Marketplace security is mostly ownership and role correctness.
- Important business events need traceability.
- Livewire server methods are callable even when buttons are hidden.

Files affected:

- `app/Policies/**`
- `app/Actions/**`
- `app/Models/AuditLog.php`
- `app/Models/AdminAction.php`
- `app/Livewire/**`
- `app/Http/Middleware/**`
- `routes/**`
- `tests/Feature/Security/**`
- `tests/Feature/Marketplace/**`
- `docs/security.md`
- `docs/roles-and-permissions.md`

How it will be tested:

- Feature tests for unauthorized guests and wrong roles.
- Livewire action authorization tests.
- Audit log creation tests.
- Upload validation tests.
- Blocked/inactive user access tests.

What must be documented:

- Authorization matrix.
- Audit event list.
- Upload rules.
- Blocked/inactive account behavior.

Suggested commits:

- `security: harden policies middleware and audit trail`

## Phase 8 - Performance And N+1 Audit

What will be changed:

- Audit product lists, category lists, seller dashboard, buyer orders, seller orders, admin tables, notifications, reviews, favorites, and images.
- Replace full-history collection loading with pagination, scopes, selected columns, and query aggregates.
- Add eager loading for relationships used in views.
- Use `withCount`, `withExists`, and scoped aggregate queries where appropriate.
- Cache stable reference data such as categories only after cache invalidation is clear.
- Add indexes only where actual queries need them.

Why it matters:

- Marketplace pages will grow quickly with products, orders, images, reviews, and notifications.
- Query regressions are cheaper to fix before new features multiply them.

Files affected:

- `app/Livewire/Frontend/Buyer/Dashboard.php`
- `app/Livewire/Frontend/Seller/Dashboard.php`
- `app/Livewire/Frontend/Buyer/Orders/Index.php`
- `app/Livewire/Frontend/Seller/Orders/Index.php`
- `app/Livewire/Frontend/Buyer/Products/Index.php`
- `app/Livewire/Backend/**`
- `app/Models/**`
- `database/migrations/**`
- `tests/Feature/Marketplace/**`
- `tests/Feature/Performance/**` if added
- `docs/performance.md`

How it will be tested:

- Focused feature tests with query count assertions where stable.
- Manual page checks with seeded many-record scenarios.
- Laravel Boost schema inspection.
- Debugbar/Telescope only locally if already available and never as production dependency.
- `php artisan test --compact`

What must be documented:

- Query strategy.
- Pagination rules.
- Cache rules and invalidation.
- Known query-heavy pages.

Suggested commits:

- `perf: fix marketplace N+1 queries`

## Phase 9 - Documentation And Release Workflow

What will be changed:

- Update README with verified project status.
- Update changelog honestly.
- Create or update installation, environment, roles, testing, demo data, architecture, design system, security, performance, and release workflow docs.
- Add release notes template usage.
- Define when version tags are created.
- Do not claim unfinished marketplace blocks are complete.

Why it matters:

- Documentation must be useful for future development and honest about reality.
- Release process prevents future foundation drift.

Files affected:

- `README.md`
- `CHANGELOG.md`
- `docs/README.md`
- `docs/installation.md`
- `docs/env.md`
- `docs/roles-and-permissions.md`
- `docs/testing.md`
- `docs/release-workflow.md`
- `docs/release-notes-template.md`
- `docs/architecture.md`
- `docs/design-system.md`
- `docs/demo-data.md`
- `docs/security.md`
- `docs/performance.md`
- `docs/roadmap.md`

How it will be tested:

- Documentation link/path check.
- Install instructions verified against fresh migration, seed, tests, and build.
- Demo account login checks.
- Changelog compared with actual commits and test results.

What must be documented:

- Project purpose.
- Install and run steps.
- Demo seed data and accounts.
- Roles.
- Common commands.
- Frontend build.
- Screenshots status.
- Roadmap.
- Release workflow.
- Major block verification requirements.

Suggested commits:

- `docs: add marketplace foundation workflow`
- `docs: update marketplace README and changelog`

## Phase 10 - Final Cleanup

What will be changed:

- Remove dead code, duplicated logic, unused components, unused helpers, old commented code, hardcoded statuses, hardcoded user-facing text, unsafe role checks, unused dependencies, broken seeders, weak tests, and unpaginated large lists.
- Run full verification.
- Prepare final stabilization commit.

Why it matters:

- Cleanup locks in the foundation after the architecture, roles, UI, database, tests, security, performance, and docs work are complete.
- The project should be ready for feature growth without dragging old inconsistencies forward.

Files affected:

- All touched files from previous phases.
- `composer.json`
- `package.json`
- `resources/views/**`
- `app/**`
- `database/**`
- `tests/**`
- `docs/**`
- `README.md`
- `CHANGELOG.md`

How it will be tested:

- `composer validate --no-check-publish`
- `php artisan migrate:fresh --seed --no-interaction`
- `vendor/bin/pint --dirty --format agent`
- `php artisan test --compact`
- `npm run build`
- Manual checks:
  - public homepage
  - catalog
  - product page
  - cart
  - checkout
  - buyer dashboard
  - seller dashboard
  - admin dashboard
  - notifications
  - buyer order pages
  - seller order pages
  - admin order pages
  - mobile layout
  - forbidden URL checks

What must be documented:

- Final verified status.
- Remaining known gaps.
- Release notes.
- Changelog entry.
- Version tag decision.

Suggested final commit:

- `refactor: stabilize marketplace foundation`

## Phase Exit Criteria

A phase is complete only when:

- Relevant tests pass.
- New or changed PHP code is formatted with Pint.
- Frontend build passes if UI changed.
- Important pages are manually checked if UI or routing changed.
- Documentation and changelog are updated for verified behavior.
- Commit contains one clear purpose.

## Final Stabilization Exit Criteria

The foundation is stable only when all of these pass:

- Fresh migration.
- Seeders.
- Full test suite.
- Frontend build.
- Public homepage.
- Catalog.
- Product page.
- Cart.
- Checkout.
- Buyer dashboard.
- Seller dashboard.
- Admin dashboard.
- Notifications.
- Order pages.
- Mobile layout.
- Forbidden URL checks.

Only after this list is green should new marketplace feature work resume.

