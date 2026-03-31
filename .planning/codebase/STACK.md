# Technology Stack

**Analysis Date:** 2026-04-01

## Languages

**Primary:**
- PHP 8.5.4 observed locally via `php artisan about --json`, with `^8.3` declared in `composer.json` - all application logic lives in `app/`, `config/`, `routes/`, `database/`, and `tests/`.
- Blade/PHP templates - server-rendered UI lives in `resources/views/`, with Livewire view templates under `resources/views/livewire/`.

**Secondary:**
- JavaScript (ES modules) - browser bootstrap and asset entrypoints live in `resources/js/app.js` and `resources/js/bootstrap.js`; build config lives in `vite.config.js`.
- CSS with Tailwind utilities - frontend styling is built from `resources/css/app.css`, `tailwind.config.js`, and `postcss.config.js`.

## Runtime

**Environment:**
- PHP application runtime - Laravel boots from `bootstrap/app.php` and `artisan`; the current local environment reports PHP `8.5.4` and Laravel `12.56.0`.
- Node.js asset build runtime - Vite-based frontend builds use the local Node.js runtime; `node -v` reports `22.22.1`.
- Browser runtime is intentionally thin - `resources/js/app.js` relies on Livewire's bundled Alpine instead of starting a separate Alpine instance.

**Package Manager:**
- Composer `2.9.5` - PHP dependencies are declared in `composer.json`.
- npm `10.9.4` - frontend dependencies are declared in `package.json`.
- Lockfiles: `composer.lock` present, `package-lock.json` present.

## Frameworks

**Core:**
- Laravel `12.56.0` - main web application framework, routing, console, config, queues, mail, and Eloquent ORM; bootstrap path is `bootstrap/app.php`.
- Livewire `3.7.13` - reactive server-driven UI for both admin and storefront flows; component classes live in `app/Livewire/`, templates live in `resources/views/livewire/`.
- Blade - SSR templating layer for layout, email, backend, and frontend views in `resources/views/`.
- Laravel Sanctum `4.3.1` - token-capable API auth support; wired in `config/sanctum.php`, `routes/api.php`, and `app/Models/Users/Buyer.php`.
- No Filament package or Filament resources were detected in `composer.json`, `composer.lock`, `app/`, or `resources/`; the admin UI is a custom Livewire + Blade implementation under `app/Livewire/Backend/` and `resources/views/backend/`.

**Testing:**
- PHPUnit `11.5.55` - test runner configured in `phpunit.xml`.
- Livewire testing utilities - component tests use `Livewire\Livewire` in files such as `tests/Feature/Controllers/Backend/Auth/LoginControllerTest.php` and `tests/Feature/Controllers/Frontend/Auth/BuyerAuthControllerTest.php`.

**Build/Dev:**
- Vite `5.4.21` - frontend asset bundler configured in `vite.config.js`.
- `laravel-vite-plugin` `1.3.0` - Laravel/Vite integration configured in `vite.config.js`.
- Tailwind CSS `3.4.19` - utility CSS system configured in `tailwind.config.js`.
- PostCSS - CSS pipeline configured in `postcss.config.js`.
- Laravel Pint `1.29.0` - PHP formatter installed via `composer.lock`.
- Laravel Debugbar `3.16.5` - local debugging and query inspection configured in `config/debugbar.php`.
- Laravel Boost `2.4.1` - local MCP/developer tooling wired through `.mcp.json`, `opencode.json`, and `boost.json`.

## Key Dependencies

