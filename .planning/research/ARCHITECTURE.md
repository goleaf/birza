# Research: Architecture

**Date:** 2026-04-01
**Scope:** How the Livewire 4 + maryUI migration should fit the current Birza monolith.

## Existing Architecture Fit

- Keep the Laravel monolith, split route files, and route-mounted Livewire page model.
- Keep Blade layouts as the top-level shell for frontend and backend.
- Keep business logic in models, actions, controllers, and Livewire classes. This initiative is about the UI/platform layer, not domain redesign.

## Recommended Integration Shape

### 1. Platform Layer

- Upgrade Composer and NPM dependencies first.
- Update Livewire 4 config and layout resolution.
- Update Vite/CSS bootstrap for Tailwind 4 and Mary vendor sources.

### 2. Shared UI Layer

- Convert `resources/views/layouts/frontend/app.blade.php` and `resources/views/layouts/backend/app.blade.php` into Mary-compatible shells.
- Refactor reusable wrappers in `resources/views/components/ui/*` so the rest of the app can adopt Mary without every screen changing all at once.
- Replace `@wireUiStyles`, `@wireUiScripts`, `<x-notifications>`, and `<x-dialog>` with Mary-safe equivalents or an explicitly temporary bridge.

### 3. Surface Migration Layer

- Migrate auth flows first because they are smaller and heavily reused.
- Migrate buyer and seller pages next because they validate the real commerce journeys.
- Migrate backend/admin pages after the shared wrapper layer is stable.

### 4. Cleanup Layer

- Remove old WireUI integration helpers such as `app/Livewire/Concerns/InteractsWithWireUi.php` once no longer needed.
- Remove obsolete package config and assets after all primary pages are on Mary.

## Critical Integration Points

- `composer.json`
- `package.json`
- `resources/css/app.css`
- `resources/js/app.js`
- `app/Providers/AppServiceProvider.php`
- `resources/views/layouts/frontend/app.blade.php`
- `resources/views/layouts/backend/app.blade.php`
- `resources/views/components/ui/*`
- `app/Livewire/Concerns/InteractsWithWireUi.php`

## Build Order

1. Platform upgrade and Mary install
2. Shared layout/theme/wrapper migration
3. Buyer, seller, and auth screens
4. Backend/admin screens
5. Cleanup, dependency removal, and hardening

## Architectural Rules

- Do not rewrite the app into a different frontend architecture.
- Do not introduce raw JS widget systems when Livewire + Blade + Mary can cover the interaction.
- Keep custom wrappers only if they reduce vendor coupling or encode project-specific behavior.
- Prefer Mary as the visible design system, not a hidden dependency under a still-WireUI app shell.

## Sources

- `.planning/codebase/ARCHITECTURE.md`
- `.planning/codebase/STRUCTURE.md`
- Livewire 4 upgrade guide: https://livewire.laravel.com/docs/4.x/upgrading
- maryUI docs: https://mary-ui.com/docs/installation
