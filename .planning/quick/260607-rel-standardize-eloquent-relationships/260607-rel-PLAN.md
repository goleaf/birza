# Quick Task 260607-rel - Standardize Eloquent Relationships

## Goal

Review all Eloquent models and make relationship names/types clear, correct, and consistent for users, profiles, sellers, buyers, products, categories, orders, carts, reviews, notifications, images, and addresses.

## Steps

1. Inspect current migrations, models, factories, and relationship usage.
2. Add missing relationship target models/tables where the requested relationship could not exist.
3. Remove stale or duplicate relationship methods.
4. Update callers that used removed relationship aliases.
5. Add focused model tests for the canonical relationship map.
6. Update documentation, changelog, format, test, and commit.

## Notes

- Keep unrelated order-status workflow changes out of the commit.
- Prefer canonical relationship names: `orderItems()` and `cartItems()` instead of ambiguous aliases.
