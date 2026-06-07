# Quick Task 260607-rel - Summary

## Completed

- Added missing models, factories, and migrations for `User`, `ProductImage`, `CartItem`, `Review`, `Notification`, and `Address`.
- Added profile links from `users_buyers` and `users_sellers` to `users`.
- Standardized core relationship names and types across account, catalog, commerce, and user-owned content models.
- Removed incorrect `Order::product()`, `Order::country()`, and duplicate `Order::items()` relationships.
- Replaced cart relationship tests and callers with canonical `cartItems()` and order callers with `orderItems()`.
- Added `tests/Unit/Models/EloquentRelationshipMapTest.php`.
- Added `docs/eloquent-relationship-map-2026-06-07.md`.

## Verification

- Focused model tests cover the canonical relationship map.
- Formatting and test results are recorded in the final task response.
