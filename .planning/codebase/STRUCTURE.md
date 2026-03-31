# Codebase Structure

**Analysis Date:** 2026-04-01

## Directory Layout

```text
birza/
├── app/                    # Application code: Livewire pages, controllers, models, providers, console commands
│   ├── Actions/            # Thin reusable action classes for auth and frontend data assembly
│   ├── Console/            # Artisan kernel and custom console commands
│   ├── Http/               # Controllers, middleware, and form requests
│   ├── Livewire/           # Backend, frontend, and shared Livewire page components
│   ├── Models/             # Eloquent models, auth models, and model concerns
│   ├── Notifications/      # Mail notifications
│   └── Providers/          # Application service providers and view composers
├── bootstrap/              # Laravel bootstrap file and runtime cache directory
├── config/                 # Framework and package configuration
├── database/               # Migrations, factories, and seeders
├── lang/                   # Translation files
├── public/                 # Web root and built assets
├── resources/              # Blade views, Vite JS, and Tailwind CSS
├── routes/                 # Split route definitions by application surface
├── storage/                # Runtime files, logs, cache, sessions, and public storage target
├── tests/                  # Feature and unit tests
├── .planning/codebase/     # Generated GSD codebase mapping documents
├── composer.json           # PHP dependencies and composer scripts
├── package.json            # Frontend toolchain dependencies
├── artisan                 # Laravel CLI entry point
└── vite.config.js          # Vite asset build configuration
```

## Directory Purposes

**`app/`:**
- Purpose: Hold the PHP application code that defines request handling, UI behavior, auth, and domain logic.
- Contains: `Actions`, `Console`, `Exceptions`, `Helpers`, `Http`, `Livewire`, `Models`, `Notifications`, `Providers`
- Key files: `app/Http/Controllers/Frontend/HomeController.php`, `app/Livewire/Frontend/Buyer/Cart/Index.php`, `app/Models/Product.php`, `app/Providers/AppServiceProvider.php`
- Subdirectories: role-oriented UI code in `app/Livewire/Backend/*` and `app/Livewire/Frontend/*`; auth models in `app/Models/Users/*`

**`app/Actions/`:**
- Purpose: Keep thin reusable operations out of controllers.
- Contains: small single-purpose classes with `handle()` methods
- Key files: `app/Actions/Auth/LogoutGuardAction.php`, `app/Actions/Auth/ResolveHomeRedirectAction.php`, `app/Actions/Frontend/BuildWelcomePageDataAction.php`
- Subdirectories: `app/Actions/Auth/` for auth flow helpers, `app/Actions/Frontend/` for public-page data composition

**`app/Http/`:**
- Purpose: Define the HTTP boundary for the small number of traditional controller endpoints.
- Contains: invokable controllers, middleware, form requests
- Key files: `app/Http/Controllers/Api/ProductSearchController.php`, `app/Http/Controllers/Auth/LogoutController.php`, `app/Http/Controllers/Frontend/LocaleSwitchController.php`, `app/Http/Middleware/SetLocale.php`
- Subdirectories: `app/Http/Controllers/Api/`, `app/Http/Controllers/Auth/`, `app/Http/Controllers/Backend/`, `app/Http/Controllers/Frontend/`, `app/Http/Requests/Frontend/`

**`app/Livewire/`:**
- Purpose: Hold the dominant UI implementation layer for admin, buyer, seller, and auth pages.
- Contains: route-mounted Livewire components plus shared component concerns
- Key files: `app/Livewire/Backend/Products/Index.php`, `app/Livewire/Backend/Auth/Login.php`, `app/Livewire/Frontend/Auth/Login.php`, `app/Livewire/Frontend/Buyer/Products/Show.php`, `app/Livewire/Frontend/Seller/Products/Edit.php`
- Subdirectories: `app/Livewire/Backend/` for admin pages, `app/Livewire/Frontend/` for buyer/seller/public auth pages, `app/Livewire/Concerns/` for shared WireUI helpers

