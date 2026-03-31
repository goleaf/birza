# Research: Summary

**Date:** 2026-04-01

## Key Findings

- Birza should stay a Laravel 12 + Blade + Livewire monolith; the correct modernization path is Livewire 4, not architectural replacement.
- maryUI v2 should become the primary component system, but the repo needs a non-colliding migration strategy because current local wrappers and generic Blade component tags would otherwise clash.
- Tailwind 4 and daisyUI 5 are part of the Mary upgrade, so the migration is both a framework upgrade and a design-system/platform upgrade.
- The safest order is: platform upgrade, shared layout/wrapper migration, buyer/seller/auth migration, admin migration, cleanup and WireUI removal.

## What This Means For Planning

- Phase 1 must prove stack compatibility and Mary availability without breaking route rendering.
- Phase 2 must establish shared Mary-compatible shells and wrappers so later phases are repeatable.
- Phases 3 and 4 should migrate user-facing and admin-facing surfaces separately to keep regressions contained.
- Final cleanup must remove or explicitly quarantine WireUI residue and close test gaps.

## Recommended Constraints

- No new marketplace business features during this initiative.
- No SPA rewrite.
- No permanent dual-design-system state.

## Sources

- `.planning/research/STACK.md`
- `.planning/research/FEATURES.md`
- `.planning/research/ARCHITECTURE.md`
- `.planning/research/PITFALLS.md`
