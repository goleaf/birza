# Requirements: Birza

**Defined:** 2026-04-01
**Core Value:** Existing buyer, seller, and admin workflows must keep working while the UI foundation is upgraded to a Mary-first Livewire 4 stack.

## v1 Requirements

### Platform

- [ ] **PLAT-01**: Admin, buyer, seller, and auth Livewire routes run successfully on Livewire 4
- [ ] **PLAT-02**: Livewire configuration, script loading, and layout resolution are updated for the Laravel 12 application structure
- [ ] **PLAT-03**: Tailwind 4, daisyUI 5, and Vite build successfully for local and production asset builds
- [ ] **PLAT-04**: The Livewire 4 upgrade uses standard `Livewire\Component` classes and Blade views only, with no `livewire/volt` dependency, Volt routes, or Volt components

### Mary Foundation

- [ ] **MARY-01**: maryUI v2 is installed and usable in Blade and Livewire views without colliding with existing project components
- [ ] **MARY-02**: Shared wrappers and layout primitives can render Mary-based buttons, forms, cards, tables, dialogs, and feedback states
- [ ] **MARY-03**: Frontend and backend layouts share a documented Mary-compatible theme and shell strategy

### Frontend Flows

- [ ] **FE-01**: Buyer and seller authentication screens use Mary-compatible forms, validation, and feedback states
- [ ] **FE-02**: Buyer catalog, product detail, cart, and order pages preserve current behavior after migration
- [ ] **FE-03**: Seller dashboard, product management, orders, profile, and transactions pages preserve current behavior after migration
- [ ] **FE-04**: Frontend shared components and layout pieces no longer rely on WireUI as the primary UI system

### Backend Flows

- [ ] **BE-01**: Admin login, dashboard, and navigation use the Mary-compatible shared shell
- [ ] **BE-02**: Admin CRUD-oriented pages migrate away from WireUI-driven primary interactions while preserving existing capabilities

### Quality

- [ ] **QA-01**: Critical PHPUnit and Livewire tests cover the upgraded platform and migrated high-value screens
- [ ] **QA-02**: Remaining WireUI usage is removed or explicitly documented as an accepted exception before closure

## v2 Requirements

### Nice-to-Have Improvements

- **V2-01**: Theme personalization beyond the baseline Mary/daisyUI theme
- **V2-02**: Additional Mary advanced components introduced only when they simplify real existing workflows
- **V2-03**: Broader visual redesign not required by the platform migration

## Out of Scope

| Feature | Reason |
|---------|--------|
| New marketplace business features | This initiative is UI and platform modernization only |
| SPA rewrite | Conflicts with the existing Laravel + Blade + Livewire architecture |
| Filament migration | Not required for the stated goal and would expand scope substantially |

## Traceability

| Requirement | Phase | Status |
|-------------|-------|--------|
| PLAT-01 | Phase 1 | Pending |
| PLAT-02 | Phase 1 | Pending |
| PLAT-03 | Phase 1 | Pending |
| PLAT-04 | Phase 1 | Pending |
| MARY-01 | Phase 1 | Pending |
| MARY-02 | Phase 2 | Pending |
| MARY-03 | Phase 2 | Pending |
| FE-01 | Phase 3 | Pending |
| FE-02 | Phase 3 | Pending |
| FE-03 | Phase 3 | Pending |
| FE-04 | Phase 3 | Pending |
| BE-01 | Phase 4 | Pending |
| BE-02 | Phase 4 | Pending |
| QA-01 | Phase 5 | Pending |
| QA-02 | Phase 5 | Pending |

**Coverage:**
- v1 requirements: 15 total
- Mapped to phases: 15
- Unmapped: 0

---
*Requirements defined: 2026-04-01*
*Last updated: 2026-04-01 after Livewire constraints*
