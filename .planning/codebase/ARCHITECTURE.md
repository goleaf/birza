# Architecture

**Analysis Date:** 2026-04-01

## Pattern Overview

**Overall:** Full-stack Laravel monolith with route-bound Livewire pages, Blade layouts, and Eloquent domain models.

**Key Characteristics:**
- `bootstrap/app.php` is the primary application bootstrap and composes multiple route files instead of relying on a single `web.php`.
- Most authenticated UI routes mount Livewire page components directly from `app/Livewire/Backend/*` and `app/Livewire/Frontend/*`; classic controllers are used only for thin entry and utility endpoints in `app/Http/Controllers/*`.
- Domain behavior is centered in Eloquent models under `app/Models/*`, with role-specific auth models in `app/Models/Users/*`.
- Cross-request state is session-driven: locale is stored by `app/Http/Middleware/SetLocale.php`, auth uses multiple session guards from `config/auth.php`, and cart state is managed through LaraCart in `app/Livewire/Frontend/Buyer/Cart/Index.php`.
- No `app/Filament/` tree is present; the admin surface is implemented with Livewire and Blade, not Filament resources.

## Layers

**Bootstrap and Configuration Layer:**
- Purpose: Build the Laravel application, register middleware aliases, and attach the route files that define each surface area.
- Location: `bootstrap/app.php`, `config/app.php`, `config/auth.php`, `vite.config.js`, `composer.json`, `package.json`
- Contains: application bootstrap, provider registration, auth guards/providers, Vite entry configuration, package manifests
- Depends on: Laravel framework configuration APIs and service providers in `app/Providers/*`
- Used by: every HTTP request, Artisan command, and asset build

**Routing and HTTP Boundary Layer:**
- Purpose: Define the URL map, guest/auth middleware boundaries, and the small set of conventional controllers.
- Location: `routes/web.php`, `routes/admin.php`, `routes/buyer.php`, `routes/seller.php`, `routes/api.php`, `app/Http/Controllers/*`, `app/Http/Middleware/*`, `app/Http/Requests/*`
- Contains: route groups, thin invokable controllers, auth redirects, locale switching, request validation, middleware
- Depends on: Livewire components, actions, Eloquent models, and Laravel routing/auth middleware
- Used by: browser navigation, API requests, and redirect flows

**Interactive UI Layer:**
- Purpose: Handle page-level state, user actions, and server-rendered HTML for admin, buyer, seller, and auth screens.
- Location: `app/Livewire/Backend/*`, `app/Livewire/Frontend/*`, `app/Livewire/Concerns/InteractsWithWireUi.php`, `resources/views/backend/*`, `resources/views/frontend/*`, `resources/views/livewire/*`, `resources/views/layouts/*`, `resources/views/components/*`
- Contains: route-mounted Livewire components, notification helpers, feature views, layouts, partials, reusable Blade components
- Depends on: Eloquent models, Laravel auth/session APIs, WireUI, translations, and sometimes direct database transactions
- Used by: `routes/admin.php`, `routes/buyer.php`, and `routes/seller.php`

**Application Service Layer:**
- Purpose: Hold reusable request-adjacent behavior that is shared across controllers, redirects, and application bootstrapping.
- Location: `app/Actions/Auth/*`, `app/Actions/Frontend/*`, `app/Providers/*`, `app/Notifications/*`, `app/Helpers/OrderStatusHelper.php`
- Contains: logout logic, home redirect resolution, welcome-page view-data assembly, view composers, custom auth providers, mail notifications, helper formatting
- Depends on: Laravel container/facades, models, cache, and view system
- Used by: controllers, middleware redirects, Blade views, and framework boot

**Domain and Persistence Layer:**
- Purpose: Represent marketplace entities, relationships, scopes, and persistence rules.
- Location: `app/Models/*`, `app/Models/Users/*`, `app/Models/Concerns/HasJsonTranslations.php`, `database/migrations/*`, `database/factories/*`, `database/seeders/*`
- Contains: buyers, sellers, admins, products, categories, orders, carts, settings, translation trait, schema history, factories, seed data
- Depends on: Eloquent ORM, database tables created by `database/migrations/*`, and auth/session context for some scopes
- Used by: Livewire components, controllers, actions, seeders, and tests

**Console and Operations Layer:**
- Purpose: Provide operational commands and legacy console bootstrapping for local maintenance tasks.
- Location: `artisan`, `app/Console/Kernel.php`, `app/Console/Commands/RefreshCommand.php`, `app/Console/Commands/SystemCommand.php`, `routes/console.php`
- Contains: Artisan entry point, command discovery, refresh/setup workflow, maintenance-mode helper
- Depends on: Laravel console kernel, storage/filesystem APIs, cache/config commands, Symfony process execution
- Used by: developers and deployment/maintenance flows

