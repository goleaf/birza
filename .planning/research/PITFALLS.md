# Research: Pitfalls

**Date:** 2026-04-01
**Scope:** Upgrade risks specific to this repository and migration target.

## 1. Component Name Collisions

**Risk**

- The repo already uses local components and wrappers that ultimately resolve to `<x-button>` and similar generic tags.
- maryUI also exposes generic Blade component names.

**Warning signs**

- Blade component resolution starts rendering the wrong vendor/local component.
- Shared wrappers break first, even before feature screens are migrated.

**Prevention**

- Use a non-colliding Mary strategy during migration.
- Prove the chosen approach on shared wrappers before bulk page migration.

**Phase**

- Phase 1

## 2. Tailwind 4 / daisyUI 5 Style Drift

**Risk**

- The current app uses Tailwind 3 and daisyUI 4 conventions.
- Tailwind 4 and daisyUI 5 change defaults, which can subtly break spacing, borders, and visual hierarchy.

**Warning signs**

- Inputs, cards, separators, and table surfaces look "off" after the asset upgrade.
- Existing utility classes compile but produce visually different output.

**Prevention**

- Upgrade the build first, then audit shared wrappers and layouts before page-by-page migration.
- Expect CSS cleanup in `resources/css/app.css` and shared component views.

**Phase**

- Phase 1 and Phase 2

## 3. Livewire 4 Config and Layout Drift

**Risk**

- Livewire 4 changes config keys and layout defaults.
- The repo already customizes script tag attributes in `AppServiceProvider`.

**Warning signs**

- Route-mounted Livewire pages fail to resolve layouts.
- Hydration/script boot issues appear after the dependency upgrade.

**Prevention**

- Update Livewire config deliberately.
- Smoke-test buyer, seller, admin, and auth routes immediately after the platform upgrade.

**Phase**

- Phase 1

## 4. Dual UI Runtime Confusion

**Risk**

- Running Mary and WireUI as equal first-class systems for too long makes view code inconsistent and harder to remove later.

**Warning signs**

- New screens keep introducing WireUI out of habit.
- Notifications, dialogs, and form components differ by surface.

**Prevention**

- Define Mary as the target system in PROJECT.md and ROADMAP.md.
- Restrict WireUI to temporary compatibility only.

**Phase**

- Phase 2 through Phase 5

## 5. Regression Across Multi-Guard Flows

**Risk**

- Birza has separate buyer, seller, and admin surfaces with different layouts and route groups.

**Warning signs**

- One role surface works while another breaks.
- Auth or redirect behavior diverges after layout/script changes.

**Prevention**

- Treat each surface as a first-class migration target.
- Add or update route/Livewire tests for critical login, dashboard, catalog, cart, order, and admin flows.

**Phase**

- Phase 3 through Phase 5

## Sources

- `.planning/codebase/STACK.md`
- `.planning/codebase/ARCHITECTURE.md`
- Livewire 4 upgrade guide: https://livewire.laravel.com/docs/4.x/upgrading
- maryUI upgrade notes: https://mary-ui.com/docs/upgrading
