# Birza

Birza is a Laravel marketplace platform for food and grocery trading. It has separate public, buyer, seller, and admin surfaces: buyers browse localized products, manage a cart, and place orders; sellers manage their product catalog and order work; admins manage marketplace data such as buyers, sellers, products, categories, attributes, countries, orders, buyer credit, and global settings.

The project is a server-rendered Laravel application. It uses Blade and Livewire for interactive screens, Eloquent models for data access, Vite and Tailwind for frontend assets, and separate authentication guards for buyers, sellers, and admins.

## Main Features

Current features verified from the repository:

- Public home page and language switcher.
- Buyer authentication, registration, email verification, password reset, dashboard, profile, product catalog, product details, cart, checkout, and order pages.
- Seller authentication, registration, email verification, password reset, dashboard, profile, product management, order pages, and transaction pages.
- Admin login, dashboard, profile, catalog management, product management, category management, attribute/value management, country management, buyer management, seller management, buyer credit management, order management, and global settings.
- Product catalog with categories, subcategories, attributes, countries of origin, price/stock/organic filters, pagination, and a small product search API.
- Cart and checkout using LaraCart session state, stock checks, VAT/portal fee calculation, order and order item creation, and stock decrementing.
- Order dashboards and order lifecycle UI. Order status enum/history work exists in the current development tree, but should still be verified through migrations and tests before treating it as production-final.
- Product images through legacy product image fields, product image records where available, public storage URLs, generated seed images, and a fallback product placeholder.
- Multilingual interface with Lithuanian and English JSON translations.
- Role-based access through separate guards and user tables for buyers, sellers, and admins.
- Factories, seeders, and PHPUnit tests for core marketplace areas.

Not currently verified as complete production features:

- Real payment gateway integration. Checkout currently simulates successful payment.
- Favorites and marketplace messaging.
- Full production deployment automation.
- Complete screenshot documentation.

## Technology Stack

- PHP `^8.3`; local inspection used PHP `8.5.5` through Laravel Herd.
- Laravel `12.61.1`.
- Livewire `4.3.1`.
- Laravel Sanctum `4.x`.
- Blade templates, no React/Vue/Inertia app.
- Eloquent models and factories.
- SQLite for the current local database; MySQL is also configured in `.env.example`.
- Vite `8.x`.
- Tailwind CSS `3.x`.
- maryUI `2.x` with `x-mary-*` prefix.
- WireUI, daisyUI, and Flowbite are still installed while the UI stack is being modernized.
- LaraCart for cart session handling.
- Intervention Image for generated and processed images.
- PHPUnit `11.x`.
- Laravel Boost / MCP for schema inspection and project-aware documentation lookup.

## System Requirements

- PHP 8.3 or newer.
- Composer 2.
- Node.js and npm. Local inspection used Node `22.22.2` and npm `10.9.7`.
- SQLite with `pdo_sqlite`, or MySQL with `pdo_mysql`.
- PHP extensions commonly required by Laravel, file uploads, and image handling, including `openssl`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, and GD or Imagick for Intervention Image.
- A local web environment. This repository is configured for Laravel Herd at `https://birza.test`.
- Mailpit or another SMTP sink if you want to test email flows locally.

No Docker Compose, Sail scaffold, or Valet-specific project files are present. `laravel/sail` is installed as a dev dependency, but the repository does not currently include Sail runtime files.

## Installation

Clone the repository and install dependencies:

```bash
git clone <repository-url> birza
cd birza
composer install
npm install
cp .env.example .env
php artisan key:generate
```

For the simplest local SQLite setup, edit `.env`:

```env
APP_NAME=Birza
APP_ENV=local
APP_DEBUG=true
APP_URL=https://birza.test

DB_CONNECTION=sqlite
DB_DATABASE=birza.sqlite
```

Create the SQLite database file if it does not exist:

```bash
touch database/birza.sqlite
```

Run migrations, seeders, and storage linking:

```bash
php artisan migrate --seed
php artisan storage:link
```

Start frontend assets in development:

```bash
npm run dev
```

With Laravel Herd, the app is available at:

```text
https://birza.test
```

If you are not using Herd, you can run Laravel's built-in server:

```bash
php artisan serve
```

For non-HTTPS local serving, also set:

```env
APP_URL=http://127.0.0.1:8000
SESSION_SECURE_COOKIE=false
```

## Environment Variables

Important variables from `.env.example` and current configuration:

