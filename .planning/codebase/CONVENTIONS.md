# Coding Conventions

**Analysis Date:** 2026-04-01

## Naming Patterns

**Files:**
- PHP source files follow PSR-4 `PascalCase.php` naming under `app/`, `database/factories/`, and `tests/`, for example `app/Actions/Frontend/BuildWelcomePageDataAction.php`, `app/Livewire/Frontend/Auth/Login.php`, and `tests/Unit/Models/ProductTest.php`.
- PHPUnit files end with `Test.php` and mirror the subject hierarchy rather than living beside source files, for example `tests/Feature/Controllers/Backend/Auth/LoginControllerTest.php`, `tests/Feature/Seeders/ProductSeederTest.php`, and `tests/Unit/Middleware/SetLocaleTest.php`.
- Blade view files use lower-case directory names with snake_case or short noun names, for example `resources/views/backend/global_settings/index.blade.php`, `resources/views/frontend/buyer/profile/edit.blade.php`, and `resources/views/components/ui/flash-messages.blade.php`.
- Route entry files are audience-based and lower-case: `routes/web.php`, `routes/admin.php`, `routes/buyer.php`, `routes/seller.php`, `routes/api.php`.

**Functions:**
- PHPUnit methods use descriptive `test_*` snake_case names that read like behavior statements, for example `test_home_page_displays_database_backed_community_stats()` in `tests/Feature/Controllers/HomeControllerTest.php` and `test_buyer_deduct_credit_insufficient_balance()` in `tests/Unit/Models/BuyerTest.php`.
- Livewire actions and controller/action methods use verb-led names such as `login()`, `save()`, `cancelOrder()`, and `handle()` in `app/Livewire/Frontend/Auth/Login.php`, `app/Livewire/Backend/Attributes/Form.php`, `app/Livewire/Frontend/Buyer/Orders/Index.php`, and `app/Actions/Frontend/BuildWelcomePageDataAction.php`.
- Eloquent scopes use Laravel `scopeXxx` naming, such as `scopeActive()` in `app/Models/Product.php`, `scopePending()` in `app/Models/Order.php`, and `scopeWithRelationsForSeller()` in `app/Models/Category.php`.
- Accessors follow Laravel `get...Attribute` naming, for example `getFormattedPackageWeightAttribute()` and `getFormattedPricePerLiterAttribute()` in `app/Models/Product.php`.

**Variables:**
- Local variables and method parameters are camelCase, for example `$resolveHomeRedirectAction`, `$buildWelcomePageDataAction`, `$communityStats`, and `$throttleKey` in `app/Http/Controllers/Frontend/HomeController.php`, `app/Actions/Frontend/BuildWelcomePageDataAction.php`, and `app/Livewire/Frontend/Auth/Login.php`.
- Properties that mirror database columns or request payload keys often stay snake_case, for example `$is_filterable`, `$is_required`, `$is_active`, `country_of_origin`, and `parent_category_id` in `app/Livewire/Backend/Attributes/Form.php`, `app/Models/Product.php`, and `app/Models/Category.php`.
- Boolean names typically use `is*` or `has*` wording when they represent state, for example `is_active`, `is_verified`, `is_filterable`, `$hasValidationErrors`, and `$hasSessionSuccess` in `app/Models/Users/Buyer.php`, `app/Livewire/Backend/Attributes/Form.php`, and `resources/views/components/ui/flash-messages.blade.php`.
- Constants use uppercase names, often as arrays rather than enums, for example `Product::UNITS` in `app/Models/Product.php`, `Order::STATUS` in `app/Models/Order.php`, and `RouteServiceProvider::HOME` in `app/Providers/RouteServiceProvider.php`.

