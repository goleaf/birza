# 260607-fks Plan - Harden Foreign Key Constraints

## Goal

Check relation fields across migrations and add missing or safer foreign keys without blindly cascading important business data.

## Scope

- Audit migration fields ending in common relationship keys.
- Add missing foreign keys where a concrete target table exists.
- Replace dangerous cascade behavior on business-history structures.
- Document decisions and verification.

## Implementation

- Add a corrective migration for already-ran tables:
  - Restrict `buyer_credit_history.buyer_id` hard deletes.
  - Add nullable `buyer_credit_history.admin_id` FK.
  - Null `categories.parent_category_id` on hard parent delete.
- Tighten the pending `reviews` migration so deleted products/users null references instead of deleting review history.
- Add focused database behavior tests.
- Update docs and changelog.

## Verification

- Run the focused foreign-key constraint test.
- Run relationship model tests.
- Run the full test suite from an isolated clean worktree before committing.
