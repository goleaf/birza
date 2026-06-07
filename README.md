# Birza

Birza is a Laravel marketplace platform with public, buyer, seller, and admin areas. It is a server-rendered Laravel application built with Blade, Livewire, Eloquent, Vite, Tailwind CSS, and separate authentication guards for admins, buyers, and sellers.

The project currently supports localized product browsing, seller-managed products, database-backed carts, checkout, orders, buyer-seller private messaging, notifications, product images, reviews, wishlists, product questions, product reports, seller discounts, promo codes, audit logs, and custom admin management screens. Payment provider integration, production deployment automation, full screenshots, and dispute workflows are still planned work.

## Main Areas

- Public marketplace: home page, language switching, public product catalog, product detail pages, comparison, product questions, and product reports.
- Buyer area: buyer auth, profile, dashboard, cart, checkout, orders, private seller messages, wishlists, stock alerts, and notifications.
- Seller area: seller auth, profile, dashboard, products, discounts, promo codes, product questions, private buyer messages, orders, transactions, and notifications.
- Admin area: admin login, dashboard, products, categories, countries, attributes, buyers, sellers, orders, read-only message moderation, product questions, product reports, settings, notifications, and audit logs.

## Current Features

- Product catalog with categories, attributes, countries of origin, price/stock/organic filters, pagination, and a throttled product search API.
- Seller product management with ownership checks, moderation notifications, image uploads, and low-stock notifications.
- Database-backed carts and cart items, including guest cart support, buyer cart merging, backend price recalculation, seller discounts, promo codes, order snapshots, and stock decrementing.
- Orders, order items, order bundles, order status history, buyer/seller/admin order views, and translatable order/payment status labels.
- Marketplace notifications through Laravel database notifications and queued mail-capable notification classes.
- Buyer-seller private messaging for product and order conversations, including unread state, recipient notifications, admin moderation visibility, and metadata-only audit logging.
- Product wishlists, product stock alerts, product reports, product questions and answers, reviews, discounts, promo codes, bundles, and audit logs.
- Multilingual UI for Lithuanian and English through JSON translation files and translated database content where models support it.
- PHPUnit feature/unit coverage for role access, auth, catalog, cart, checkout, orders, images, notifications, translations, factories, seeders, security, and performance query budgets.

## Product Bundles

Sellers can group their own products into product bundles. A bundle belongs to one seller, must contain at least two unique seller-owned products before publishing, and can be draft, active, inactive, expired, or archived. Sellers can set quantities, ordering, an optional image, optional availability dates, and either a percentage or fixed bundle discount.

Bundle pricing is calculated dynamically from current active product prices and item quantities. The backend recalculates the base price, discount amount, and final price when bundles are added to cart and again during checkout. Frontend or stored cart prices are not trusted. Fixed discounts cannot make the final bundle price negative.

Cart rows store bundles separately from normal product rows in `cart_bundle_items`. Checkout keeps each bundle inside the seller order that owns it, revalidates every included product, checks combined stock, creates an `order_bundles` snapshot, writes linked `order_items` rows for included products, decrements stock, audits purchase activity, and clears bundle cart rows only after the transaction succeeds.

Demo data seeds active, draft, archived, discounted, unavailable-product, and out-of-stock bundles, plus bundle cart and order snapshot examples. Focused bundle tests can be run with:

```bash
php artisan test --compact tests/Feature/ProductBundleFeatureTest.php tests/Feature/BundleCartCheckoutTest.php
```

## Planned Or Incomplete

- Real payment gateway integration. Checkout currently simulates successful payment.
- Dedicated dispute workflow. Private product/order messaging exists, but formal dispute records are still planned.
- Complete production deployment automation.
- Full screenshot set for every major page.
- Final UI consolidation. maryUI is active, while WireUI, daisyUI, and Flowbite remain during migration.
- Final confirmation of all release tags and GitHub releases. No local git tags or GitHub releases were found during the 2026-06-07 documentation audit.

## Technology Stack

Backend:

- PHP `^8.3`; local audit used PHP `8.5.5`.
- Laravel `12.61.1`.
- Livewire `4.3.1`.
- Laravel Sanctum `4.3.2`.
- Laravel Boost `2.4.9` and Laravel MCP `0.7.2`.
- Eloquent models, model factories, policies, actions, services, observers, and notifications.
- SQLite for local development; MySQL/PostgreSQL/SQL Server connection stubs remain available through Laravel config.

