# Research: Features

**Date:** 2026-04-01
**Scope:** What a successful "maximum maryUI integration" initiative should include in this codebase.

## Table Stakes

### Platform Upgrade

- Livewire 4 upgrade with no framework boot errors on admin, buyer, seller, and auth routes.
- Tailwind 4 and daisyUI 5 asset pipeline working in local build and production build.
- maryUI installed and callable from Blade/Livewire views without colliding with current local components.

### Shared UI Foundation

- Shared layout shell updated for Mary-compatible theme, navigation, alerts, and dialog patterns.
- Existing reusable wrappers in `resources/views/components/ui/*` either point to Mary or are replaced by Mary-native usage.
- Consistent feedback patterns for validation, flash states, notifications, and confirmations.

### User-Facing Flow Migration

- Buyer auth, browsing, cart, and order flows render and behave correctly on the new stack.
- Seller auth, dashboard, product management, orders, and transactions render and behave correctly on the new stack.
- Admin login, dashboard, and CRUD-oriented pages stop depending on WireUI as the primary UI layer.

### Regression Safety

- High-value Livewire/feature tests cover migrated flows.
- Legacy dependency cleanup is measurable so WireUI does not become a silent permanent dependency.

## Differentiators

- A Mary-first compatibility layer that lets the repo keep its custom wrappers while steadily reducing direct vendor coupling.
- Shared daisyUI theme decisions across frontend and backend instead of ad hoc classes.
- A migration order that preserves business flows and lets UI refactors happen in batches instead of one risky rewrite.

## Anti-Features

- Big-bang rewrite of all screens at once.
- New marketplace business capabilities mixed into the UI migration.
- Parallel long-term operation of both Mary and WireUI as first-class design systems.
- Frontend architecture rewrite to SPA frameworks.

## Recommended Priority

1. Keep the app booting and testable on Livewire 4.
2. Establish shared Mary-compatible layout and wrapper patterns.
3. Migrate highest-traffic buyer/seller/auth flows.
4. Migrate backend/admin surface.
5. Remove or tightly document remaining WireUI residue.

## Sources

- Livewire 4 upgrade guide: https://livewire.laravel.com/docs/4.x/upgrading
- maryUI docs: https://mary-ui.com/docs/installation
- Existing repo architecture and structure: `.planning/codebase/ARCHITECTURE.md`, `.planning/codebase/STRUCTURE.md`
