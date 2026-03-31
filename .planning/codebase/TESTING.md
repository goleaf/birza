# Testing Patterns

**Analysis Date:** 2026-04-01

## Test Framework

**Runner:**
- PHPUnit 11 is the active test runner via root `phpunit.xml` and `phpunit/phpunit` in `composer.json`.
- `phpunit.xml` defines two suites: `Unit` from `tests/Unit` and `Feature` from `tests/Feature`.
- The coverage source in `phpunit.xml` includes `app/` only.
- Base classes are minimal wrappers: `tests/TestCase.php`, `tests/Feature/TestCase.php`, and `tests/Unit/TestCase.php`.
- The default test database is in-memory SQLite from `phpunit.xml` (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`).

**Assertion Library:**
- Core assertions come from PHPUnit.
- HTTP/database assertions come from Laravel’s testing layer, for example `assertOk()`, `assertRedirect()`, `assertViewHas()`, `assertJsonStructure()`, `assertDatabaseHas()`, and `assertSoftDeleted()` in `tests/Feature/Controllers/HomeControllerTest.php`, `tests/Feature/Controllers/Api/ProductSearchControllerTest.php`, `tests/Feature/DatabaseSeederTest.php`, and `tests/Unit/Models/ProductTest.php`.
- Livewire assertions are used for component flows in feature tests via `Livewire::test(...)`, for example `tests/Feature/Controllers/Backend/Auth/LoginControllerTest.php` and `tests/Feature/Controllers/Frontend/Auth/BuyerAuthControllerTest.php`.

**Run Commands:**
```bash
php artisan test --compact                                                # Run all tests
php artisan test --compact tests/Feature/Controllers/HomeControllerTest.php # Run one file
php artisan test --compact --filter=test_login_with_valid_credentials     # Run one test name/filter
php artisan test --compact --coverage                                     # Coverage when Xdebug/PCOV is available
```
- Watch mode is not configured. There is no Composer or npm watch-test script in `composer.json` or `package.json`.

## Test File Organization

**Location:**
- Tests live in a dedicated `tests/` tree, not beside the source files.
- Feature tests are grouped by route or user-facing subject under `tests/Feature/Controllers/Api`, `tests/Feature/Controllers/Backend`, `tests/Feature/Controllers/Frontend`, plus `tests/Feature/Seeders`.
- Unit tests are grouped by target type under `tests/Unit/Commands`, `tests/Unit/Helpers`, `tests/Unit/Middleware`, `tests/Unit/Models`, `tests/Unit/Notifications`, and `tests/Unit/Providers`.
- Route/page tests sit under `tests/Feature/Controllers/...` even when the route renders a Livewire component instead of a conventional controller action, for example `tests/Feature/Controllers/Backend/Auth/LoginControllerTest.php`.

**Naming:**
- Every test file ends with `Test.php`.
- Test class names mirror the subject plus `Test`, for example `HomeControllerTest`, `ProductTest`, `SetLocaleTest`, and `ResetSellerPasswordTest`.
- Test methods use verbose `test_*` snake_case names that describe the observable behavior, for example `test_product_seeder_is_idempotent()` and `test_home_logs_out_inactive_seller()`.

**Structure:**
```text
tests/
├── TestCase.php
├── Feature/
│   ├── TestCase.php
│   ├── Controllers/
│   │   ├── Api/
│   │   ├── Backend/
│   │   └── Frontend/
│   ├── Seeders/
│   ├── DatabaseSeederTest.php
│   └── CodexGsdIntegrationTest.php
└── Unit/
    ├── TestCase.php
    ├── Commands/
    ├── Helpers/
    ├── Middleware/
    ├── Models/
    ├── Notifications/
    └── Providers/
```

## Test Structure

**Suite Organization:**
```php
class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_for_guest(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertViewHas('locales')
            ->assertViewHas('communityStats');
    }
}
```
- This class-and-method structure matches `tests/Feature/Controllers/HomeControllerTest.php`.

**Patterns:**
- `use RefreshDatabase;` is the default for tests that hit Eloquent, routes, seeders, notifications, or commands. It appears across most files in `tests/Feature/Controllers/*`, `tests/Feature/Seeders/*`, and many files in `tests/Unit/Models/*`.
- Arrange/Act/Assert is followed informally with blank lines between setup, execution, and assertions, for example `tests/Feature/Controllers/Backend/Auth/LoginControllerTest.php` and `tests/Unit/Models/BuyerTest.php`.
- `setUp()` and `tearDown()` are only introduced when a test needs custom environment manipulation, for example `tests/Feature/DatabaseSeederTest.php` and `tests/Unit/Helpers/OrderStatusHelperTest.php`.
- There are no custom shared helper traits beyond the base TestCase classes. Tests keep setup local to the class.
- Data providers, dataset-style helpers, and Pest syntax are not used.

## Mocking

**Framework:**
- The suite relies on Laravel fakes/facades and Livewire testing helpers rather than bespoke mock classes.
- Concrete patterns in the repo:
  - `Storage::fake('local')` in `tests/Feature/Seeders/ProductSeederTest.php`
  - `Lang::shouldReceive('get')` in `tests/Unit/Helpers/OrderStatusHelperTest.php`
  - `Livewire::test(...)` in `tests/Feature/Controllers/Backend/Auth/LoginControllerTest.php` and `tests/Feature/Controllers/Frontend/Auth/BuyerAuthControllerTest.php`
- `mockery/mockery` is installed in `composer.json`, but direct `Mockery` usage was not detected in the sampled suite.

**Patterns:**
```php
Storage::fake('local');

Livewire::test(AdminLogin::class)
    ->set('email', $admin->email)
    ->set('password', 'wrong-password')
    ->call('login')
    ->assertHasErrors(['email']);
```

```php
Lang::shouldReceive('get')
    ->andReturnUsing(function ($key, $replace = [], $locale = null) {
        return str_replace('orders.status_', '', $key);
    });
```

**What to Mock:**
- Filesystem writes or uploads around seeders and generated assets, following `tests/Feature/Seeders/ProductSeederTest.php`.
- Translation lookup when isolating helper output, following `tests/Unit/Helpers/OrderStatusHelperTest.php`.
- Livewire transport and validation behavior through `Livewire::test(...)` rather than browser-style tests, following `tests/Feature/Controllers/Backend/Auth/LoginControllerTest.php`.

**What NOT to Mock:**
- Eloquent relationships, scopes, accessors, and persistence are usually tested against the real test database, for example `tests/Unit/Models/ProductTest.php`, `tests/Unit/Models/BuyerTest.php`, and `tests/Feature/Controllers/HomeControllerTest.php`.
- Authentication flows are usually exercised with real guards and `actingAs(...)`, not mocked auth internals, for example `tests/Feature/Controllers/HomeControllerTest.php`, `tests/Feature/Controllers/Frontend/Buyer/ProductControllerTest.php`, and `tests/Feature/Controllers/Backend/ProductControllerTest.php`.
- No `Http::fake()`, `Event::fake()`, `Notification::fake()`, `Mail::fake()`, `Queue::fake()`, or `Bus::fake()` patterns were detected in the current suite.

## Fixtures and Factories

**Test Data:**
```php
$buyer = Buyer::factory()->create();
Order::factory()->count(3)->create(['buyer_id' => $buyer->id]);

$response = $this->actingAs($buyer, 'buyer')
    ->get(route('buyer.orders.index'));
```
- This inline relationship-building pattern matches `tests/Feature/Controllers/Frontend/Buyer/OrderControllerTest.php`.

```php
Product::factory()->count(3)->active()->create();
Product::factory()->count(2)->inactive()->create();

$activeProducts = Product::active()->get();
```
- This factory-state pattern matches `tests/Unit/Models/ProductTest.php`.

**Location:**
- Shared factory definitions live in `database/factories/`.
- Common reusable states already exist and should be reused before inventing ad hoc booleans:
  - `active()` / `inactive()` in `database/factories/ProductFactory.php`, `database/factories/BuyerFactory.php`, `database/factories/SellerFactory.php`, `database/factories/CountryFactory.php`
  - `pending()` / `paid()` in `database/factories/OrderFactory.php`
- Seeder integration tests call seeders from `database/seeders/test_information/`, for example `database/seeders/test_information/ProductSeeder.php` and `database/seeders/test_information/ProductAttributeSeeder.php`.
- A separate `tests/fixtures/` or `tests/factories/` helper directory is not used. Tests compose data inline in the test method.

## Coverage

**Requirements:**
- No minimum coverage threshold is enforced in `phpunit.xml`.
- No CI workflow was detected under `.github/workflows/`, so coverage appears to be local/developer-driven rather than gate-enforced.

**Configuration:**
- Coverage source is limited to `app/` in `phpunit.xml`.
- No custom coverage exclusions, HTML report path, or threshold rules are configured in the repo.

**View Coverage:**
```bash
php artisan test --compact --coverage
vendor/bin/phpunit --coverage-text
```
- Use either command only when a coverage driver is available locally.

## Test Types

**Unit Tests:**
- Model tests verify relationships, casts, fillable fields, scopes, soft deletes, and custom methods in `tests/Unit/Models/*`.
- Middleware/provider/command tests mostly verify class wiring, configuration, and simple behavioral branches in `tests/Unit/Middleware/*`, `tests/Unit/Providers/*`, and `tests/Unit/Commands/*`.
- Helper and notification tests cover string output and mail-channel behavior in `tests/Unit/Helpers/OrderStatusHelperTest.php` and `tests/Unit/Notifications/ResetSellerPasswordTest.php`.

**Integration Tests:**
- Feature tests cover HTTP routes, auth redirects, view data, JSON response shape, and page availability in `tests/Feature/Controllers/*`.
- Livewire component flows are verified through route-level feature tests, not a separate component suite, for example `tests/Feature/Controllers/Backend/Auth/LoginControllerTest.php` and `tests/Feature/Controllers/Frontend/Auth/BuyerAuthControllerTest.php`.
- Seeder behavior is treated as integration work and checked for idempotency in `tests/Feature/Seeders/*` and `tests/Feature/DatabaseSeederTest.php`.

**E2E Tests:**
- Not used.
- No Laravel Dusk, Cypress, or Playwright end-to-end suite was detected in the repo.

## Common Patterns

**Async Testing:**
```php
$this->artisan('migrate:fresh', [
    '--seed' => true,
    '--database' => 'sqlite',
    '--no-interaction' => true,
])->assertExitCode(0);
```
- The suite is effectively synchronous. Current tests do not exercise queues, async jobs, or background workers. Command execution is asserted inline as shown in `tests/Feature/DatabaseSeederTest.php`.

**Error Testing:**
```php
Livewire::test(AdminLogin::class)
    ->set('email', $admin->email)
    ->set('password', 'wrong-password')
    ->call('login')
    ->assertHasErrors(['email']);

$this->assertGuest('admin');
```
- This pattern from `tests/Feature/Controllers/Backend/Auth/LoginControllerTest.php` is the standard way to verify validation/auth failures.

```php
$response = $this->get(route('buyer.products.index'));

$response->assertRedirect(route('home'));
```
- Redirect-on-unauthorized behavior is checked repeatedly in `tests/Feature/Controllers/Frontend/*` and `tests/Feature/Controllers/Backend/*`.

**Snapshot Testing:**
- Not used.
- No snapshot directories or snapshot assertions were detected.

## Placement Guidance

- When adding a route/page test, place it under the closest path inside `tests/Feature/Controllers/...`, even if the route resolves to a Livewire component instead of a controller class.
- When adding a model/relationship/scope/accessor test, place it under `tests/Unit/Models/`.
- When adding middleware or provider wiring tests, place them under `tests/Unit/Middleware/` or `tests/Unit/Providers/`.
- Reuse existing factories and named states from `database/factories/` before adding manual array-heavy setup.
- Default to `RefreshDatabase` for anything that touches the database, matching the current suite.

---

*Testing analysis: 2026-04-01*
*Update when test patterns change*
