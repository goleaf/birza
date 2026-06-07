# Quick Task 260607-sto: Add Feature Coverage for Core Marketplace Flows

**Date:** 2026-06-07
**Mode:** quick-full

## Objective

Add deterministic feature coverage for the existing buyer, seller, admin, catalog, cart, checkout, order, image, notification, and multilingual marketplace flows without inventing unsupported behavior.

## Must Haves

- A pre-implementation testing report exists before new tests are added.
- New tests use PHPUnit, `RefreshDatabase`, factories, `Storage::fake()`, `Notification::fake()`, and Livewire test helpers where appropriate.
- Dangerous role and ownership paths are covered directly.
- Checkout totals and product availability are enforced by backend state, not client-controlled cart data.
- Inactive account access is enforced consistently where `is_active` already exists.
- Unsupported flows such as favorites and public catalog are documented as gaps instead of fake-tested.
- Focused tests and formatting are run before completion.

## Tasks

### 1. Audit Existing Coverage

**files:** `tests/`, `routes/`, `database/factories/`, `database/seeders/`, `.planning/quick/260607-sto-add-feature-coverage-for-core-marketplac/TESTING-REPORT.md`

**action:** Inspect existing tests, route guards, factories, seeders, schema, Livewire components, and baseline feature-suite status. Write the report before implementation.

**verify:** Report lists current tests, missing flows, role gaps, dangerous cases, missing/weak fixtures, duplicate/weak tests, and first tests to create.

**done:** Report is committed with the quick task artifacts.

### 2. Add Scenario Tests and Small Hardening Fixes

**files:** `tests/Feature/Marketplace/*`, `tests/Feature/Support/*`, auth middleware/Login components/routes, cart checkout component, order/image actions if needed.

**action:** Add focused feature and Livewire coverage for supported authentication, role access, catalog, seller product ownership, image validation, cart/checkout, order visibility/status, notifications, and translation keys. Fix real security/business gaps revealed by those tests.

**verify:** Run each new or changed test file with `php artisan test --compact <file>`.

**done:** New tests fail for real regressions before hardening, then pass after fixes.

### 3. Document and Verify

**files:** `docs/testing.md`, `CHANGELOG.md`, `.planning/quick/260607-sto-add-feature-coverage-for-core-marketplac/260607-sto-SUMMARY.md`, `.planning/quick/260607-sto-add-feature-coverage-for-core-marketplac/260607-sto-VERIFICATION.md`, `.planning/STATE.md`

**action:** Document how to run tests and update the changelog. Run focused tests, `pint --dirty`, feature/unit suites as feasible, migrations/seeders, and frontend build only if UI changed.

**verify:** Verification artifact records exact commands and any pre-existing failures.

**done:** Commit isolated changes with `test: add feature coverage for core marketplace flows`.
