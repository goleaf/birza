# Seeders And Factories Guide

Birza uses production-safe seeders, local demo seeders, and factories for tests.

## Seeder Layers

| Layer | Seeder | Purpose |
| --- | --- | --- |
| Production required | `MinimalSeeder` | Required countries, categories, global settings, and legacy admin. |
| Local demo | `Database\Seeders\Demo\DemoScenarioSeeder` | Full marketplace demo data. Runs only outside production through `DatabaseSeeder`. |
| Demo audit | `AuditLogSeeder` | Demo audit rows. Runs outside production through `DatabaseSeeder`. |
| Legacy/test information | `database/seeders/test_information/*` | Older deterministic catalog, countries, attributes, users, products, and settings data. |

`DatabaseSeeder` always calls `MinimalSeeder`. It returns early in `production`, so fake demo data is not seeded in production by default.

## Commands

Full local demo:

```bash
php artisan migrate:fresh --seed
```

Seed existing database:

```bash
php artisan db:seed
```

Minimal only:

```bash
php artisan migrate:fresh
php artisan db:seed --class=MinimalSeeder
```

Specific demo seed:

```bash
php artisan db:seed --class='Database\Seeders\Demo\DemoScenarioSeeder'
```

## Demo Users

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@example.com` | `password` |
| Legacy admin | `admin@admin.com` | `password` |
| Buyer | `buyer@example.com` | `password` |
| Empty buyer | `demo-empty-buyer@example.com` | `password` |
| Cart buyer | `demo-cart-buyer@example.com` | `password` |
| Orders buyer | `demo-orders-buyer@example.com` | `password` |
| Seller | `seller@example.com` | `password` |
| Seller one | `demo-seller-one@example.com` | `password` |
| Seller two | `demo-seller-two@example.com` | `password` |
| Empty seller | `seller-empty@example.com` | `password` |
| Buyer and seller | `buyer-seller@example.com` | `password` |
| Test buyers | `buyer1@birza.lt` to `buyer10@birza.lt` | `password123` |
| Test sellers | `seller1@birza.lt` to `seller10@birza.lt` | `password123` |

## Demo Data

Demo seeders create data for:

- active, inactive, blocked, and unverified accounts
- categories and countries
- products with active/inactive/out-of-stock/low-stock/no-image states
- product images and gallery placeholders
- carts, cart items, bundle carts, and checkout scenarios
- orders, order items, bundles, status histories, and snapshots
- discounts, promo codes, and redemptions
- wishlists and wishlist items
- reviews
- product questions and answers
- product reports
- stock alerts
- buyer-seller conversations and messages
- notifications
- buyer credit history and attachments
- seller transactions
- audit logs and admin actions

## Factories

Factories exist in `database/factories` for the main models. Use factories in tests rather than manual inserts.

Factory rules:

- Use model columns that exist in migrations.
- Respect casts and enum values.
- Use relationship helpers for graph setup.
- Keep deterministic states for common scenarios, such as active/inactive products or pending/completed orders.
- Add coverage to `tests/Feature/Factories/ModelFactoriesTest.php` when adding a new factory or important state.

Messaging factories:

- `ConversationFactory` creates buyer-seller conversations and supports active, closed, blocked, buyer-archived, seller-archived, product-linked, order-linked, and message-populated states.
- `MessageFactory` creates buyer/seller/admin-role messages, read/unread states, and conversation-linked message rows.

## Adding A Seeder

```bash
php artisan make:seeder Demo/ExampleSeeder --no-interaction
```

Rules:

- Put required production data in `MinimalSeeder`.
- Put fake marketplace data in `DemoScenarioSeeder` or a domain seeder it calls.
- Use `firstOrNew`, `updateOrCreate`, or deterministic top-up logic for idempotency.
- Use factories for volume rows.
- Keep named demo records stable for manual QA and tests.
- Do not seed fake demo data in production.

## Tests

```bash
php artisan test --compact tests/Feature/Factories/ModelFactoriesTest.php
php artisan test --compact tests/Feature/Seeders
php artisan test --compact tests/Feature/DatabaseSeederTest.php
```

See [demo seeding guide](demo-seeding.md) for the longer scenario map.
