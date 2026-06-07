# Roadmap

This roadmap is practical documentation for future development. It does not claim unfinished modules are complete.

## Phase 1 - Stabilize Role Architecture

Confirm guest, buyer, seller, and admin access rules across routes, guards, middleware, gates, policies, redirects, and tests. This matters because Birza uses separate guard/profile tables and role-specific Livewire surfaces.

## Phase 2 - Standardize The UI System

Complete the Mary-first Livewire UI direction and retire or isolate remaining WireUI, daisyUI, and Flowbite overlap. This makes forms, buttons, dialogs, tables, alerts, and navigation predictable for future work.

## Phase 3 - Clean Database Architecture

Keep the current schema documented, resolve stale legacy paths, and reduce split sources of truth such as product attribute pivots and legacy image fields. This protects order history, product ownership, and future feature work.

## Phase 4 - Keep Factories And Seeders Complete

Maintain production-safe minimal seeders and realistic local demo data for every major module. This keeps onboarding, local QA, and automated tests trustworthy.

## Phase 5 - Maintain Feature Tests

Broaden and keep focused PHPUnit coverage for auth, role access, catalog, cart, checkout, orders, notifications, images, translations, seeders, and performance budgets. Tests should prove behavior, not just implementation details.

## Phase 6 - Harden Security

Continue enforcing policies, ownership, active/verified account checks, dangerous-field protection, audit logging, and file upload validation. Every new private feature should include forbidden-access tests.

## Phase 7 - Optimize Performance

Preserve query-budget tests, eager loading, pagination, selected columns, indexes, and reference-data caching. Avoid N+1 regressions in Livewire and Blade loops.

## Phase 8 - Improve Notifications

Keep database notifications reliable, translated, ownership-safe, and queued after commit. Realtime broadcasting is not currently enabled and should be introduced only with explicit infrastructure decisions.

## Phase 9 - Improve Cart And Checkout

Continue hardening stock validation, backend price recalculation, seller discounts, promo codes, bundles, order snapshots, payment states, and rollback behavior. Real payment provider integration remains planned.

## Phase 10 - Production Hardening

Finalize environment defaults, build steps, cache commands, queue/mail choices, storage permissions, backups, monitoring, logs, and deployment verification. Do not use destructive local reset commands in production.

## Phase 11 - Add New Marketplace Features

After the core architecture is stable, consider marketplace messaging, disputes, payment provider integration, shipping/delivery records, richer review workflows, seller analytics, and public SEO improvements.

## Local GSD Roadmap

The repository also has local GSD planning state under `.planning/`. That roadmap currently tracks a Livewire 4 and Mary-first UI modernization effort. Treat `.planning/` as the active planning system for multi-step implementation work.
