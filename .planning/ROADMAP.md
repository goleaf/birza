# Roadmap: Birza

## Overview

This roadmap modernizes Birza's UI platform without rewriting the product. The work starts by upgrading the framework and asset stack, then establishes a Mary-first shared UI layer, then migrates user-facing and admin-facing surfaces, and finally removes legacy WireUI residue while closing regression gaps.

## Phases

**Phase Numbering:**
- Integer phases (1, 2, 3): Planned milestone work
- Decimal phases (2.1, 2.2): Urgent insertions (marked with INSERTED)

- [ ] **Phase 1: Platform Upgrade** - Upgrade Livewire and frontend build tooling, then install Mary safely in the brownfield app.
- [ ] **Phase 2: Shared Mary Foundation** - Establish Mary-compatible layouts, wrappers, theme rules, and shared feedback patterns.
- [ ] **Phase 3: Frontend Flow Migration** - Migrate auth, buyer, and seller surfaces to the Mary-first stack.
- [ ] **Phase 4: Admin Flow Migration** - Migrate backend/admin surfaces away from WireUI-first patterns.
- [ ] **Phase 5: Cleanup and Hardening** - Remove legacy UI residue, close test gaps, and lock in conventions.

## Phase Details

### Phase 1: Platform Upgrade
**Goal**: Upgrade the platform to Livewire 4, Tailwind 4, daisyUI 5, and maryUI v2 while keeping the application bootable across all role surfaces.
**Depends on**: Nothing (first phase)
**Requirements**: [PLAT-01, PLAT-02, PLAT-03, MARY-01]
**UI hint**: no
**Canonical refs**: `.planning/research/STACK.md`, `.planning/research/PITFALLS.md`, `.planning/codebase/STACK.md`, `composer.json`, `package.json`, `resources/css/app.css`, `resources/js/app.js`, `resources/views/layouts/frontend/app.blade.php`, `resources/views/layouts/backend/app.blade.php`, `resources/views/components/ui/button.blade.php`
**Success Criteria** (what must be TRUE):
  1. Composer and npm dependencies install and build on the target Livewire 4 + Mary stack.
  2. Admin, buyer, seller, and auth routes render without framework-level Livewire boot failures.
  3. Mary components are available through a non-colliding strategy suitable for the existing Blade component tree.
**Plans**: 3 plans

Plans:
- [ ] 01-01: Upgrade Composer, npm, Vite, Tailwind, and daisyUI dependencies.
- [ ] 01-02: Update Livewire config, layout resolution, and runtime boot integration.
- [ ] 01-03: Install maryUI and prove a safe component namespace or wrapper bridge.

### Phase 2: Shared Mary Foundation
**Goal**: Build the shared Mary-first layout and wrapper layer used by the rest of the application.
**Depends on**: Phase 1
**Requirements**: [MARY-02, MARY-03]
**UI hint**: yes
**Canonical refs**: `.planning/research/ARCHITECTURE.md`, `.planning/research/PITFALLS.md`, `.planning/codebase/ARCHITECTURE.md`, `resources/views/layouts/frontend/app.blade.php`, `resources/views/layouts/backend/app.blade.php`, `resources/views/components/ui/button.blade.php`, `resources/views/components/ui/card.blade.php`, `resources/views/components/ui/form-actions.blade.php`, `app/Providers/AppServiceProvider.php`
**Success Criteria** (what must be TRUE):
  1. Frontend and backend shells use a Mary-compatible theme and layout strategy.
  2. Shared button, card, form, flash, modal, and action wrappers stop depending on WireUI as the primary rendering layer.
  3. The app has a documented Mary-first pattern for feedback, confirmations, and common form controls.
**Plans**: 3 plans

Plans:
- [ ] 02-01: Convert shared layouts and navigation shell to the new design-system baseline.
- [ ] 02-02: Migrate shared UI wrappers to Mary-compatible abstractions.
- [ ] 02-03: Replace shared dialog, notification, and flash-message patterns.