Frontend:

- Blade templates only. No React, Vue, Inertia, or SPA frontend.
- Vite `8.0.16`.
- Tailwind CSS `3.4.19`.
- Alpine.js `3.15.3`.
- maryUI `2.8.3` with `x-mary-*` prefix.
- WireUI `2.6.0`, daisyUI `4.12.24`, and Flowbite `2.5.2` are still installed during the UI modernization period.

Development and testing:

- PHPUnit `11.5.55`.
- Laravel Pint `1.29.1`.
- Intervention Image `2.7.2`.
- Kettasoft Filterable `2.15.0`.
- Laravel Debugbar `3.16.5`, local opt-in only.

## System Requirements

- PHP 8.3 or newer.
- Composer 2.
- Node.js and npm. Node 22 and npm 10 were used during the audit; Node 20+ is a reasonable local recommendation.
- SQLite with `pdo_sqlite`, or another configured Laravel-supported database.
- Required PHP extensions normally needed by Laravel and this app: `openssl`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `pdo`, `pdo_sqlite` or `pdo_mysql`, and GD or Imagick for image work.
- Git.
- Writable `storage/` and `bootstrap/cache/`.
- Laravel Herd is the preferred local environment for this repo at `https://birza.test`. `php artisan serve` also works for simple local serving.

## Installation

```bash
git clone <repository-url> birza
cd birza
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/birza.sqlite
php artisan migrate
php artisan db:seed
php artisan storage:link
npm run dev
```

With Herd, open:

```text
https://birza.test
```

Without Herd, run:

```bash
php artisan serve
```

For a complete local reset, use:

```bash
php artisan migrate:fresh --seed
```

`migrate:fresh --seed` deletes local data. Do not use it in production.

See [docs/installation.md](docs/installation.md) for zero-install details and troubleshooting.

## Environment Setup

Important local defaults from `.env.example`:

| Variable | Default | Notes |
| --- | --- | --- |
| `APP_NAME` | `Birza` | App and mail name. |
| `APP_ENV` | `local` | Use `production` in production. |
| `APP_DEBUG` | `true` | Use `false` in production. |
| `APP_URL` | `https://birza.test` | Herd URL. |
| `DB_CONNECTION` | `sqlite` | Local default. |
| `DB_DATABASE` | `birza.sqlite` | Resolved under `database/`. |
| `FILESYSTEM_DISK` | `local` | Product image code uses the `public` disk where needed. |
| `QUEUE_CONNECTION` | `sync` | No worker required locally. |
| `MAIL_MAILER` | `log` | Safe local default. |
| `DEBUGBAR_ENABLED` | `false` | Local opt-in only. |
| `MARKETPLACE_LOW_STOCK_THRESHOLD` | `5` | Seller low-stock alert threshold. |
| `MARKETPLACE_ALLOW_GUEST_PRODUCT_REPORTS` | `true` | Allows guest product reports with email. |

Production-safe values:

```env
APP_ENV=production
APP_DEBUG=false
DEBUGBAR_ENABLED=false
```

See [docs/environment.md](docs/environment.md) for the full environment guide.

## Demo Accounts

These accounts are seeded for local development only.

| Role | Email | Password | Purpose |
| --- | --- | --- | --- |
| Admin | `admin@example.com` | `password` | Demo admin dashboard. |
| Admin | `admin@admin.com` | `password` | Minimal legacy admin. |
| Buyer | `buyer@example.com` | `password` | Main buyer dashboard, cart, orders, wishlist, notifications. |
| Buyer | `demo-empty-buyer@example.com` | `password` | Empty-state buyer. |
| Buyer | `demo-cart-buyer@example.com` | `password` | Buyer with seeded cart. |
| Buyer | `demo-orders-buyer@example.com` | `password` | Buyer with seeded orders. |
| Seller | `seller@example.com` | `password` | Main seller dashboard and products. |
| Seller | `demo-seller-one@example.com` | `password` | Seller with demo catalog/promotions. |
| Seller | `demo-seller-two@example.com` | `password` | Second seller for multi-seller flows. |
| Seller | `seller-empty@example.com` | `password` | Empty-state seller. |
| Buyer and seller | `buyer-seller@example.com` | `password` | Shared user with both buyer and seller profiles. |
| Test buyers | `buyer1@birza.lt` to `buyer10@birza.lt` | `password123` | Legacy/test-information seeders. |
| Test sellers | `seller1@birza.lt` to `seller10@birza.lt` | `password123` | Legacy/test-information seeders. |

