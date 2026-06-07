# Changelog

## Unreleased

### Changed

- Hardened foreign key delete behavior for buyer credit history, category trees, and review history, and added the missing credit-history admin constraint.
- Standardized Eloquent relationship models for users, buyer and seller profiles, products, orders, carts, reviews, notifications, images, and addresses.

### Documentation

- Added a foreign key constraint audit documenting cascade, restrict, and `nullOnDelete()` decisions across relationship fields.
- Added a database structure audit covering the live SQLite schema, migrations, models, relationships, factories, seeders, missing domain tables, missing fields, missing indexes, missing foreign keys, and pre-feature hardening priorities.