**Critical:**
- `laravel/framework` `12.56.0` - application foundation for routing, Eloquent, validation, sessions, cache, queue, mail, and console; see `composer.json` and `bootstrap/app.php`.
- `livewire/livewire` `3.7.13` - primary UI interaction model for the storefront and admin experiences; see `app/Livewire/` and `routes/admin.php`, `routes/buyer.php`, `routes/seller.php`.
- `wireui/wireui` `2.6.0` plus `wireui/heroicons` `2.10.0` and `wireui/phosphoricons` `2.4.0` - component and icon layer used by the Livewire UI; see `config/wireui.php`, `config/wireui/heroicons.php`, `config/wireui/phosphoricons.php`, `app/Livewire/Concerns/InteractsWithWireUi.php`, and `tailwind.config.js`.
- `lukepolo/laracart` `2.7.0` - shopping cart state for buyer flows; see `config/laracart.php`, `app/Livewire/Frontend/Buyer/Cart/Index.php`, and `app/Livewire/Frontend/Buyer/Products/Show.php`.
- `intervention/image` `2.7.2` - image resizing and WebP encoding for product uploads and seeded test assets; see `config/image.php`, `app/Livewire/Frontend/Seller/Products/Create.php`, `app/Livewire/Frontend/Seller/Products/Edit.php`, `app/Livewire/Backend/Products/Edit.php`, and `database/seeders/test_information/ProductSeeder.php`.
- `laravel/sanctum` `4.3.1` - token support for authenticated API access; see `config/sanctum.php`, `routes/api.php`, and `app/Models/Users/Buyer.php`.

**Infrastructure:**
- `barryvdh/laravel-debugbar` `3.16.5` - developer-time request and query inspection; see `config/debugbar.php`.
- `spatie/laravel-ignition` `2.12.0` and Flare support - exception page and optional Flare reporting; see `config/ignition.php` and `config/flare.php`.
- `guzzlehttp/guzzle` `7.10.0` - installed as an HTTP client dependency in `composer.lock`, but no direct outbound HTTP client usage was detected in `app/` or `routes/`.

## Configuration

**Environment:**
- Environment configuration is file-based: `.env` is present, `.env.example` is present, and runtime config is read through `config/*.php`.
- Core application config lives in `config/app.php`, `config/auth.php`, `config/database.php`, `config/cache.php`, `config/session.php`, `config/mail.php`, `config/filesystems.php`, `config/logging.php`, `config/services.php`, `config/broadcasting.php`, and `config/sanctum.php`.
- Local MCP tooling is configured in `.mcp.json` and `opencode.json`; `boost.json` enables Laravel-specific agent guidance and MCP support.
- Test runtime overrides live in `phpunit.xml`, which switches tests to in-memory SQLite, array mail, sync queue, and array/session-safe test drivers.

**Build:**
- Frontend build config lives in `vite.config.js`.
- Tailwind theme, WireUI preset wiring, DaisyUI themes, and plugin registration live in `tailwind.config.js`.
- PostCSS plugin ordering lives in `postcss.config.js`.
- Frontend entrypoints are `resources/css/app.css` and `resources/js/app.js`.

## Platform Requirements

**Development:**
- PHP `8.3+` is required by `composer.json`; the checked local environment is `8.5.4`.
- Composer is required to install PHP packages from `composer.json` / `composer.lock`.
- Node.js and npm are required to build frontend assets from `package.json` / `package-lock.json`.
- SQLite works out of the box through `config/database.php`, which points to `database/birza.sqlite` when `DB_CONNECTION=sqlite`.
- The image pipeline expects the GD extension because `config/image.php` sets the Intervention driver to `gd`.
- Writable `storage/` and the `public/storage` symlink are required; `php artisan about --json` reports `/Users/andrejprus/Herd/birza/public/storage` as linked.

**Production:**
- No deployment manifest was detected: no `Dockerfile`, `docker-compose*`, `render.yaml`, `vercel.json`, `netlify.toml`, or `.github/workflows/*` files are present in the repo root.
- The app expects a standard PHP/Laravel host with writable `storage/`, configured database credentials, SMTP or provider-based mail credentials, and whichever cache/session/queue/storage drivers are selected through `config/*.php`.
- `app/Console/Commands/SystemCommand.php` references `https://birza.prus.dev/{secret}` for maintenance-mode bypass access; this indicates an externally hosted environment exists, but the hosting platform is not defined in-repo.

---

*Stack analysis: 2026-04-01*
