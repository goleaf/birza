# Changelog

All notable changes to this project must be documented in this file.

This changelog follows the Keep a Changelog structure and uses semantic versioning. The project is still in active development, so versions may remain in the `0.x` range until the first stable `1.0.0` release.

Release notes live in [docs/releases](docs/releases/README.md).

## [Unreleased]

### Added

- Added a complete root README with project overview, setup, roles, demo accounts, commands, testing, storage, localization, roadmap, known issues, and production notes.
- Added a marketplace feature test suite covering authentication, cross-role access, catalog visibility, seller product ownership, cart, checkout, order status workflow, audit logging, image uploads, product questions, product reports, stock alerts, wishlists, seller discounts, promo codes, performance budgets, and multilingual behavior.

- Added release management documentation, release-note rules, release checklist, semantic versioning guidance, and git tag instructions.
- Added a reusable release notes template for future major project blocks.
- Added an initial `0.1.0` baseline release note for the current active-development state.
- Added query-driven composite indexes for common order, seller, buyer, category, country, transaction, credit-history, and attribute-value filters.
- Added a query index audit documenting the real query evidence behind each new index and fields intentionally left unindexed.
- Added a foreign key constraint audit documenting cascade, restrict, and `nullOnDelete()` decisions across relationship fields.
- Added a database structure audit covering the live SQLite schema, migrations, models, relationships, factories, seeders, missing domain tables, missing fields, missing indexes, missing foreign keys, and pre-feature hardening priorities.
- Added a reusable image pipeline for product uploads, variants, validation, gallery ordering, safe replacement, deletion, fallbacks, seed images, and pipeline documentation.

### Changed

- Hardened foreign key delete behavior for buyer credit history, category trees, and review history, and added the missing credit-history admin constraint.
- Moved Laravel Debugbar to development-only dependencies and disabled it outside local opt-in environments.
- Standardized Eloquent relationship models for users, buyer and seller profiles, products, orders, carts, reviews, notifications, images, and addresses.
- Added a strict `OrderStatus` enum as the single source for order lifecycle values, model casts, filters, badges, helper output, and order flow tests.
- Standardized product image display to use generated variants and configured fallbacks instead of hardcoded public paths.

### Fixed

- None yet.

### Removed

- None yet.

### Security

- None yet.

### Deprecated

- None yet.

## [0.1.0] - 2026-06-07

### Added

- Established the initial active-development baseline for Birza, a Laravel marketplace with buyer, seller, and admin surfaces.
- Documented existing Blade and Livewire marketplace foundations, including localized product browsing, cart and order flows, seller product and order tools, and custom admin CRUD surfaces observed in the repository.
- Documented the baseline technical stack around Laravel 12, Blade, Livewire, Eloquent, Vite, Tailwind, and the current UI modernization effort.

### Changed

- Marked the project as not yet production-stable; future modules should graduate through `0.x` releases until the first verified `1.0.0` release.

### Fixed

- None recorded.

### Removed

- None recorded.

### Security

- No release-specific security fixes recorded.

### Deprecated

- None recorded.