**`app/Models/`:**
- Purpose: Centralize Eloquent models and persistence behavior.
- Contains: marketplace entities, auth entities, model traits
- Key files: `app/Models/Product.php`, `app/Models/Category.php`, `app/Models/Order.php`, `app/Models/GlobalSettings.php`, `app/Models/Users/Buyer.php`, `app/Models/Users/Seller.php`, `app/Models/Concerns/HasJsonTranslations.php`
- Subdirectories: `app/Models/Users/` for `Authenticatable` models, `app/Models/Concerns/` for reusable model traits

**`app/Providers/`:**
- Purpose: Register application boot logic, view composers, auth providers, and routing compatibility code.
- Contains: service providers only
- Key files: `app/Providers/AppServiceProvider.php`, `app/Providers/AuthServiceProvider.php`, `app/Providers/GlobalSettingsServiceProvider.php`, `app/Providers/UserGuardServiceProvider.php`, `app/Providers/RouteServiceProvider.php`
- Subdirectories: flat structure; no nested provider folders

**`resources/views/`:**
- Purpose: Hold all Blade templates used by traditional controllers and Livewire components.
- Contains: backend and frontend feature views, layouts, reusable components, mail templates, error views, pagination views
- Key files: `resources/views/frontend/welcome.blade.php`, `resources/views/backend/products/index.blade.php`, `resources/views/frontend/buyer/products/show.blade.php`, `resources/views/livewire/frontend/auth/login.blade.php`, `resources/views/layouts/frontend/app.blade.php`, `resources/views/layouts/backend/app.blade.php`
- Subdirectories: `resources/views/backend/` and `resources/views/frontend/` for most feature pages, `resources/views/livewire/` mostly for auth-only component views, `resources/views/components/` for reusable Blade components

**`resources/js/` and `resources/css/`:**
- Purpose: Define the Vite asset entrypoints consumed by the Blade layouts.
- Contains: small JS bootstrap files and Tailwind entry CSS
- Key files: `resources/js/app.js`, `resources/js/bootstrap.js`, `resources/css/app.css`
- Subdirectories: flat structure; no feature-specific asset folders

**`routes/`:**
- Purpose: Split route definitions by surface area instead of concentrating them in a single file.
- Contains: web, admin, buyer, seller, API, broadcast, and console route files
- Key files: `routes/web.php`, `routes/admin.php`, `routes/buyer.php`, `routes/seller.php`, `routes/api.php`, `routes/console.php`, `routes/channels.php`
- Subdirectories: flat structure; route separation is file-based

**`database/`:**
- Purpose: Define schema history and supply repeatable test/dev data.
- Contains: migrations, model factories, seeders, JSON seed assets
- Key files: `database/migrations/2024_03_20_000008_create_products_table.php`, `database/migrations/2026_03_27_000000_optimize_core_indexes.php`, `database/factories/ProductFactory.php`, `database/seeders/DatabaseSeeder.php`
- Subdirectories: `database/factories/`, `database/migrations/`, `database/seeders/`, `database/seeders/test_information/`

**`tests/`:**
- Purpose: Hold PHPUnit feature and unit coverage for routes, models, middleware, commands, providers, and seeders.
- Contains: base test cases, feature tests, unit tests
- Key files: `tests/TestCase.php`, `tests/Feature/Controllers/HomeControllerTest.php`, `tests/Feature/Controllers/Api/ProductSearchControllerTest.php`, `tests/Unit/Models/ProductTest.php`, `tests/Unit/Providers/AppServiceProviderTest.php`
- Subdirectories: `tests/Feature/Controllers/` for route-level behavior, `tests/Feature/Seeders/` for seeders, `tests/Unit/*` for lower-level code areas

**`.planning/codebase/`:**
- Purpose: Store generated codebase reference documents for the local GSD workflow.
- Contains: markdown analysis files such as `ARCHITECTURE.md` and `STRUCTURE.md`
- Key files: `.planning/codebase/ARCHITECTURE.md`, `.planning/codebase/STRUCTURE.md`
- Subdirectories: flat structure today

