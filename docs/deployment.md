# Deployment Notes

## Debugbar

Laravel Debugbar is a development-only package. It must not be installed or enabled in production because it can expose request details, queries, session data, routes, and application internals.

Production deployments should install Composer dependencies without development packages:

```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache
```

Production environment values must keep debugging disabled:

```env
APP_ENV=production
APP_DEBUG=false
DEBUGBAR_ENABLED=false
```

Local development may enable Debugbar explicitly:

```env
APP_ENV=local
APP_DEBUG=true
DEBUGBAR_ENABLED=true
```

The published Debugbar config also checks `APP_ENV`, so `DEBUGBAR_ENABLED=true` is only honored when the application environment is `local`.

Laravel package auto-discovery is disabled for Debugbar in `composer.json`. The Debugbar service provider is registered from `config/app.php` only when both `APP_ENV=local` and `DEBUGBAR_ENABLED=true`, which keeps the `_debugbar` routes unavailable in production mode even if development packages are present by mistake.