**Types:**
- Classes, traits, requests, actions, and Livewire components use PascalCase names under `App\...`, for example `App\Models\Concerns\HasJsonTranslations`, `App\Http\Requests\Frontend\SwitchLocaleRequest`, and `App\Livewire\Frontend\Buyer\Orders\Index`.
- PHP 8 attributes are used where Livewire expects metadata, for example `#[Layout('layouts.frontend.app')]` in `app/Livewire/Frontend/Auth/Login.php` and `app/Livewire/Frontend/Home.php`.
- Framework interfaces are imported directly with no prefix convention, for example `MustVerifyEmail` in `app/Models/Users/Buyer.php`.
- Native PHP enums are not detected. Status-like values remain arrays/constants on models such as `app/Models/Order.php`.

## Code Style

**Formatting:**
- PHP formatting is governed by Laravel Pint from `composer.json`. No project-specific Pint config file was detected (`pint.json`, `.pint.json`, and `pint.php` are absent).
- Workspace whitespace defaults come from `.editorconfig`: tabs with size `4`, LF line endings, final newline required, trailing whitespace trimmed except in Markdown.
- No PHP file under `app/`, `tests/`, `database/`, or `routes/` uses `declare(strict_types=1);`.
- Single quotes are the default for PHP strings, route names, view names, and translation keys, as seen in `app/Http/Controllers/Frontend/HomeController.php`, `app/Livewire/Frontend/Auth/Login.php`, and `tests/Feature/Controllers/HomeControllerTest.php`.
- Newer files use explicit return types and trailing commas in multi-line calls/arrays, for example `app/Http/Controllers/Frontend/HomeController.php` and `app/Actions/Frontend/BuildWelcomePageDataAction.php`. Older files are looser, for example `app/Models/Product.php` and `app/Livewire/Frontend/Auth/Login.php`. Match the style of the nearest sibling file instead of reformatting whole directories.

**Linting:**
- PHP lint/format tooling: Laravel Pint from `composer.json`.
- JavaScript or Blade-specific lint configs are not detected. No `eslint.config.*`, `.eslintrc*`, `.prettierrc*`, or `biome.json` files are present.
- Static analysis is not configured. No `phpstan`, `psalm`, `rector`, `phpcs`, or `phpmd` config files were found.
- Use `vendor/bin/pint --dirty --format agent` after PHP edits. There is no parallel repo-wide lint command in `composer.json`.

**Representative Style Pattern:**
```php
class HomeController extends Controller
{
    public function __invoke(
        Request $request,
        ResolveHomeRedirectAction $resolveHomeRedirectAction,
        BuildWelcomePageDataAction $buildWelcomePageDataAction,
    ): View|RedirectResponse {
        $redirect = $resolveHomeRedirectAction->handle($request);

        if ($redirect !== null) {
            return $redirect;
        }

        return view('frontend.welcome', $buildWelcomePageDataAction->handle());
    }
}
```
- Follow this thin-controller pattern when working near `app/Http/Controllers/Frontend/HomeController.php`.

## Import Organization

**Order:**
1. Parent/base classes or aliases when needed, for example `use Tests\TestCase as BaseTestCase;` in `tests/Feature/TestCase.php`.
2. Application classes from `App\...` and `Database\...`, for example `App\Models\Category`, `App\Actions\Frontend\BuildWelcomePageDataAction`, and `Database\Seeders\test_information\ProductSeeder`.
3. Framework classes from `Illuminate\...`, `Livewire\...`, or other vendor packages, for example `Illuminate\Http\Request`, `Illuminate\Support\Facades\Auth`, and `Livewire\Component`.
4. Test support imports such as `use Tests\TestCase;` in concrete test files.

**Grouping:**
- Imports are one-per-line. Group-use syntax is not used.
- Import sorting is not fully standardized. `app/Http/Controllers/Frontend/HomeController.php` places `App\...` before `Illuminate\...`, while `tests/Unit/Models/ProductTest.php` starts with `use Tests\TestCase;` before app imports. Mirror the surrounding file instead of normalizing unrelated imports.
- Aliases are used where class names would otherwise collide or become too long, for example `use App\Livewire\Backend\Auth\Login as AdminLogin;` in `tests/Feature/Controllers/Backend/Auth/LoginControllerTest.php`.