## Key File Locations

**Entry Points:**
- `bootstrap/app.php`: Main Laravel bootstrap; wires route files and middleware aliases.
- `artisan`: CLI entry point for all Artisan commands.
- `routes/web.php`: Public landing and locale-switching entry routes.
- `routes/admin.php`: Admin/authenticated backend route entrypoint.
- `routes/buyer.php`: Buyer auth and marketplace route entrypoint.
- `routes/seller.php`: Seller auth and seller workspace route entrypoint.
- `routes/api.php`: API route entrypoint.

**Configuration:**
- `composer.json`: PHP package manifest and composer scripts.
- `package.json`: Vite/Tailwind frontend dependency manifest.
- `config/app.php`: Application providers, locale defaults, VAT config, app metadata.
- `config/auth.php`: Multi-guard authentication configuration for `admin`, `buyer`, and `seller`.
- `config/services.php`: Third-party service configuration hooks.
- `vite.config.js`: Vite build inputs and Blade refresh settings.
- `tailwind.config.js`: Tailwind configuration.
- `.env.example`: Environment variable template.
- `.env`: Local environment file present; contains environment configuration and was not inspected.

**Core Logic:**
- `app/Livewire/Backend/`: Admin pages and CRUD screens.
- `app/Livewire/Frontend/`: Buyer, seller, and shared auth/customer pages.
- `app/Http/Controllers/`: Thin controller endpoints for home, logout, locale, and API search.
- `app/Actions/`: Small reusable operations invoked from controllers.
- `app/Models/`: Eloquent models and domain relationships.
- `app/Providers/`: Bootstrapping, view sharing, auth provider registration.

**Testing:**
- `tests/Feature/Controllers/`: Browser/request tests grouped by route surface.
- `tests/Feature/Seeders/`: Seeder integration tests.
- `tests/Unit/Models/`: Model relationship and scope tests.
- `tests/Unit/Providers/`: Provider boot logic tests.
- `database/factories/`: Factory definitions used by tests.

**Documentation:**
- `AGENTS.md`: Repository-specific working rules for coding agents.
- `.codex/get-shit-done/templates/codebase/architecture.md`: Source template for architecture docs.
- `.codex/get-shit-done/templates/codebase/structure.md`: Source template for structure docs.
- `.planning/codebase/ARCHITECTURE.md`: Current architecture map.
- `.planning/codebase/STRUCTURE.md`: Current structure map.

## Naming Conventions

**Files:**
- `PascalCase.php` for PHP classes under `app/`, for example `app/Livewire/Frontend/Auth/Register.php` and `app/Models/Users/Seller.php`.
- `*Controller.php` for conventional controllers, for example `app/Http/Controllers/Api/ProductSearchController.php`.
- `*Request.php` for form requests, for example `app/Http/Requests/Frontend/SwitchLocaleRequest.php`.
- `index.blade.php`, `form.blade.php`, and `show.blade.php` for feature views, for example `resources/views/backend/products/index.blade.php` and `resources/views/frontend/seller/products/form.blade.php`.
- Lowercase route files with area names, for example `routes/admin.php`, `routes/buyer.php`, and `routes/seller.php`.
- `*Test.php` for PHPUnit tests, for example `tests/Feature/Controllers/HomeControllerTest.php`.

**Directories:**
- `PascalCase` PSR-4 directories inside `app/`, such as `app/Livewire/Backend/Products/` and `app/Models/Users/`.
- Lowercase, URL-like feature folders inside `resources/views/`, such as `resources/views/frontend/buyer/products/` and `resources/views/backend/orders/`.
- Flat lowercase routing/config directories such as `routes/` and `config/`.

**Special Patterns:**
- Livewire page classes usually mirror their rendered feature views by area and feature:
  - `app/Livewire/Backend/Products/Index.php` -> `resources/views/backend/products/index.blade.php`
  - `app/Livewire/Frontend/Buyer/Products/Show.php` -> `resources/views/frontend/buyer/products/show.blade.php`