| Variable | Local value | Notes |
| --- | --- | --- |
| `APP_NAME` | `Birza` recommended | Application name used by Laravel and mail. |
| `APP_ENV` | `local` | Use `production` in production. |
| `APP_KEY` | generated | Create with `php artisan key:generate`; never commit real keys. |
| `APP_DEBUG` | `true` local, `false` production | Must be `false` in production. |
| `APP_URL` | `https://birza.test` | Herd URL for this project. |
| `MAINTENANCE_BYPASS_SECRET` | empty local | Required before using `php artisan system close`. |
| `CORS_ALLOWED_ORIGINS` | `${APP_URL}` | Comma-separated allowed origins. |
| `DB_CONNECTION` | `sqlite` local | `.env.example` currently shows MySQL; local repo uses SQLite. |
| `DB_DATABASE` | `birza.sqlite` for SQLite | Maps to `database/birza.sqlite`. |
| `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD` | MySQL only | Required if using MySQL instead of SQLite. |
| `CACHE_DRIVER` | `file` | File cache is the example default. |
| `FILESYSTEM_DISK` | `local` | Public product assets still use the configured `public` disk where code asks for it. |
| `QUEUE_CONNECTION` | `sync` | No separate worker is needed for the default local setup. |
| `SESSION_DRIVER` | `file` | Tests use array sessions from `phpunit.xml`. |
| `SESSION_ENCRYPT` | `true` | Encrypts session payloads. |
| `SESSION_SECURE_COOKIE` | `true` for HTTPS | Set `false` only for plain HTTP local development. |
| `MAIL_MAILER` | `smtp` | Local example targets Mailpit. |
| `MAIL_HOST` | `mailpit` | Change for your local or production SMTP server. |
| `MAIL_PORT` | `1025` | Mailpit default. |
| `MAIL_FROM_ADDRESS` | `hello@example.com` | Replace for production. |
| `BROADCAST_DRIVER` | `log` | Pusher keys are present but not required for the current local setup. |
| `PUSHER_*` and `VITE_PUSHER_*` | empty/example | Only needed if realtime broadcasting is enabled. |
| `AWS_*` | empty/example | Only needed for S3. |
| `DEBUGBAR_ENABLED` | `false` recommended | Supported by `config/debugbar.php`; keep disabled in production. |
| `VAT_RATE` | optional | `config/app.php` defaults to `0.21` if unset. |

Production-safe debug values:

```env
APP_ENV=production
APP_DEBUG=false
DEBUGBAR_ENABLED=false
```

## Database Setup

The current local database connection is SQLite and uses:

```env
DB_CONNECTION=sqlite
DB_DATABASE=birza.sqlite
```

Useful commands:

```bash
php artisan migrate
php artisan migrate:status
php artisan migrate:fresh --seed
php artisan db:seed
```

Use `migrate:fresh --seed` only when you are okay with deleting local data.

The live local database inspected for this README had older migrations already run and several newest development migrations pending, including relationship/media/status hardening work. A fresh setup should run all migrations from disk.

## Migrations

Migrations cover:

- Authentication support tables: password reset tokens, failed jobs, Sanctum personal access tokens.
- Admins, buyers, sellers, and a newer generic users/profile relationship direction.
- Countries, categories, attributes, attribute values, product/category/attribute pivots.
- Products, product fields, product filters, product images, and image-library fields.
- Carts and cart items.
- Orders, order items, payment fields, order statuses, and order status history.
- Buyer credit history and credit attachments.
- Seller transactions.
- Activities, cache tables, reviews, notifications, addresses, global settings, and admin theme settings.

Inspect migration state with:

```bash
php artisan migrate:status --no-interaction
```

## Seeders

`DatabaseSeeder` currently calls these seeders:

- `CountriesSeeder`
- `CategorySeeder`
- `GlobalSettingsSeeder`
- `TestUsersSeeder`
- `ProductSeeder`
- `AttributesSeeder`
- `ProductAttributeSeeder`
- `AdminSeeder`

Run the full demo seed:

```bash
php artisan db:seed
```

Run a specific seeder:

```bash
php artisan db:seed --class=AdminSeeder
php artisan db:seed --class='Database\Seeders\test_information\TestUsersSeeder'
```

`ProductSeeder` generates WebP product images under `storage/app/public/products` when missing. `CategorySeeder`, `CountriesSeeder`, and product descriptions seed Lithuanian and English content.

## Demo/Test Accounts

These accounts are created by seeders for local development only.

