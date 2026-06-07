# Testing Guide

Birza uses PHPUnit through Laravel's `php artisan test` command. The test environment is defined in `phpunit.xml` and uses an in-memory SQLite database:

```env
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
CACHE_DRIVER=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
MAIL_MAILER=array
```

Most feature tests use `Illuminate\Foundation\Testing\RefreshDatabase`, so they run from a clean schema and should create only the data required for the scenario.

## Commands

Run all tests:

```bash
php artisan test --compact
```

Run feature tests:

```bash
php artisan test --compact tests/Feature
php artisan test --compact --testsuite=Feature
```

Run unit tests:

```bash
php artisan test --compact tests/Unit
php artisan test --compact --testsuite=Unit
```

Run the marketplace scenario suite:

```bash
php artisan test --compact tests/Feature/Marketplace
```

Run focused marketplace feature groups:

```bash
php artisan test --compact tests/Feature/Marketplace/ProductReportFeatureTest.php
php artisan test --compact tests/Feature/Marketplace/ProductQuestionFeatureTest.php
php artisan test --compact tests/Feature/Marketplace/ProductStockAlertFeatureTest.php
php artisan test --compact tests/Feature/Marketplace/WishlistFeatureTest.php
php artisan test --compact tests/Feature/Marketplace/SellerDiscountPromoCodeFeatureTest.php
```

Run Livewire component tests by file or filter:

```bash
php artisan test --compact tests/Feature/Marketplace/CartCheckoutFeatureTest.php
php artisan test --compact --filter=test_authenticated_buyer_can_add_active_product_to_cart
```

Run policy tests:

```bash
php artisan test --compact tests/Unit/Policies/ProductPolicyTest.php
php artisan test --compact tests/Unit/Policies/OrderPolicyTest.php
```

Run image upload tests:

```bash
php artisan test --compact tests/Feature/Marketplace/ImageUploadFeatureTest.php
php artisan test --compact tests/Feature/Images/ProductImagePipelineTest.php
```

Run notification tests:

```bash
php artisan test --compact tests/Feature/Notifications/MarketplaceNotificationSystemTest.php
```

Run query-count/performance tests:

```bash
php artisan test --compact tests/Feature/Marketplace/PerformanceQueryBudgetTest.php
```

Run seeder and factory tests:

```bash
php artisan test --compact tests/Feature/Seeders
php artisan test --compact tests/Feature/Factories/ModelFactoriesTest.php
php artisan test --compact tests/Feature/DatabaseSeederTest.php
```

There is no `composer test` script in `composer.json`; use `php artisan test`.

## Test Data

Use factories for all test data. Avoid depending on demo seeders unless the scenario is explicitly about the seeder.

Marketplace helpers live in `tests/Feature/Support/MarketplaceTestHelpers.php` and provide readable setup for:

- `createAdmin()`
- `createBuyer()`
- `createSeller()`
- `createProduct()`
- `createCartWithItem()`
- `createOrderWithItem()`
- `actingAsAdmin()`, `actingAsBuyer()`, and `actingAsSeller()`

Keep helpers simple. If a scenario depends on exact status, price, stock, owner, or locale values, set those values in the test body so the business rule stays visible.

## Fakes

Use Laravel fakes for external side effects:

- `Storage::fake('public')` for image upload and generated variant tests.
- `Notification::fake()` for notification assertions.
- `Mail::fake()` for mail-specific registration, reset, and notification tests.
- `Queue::fake()` only when queue dispatch is the behavior under test.
- Queue/mail/event fakes only when the fake does not hide the behavior being tested.

Image tests should assert both validation and storage effects. Checkout and order tests should assert database records and snapshots, not only redirects.

## Covered Marketplace Flows

The main marketplace feature suite covers:

- Authentication, logout, invalid login, inactive account protection, and seller verification restrictions.
- Cross-guard role access for guests, buyers, sellers, admins, and generic web users.
- Buyer catalog visibility for active, inactive, deleted, filtered, empty, and detail-page product states.
- Seller product ownership boundaries.
- Database-backed cart add/update/remove/empty-cart/checkout flows.
- Checkout stock validation, deleted-product blocking, backend price recalculation, order item snapshots, address snapshots, and cart conversion.
- Order status transitions, invalid transitions, admin reason requirements, status history, audit logging, and notifications.
- Product image upload through seller forms, oversized-image validation, image fallback behavior, and lower-level variant pipeline tests.
- Product questions, product reports, product stock alerts, product wishlists, and seller discount/promo-code flows.
- Locale switching, guest/authenticated locale rendering, translated order status labels, and required marketplace translation keys.

When adding a new feature test, prefer the narrowest file that matches the user scenario and run that file before broader suites.

## Adding New Tests

1. Use PHPUnit classes, not Pest.
2. Generate tests with Artisan when creating a new file:

```bash
php artisan make:test Marketplace/ExampleFeatureTest --phpunit --no-interaction
php artisan make:test Models/ExampleTest --unit --phpunit --no-interaction
```

3. Use factories for setup.
4. Use `RefreshDatabase` for database feature tests.
5. Use guard-specific authentication helpers or `actingAs($user, 'buyer')`, `actingAs($seller, 'seller')`, and `actingAs($admin, 'admin')`.
6. Test happy path, failure path, ownership/authorization failure, and important edge cases.
7. Prefer assertions on database state, notifications, storage, and redirects over implementation details.

## Livewire Example

```php
Livewire::actingAs($buyer, 'buyer')
    ->test(BuyerCartIndex::class)
    ->call('updateQuantity', $cartItem->id, 2)
    ->assertOk();
```

## Image Example

```php
Storage::fake('public');

Livewire::actingAs($seller, 'seller')
    ->test(SellerProductCreate::class, ['categoryId' => $category->id])
    ->set('imageUploads', [$file])
    ->call('save')
    ->assertOk();
```

## Query Count Tests

Use query-count tests for high-risk list pages:

- buyer catalog
- buyer order list
- seller product list
- seller order list
- admin product/order tables
- checkout with many cart items

Keep budgets generous enough to survive framework noise, but strict enough to catch per-row query regressions.

## Related Docs

- [Performance guide](performance.md)
- [Seeders guide](seeders.md)
- [Security guide](security.md)
