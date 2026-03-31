# Birza

## What This Is

Birza is a Laravel marketplace with separate buyer, seller, and admin experiences built on route-mounted Livewire pages and Blade layouts. The current initiative upgrades the app to Livewire 4 and makes maryUI the primary UI system so the product keeps its marketplace behavior while moving to a cleaner, more maintainable component stack.

## Core Value

Existing buyer, seller, and admin workflows must keep working while the UI foundation is upgraded to a Mary-first Livewire 4 stack.

## Requirements

### Validated

- [x] Buyers can browse localized products, manage a cart, and place orders - existing
- [x] Sellers can manage products, review orders, and view transactions - existing
- [x] Admins can manage catalog, orders, buyers, sellers, attributes, countries, and settings - existing
- [x] Role-specific authentication exists for admin, buyer, and seller surfaces - existing

### Active

- [ ] Upgrade the application from Livewire 3 to Livewire 4 without breaking current workflows
- [ ] Install maryUI v2 as the primary component library for shared Blade and Livewire UI
- [ ] Migrate shared layouts, navigation, forms, dialogs, tables, and feedback patterns to Mary-compatible abstractions
- [ ] Replace or retire WireUI usage so Mary becomes the dominant UI system across buyer, seller, and admin surfaces
- [ ] Keep the Livewire 4 migration on standard `Livewire\Component` + Blade patterns only, with no Volt dependency or Volt-generated components
- [ ] Preserve localization, multi-guard auth, cart/order flows, uploads, and admin CRUD behavior through the migration
- [ ] Add regression coverage for upgraded platform and migrated critical screens

### Out of Scope

- New marketplace business features - this effort is platform and UI modernization only
- SPA, React, Vue, or Inertia rewrite - the app remains Laravel + Blade + Livewire
- Filament migration - the current custom Livewire admin remains the admin architecture

## Context

- The repository is a brownfield Laravel 12 application with a clear buyer/seller/admin split and heavy Livewire usage.
- The current frontend stack is Livewire 3, WireUI, Tailwind 3, and daisyUI 4.
- The repo already has local UI wrappers in `resources/views/components/ui/*`, which makes a wrapper-first Mary migration realistic.
- The codebase map already exists under `.planning/codebase/` and confirms this is a custom Livewire admin, not Filament.

## Constraints

- **Tech stack**: Keep Laravel 12, Blade, and Livewire - the upgrade should modernize within the current architecture
- **Livewire implementation**: Use standard `Livewire\Component` classes and Blade views only - Volt is excluded from this project
- **Compatibility**: Preserve buyer, seller, and admin flows during migration - this is a production brownfield app
- **Localization**: Keep current `lang/` and JSON translation behavior intact - multilingual UX already exists
- **Incremental delivery**: Mary must be phased in safely - current WireUI usage and generic Blade components create collision risk
- **Quality**: Critical paths need automated regression coverage - the migration touches shared layouts and role-specific auth flows

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| Use Livewire 4 as the upgrade target | It preserves the app's current interaction model while modernizing the framework layer | - Pending |
| Exclude Volt from the migration | The project should stay on standard Livewire components and Blade patterns only | - Accepted |
| Make maryUI the primary UI system | It aligns with the requested direction and fits Blade + Livewire well | - Pending |
| Use a phased migration instead of a big-bang rewrite | The repo is brownfield and spans multiple role-based surfaces | - Pending |
| Keep the app server-rendered | Current architecture already fits Laravel + Blade + Livewire | - Pending |

## Evolution

This document evolves at phase transitions and milestone boundaries.

**After each phase transition**:
1. Requirements invalidated? -> Move to Out of Scope with reason
2. Requirements validated? -> Move to Validated with phase reference
3. New requirements emerged? -> Add to Active
4. Decisions to log? -> Add to Key Decisions
5. "What This Is" still accurate? -> Update if drifted

**After each milestone**:
1. Full review of all sections
2. Core Value check - still the right priority?
3. Audit Out of Scope - reasons still valid?
4. Update Context with current state

---
*Last updated: 2026-04-01 after initialization and Livewire constraints*