| Role | Email | Password | Seeder |
| --- | --- | --- | --- |
| Admin | `admin@admin.com` | `password` | `AdminSeeder` |
| Buyer 1 | `buyer1@birza.lt` | `password123` | `TestUsersSeeder` |
| Buyer 2-10 | `buyer2@birza.lt` to `buyer10@birza.lt` | `password123` | `TestUsersSeeder` |
| Seller 1 | `seller1@birza.lt` | `password123` | `TestUsersSeeder` |
| Seller 2-10 | `seller2@birza.lt` to `seller10@birza.lt` | `password123` | `TestUsersSeeder` |

Buyer, seller, and admin models cast passwords as `hashed`, so seeder password strings are hashed when persisted.

## User Roles

Roles are implemented as separate guards and user tables, not a shared roles table.

| Role | Access | Can do | Cannot do |
| --- | --- | --- | --- |
| Guest | Public home, language switching, buyer/seller login and registration, public product search API. | Choose buyer or seller flow, register, request password resets, verify email links. | Access buyer, seller, or admin dashboards. |
| Buyer | `auth:buyer` routes under `/buyer`. | View dashboard, edit profile, browse catalog, filter products, view product details, manage cart, checkout, view orders. | Manage seller products, seller transactions, admin CRUD/settings. |
| Seller | `auth:seller` routes under `/seller`. | View dashboard, edit profile/categories, manage own products, view seller orders, view transactions. | Use buyer cart/checkout, manage other sellers' products, access admin tools. |
| Admin | `auth:admin` routes under `/admin`. | Use backend dashboard, manage buyers, sellers, buyer credit, countries, categories, products, attributes, attribute values, orders, settings, and admin profile. | Use buyer/seller dashboards as those guards unless separately logged in. |

## Local Development Commands

Common commands:

```bash
php artisan optimize:clear
php artisan config:clear
php artisan route:list --except-vendor
php artisan migrate:fresh --seed
php artisan storage:link
php artisan test --compact
```

Development server commands:

```bash
npm run dev
php artisan serve
```

Herd users normally do not need `php artisan serve`; use `https://birza.test`.

Project-specific or notable commands:

```bash
composer all
php artisan refresh
php artisan system close
php artisan system open
php artisan filterable:list
php artisan filterable:inspect
php artisan debugbar:clear
```

Notes:

- `composer all` installs Composer dependencies, generates an app key, links storage, and clears caches.
- `php artisan refresh` is destructive/local-only: it clears caches, runs `migrate:fresh --seed`, creates storage directories, adjusts storage permissions, cleans generated storage files, runs IDE helper commands, and optimizes/clears caches.
- `php artisan system close` requires `MAINTENANCE_BYPASS_SECRET` and enables maintenance mode with `resources/views/errors/maintenance.blade.php`.

## Frontend Build Commands

Install and run assets:

```bash
npm install
npm run dev
npm run build
```

Frontend setup:

- Vite entry points are `resources/css/app.css` and `resources/js/app.js`.
- Tailwind scans Blade views plus maryUI and WireUI package component paths.
- Tailwind plugins include Flowbite, typography, aspect-ratio, and daisyUI.
- daisyUI themes currently include `corporate` and `light`.
- maryUI is configured with the `mary-` component prefix in `config/mary.php`.
- Livewire injects its scripts/styles through layouts; `resources/js/app.js` intentionally does not start a second Alpine instance.

## Testing Commands

The project uses PHPUnit.

Run all tests:

```bash
php artisan test --compact
```

Run feature tests:

```bash
php artisan test --compact tests/Feature
```

Run unit tests:

```bash
php artisan test --compact tests/Unit
```

Run one test file:

```bash
php artisan test --compact tests/Feature/Controllers/Frontend/Buyer/CartControllerTest.php
```

Run a filtered test:

```bash
php artisan test --compact --filter=test_supported_json_translation_files_have_identical_keys
```

Livewire components are tested from PHPUnit using Livewire testing helpers in the existing feature test suite.

There is no `composer test` script configured in `composer.json`; use `php artisan test`.

## Storage And Image Setup

Required command:

```bash
php artisan storage:link
```

Storage conventions observed in the project:

- Public storage link: `public/storage` -> `storage/app/public`.
- Seeded product images: `storage/app/public/products`.
- Legacy product image columns normalize filenames to the `products/` public-disk path.
- Current image-pipeline work uses public-disk product image records and variant paths where available.
- Product fallback image: `public/images/admin-product-placeholder.svg`.
- Buyer credit attachments are uploaded to `storage/app/public/credit-attachments`.
- Livewire temporary uploads use `storage/app/livewire-tmp` unless `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK` is configured.
- `scripts/clean-storage.php` delegates to OS-specific cleanup scripts.

