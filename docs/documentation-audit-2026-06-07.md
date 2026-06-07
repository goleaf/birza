# Documentation Audit - 2026-06-07

## Scope

This audit was completed before rewriting the main project documentation. It inspected repository files, Laravel Boost application info, Laravel Boost database schema, route files, package metadata, environment examples, migrations, seeders, factories, tests, frontend config, storage/queue/mail config, translations, screenshots, git tags, and GitHub releases.

## Sources Checked

- `README.md`
- `CHANGELOG.md`
- `docs/`
- `composer.json`, `composer.lock`
- `package.json`, `package-lock.json`
- `.env.example`
- `routes/web.php`, `routes/buyer.php`, `routes/seller.php`, `routes/admin.php`, `routes/api.php`
- `bootstrap/app.php`
- `database/migrations`
- `database/seeders`
- `database/factories`
- `tests`
- `resources/css`, `resources/js`, `tailwind.config.js`, `vite.config.js`
- `config/app.php`, `config/auth.php`, `config/database.php`, `config/filesystems.php`, `config/queue.php`, `config/mail.php`, `config/debugbar.php`, `config/marketplace.php`, `config/notifications.php`
- `lang/en.json`, `lang/lt.json`, `lang/en/`, `lang/lt/`
- `public`, repository screenshot files, and seed image files
- `git tag --list`
- `gh release list --limit 20`
- Laravel Boost `application_info`, `search_docs`, and `database_schema`

## Current Stack Snapshot

- PHP: `8.5.5` locally; `composer.json` requires `^8.3`.
- Laravel: `12.61.1`.
- Livewire: `4.3.1`.
- Laravel Sanctum: `4.3.2`.
- Laravel Boost: `2.4.9`.
- Laravel MCP: `0.7.2`.
- Database engine inspected by Boost: SQLite.
- PHPUnit: `11.5.55`.
- Vite: `8.0.16`.
- Tailwind CSS: `3.4.19`.
- Alpine.js: `3.15.3`.
- maryUI: `2.8.3`.
- WireUI: `2.6.0`.
- daisyUI: `4.12.24`.
- Flowbite: `2.5.2`.
- Intervention Image: `2.7.2`.
- Kettasoft Filterable: `2.15.0`.
- LaraCart: `2.7.0`, present but not the current database-backed buyer cart path.

## Existing Documentation Files

Project-owned Markdown files currently exist at:

- `README.md`
- `CHANGELOG.md`
- `docs/README.md`
- `docs/audit-log-system.md`
- `docs/cart-checkout-workflow-2026-06-07.md`
- `docs/database-structure-audit-2026-06-07.md`
- `docs/demo-seeding.md`
- `docs/deployment.md`
- `docs/eloquent-relationship-map-2026-06-07.md`
- `docs/foreign-key-constraint-audit-2026-06-07.md`
- `docs/frontend-stack-compatibility-2026-06-07.md`
- `docs/image-pipeline.md`
- `docs/notifications.md`
- `docs/performance.md`
- `docs/product-reports.md`
- `docs/product-wishlists.md`
- `docs/query-index-audit-2026-06-07.md`
- `docs/release-notes-template.md`
- `docs/releases/README.md`
- `docs/releases/0.1.0.md`
- `docs/releases/0.2.0.md`
- `docs/releases/0.3.0.md`
- `docs/releases/unreleased-product-questions.md`
- `docs/releases/unreleased-product-reports.md`
- `docs/releases/unreleased-product-wishlists.md`
- `docs/releases/unreleased-seller-discounts-promo-codes.md`
- `docs/security-authorization.md`
- `docs/stock-alerts.md`
- `docs/testing.md`
- `docs/translations.md`

## Missing Or Incomplete Documentation Files

The following requested guide files are missing or incomplete:

- `docs/installation.md` is missing.
- `docs/environment.md` is missing.
- `docs/roles.md` is missing.
- `docs/architecture.md` is missing.
- `docs/database.md` is missing as a current guide. The existing database audit is historical and partly stale relative to the current schema.
- `docs/frontend.md` is missing as a concise developer guide. The compatibility report exists.
- `docs/seeders.md` is missing. `docs/demo-seeding.md` exists and should be retained.
- `docs/security.md` is missing. `docs/security-authorization.md` exists and should be retained.
- `docs/roadmap.md` is missing.
- `docs/developer-workflow.md` is missing.
- `docs/production.md` is missing. `docs/deployment.md` exists but is Debugbar-focused.
- `docs/screenshots/` is missing.
- `docs/documentation-audit-2026-06-07.md` did not exist before this task.

## Audit Findings