**Path Aliases:**
- PHP relies on Composer PSR-4 roots from `composer.json`: `App\\`, `Database\\Factories\\`, `Database\\Seeders\\`, and `Tests\\`.
- No front-end path aliasing is configured in `vite.config.js`.

## Error Handling

**Patterns:**
- Prefer framework-native validation failures and redirects over custom result objects. `app/Http/Requests/Frontend/SwitchLocaleRequest.php` uses a Form Request, while `app/Livewire/Frontend/Auth/Login.php` and `app/Livewire/Backend/Attributes/Form.php` call `$this->validate(...)`.
- Livewire actions surface user-facing failures with `ValidationException::withMessages(...)` or `session()->flash(...)`, as shown in `app/Livewire/Frontend/Auth/Login.php` and `app/Livewire/Frontend/Buyer/Orders/Index.php`.
- Guard clauses and early returns are common and preferred over nested `else` blocks. See `app/Http/Controllers/Frontend/HomeController.php`, `app/Actions/Auth/ResolveHomeRedirectAction.php`, and `app/Livewire/Frontend/Auth/Login.php`.
- Multi-write updates use database transactions when consistency matters, for example `DB::transaction(...)` in `app/Livewire/Frontend/Buyer/Orders/Index.php`.

**Error Types:**
- Central exception handling stays close to Laravel defaults in `app/Exceptions/Handler.php`; no project-specific exception hierarchy is established.
- Request validation belongs in Form Requests or component validation arrays, not inline manual response assembly, following `app/Http/Requests/Frontend/SwitchLocaleRequest.php` and `app/Livewire/Backend/Attributes/Form.php`.
- UI-facing errors are typically session flash messages rendered by `resources/views/components/ui/flash-messages.blade.php`.

## Logging

**Framework:**
- No application-wide structured logging abstraction was detected. Searches did not find committed `Log::...` or `logger(...)` calls in `app/`.
- Console commands rely on Artisan console output methods such as `$this->info()` and `$this->error()` in `app/Console/Commands/RefreshCommand.php` and `app/Console/Commands/SystemCommand.php`.

**Patterns:**
- Web and Livewire flows favor validation errors, redirects, and flash messages over explicit logs, for example `app/Livewire/Frontend/Auth/Login.php` and `app/Livewire/Frontend/Buyer/Orders/Index.php`.
- If you add logging near existing code, place it at command or integration boundaries instead of in Blade views, helpers, or simple accessors.

## Comments

**When to Comment:**
- Prefer comments only for framework quirks, cross-package behavior, or domain rules that are not obvious from the code itself.
- Good examples: namespace remapping and deferred Livewire script behavior in `app/Providers/AppServiceProvider.php`; translation fallback behavior in `app/Models/Concerns/HasJsonTranslations.php`; UI workflow constraints in `app/Livewire/Frontend/Seller/Orders/Show.php`.
- Avoid placeholder comments like the generated `//` stubs left in `tests/Feature/TestCase.php`, `tests/Unit/TestCase.php`, and `app/Exceptions/Handler.php`.

**JSDoc/TSDoc:**
- PHPDoc is used selectively when native typing is not enough, especially for array shapes and magic framework interactions. See `app/Actions/Frontend/BuildWelcomePageDataAction.php` and `app/Http/Requests/Frontend/SwitchLocaleRequest.php`.
- Inline `@var` annotations are used when guards return framework user types that need narrowing, for example `/** @var Buyer $buyer */` in `app/Livewire/Frontend/Buyer/Orders/Index.php`.
- JSDoc/TSDoc is not applicable; front-end code is Blade plus small Alpine fragments rather than TypeScript modules.