## Queue Setup

Default queue configuration:

```env
QUEUE_CONNECTION=sync
```

With `sync`, no queue worker is required. If you change to `database`, `redis`, `sqs`, or another async queue driver, run a worker in production:

```bash
php artisan queue:work
```

No `app/Jobs` directory was present during this README audit, but notifications and mail flows should still be reviewed if queueing is introduced.

## Mail Setup

`.env.example` targets SMTP/Mailpit:

```env
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

The app sends email for buyer/seller registration, verification, password reset, and order status notifications. For local work, use Mailpit or switch to:

```env
MAIL_MAILER=log
```

For production, configure a real SMTP, SES, Mailgun, or Postmark transport and never commit mail credentials.

## Multilingual Setup

Supported locales are configured in `config/app.php`:

```php
'locale' => 'lt',
'locales' => ['lt', 'en'],
'fallback_locale' => 'en',
```

Translation files:

- `lang/lt.json`
- `lang/en.json`

Language switching route:

```text
GET /language/{locale}
```

Translation conventions:

- Keep `lang/lt.json` and `lang/en.json` key sets identical.
- Core newer keys use dot notation such as `orders.status.pending` and `ui.actions.save`.
- Older underscore keys still exist and should not be removed unless all usages are migrated.
- Use `__('key')` in Blade, Livewire, notifications, and controllers.
- Category, country, product description, attribute, and attribute value data can store per-locale JSON translations through model translation helpers.
- Add a locale by updating `config/app.php`, adding `lang/{locale}.json`, seeding translated data where needed, and extending translation tests.

Useful translation checks:

```bash
php artisan test --compact tests/Feature/Translations/TranslationFilesTest.php
php scripts/validate-json-translations.php
```

## Screenshots

No screenshots directory was found during this README audit.

TODO: add screenshots for:

- Homepage.
- Product catalog.
- Product detail page.
- Cart.
- Checkout.
- Buyer dashboard.
- Seller dashboard.
- Admin dashboard.
- Admin product management.
- Order detail/status pages.

Do not add broken image links to this README. Add screenshot files first, then link them.

## Project Structure

Important folders and files:

| Path | Purpose |
| --- | --- |
| `app/Actions` | Single-purpose business actions for API search, auth/logout redirects, cart operations, frontend page data, image pipeline work, and order status changes. |
| `app/Console/Commands` | Custom Artisan commands such as `refresh` and `system`. |
| `app/Enums` | Domain enums, including order status/payment status work in the current development tree. |
| `app/Helpers` | Project helper files autoloaded by Composer, including order status helpers. |
| `app/Http/Controllers` | Thin route controllers for public, API, auth/logout, and admin landing flows. |
| `app/Http/Middleware` | Custom middleware such as locale handling and auth redirects. |
| `app/Http/Requests` | Form request validation for API and frontend requests. |
| `app/Livewire` | Buyer, seller, admin, and auth Livewire components. |
| `app/Models` | Eloquent models for marketplace entities. |
| `app/Policies` | Authorization policies. |
| `app/Providers` | Application, auth, event, route, user guard, view, and global settings providers. |
| `app/Support` | Support classes such as locale formatting, markdown safety, spotlight, and image result DTOs. |
| `config` | Laravel and package configuration, including auth guards, filesystems, Livewire, maryUI, WireUI, Debugbar, queue, mail, and image config. |
| `database/migrations` | Database schema migrations. |
| `database/factories` | Model factories for tests and seed data. |
| `database/seeders` | Demo/test seeders and country JSON data. |
| `docs` | Project-owned technical audits and documentation. |
| `lang` | JSON translation files for Lithuanian and English. |
| `public` | Web entrypoints, built assets, robots file, and public fallback images. |
| `resources/css` | Tailwind/Vite CSS entrypoint. |
| `resources/js` | Vite JavaScript entrypoint. |
| `resources/views` | Blade layouts, components, Livewire views, frontend pages, backend pages, emails, notifications, pagination, and errors. |
| `routes` | Public, API, buyer, seller, admin, console, and broadcast route files. |
| `scripts` | Translation and storage utility scripts. |
| `storage` | Local generated files, logs, framework cache/session/view files, public uploads, and Livewire temp uploads. |
| `tests` | PHPUnit feature and unit tests. |

## Documentation Files

Project documentation lives in:

- `CHANGELOG.md`
- `docs/database-structure-audit-2026-06-07.md`
- `docs/eloquent-relationship-map-2026-06-07.md`
- `docs/`
- `.planning/` for local GSD planning state
- `AGENTS.md`, `CLAUDE.md`, and `GEMINI.md` for agent/project instructions

Some additional docs may exist in active local work. Commit them before linking them from this README.

## Roadmap

### Phase 1 - Stabilize Roles And Access Control

Confirm guest, buyer, seller, and admin access rules, route guards, policies, redirects, and authorization tests. This matters because the marketplace uses separate user tables and guards instead of one shared roles table.

### Phase 2 - Standardize The UI System

Finish the Mary-first Livewire UI direction and retire or isolate remaining WireUI/daisyUI/Flowbite overlap. This lowers frontend maintenance cost and makes shared components predictable.

### Phase 3 - Improve Livewire Flows

Audit buyer, seller, admin, auth, upload, filtering, sorting, pagination, confirmation, and notification flows. This matters because most screens are route-mounted Livewire components.

### Phase 4 - Improve Database Structure

Finish pending relationship/media/status migrations, reconcile legacy and normalized tables, and verify indexes/foreign keys. This protects business history and reduces broken relationships.

### Phase 5 - Standardize Order Statuses

Complete the strict order lifecycle, actor permissions, status history, notifications, and UI badges. This is central to buyer, seller, and admin order behavior.

### Phase 6 - Improve Cart And Checkout

Reconcile LaraCart session state with database cart/cart item tables, add stronger checkout tests, and replace simulated payment when a real payment method is selected.

### Phase 7 - Improve Image Pipeline

Finish product image variants, migration from legacy image fields, cleanup behavior, fallback handling, upload validation, and storage documentation.

### Phase 8 - Harden Factories And Seeders

Keep demo data realistic, idempotent, localized, and aligned with model casts/enums. This keeps local setup and tests trustworthy.

### Phase 9 - Add Feature Tests

Broaden regression coverage for buyer catalog/cart/checkout/orders, seller products/orders/transactions, admin CRUD/settings, translations, storage, and access control.

### Phase 10 - Production Hardening

Finalize environment defaults, cache/build steps, debugbar safety, queue/mail choices, storage permissions, deployment notes, logging, backups, and monitoring.

## Known Issues

- The current local database had pending development migrations during this README audit. Run `php artisan migrate:status` and migrate before testing current branch work.
- `.env.example` still defaults to MySQL while the local project uses SQLite. Set `DB_CONNECTION=sqlite` and `DB_DATABASE=birza.sqlite` for the easiest local setup.
- Checkout currently simulates payment success; no real payment provider is configured.
- Screenshots are missing.
- UI packages are in transition: maryUI is active, while WireUI, daisyUI, and Flowbite are still present.
- Product images have legacy fields and newer image-record/variant direction; finish and verify the migration path before relying on one canonical image model.
- Cart behavior still uses LaraCart session state; database cart/cart item tables need final alignment.
- Some newer relationship/status/media work is in active development and should be verified with migrations and tests before production use.
- No `LICENSE` file exists, although `composer.json` declares `MIT`.

## Contributing Rules

- Follow `AGENTS.md` and Laravel Boost guidance.
- Use Eloquent models, scopes, relationships, and actions. Do not add raw SQL strings.
- Do not query inside Blade views or loops.
- Keep controllers and Livewire components thin; move reusable business logic to `app/Actions` or model scopes.
- Validate input with Form Requests where possible.
- Eager load relationships used by views and tables.
- Keep all visible UI text translatable.
- Add or update PHPUnit tests for code changes.
- Run focused tests before committing.
- Run `vendor/bin/pint --dirty --format agent` after PHP changes.
- Keep commits focused and avoid mixing unrelated refactors with feature work.

## Production Notes

Production install/build checklist:

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

Production environment requirements:

```env
APP_ENV=production
APP_DEBUG=false
DEBUGBAR_ENABLED=false
QUEUE_CONNECTION=sync
```

If `QUEUE_CONNECTION` is changed to an async driver, run a supervised queue worker:

```bash
php artisan queue:work
```

Also verify:

- `storage` and `bootstrap/cache` are writable by the web server user.
- Real mail credentials are configured.
- `APP_KEY` is set and secret.
- Database backups are in place.
- Uploaded files are backed up if using local/public storage.
- Debugbar is not installed/enabled in production deployments.

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## License

`composer.json` declares the project license as `MIT`. No standalone `LICENSE` file was present during this README audit.
