# Research: Stack

**Date:** 2026-04-01
**Scope:** Brownfield upgrade of Birza from Livewire 3 + WireUI + Tailwind 3 to Livewire 4 + maryUI.

## Current Baseline

- Laravel 12.56.x monolith with route-bound Livewire pages and Blade layouts.
- Livewire 3.7.x drives admin, buyer, seller, and auth screens.
- WireUI is the current component and feedback layer.
- Tailwind 3.4 + daisyUI 4 power the CSS build.
- Existing UI wrappers in `resources/views/components/ui/*` hide some component choices, but there are still direct WireUI patterns in layouts and views.

## Recommended Target Stack

### Core Platform

- Keep Laravel 12 and PHP 8.3+.
- Upgrade to `livewire/livewire:^4.0`.
- Install `robsontenorio/mary:^2.0`.
- Move frontend build to Tailwind 4 + daisyUI 5 + `@tailwindcss/vite`.

### UI System

- Make maryUI the primary component library for Blade and Livewire views.
- Use a non-colliding Mary component strategy during migration because the repo already has local wrappers like `resources/views/components/ui/button.blade.php` that resolve to `<x-button>`.
- Keep the app server-rendered. Do not introduce React, Vue, or Inertia.

### Dependency Direction

- Phase in Mary before removing WireUI.
- Remove `wireui/wireui` only after all required dialogs, notifications, buttons, inputs, and tables have Mary-based replacements or documented local wrappers.
- Preserve the current "Livewire owns Alpine" approach in `resources/js/app.js`.

## Why This Stack

- Livewire 4 is the direct continuation of the app's existing interaction model, so the upgrade keeps architecture stable while modernizing behavior.
- maryUI is designed for Laravel + Livewire + Blade and aligns with the user's goal of maximum integration without leaving SSR.
- Tailwind 4 + daisyUI 5 are the styling baseline Mary v2 is built around, so staying on Tailwind 3 would create unnecessary friction.

## Required Stack Changes

### Composer

- Upgrade `livewire/livewire` from `^3.5` to `^4.0`.
- Add `robsontenorio/mary:^2.0`.
- Plan later removal of `wireui/wireui`, `wireui/heroicons`, and `wireui/phosphoricons` once replacement work is complete.

### NPM / Vite

- Replace Tailwind 3 pipeline with Tailwind 4 pipeline.
- Add `@tailwindcss/vite`.
- Upgrade daisyUI from v4 to v5.
- Remove old Tailwind 3/PostCSS-only setup when Mary/Tailwind 4 migration is complete.

### CSS

- Replace `@tailwind base/components/utilities` bootstrap with Tailwind 4 import syntax.
- Add Mary `@source` entries for vendor components.
- Revisit custom CSS assumptions because Tailwind 4 and daisyUI 5 change defaults.

## Confidence

- High: Laravel 12 + Livewire 4 is the correct framework path.
- High: maryUI v2 is the correct Mary target for Laravel 12 era apps.
- High: prefix/non-collision strategy is mandatory in this repo.
- Medium: exact Mary namespacing approach should be proven in Phase 1 against the existing wrapper tree and layout boot sequence.

## Sources

- Livewire 4 upgrade guide: https://livewire.laravel.com/docs/4.x/upgrading
- maryUI installation and docs hub: https://mary-ui.com/docs/installation
- maryUI upgrade guidance for Tailwind 4 / daisyUI 5 / Mary v2: https://mary-ui.com/docs/upgrading
- Existing repo stack baseline: `.planning/codebase/STACK.md`
