# 260607-fks Summary - Harden Foreign Key Constraints

## Completed

- Added `2026_06_07_174227_harden_foreign_key_constraints.php`.
- Added the missing `buyer_credit_history.admin_id -> users_admins.id` foreign key with `nullOnDelete()`.
- Changed `buyer_credit_history.buyer_id` from cascade delete to restricted delete.
- Changed `categories.parent_category_id` from cascade delete to `nullOnDelete()`.
- Changed pending review foreign keys to nullable `nullOnDelete()` references.
- Added `tests/Unit/Database/ForeignKeyConstraintTest.php`.
- Added `docs/foreign-key-constraint-audit-2026-06-07.md`.

## Verification

- `php artisan test --compact tests/Unit/Database/ForeignKeyConstraintTest.php` passed.

## Notes

- No committed `address_id`, `imageable_id`, `created_by`, or `updated_by` columns were found.
- Polymorphic framework-owned fields remain intentionally unconstrained.
