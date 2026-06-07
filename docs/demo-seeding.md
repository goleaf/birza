# Demo Seeding Guide

Birza has two seeding layers:

- `Database\Seeders\MinimalSeeder` creates production-safe required records: countries, categories, global settings, and the legacy admin.
- `Database\Seeders\Demo\DemoScenarioSeeder` creates local/testing marketplace data. `DatabaseSeeder` runs it only outside `production`.

## Commands

Fresh local database with demo data:

```bash
php artisan migrate:fresh --seed
```

Minimal records only:

```bash
php artisan migrate:fresh
php artisan db:seed --class=MinimalSeeder
```

Demo data after existing migrations:

```bash
php artisan db:seed --class='Database\Seeders\Demo\DemoScenarioSeeder'
```

Reset local data:

```bash
php artisan migrate:fresh --seed
```

Run seeder and factory tests:

```bash
php artisan test --compact tests/Feature/Factories/ModelFactoriesTest.php
php artisan test --compact tests/Feature/Seeders
php artisan test --compact tests/Feature/DatabaseSeederTest.php
```

## Demo Credentials

All demo passwords are local-only.

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@example.com` | `password` |
| Legacy admin | `admin@admin.com` | `password` |
| Buyer | `buyer@example.com` | `password` |
| Seller | `seller@example.com` | `password` |
| Buyer and seller profile | `buyer-seller@example.com` | `password` |
| Seeded test buyers | `buyer1@birza.lt` to `buyer10@birza.lt` | `password123` |
| Seeded test sellers | `seller1@birza.lt` to `seller10@birza.lt` | `password123` |

## Data Created

Demo seeders create:

- active, inactive, blocked-equivalent, and unverified buyers/sellers.
- one seller with no products and one buyer with no orders.
- category trees, an empty category, and an inactive category.
- active, inactive, out-of-stock, low-stock, no-image, long-title, high-price, minimum-price, soft-deleted, and pagination products.
- normalized local product images under `images/products/...` plus legacy image columns.
- orders for every supported `OrderStatus` and valid `OrderPaymentStatus` values.
- order items with product title, product price, seller name, and address snapshots.
- database carts with empty, filled, guest-like, unavailable-product, out-of-stock, and changed-price cases.
- wishlists and wishlist items for favorites-like buyer testing.
- reviews, product reports, notifications, buyer credit history, credit attachments, seller transactions, audit logs, activity rows, and admin action rows.

Unsupported by current schema: messages/conversations, delivery-method records, static pages, and separate payment records. Payment and delivery values currently live on `orders`.

## Adding New Factories

Use Artisan, then add useful states:

```bash
php artisan make:factory ExampleFactory --model=Example --no-interaction
```

Factory rules:

- use existing model columns only.
- respect enum values, foreign keys, nullable fields, soft deletes, and price precision.
- add graph helpers such as `withItems()`, `withImages()`, or `withStatusHistory()` only when the relationship exists.
- add a focused factory assertion to `tests/Feature/Factories/ModelFactoriesTest.php`.

## Adding New Seeders

Use Artisan:

```bash
php artisan make:seeder Demo/ExampleSeeder --no-interaction
```

Seeder rules:

- keep production-required rows in `MinimalSeeder`.
- keep fake marketplace rows in `DemoScenarioSeeder` or a domain seeder it calls.
- guard demo seeders with existing schema checks when they target optional/pending tables.
- use `updateOrCreate`, `firstOrNew`, or top-up counts for stable records.
- use factories for volume data, but keep named demo records deterministic for tests and manual QA.