## Data Flow

**Public Landing Request:**

1. The request enters through `public/index.php` and the application built by `bootstrap/app.php`.
2. `bootstrap/app.php` loads `routes/web.php` and appends `app/Http/Middleware/SetLocale.php` to the web middleware stack.
3. `routes/web.php` maps `/` to `app/Http/Controllers/Frontend/HomeController.php`.
4. `HomeController` delegates redirect decisions to `app/Actions/Auth/ResolveHomeRedirectAction.php`.
5. If the visitor is a guest, `HomeController` asks `app/Actions/Frontend/BuildWelcomePageDataAction.php` for localized landing-page data.
6. Blade renders `resources/views/frontend/welcome.blade.php` inside `resources/views/layouts/frontend/app.blade.php`.

**Role-Specific Page Request:**

1. `bootstrap/app.php` groups `routes/admin.php`, `routes/buyer.php`, or `routes/seller.php` into the web middleware stack.
2. Route middleware such as `guest:*` or `auth:*` use the custom guards defined in `config/auth.php`.
3. A matching Livewire page class from `app/Livewire/Backend/*` or `app/Livewire/Frontend/*` is mounted directly by the route.
4. The component performs `mount()` loading, inline validation, and any request-driven query building.
5. `render()` returns a feature Blade view such as `resources/views/backend/products/index.blade.php` or `resources/views/frontend/buyer/products/index.blade.php`.
6. The selected layout attribute, for example `#[Layout('layouts.backend.app')]`, wraps the page in the corresponding layout from `resources/views/layouts/*`.

**Buyer Checkout Flow:**

1. `routes/buyer.php` mounts `app/Livewire/Frontend/Buyer/Cart/Index.php` for `buyer.cart.index`.
2. The component reads session-backed LaraCart items and synchronizes quantities during `mount()` and `render()`.
3. `checkout()` validates buyer auth, calculates totals, and opens a database transaction inside the Livewire component.
4. The component creates the order through `app/Models/Order.php`, creates `app/Models/OrderItem.php` rows, and decrements `app/Models/Product.php` stock.
5. On success, the cart is destroyed and the component redirects to `buyer.orders.index`; on failure, it catches the throwable and flashes an error message.

**API Search Request:**

1. `routes/api.php` registers `/api/products/search` under the API middleware group.
2. `app/Http/Controllers/Api/ProductSearchController.php` reads the query string and locale.
3. The controller queries `app/Models/Category.php` and `app/Models/Product.php`, shapes a lightweight array payload, and returns JSON directly.

**Console Command Execution:**

1. `artisan` boots the application and resolves the console kernel in `app/Console/Kernel.php`.
2. The kernel auto-loads commands from `app/Console/Commands/*` and includes `routes/console.php`.
3. Commands such as `refresh` or `system` execute operational workflows using filesystem, cache, migration, and maintenance APIs.

**State Management:**
- Persistent business state lives in the database tables modeled by `app/Models/*`.
- Request/session state lives in Laravel sessions: auth guards from `config/auth.php`, locale from `app/Http/Middleware/SetLocale.php`, and cart contents via LaraCart in `app/Livewire/Frontend/Buyer/Cart/Index.php`.
- Shared read-mostly view state is injected by service providers, notably `app/Providers/UserGuardServiceProvider.php` and `app/Providers/GlobalSettingsServiceProvider.php`.
- Frontend interactivity is server-driven through Livewire; `resources/js/app.js` is intentionally minimal and defers Alpine bootstrapping to Livewire/WireUI.

## Key Abstractions

**Route-Bound Livewire Page:**
- Purpose: Represent a full page that owns its query logic, validation, actions, and rendered view.
- Examples: `app/Livewire/Backend/Products/Index.php`, `app/Livewire/Backend/Orders/Show.php`, `app/Livewire/Frontend/Buyer/Products/Show.php`, `app/Livewire/Frontend/Auth/Login.php`
- Pattern: Livewire page component mounted directly from `routes/*.php`, usually with a `#[Layout(...)]` attribute and a feature-specific Blade view

**Action Class:**
- Purpose: Keep small pieces of controller-adjacent behavior out of controllers.
- Examples: `app/Actions/Auth/LogoutGuardAction.php`, `app/Actions/Auth/ResolveHomeRedirectAction.php`, `app/Actions/Frontend/BuildWelcomePageDataAction.php`
- Pattern: Single-purpose service object with a `handle()` method, resolved through the container

**Guard-Specific User Model:**
- Purpose: Separate authentication concerns and persistence tables by role.
- Examples: `app/Models/Users/Admin.php`, `app/Models/Users/Buyer.php`, `app/Models/Users/Seller.php`
- Pattern: Multiple `Authenticatable` models backed by separate tables and mapped to guards/providers in `config/auth.php`