**TODO Comments:**
- No `TODO`, `FIXME`, `HACK`, or `XXX` comments were detected under `app/`, `tests/`, `resources/views/`, `routes/`, or `database/`.

## Function Design

**Size:**
- Controllers and Action classes are expected to stay thin and delegate, as shown by `app/Http/Controllers/Frontend/HomeController.php` calling `ResolveHomeRedirectAction` and `BuildWelcomePageDataAction`.
- Livewire components are often the exception: many components combine form state, validation, persistence, flash messaging, and rendering in one class, for example `app/Livewire/Frontend/Auth/Login.php`, `app/Livewire/Backend/Attributes/Form.php`, and `app/Livewire/Frontend/Buyer/Orders/Index.php`. When extending a component, keep new logic consistent with that local pattern unless you are explicitly refactoring it.

**Parameters:**
- Newer code uses explicit scalar/model types and nullable parameters where appropriate, for example `mount(?Attribute $attribute = null): void` in `app/Livewire/Backend/Attributes/Form.php` and `mount(?string $userType = null): void` in `app/Livewire/Frontend/Auth/Login.php`.
- Query scopes and older model helper methods frequently leave the query argument untyped, for example `scopeActive($query)` in `app/Models/Product.php` and `scopeWithRelationsForSeller($query)` in `app/Models/Category.php`.
- Prefer passing explicit dependencies into controllers/actions when the file already does so, for example constructor/method injection in `app/Http/Controllers/Frontend/HomeController.php`. In older Livewire code, `app(...)`, `request()`, and facade access are common and acceptable if that folder already follows the pattern.

**Return Values:**
- Tests consistently declare `: void`.
- Newer controllers, requests, and actions declare concrete return types like `: array`, `: bool`, `: View`, and `: View|RedirectResponse`, as seen in `app/Actions/Frontend/BuildWelcomePageDataAction.php`, `app/Http/Requests/Frontend/SwitchLocaleRequest.php`, and `app/Http/Controllers/Frontend/HomeController.php`.
- Older models and Livewire components omit return types more often, for example `render()` in `app/Livewire/Frontend/Auth/Login.php`, `attributeValues()` in `app/Models/Product.php`, and `orders()` in `app/Models/Users/Seller.php`.
- Guard-clause returns are the default branching style. Prefer `if (...) { return ...; }` over wrapping the rest of the function in `else`.

## Module Design

**Exports:**
- One class or trait per PHP file is the standard across `app/`, `database/factories/`, and `tests/`.
- Shared non-class helpers are rare. The main exception is Composer `autoload.files` loading `app/Helpers/OrderStatusHelper.php`.
- Blade components are the primary UI reuse mechanism. Reusable pieces live under `resources/views/components/` and `resources/views/components/ui/`, for example `resources/views/components/ui/page.blade.php` and `resources/views/components/modal.blade.php`.

**Barrel Files:**
- PHP barrel files or re-export modules are not used. Classes are imported directly from their concrete namespaces.
- Route organization is split by audience rather than re-exports. Add new routes to the closest route file and preserve existing middleware/name grouping in `routes/web.php`, `routes/admin.php`, `routes/buyer.php`, or `routes/seller.php`.

**Representative Blade Pattern:**
```blade
@props([
    'title' => null,
    'description' => null,
])

<div {{ $attributes->class('space-y-6') }}>
    @if (is_string($title) && $title !== '')
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div class="space-y-1">
                <h1 class="text-3xl font-semibold tracking-tight">
                    {{ $title }}
                </h1>
            </div>

            @isset($actions)
                <div class="flex items-center gap-2">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    @endif

    {{ $slot }}
</div>
```
- Reuse `@props`, `$attributes->class(...)`, and named slots the same way as `resources/views/components/ui/page.blade.php` and `resources/views/components/modal.blade.php`.

---

*Convention analysis: 2026-04-01*
*Update when patterns change*