| Area | Status | Notes |
| --- | --- | --- |
| README completeness | Partial | The existing README is detailed, but it still needs a cleaner final structure, docs index links, updated `.env.example` alignment, and a less stale known-issues section. |
| Installation instructions | Partial | Commands are present, but zero-install was not fully re-run during this audit. `composer install`, `npm install`, migration, storage, and asset commands are documented. |
| Environment variables | Partial | `.env.example` includes many important keys, but it currently uses `APP_NAME=Laravel`, `APP_DEBUG=false`, `DB_CONNECTION=mysql`, and `DB_DATABASE=laravel` while the local docs and Boost inspection point to Birza, Herd, and SQLite. |
| Demo users | Present | Seeders create local admin, buyer, seller, and hybrid buyer/seller users. README and `docs/demo-seeding.md` document them. |
| Seeders | Partial | `DatabaseSeeder` uses `MinimalSeeder`, then non-production `DemoScenarioSeeder` and `AuditLogSeeder`. Dedicated `docs/seeders.md` is missing. |
| Tests | Present | PHPUnit tests exist across unit, feature, marketplace, security, image, notification, translation, seeder, factory, and performance areas. `docs/testing.md` exists. |
| Roles | Partial | README and security docs describe roles, but a dedicated `docs/roles.md` is missing. |
| Architecture | Missing | No dedicated practical architecture guide exists. |
| Database | Partial | Schema and audit docs exist, but the older database audit is stale relative to current migrations and should not be the main database guide. |
| Frontend stack | Partial | README and frontend compatibility report document the stack, but a concise `docs/frontend.md` is missing. |
| Security rules | Partial | `docs/security-authorization.md` exists; requested `docs/security.md` is missing. |
| Performance rules | Present | `docs/performance.md` exists and covers query and cache rules. It needs alignment with the new docs index. |
| Changelog | Present | `CHANGELOG.md` exists and uses Keep a Changelog sections. |
| Release workflow | Present | `docs/releases/README.md` and `docs/release-notes-template.md` exist. |
| Release notes | Present | `0.1.0`, `0.2.0`, `0.3.0`, and several unreleased module notes exist. |
| Git tags | Missing | `git tag --list` returned no tags. |
| GitHub releases | Missing | `gh release list --limit 20` returned no releases. |
| Screenshots | Missing | Only generated seed product images were found under `storage/app/seed-images`. No committed UI screenshot set was found. |
| Production notes | Partial | README and `docs/deployment.md` contain production notes, but a full `docs/production.md` is missing. |
| Developer workflow | Partial | README and release workflow include pieces, but a dedicated workflow guide is missing. |

## Current Route And Access Snapshot

- Public: `/`, `/language/{locale}`.
- Buyer prefix: `/buyer`, route names `buyer.*`.
- Seller prefix: `/seller`, route names `seller.*`.
- Admin prefix: `/admin`, route names standardized on `admin.*`.
- API: `/api/products/search` throttled at `30,1`; `/api/user` protected by Sanctum.
- Private buyer routes use `auth:buyer`, `active.account:buyer`, `verified.account:buyer`, and `can:accessBuyerCabinet`.
- Private seller routes use `auth:seller`, `active.account:seller`, `verified.account:seller`, and `can:accessSellerCabinet`.
- Private admin routes use `auth:admin`, `active.account:admin`, and `can:accessAdminPanel`.

## Current Database Snapshot

Laravel Boost reported the current SQLite schema includes marketplace tables for:

- accounts: `users`, `users_admins`, `users_buyers`, `users_sellers`
- catalog: `products`, `product_images`, `categories`, `countries`, `attributes`, `attribute_values`, product/attribute pivots
- cart and checkout: `carts`, `cart_items`, `cart_bundle_items`
- orders: `orders`, `order_items`, `order_bundles`, `order_status_histories`
- promotions: `discounts`, `promo_codes`, `promo_code_redemptions`
- buyer features: `wishlists`, `wishlist_items`, `product_stock_alerts`, `addresses`
- feedback and moderation: `reviews`, `product_questions`, `product_reports`
- notifications and audit: `notifications`, `admin_actions`, `audit_logs`, `activities`
- settings and support: `global_settings`, `cache`, `cache_locks`, `failed_jobs`, `password_reset_tokens`, `personal_access_tokens`

`php artisan migrate:status --no-interaction` reported all migrations in the current tree as run in the local database.

## Files To Create Or Update

Create:

- `docs/installation.md`
- `docs/environment.md`
- `docs/roles.md`
- `docs/architecture.md`
- `docs/database.md`
- `docs/frontend.md`
- `docs/seeders.md`
- `docs/security.md`
- `docs/roadmap.md`
- `docs/developer-workflow.md`
- `docs/production.md`
- `docs/screenshots/.gitkeep`

Update:

- `README.md`
- `CHANGELOG.md`
- `docs/README.md`
- `docs/performance.md`
- `docs/testing.md`
- `docs/releases/README.md`
- `docs/release-notes-template.md`
- `.env.example`

Retain as supporting historical or topic-specific docs:

- `docs/database-structure-audit-2026-06-07.md`
- `docs/frontend-stack-compatibility-2026-06-07.md`
- `docs/security-authorization.md`
- `docs/demo-seeding.md`
- existing feature and release notes

## Accuracy Caveats

- The worktree is dirty and includes many uncommitted code, migration, test, and documentation changes. This documentation pass should stage only prompt-owned docs and safe environment example changes.
- Some older audit documents predate newer local migrations and are now historical. Current guides should use the current schema, migrations, and route files.
- Payment integration is still simulated in checkout documentation unless a real provider is later added and verified.
- No GitHub releases or git tags exist yet, so release workflow documentation must describe future process rather than published releases.
- No committed UI screenshots were found. The main screenshot section should avoid broken links until screenshots are added under `docs/screenshots/`.
