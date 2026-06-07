# Quick Task 260607-dbg - Move Debugbar to Development Dependencies

## Goal

Keep Laravel Debugbar available for explicit local development while making it unavailable in production.

## Steps

1. Audit Composer, config, environment examples, providers, docs, deployment notes, and tests for Debugbar exposure.
2. Move `barryvdh/laravel-debugbar` from production dependencies to development dependencies.
3. Disable Debugbar by default and only register it for `APP_ENV=local` plus `DEBUGBAR_ENABLED=true`.
4. Update `.env.example`, deployment documentation, changelog, and focused safety tests.
5. Verify Composer install/autoload, config/cache clears, local opt-in, production disablement, route availability, and tests.