## Roles And Access

Roles are implemented through separate guards and user tables rather than a shared roles table. `App\Enums\MarketplaceRole` is the shared source for role names, guard names, dashboard routes, login routes, and notification-capable guards.

| Role | Route area | Dashboard | Summary |
| --- | --- | --- | --- |
| Guest | `/`, `/buyer/login`, `/seller/login`, `/admin/login`, public catalog routes | None | Can browse public marketplace pages, switch language, compare products, ask public questions, and report active products when guest reports are enabled. |
| Buyer | `/buyer` | `/buyer/dashboard` | Can manage own profile, cart, checkout, orders, product/order conversations with sellers, wishlists, stock alerts, notifications, and product interactions. Cannot manage seller products or admin data. |
| Seller | `/seller` | `/seller/dashboard` | Can manage own profile, products, discounts, promo codes, product questions, product/order conversations with buyers, orders, transactions, and notifications. Cannot manage another seller's products or access admin tools. |
| Admin | `/admin` | `/admin/dashboard` | Can manage platform data according to policies, gates, middleware, and audit rules. Message moderation is read-only and audited. Dangerous actions should be audited. |

Buyer and seller abilities can belong to the same base `users` record when both profile rows exist; the demo `buyer-seller@example.com` account covers this path. Admin accounts stay in the `admin` guard and admin route names use the `admin.*` prefix.

Private route groups must use the role guard, account-state middleware, and the role access middleware alias:

| Area | Route file | Middleware contract |
| --- | --- | --- |
| Buyer | `routes/buyer.php` | `auth:buyer`, `active.account:buyer`, `verified.account:buyer`, `buyer.access` |
| Seller | `routes/seller.php` | `auth:seller`, `active.account:seller`, `verified.account:seller`, `seller.access` |
| Admin | `routes/admin.php` | `auth:admin`, `active.account:admin`, `admin.access` |

Use gates only for global abilities such as `accessBuyerCabinet`, `accessSellerCabinet`, `accessAdminPanel`, `viewAdminDashboard`, `manageSystemSettings`, and `viewAnalytics`. Use policies for model-specific ownership and dangerous actions. Livewire private screens must authorize on `mount()` and authorize again before mutating, deleting, uploading, changing status, exporting, or marking notifications.

To add a role-protected page: put the route in the matching route file with prefix/name group intact, mount it with the matching buyer/seller/admin layout, add or reuse the relevant policy/gate, hide navigation through backend authorization checks, and add a feature or Livewire test for both allowed and forbidden access.

See [docs/roles.md](docs/roles.md) and [docs/security.md](docs/security.md).

## Project Structure

| Path | Purpose |
| --- | --- |
| `app/Actions` | Single-purpose business operations for carts, orders, images, messaging, notifications, reports, products, promotions, stock alerts, and wishlists. |
| `app/Enums` | Domain enums such as order and moderation status values. |
| `app/Http/Controllers` | Thin HTTP controllers for public/API/auth/notification entry points. |
| `app/Http/Middleware` | Locale, active account, verified account, auth redirect, and signature middleware. |
| `app/Livewire` | Route-mounted buyer, seller, admin, auth, notification, product, cart, and order screens. |
| `app/Models` | Eloquent marketplace models and scopes. |
| `app/Policies` | Role and ownership authorization rules. |
| `app/Services` | Shared services such as audit logging. |
| `app/Observers` | Model observers for side effects. |
| `app/Support` | Support DTOs/helpers such as image results and locale formatting. |
| `config` | Laravel, auth, Livewire, maryUI, WireUI, Debugbar, queue, mail, image, marketplace, and notification config. |
| `database/migrations` | Schema migrations. |
| `database/factories` | Factories for tests and seeders. |
| `database/seeders` | Minimal, demo, and legacy test-information seeders. |
| `docs` | Project documentation and release notes. |
| `lang` | Lithuanian and English JSON translations plus keyed PHP translation files. |
| `resources/views` | Blade layouts, components, Livewire views, emails, notifications, pagination, and errors. |
| `resources/css`, `resources/js` | Vite entrypoints. |
| `routes` | Public, API, buyer, seller, admin, console, and channel routes. |
| `tests` | PHPUnit feature and unit tests. |