### Phase 3: Frontend Flow Migration
**Goal**: Migrate auth, buyer, and seller pages to the Mary-first stack while preserving current marketplace behavior.
**Depends on**: Phase 2
**Requirements**: [FE-01, FE-02, FE-03, FE-04]
**UI hint**: yes
**Canonical refs**: `.planning/research/SUMMARY.md`, `.planning/research/PITFALLS.md`, `.planning/codebase/ARCHITECTURE.md`, `routes/buyer.php`, `routes/seller.php`, `app/Livewire/Frontend`, `resources/views/frontend`, `resources/views/livewire/frontend`
**Success Criteria** (what must be TRUE):
  1. Buyer and seller auth screens use Mary-compatible form, validation, and feedback patterns.
  2. Buyer catalog, product, cart, and order flows retain their current behavior and render correctly on the upgraded stack.
  3. Seller dashboard, products, orders, profile, and transactions flows retain their current behavior and use the Mary-first shared UI layer.
**Plans**: 4 plans

Plans:
- [ ] 03-01: Migrate buyer and seller auth flows.
- [ ] 03-02: Migrate buyer commerce flows.
- [ ] 03-03: Migrate seller workspace flows.
- [ ] 03-04: Add regression coverage for migrated frontend flows.

### Phase 4: Admin Flow Migration
**Goal**: Move backend/admin pages from WireUI-first patterns to Mary-first patterns without losing existing admin capabilities.
**Depends on**: Phase 3
**Requirements**: [BE-01, BE-02]
**UI hint**: yes
**Canonical refs**: `.planning/research/SUMMARY.md`, `.planning/codebase/ARCHITECTURE.md`, `routes/admin.php`, `app/Livewire/Backend`, `resources/views/backend`, `resources/views/livewire/backend`
**Success Criteria** (what must be TRUE):
  1. Admin login, dashboard, navigation, and shared shell run on the Mary-first stack.
  2. Admin CRUD-oriented pages preserve existing capabilities after the UI migration.
  3. Admin confirmations, validation, and feedback flows no longer rely on WireUI as the dominant UI runtime.
**Plans**: 4 plans

Plans:
- [ ] 04-01: Migrate admin auth and shared shell.
- [ ] 04-02: Migrate admin catalog and order surfaces.
- [ ] 04-03: Migrate admin buyer, seller, attribute, country, and settings surfaces.
- [ ] 04-04: Add regression coverage for migrated admin flows.

### Phase 5: Cleanup and Hardening
**Goal**: Remove obsolete UI dependencies, close remaining regressions, and document the final Livewire 4 + Mary conventions.
**Depends on**: Phase 4
**Requirements**: [QA-01, QA-02]
**UI hint**: no
**Canonical refs**: `.planning/research/PITFALLS.md`, `.planning/codebase/CONVENTIONS.md`, `tests`, `app/Livewire/Concerns/InteractsWithWireUi.php`, `config/wireui.php`, `resources/views/components/ui`
**Success Criteria** (what must be TRUE):
  1. Critical PHPUnit and Livewire coverage passes for the upgraded and migrated surfaces.
  2. WireUI is removed from primary usage or any residual exceptions are explicitly documented and justified.
  3. Repo conventions and planning artifacts reflect Livewire 4 + Mary as the default UI stack.
**Plans**: 3 plans

Plans:
- [ ] 05-01: Remove or isolate remaining WireUI dependencies and config.
- [ ] 05-02: Close test gaps and run final upgrade verification.
- [ ] 05-03: Refresh codebase and workflow documentation for the new defaults.

## Progress

**Execution Order:**
Phases execute in numeric order: 1 -> 2 -> 3 -> 4 -> 5

| Phase | Plans Complete | Status | Completed |
|-------|----------------|--------|-----------|
| 1. Platform Upgrade | 0/3 | Not started | - |
| 2. Shared Mary Foundation | 0/3 | Not started | - |
| 3. Frontend Flow Migration | 0/4 | Not started | - |
| 4. Admin Flow Migration | 0/4 | Not started | - |
| 5. Cleanup and Hardening | 0/3 | Not started | - |