- Auth Livewire components are the main exception and render from `resources/views/livewire/*`:
  - `app/Livewire/Backend/Auth/Login.php` -> `resources/views/livewire/backend/auth/login.blade.php`
  - `app/Livewire/Frontend/Auth/Login.php` -> `resources/views/livewire/frontend/auth/login.blade.php`
- Role-specific auth models live under `app/Models/Users/`, not alongside general models only.

## Where to Add New Code

**New Backend Admin Page:**
- Primary code: add the route to `routes/admin.php`.
- Livewire implementation: add the page component under `app/Livewire/Backend/{Feature}/`.
- Blade view: add the matching feature view under `resources/views/backend/{feature}/`.
- Tests: add request/route coverage under `tests/Feature/Controllers/Backend/`.

**New Buyer or Seller Page:**
- Buyer routes: `routes/buyer.php`.
- Seller routes: `routes/seller.php`.
- Buyer Livewire classes: `app/Livewire/Frontend/Buyer/{Feature}/`.
- Seller Livewire classes: `app/Livewire/Frontend/Seller/{Feature}/`.
- Blade views: `resources/views/frontend/buyer/{feature}/` or `resources/views/frontend/seller/{feature}/`.
- Tests: keep route-level coverage under `tests/Feature/Controllers/Frontend/Buyer/` or `tests/Feature/Controllers/Frontend/Seller/`.

**New Public Page or Utility Endpoint:**
- Public route definition: `routes/web.php`.
- API route definition: `routes/api.php`.
- Thin controller: `app/Http/Controllers/Frontend/` or `app/Http/Controllers/Api/`.
- Request validation: `app/Http/Requests/{Area}/`.
- If the endpoint becomes interactive UI, prefer a Livewire component in `app/Livewire/Frontend/` instead of expanding controller logic.

**New Shared Auth Screen:**
- Route definition: `routes/buyer.php` or `routes/seller.php` depending on the guard surface.
- Livewire implementation: `app/Livewire/Frontend/Auth/`.
- Blade view: `resources/views/livewire/frontend/auth/`.
- Shared UI pieces: `resources/views/components/ui/` or `resources/views/components/`.

**New Model or Persistence Rule:**
- Eloquent model: `app/Models/`.
- Auth-backed model: `app/Models/Users/`.
- Model concern/trait: `app/Models/Concerns/`.
- Factory: `database/factories/`.
- Migration: `database/migrations/`.
- Seeder: `database/seeders/` or `database/seeders/test_information/`.
- Unit tests: `tests/Unit/Models/`.

**Utilities and Shared Behavior:**
- Reusable request-adjacent logic: `app/Actions/`.
- Global boot/view composition: `app/Providers/`.
- Livewire-specific helper trait: `app/Livewire/Concerns/`.
- Global helper function file: `app/Helpers/` only if the behavior truly needs function-style access.

## Special Directories

**`public/build/`:**
- Purpose: Vite build output consumed by `@vite(...)` in `resources/views/layouts/frontend/app.blade.php` and `resources/views/layouts/backend/app.blade.php`.
- Source: generated by `npm run build`.
- Committed: No tracked files detected in `public/build/`.

**`storage/`:**
- Purpose: Runtime logs, cache, sessions, testing artifacts, Debugbar output, and the public storage target.
- Source: framework runtime plus setup performed by `app/Console/Commands/RefreshCommand.php`.
- Committed: Partially; the repository tracks placeholder `.gitignore` files and at least one cached framework file under `storage/framework/cache/`.

**`bootstrap/cache/`:**
- Purpose: Framework bootstrap cache directory.
- Source: generated by Laravel optimize/cache commands.
- Committed: Placeholder `.gitignore` only.

**`.planning/codebase/`:**
- Purpose: Generated architecture/stack/quality/concern documents used by local GSD commands.
- Source: generated from codebase mapping workflows.
- Committed: Not yet tracked before document generation in this workspace.

---

*Structure analysis: 2026-04-01*