## Frontend Development

```bash
npm install
npm run dev
npm run build
```

- Vite entrypoints are `resources/css/app.css` and `resources/js/app.js`.
- Tailwind scans local Blade views plus maryUI and WireUI package component paths.
- maryUI is the intended primary component system.
- WireUI, daisyUI, and Flowbite remain installed during migration and should not be expanded for new component categories without a clear reason.
- Livewire owns interactivity; do not add React/Vue/Inertia.
- Keep visible text translatable.

See [docs/frontend.md](docs/frontend.md) and [docs/frontend-stack-compatibility-2026-06-07.md](docs/frontend-stack-compatibility-2026-06-07.md).

## Storage And Images

Run:

```bash
php artisan storage:link
```

Storage conventions:

- Public uploads are served through `public/storage`.
- Product image records live in `product_images`.
- Product images are stored on the `public` disk under relative paths.
- Legacy product image fields still exist for compatibility.
- Product fallback image: `public/images/admin-product-placeholder.svg`.
- Do not store absolute local server paths in the database.
- Use the image actions under `app/Actions/Images` and helpers such as `Product::imageUrl()` and `Product::imageGalleryUrls()`.

See [docs/image-pipeline.md](docs/image-pipeline.md).

## Queue Setup

Local default:

```env
QUEUE_CONNECTION=sync
```

With `sync`, no queue worker is required. Marketplace notification classes are queueable and use `afterCommit()`, so production environments that switch to an async driver should run a supervised worker:

```bash
php artisan queue:work
```

If the database queue driver is selected later, add the jobs table before using it. The current repo has `failed_jobs` but no `jobs` table migration documented as active.

## Mail Setup

Local default:

```env
MAIL_MAILER=log
```

Mailpit can be used locally by switching to:

```env
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
```

The app uses mail-capable notifications for registration/verification/password reset and selected marketplace notifications. Never commit real SMTP credentials.

## Multilingual Setup

Configured locales:

- `lt`: default locale.
- `en`: fallback locale.

Files:

- `lang/lt.json`
- `lang/en.json`
- `lang/lt/*.php`
- `lang/en/*.php`

Rules:

- Keep JSON translation key sets identical.
- Use dot-based keys for new text, such as `ui.actions.save`, `orders.status.pending`, `products.fields.title`, and `notifications.orders.created.title`.
- Use `__('key')` in Blade, Livewire, actions, notifications, and emails.
- Database content with translation helpers should seed every configured locale.

Useful check:

```bash
php artisan test --compact tests/Feature/Translations/TranslationFilesTest.php
```

See [docs/translations.md](docs/translations.md).

## Testing

Run all tests:

```bash
php artisan test --compact
```

Run focused suites:

```bash
php artisan test --compact tests/Feature
php artisan test --compact tests/Unit
php artisan test --compact tests/Feature/Marketplace
php artisan test --compact tests/Feature/Seeders
php artisan test --compact tests/Feature/Factories/ModelFactoriesTest.php
```

Run one test:

```bash
php artisan test --compact --filter=test_authenticated_buyer_can_add_active_product_to_cart
```

There is no `composer test` script configured; use `php artisan test`.

See [docs/testing.md](docs/testing.md).

## Database And Seeders

Commands:

```bash
php artisan migrate
php artisan db:seed
php artisan migrate:fresh --seed
php artisan migrate:status --no-interaction
```

Seeder layers:

- `MinimalSeeder`: production-safe required records.
- `DemoScenarioSeeder`: local/testing demo marketplace data, only outside production.
- `AuditLogSeeder`: demo audit rows, called by `DatabaseSeeder` outside production.

See [docs/database.md](docs/database.md), [docs/seeders.md](docs/seeders.md), and [docs/demo-seeding.md](docs/demo-seeding.md).

