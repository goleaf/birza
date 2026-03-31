# Phase 1: Platform Upgrade - Discussion Log

> **Audit trail only.** Do not use as input to planning, research, or execution agents.
> Decisions are captured in CONTEXT.md - this log preserves the alternatives considered.

**Date:** 2026-04-01
**Phase:** 1-Platform Upgrade
**Areas discussed:** Migration Scope, Component Strategy, Rollout Shape

---

## Migration Scope

| Option | Description | Selected |
|--------|-------------|----------|
| All app surfaces | Admin + buyer + seller + auth pages are in scope for the migration target | ✓ |
| Frontend surfaces first | Frontend and auth first, admin later | |
| Admin only first | Admin-first migration | |

**User's choice:** All app surfaces
**Notes:** The overall migration target covers every role-based surface. Phase 1 should validate compatibility across all of them.

---

## Component Strategy

| Option | Description | Selected |
|--------|-------------|----------|
| Mary primary with phased WireUI removal | Mary becomes the target UI system; use a prefix or other non-colliding strategy during migration | ✓ |
| Mary only for new screens | Keep existing WireUI screens unchanged and use Mary only for future work | |
| Hard cut replacement | Replace WireUI and local wrappers in one pass | |

**User's choice:** Mary primary with phased WireUI removal
**Notes:** Long-term dual design systems are not acceptable. Collision avoidance is required because the repo already uses generic Blade component names and wrappers.

---

## Rollout Shape

| Option | Description | Selected |
|--------|-------------|----------|
| Two-step rollout | First do the platform upgrade, then do the systematic Mary refactor | ✓ |
| Big-bang rewrite | Upgrade and refactor in one large pass | |
| Compatibility-only now | Upgrade dependencies now and postpone UI migration | |

**User's choice:** Two-step rollout
**Notes:** Phase 1 is explicitly the compatibility and install proof. Broader Mary refactor work belongs in later phases.

---

## the agent's Discretion

- Exact Mary namespacing or wrapper-bridge mechanism
- Exact sequence of Phase 1 smoke checks

## Deferred Ideas

None.
