# Changelog

All notable changes to this project must be documented in this file.

This changelog follows the Keep a Changelog structure and uses semantic versioning. The project is still in active development, so versions may remain in the `0.x` range until the first stable `1.0.0` release.

Release notes live in [docs/releases](docs/releases/README.md).

## [Unreleased]

### Added

- Added a documentation audit report and dedicated installation, environment, roles, architecture, database, frontend, seeders, security, roadmap, developer workflow, production, and screenshots documentation surfaces.
- Added local-safe `.env.example` defaults for Birza, SQLite, log mail, Sanctum placeholders, foreign keys, and production-safe Debugbar disabling.
- Added a unified audit log system for critical marketplace actions, including audit log schema/model/service/policy, sanitized JSON payloads, seller product audits, buyer checkout audits, order status audits, admin moderation reasons, buyer credit/settings audits, admin audit pages, entity history panels, demo audit seeding, documentation, and tests.
- Added centralized marketplace authorization policies, global access gates, verified-account middleware, an admin action audit trail, security documentation, and focused policy/security tests.
- Added a session-backed product comparison system for guests and authenticated buyers, including catalog/detail compare controls, a responsive side-by-side comparison page, public-product safety checks, translations, tests, seed data, and release notes.
- Added product stock alerts and back-in-stock database notifications for authenticated buyers, including alert management UI, duplicate prevention, audit logs, demo seed data, translations, documentation, and tests.
- Added a complete root README with project overview, setup, roles, demo accounts, commands, testing, storage, localization, roadmap, known issues, and production notes.
- Added a frontend stack compatibility report documenting the current Tailwind, Vite, Livewire 4, Mary UI, WireUI, daisyUI, Flowbite, Alpine, Blade component, CSS, and JavaScript upgrade risks.
- Added a multilingual system analysis report and translation guide covering supported languages, key naming, status labels, validation attributes, notifications, database content, and missing-key checks.
- Added dot-based translation keys for shared UI actions, marketplace product statuses, order lifecycle and payment statuses, cart/checkout/auth surfaces, notification mail, seller reset mail, and validation attributes.
- Added translation parity, locale formatting, notification localization, validation attribute, product status, and locale switcher tests.
- Added a `LocaleFormatter` helper for locale-aware currency and date-time display.
- Added release management documentation, release-note rules, release checklist, semantic versioning guidance, and git tag instructions.
- Added a reusable release notes template for future major project blocks.
- Added an initial `0.1.0` baseline release note for the current active-development state.
- Added query-driven composite indexes for common order, seller, buyer, category, country, transaction, credit-history, and attribute-value filters.
- Added a query index audit documenting the real query evidence behind each new index and fields intentionally left unindexed.
- Added a foreign key constraint audit documenting cascade, restrict, and `nullOnDelete()` decisions across relationship fields.
- Added a database structure audit covering the live SQLite schema, migrations, models, relationships, factories, seeders, missing domain tables, missing fields, missing indexes, missing foreign keys, and pre-feature hardening priorities.
- Added a reusable image pipeline for product uploads, variants, validation, gallery ordering, safe replacement, deletion, fallbacks, seed images, and pipeline documentation.
- Added database-backed cart and checkout workflow documentation covering guest carts, buyer carts, cart merge, validation, order snapshots, and one-order-per-seller checkout.
- Added cart and checkout workflow tests covering guest add, authenticated add, merge, quantity changes, clear, invalid checkout, backend price recalculation, snapshots, rollback, and multi-seller order creation.
- Added demo cart and checkout seed scenarios for empty buyers, cart buyers, guest-like carts, active products, inactive products, out-of-stock products, changed-price products, multi-seller products, completed orders, and failed checkout carts.
- Added a marketplace notification system with standard database notifications, queued mail-capable notification classes, buyer/seller/admin dropdowns and list pages, dashboard panels, stock/moderation/order/report triggers, demo notification seeds, translations, tests, and documentation.
- Added a marketplace feature test suite covering authentication, cross-role access, catalog visibility, seller product ownership, cart, checkout, order status workflow, audit logging, image uploads, product questions, product reports, stock alerts, wishlists, seller discounts, promo codes, performance budgets, and multilingual behavior.
- Added a marketplace performance audit, performance guide, and query-budget tests for catalog, buyer orders, seller orders, seller products, and checkout batching.
- Added a testing guide with PHPUnit, feature, unit, Livewire, image-upload, role-helper, factory, and marketplace scenario commands.
- Added complete marketplace factory states, minimal/demo seeder separation, demo credentials, product image placeholders, lifecycle order data, carts, wishlists, reviews, product reports, notifications, credit history, transactions, audit/activity rows, and seeder/factory coverage tests.
- Added a demo seeding guide with fresh migration, minimal seed, demo seed, reset, test, credential, data-map, factory, and seeder instructions.
- Added product questions and answers for active products, including guest and buyer questions, public seller answers, seller unanswered-question management, admin moderation, notifications, audit logs, demo seed data, and tests.
- Added product reports and abuse moderation for guest and buyer reports, duplicate and rate-limit protection, admin triage, product hiding, seller notifications without reporter identity, audit logs, demo seed data, documentation, and tests.
- Added authenticated buyer product wishlists with named private/public lists, default list creation, catalog/detail save controls, wishlist management pages, move/remove/clear actions, add-to-cart handoff, translations, demo seeding, documentation, and feature tests.
- Added seller discounts and promo codes with seller management pages, buyer cart promo application, backend checkout revalidation, multi-seller scoping, usage/per-user limits, order snapshots, redemption records, audit logs, demo seeding, translations, documentation, and feature tests.