## Security Notes

- Guard-specific routes protect buyer, seller, and admin areas.
- Policies own model-level authorization.
- Livewire actions must authorize before loading private data and again before mutations.
- Buyer data is private to the buyer.
- Seller product/order data is private to the owning seller.
- Admin sensitive actions should require a reason and write audit logs.
- Debugbar must not be enabled in production.
- `APP_DEBUG=false` in production.
- File uploads must use Laravel Storage and the shared image validation/actions.

See [docs/security.md](docs/security.md) and [docs/audit-log-system.md](docs/audit-log-system.md).

## Performance Notes

- Paginate large lists.
- Eager-load relationships used by Blade/Livewire loops.
- Use database aggregates such as `withCount`, `withExists`, and `sum()` instead of loading full collections for counters.
- Use selected columns for large lists.
- Cache stable reference data only; do not leak private user data through shared cache keys.
- Add indexes for frequently filtered/sorted columns.
- Keep Debugbar local-only.

See [docs/performance.md](docs/performance.md).

## Screenshots

No committed UI screenshots were found during the documentation audit.

TODO: add screenshots under `docs/screenshots/` for the homepage, catalog, product detail, cart, checkout, buyer dashboard, seller dashboard, admin dashboard, admin product management, and order status pages. Do not add broken image links before the files exist.

## Roadmap

The practical documentation roadmap is in [docs/roadmap.md](docs/roadmap.md). Current priorities are:

1. Stabilize role architecture.
2. Standardize the UI system.
3. Clean and document database architecture.
4. Keep factories and seeders complete.
5. Maintain feature tests.
6. Harden security.
7. Optimize performance.
8. Improve notifications.
9. Improve cart and checkout.
10. Complete production hardening.

The local GSD modernization roadmap also lives in `.planning/`.

## Known Issues

- Payment provider integration is not complete; checkout simulates successful payment.
- Full screenshot documentation is missing.
- UI dependencies are still in transition: maryUI is the target, while WireUI/daisyUI/Flowbite remain installed.
- Product images still have legacy compatibility fields alongside normalized `product_images`.
- Some release notes are local docs only; no git tags or GitHub releases exist yet.
- No standalone `LICENSE` file exists, although `composer.json` declares `MIT`.
- Formal dispute records are not implemented. Product/order messaging exists, but it is not a full dispute workflow.
- Production deployment automation is not complete.

## Release Management

- Changelog: [CHANGELOG.md](CHANGELOG.md)
- Release workflow: [docs/releases/README.md](docs/releases/README.md)
- Release notes template: [docs/release-notes-template.md](docs/release-notes-template.md)

Tag example:

```bash
git tag -a v0.1.0 -m "Release v0.1.0"
git push origin v0.1.0
```

## Developer Workflow

1. Create a focused branch.
2. Inspect related routes, models, migrations, seeders, tests, docs, and policies.
3. Make the smallest coherent change.
4. Add or update tests for behavior changes.
5. Run focused tests, then broader tests when appropriate.
6. Run `npm run build` for frontend-impacting changes.
7. Update README/docs/CHANGELOG/release notes when behavior changes.
8. Commit with a clear message.

Examples:

```text
refactor: standardize role architecture
test: add feature coverage for core flows
security: harden marketplace authorization
perf: audit and optimize marketplace queries
docs: add project documentation
```

See [docs/developer-workflow.md](docs/developer-workflow.md).

## Production Notes

Production checklist:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Do not run `migrate:fresh` in production. Configure backups, writable storage/cache directories, real mail credentials, debug-safe environment values, and a queue worker if async queues are enabled.

See [docs/production.md](docs/production.md).

## Contributing Rules

- Follow `AGENTS.md` and Laravel Boost guidance.
- Use Eloquent models, scopes, relationships, and actions. Do not add raw SQL strings.
- Do not query inside Blade views or loops.
- Keep controllers and Livewire components thin.
- Validate input and authorize actions.
- Keep visible UI text translatable.
- Add or update PHPUnit tests for code changes.
- Run `vendor/bin/pint --dirty --format agent` after PHP changes.
- Keep commits focused.

## License

`composer.json` declares the project license as `MIT`. No standalone `LICENSE` file was found during the documentation audit.