**Translation-Aware Model Trait:**
- Purpose: Treat JSON columns as locale-aware attributes with fallback behavior.
- Examples: `app/Models/Concerns/HasJsonTranslations.php`, consumed by `app/Models/Category.php`, `app/Models/Country.php`, `app/Models/Product.php`
- Pattern: Model concern overriding attribute accessors/mutators for a declared `$translatable` list

**View-Shared Context Provider:**
- Purpose: Push global UI context into every rendered view without repeating controller logic.
- Examples: `app/Providers/UserGuardServiceProvider.php`, `app/Providers/GlobalSettingsServiceProvider.php`
- Pattern: Service provider boot logic using `View::composer('*', ...)` or `View::share(...)`

## Entry Points

**HTTP Application Bootstrap:**
- Location: `bootstrap/app.php`
- Triggers: every web/API request and framework bootstrap
- Responsibilities: register route files, add middleware, define aliases, create the application instance

**Public Web Surface:**
- Location: `routes/web.php`
- Triggers: guest requests to `/` and locale switching requests
- Responsibilities: send visitors to the landing page or language switch controller

**Role-Specific UI Surfaces:**
- Location: `routes/admin.php`, `routes/buyer.php`, `routes/seller.php`
- Triggers: admin, buyer, and seller browser navigation
- Responsibilities: enforce per-guard middleware and mount Livewire pages for each area

**API Surface:**
- Location: `routes/api.php`
- Triggers: HTTP requests under `/api/*`
- Responsibilities: expose JSON endpoints and the Sanctum-authenticated `/api/user` route

**Console Surface:**
- Location: `artisan`, `app/Console/Kernel.php`, `app/Console/Commands/*`
- Triggers: Artisan command execution
- Responsibilities: register commands and run operational workflows

**Asset Surface:**
- Location: `resources/js/app.js`, `resources/css/app.css`, `vite.config.js`
- Triggers: Vite dev/build and page layout asset inclusion from `resources/views/layouts/*.blade.php`
- Responsibilities: load JS/CSS bundles, bootstrap axios, and expose Tailwind-generated styles

## Error Handling

**Strategy:** Default Laravel exception handling with redirect-oriented auth middleware, targeted validation exceptions in Livewire, and selective local try/catch blocks around transactional workflows.

**Patterns:**
- `app/Exceptions/Handler.php` stays close to the framework default and delegates reporting/rendering to Laravel.
- `app/Http/Middleware/Authenticate.php` redirects unauthenticated browser traffic to `route('home')`.
- `app/Http/Requests/Frontend/SwitchLocaleRequest.php` handles request validation at the controller boundary.
- Livewire components such as `app/Livewire/Frontend/Auth/Login.php` and `app/Livewire/Backend/Auth/Login.php` use `$this->validate()` and throw `ValidationException` for form errors.
- Route-mounted components abort explicitly for missing or inactive resources, for example `app/Livewire/Frontend/Buyer/Products/Show.php`.
- Transaction-sensitive flows catch throwables locally, for example `app/Livewire/Frontend/Buyer/Cart/Index.php`.

## Cross-Cutting Concerns

**Logging:**
- No project-specific logging abstraction is defined in `app/*`; the codebase relies on Laravel logging configured in `config/logging.php`.
- Local debugging support is enabled through `barryvdh/laravel-debugbar` in `composer.json` and `config/debugbar.php`.

**Validation:**
- Conventional HTTP validation lives in Form Requests such as `app/Http/Requests/Frontend/SwitchLocaleRequest.php`.
- Most interactive page validation happens inline inside Livewire components, for example `app/Livewire/Frontend/Auth/Register.php` and `app/Livewire/Frontend/Buyer/Cart/Index.php`.

**Authentication:**
- Multi-guard session auth is defined in `config/auth.php` for `admin`, `buyer`, and `seller`.
- `app/Providers/AuthServiceProvider.php` registers custom providers for buyer and seller auth resolution.
- `app/Providers/UserGuardServiceProvider.php` shares the active guard and authenticated user with all views.

**Localization:**
- `app/Http/Middleware/SetLocale.php` derives the current locale from the session and applies it on every web request.
- `app/Models/Concerns/HasJsonTranslations.php` makes JSON-backed model attributes locale-aware.
- `app/Providers/AppServiceProvider.php` points Laravel translations at `lang/` and remaps several vendor-style view namespaces.

**Caching:**
- `app/Providers/GlobalSettingsServiceProvider.php` caches the `portal_additional_price` setting and shares it with the entire view layer.

---

*Architecture analysis: 2026-04-01*