### Changed

- Standardized order lifecycle labels, payment labels, product status labels, seller reset email copy, order status notifications, and order workflow messages on translatable dot-based keys.
- Updated locale switching to replace invalid locales with the fallback locale and apply the selected locale to Laravel and Carbon on every web request.
- Hardened foreign key delete behavior for buyer credit history, category trees, and review history, and added the missing credit-history admin constraint.
- Moved Laravel Debugbar to development-only dependencies and disabled it outside local opt-in environments.
- Standardized Eloquent relationship models for users, buyer and seller profiles, products, orders, carts, reviews, notifications, images, and addresses.
- Added a strict `OrderStatus` enum as the single source for order lifecycle values, model casts, filters, badges, helper output, and order flow tests.
- Standardized product image display to use generated variants and configured fallbacks instead of hardcoded public paths.
- Replaced runtime LaraCart buyer cart usage with database-backed `carts` and `cart_items` actions for product add-to-cart, cart display, header count, quantity updates, removal, clear cart, guest cart, and checkout.
- Refactored checkout to validate backend product price, product availability, stock, seller activity, buyer activity, address data, and payment method before transactional order creation.
- Optimized buyer and seller order lists, buyer and seller dashboards, seller product lists, backend order tables, catalog reference filters, and checkout product loading to avoid N+1 queries and unpaginated large collections.
- Added reusable validation rules to seller product Livewire forms so media-sync upload validation runs consistently.
- Protected fake marketplace demo data from production seeding by routing `DatabaseSeeder` through `MinimalSeeder` and only running `DemoScenarioSeeder` outside production.

### Fixed

- Fixed duplicate constructor declarations in the order status action.

### Removed

- None yet.

### Security

- Added append-only audit logging for sensitive product, order, account, credit, and settings actions with actor role, entity, reason, IP address, user agent, sanitized old/new values, and admin-only visibility.
- Hardened buyer, seller, and admin route groups with area gates and verified-account checks; added backend policy checks to critical Livewire product/order/settings/credit actions; removed dangerous ownership, status, verification, balance, and order-total fields from normal mass assignment; and added admin audit records for destructive/status-sensitive admin actions.
- Cart and checkout no longer trust frontend or session item prices during final order creation.
- Checkout now rejects carts owned by another buyer and only clears carts after successful transactional order creation.

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
