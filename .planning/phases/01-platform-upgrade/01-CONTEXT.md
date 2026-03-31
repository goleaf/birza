# Phase 1: Platform Upgrade - Context

**Gathered:** 2026-04-01
**Status:** Ready for planning

<domain>
## Phase Boundary

Upgrade the Birza platform to Livewire 4, Tailwind 4, daisyUI 5, and maryUI v2 while keeping the existing buyer, seller, admin, and auth surfaces bootable. This phase proves stack compatibility and establishes a safe Mary entry path; it does not complete the full UI refactor.

</domain>

<decisions>
## Implementation Decisions

### Migration Scope
- **D-01:** The upgrade targets the entire application surface area: admin, buyer, seller, and auth.
- **D-02:** Phase 1 must validate bootability and compatibility across all of those surfaces, not just a subset.

### Component Strategy
- **D-03:** maryUI becomes the primary UI system for the project.
- **D-04:** WireUI is phased out rather than kept as a long-term parallel design system.
- **D-05:** Mary must be introduced through a non-colliding component strategy because the repo already uses generic Blade component names and local wrappers.
- **D-06:** Volt is explicitly out of scope for this migration; Phase 1 must stay on standard `Livewire\Component` classes and Blade views only.

### Rollout Shape
- **D-07:** The migration is a two-step rollout.
- **D-08:** Step 1 is the platform upgrade: Livewire 4, Tailwind 4, daisyUI 5, and maryUI installation.
- **D-09:** Step 2 is the systematic Mary refactor across shared wrappers and feature surfaces.

### the agent's Discretion
- Exact Mary prefixing or wrapper-bridge implementation, as long as it avoids collisions with current local components.
- Exact ordering of compatibility smoke checks inside Phase 1, as long as all role-based surfaces are covered.
- Exact dependency cleanup timing inside Phase 1, as long as WireUI is not prematurely removed before a safe bridge exists and Volt is not introduced.

</decisions>

<canonical_refs>
## Canonical References

**Downstream agents MUST read these before planning or implementing.**

### Project and phase definition
- `.planning/PROJECT.md` — Overall project goal, constraints, and key decisions for the Livewire 4 + Mary migration.
- `.planning/REQUIREMENTS.md` — Phase 1 requirement mapping for platform upgrade and safe Mary introduction.
- `.planning/ROADMAP.md` — Phase 1 goal, success criteria, plan slots, and canonical phase boundary.
- `.planning/STATE.md` — Current project position and active focus.

### Research and codebase map
- `.planning/research/STACK.md` — Recommended target stack and dependency direction.
- `.planning/research/PITFALLS.md` — Known brownfield migration risks, especially collision and style-drift risks.
- `.planning/codebase/STACK.md` — Current repo baseline: Livewire 3, WireUI, Tailwind 3, daisyUI 4.
- `.planning/codebase/CONVENTIONS.md` — Repo conventions for Blade wrappers, Livewire patterns, and PHP style.

### Runtime and dependency touchpoints
- `composer.json` — Current PHP dependencies and Livewire / WireUI package constraints.
- `package.json` — Current frontend build stack and Tailwind / daisyUI versions.
- `resources/css/app.css` — Current Tailwind 3 entrypoint that will change for Tailwind 4 + Mary.
- `resources/js/app.js` — Current frontend bootstrap and Livewire/Alpine expectations.
- `app/Providers/AppServiceProvider.php` — Current Livewire script tag customization and lang path setup.

### Shared UI integration points
- `resources/views/layouts/frontend/app.blade.php` — Frontend shell with current WireUI and Livewire asset loading.
- `resources/views/layouts/backend/app.blade.php` — Backend shell with current WireUI and Livewire asset loading.
- `resources/views/components/ui/button.blade.php` — Existing wrapper that forwards to generic `<x-button>`, creating collision risk.
- `resources/views/components/ui/card.blade.php` — Existing wrapper pattern for shared card UI.
- `resources/views/components/ui/form-actions.blade.php` — Existing form action pattern using daisyUI button classes.
- `resources/views/components/ui/flash-messages.blade.php` — Existing alert/feedback wrapper currently tied to vendor alert components.
- `app/Livewire/Concerns/InteractsWithWireUi.php` — Existing WireUI notification and confirmation integration to replace or bridge.

### External vendor references
- `https://livewire.laravel.com/docs/4.x/upgrading` — Official Livewire 4 upgrade path and breaking-change review.
- `https://mary-ui.com/docs/installation` — Official maryUI installation guidance.
- `https://mary-ui.com/docs/upgrading` — Official maryUI v2 upgrade guidance for Tailwind 4 and daisyUI 5.
- `https://github.com/robsontenorio/mary` — Upstream Mary package reference and examples.

</canonical_refs>

<code_context>
## Existing Code Insights

### Reusable Assets
- `resources/views/components/ui/button.blade.php`: Existing project wrapper layer that can hide the eventual Mary invocation strategy from many views.
- `resources/views/components/ui/card.blade.php`: Existing wrapper pattern for shared card UI that can be redirected to Mary-compatible rendering.
- `resources/views/components/ui/form-actions.blade.php`: Existing reusable submit/cancel action pattern that should become Mary-compatible instead of being rewritten page by page.
- `resources/views/components/ui/flash-messages.blade.php`: Existing cross-layout feedback aggregator for validation and session messages.

### Established Patterns
- Route-mounted Livewire pages under `app/Livewire/Backend/*` and `app/Livewire/Frontend/*` are the dominant UI pattern.
- Shared Blade shells in `resources/views/layouts/frontend/app.blade.php` and `resources/views/layouts/backend/app.blade.php` are the global integration points for scripts, styles, dialogs, and notifications.
- `resources/js/app.js` intentionally lets Livewire own Alpine boot, so Phase 1 should preserve that runtime assumption.
- `app/Livewire/Concerns/InteractsWithWireUi.php` shows that notifications and confirmations are currently abstracted through a project trait, which creates a natural migration seam.

### Integration Points
- Dependency upgrade work lands in `composer.json` and `package.json`.
- Asset pipeline changes land in `resources/css/app.css`, `resources/js/app.js`, and Vite-related config files.
- Livewire 4 runtime adjustments land in `app/Providers/AppServiceProvider.php` and any Livewire config the upgrade introduces.
- Mary collision handling must connect to shared Blade wrappers and the global frontend/backend layout shells before deeper feature-page migration begins.

</code_context>

<specifics>
## Specific Ideas

- The final migration scope is full-app: admin + buyer + seller + auth.
- Mary is not just for new pages; it is the target design system for the whole repository.
- The upgrade should avoid a big-bang rewrite and instead separate platform compatibility from the broader component refactor.
- Mary docs and the upstream package repository are explicit references for how the migration should be planned.

</specifics>

<deferred>
## Deferred Ideas

None — discussion stayed within phase scope

</deferred>

---

*Phase: 01-platform-upgrade*
*Context gathered: 2026-04-01*
