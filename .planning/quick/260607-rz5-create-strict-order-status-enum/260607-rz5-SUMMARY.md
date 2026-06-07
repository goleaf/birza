---
quick_id: 260607-rz5
description: Create strict order status enum
date: 2026-06-07
status: completed
commit: pending
---

# Quick Task 260607-rz5 Summary

Added `App\Enums\OrderStatus` as the central source for the order statuses currently used by the application: pending, paid, failed, processing, shipped, delivered, cancelled, and refunded.

`Order.status` and `Order.payment_status` now cast through the enum. Order status labels, badge classes, UI colors, lifecycle panel data, timeline milestones, calendar CSS, select options, and collection counts are delegated to the enum instead of scattered string arrays or Blade matches.

Updated buyer, seller, and admin order screens and Livewire components to use enum cases and model helpers instead of hardcoded status values. The order badge helper now accepts an `OrderStatus` or valid backed value and rejects unknown values.

Updated factory states, old migration defaults, tests, changelog, and database audit documentation.

Verification:

- `vendor/bin/pint --format agent ...` passed for the scoped PHP files.
- `php artisan test --compact tests/Unit/Helpers/OrderStatusHelperTest.php` passed with 6 tests and 11 assertions.
- Focused order PHPUnit coverage was attempted, but the run is blocked by unrelated active work in the same repository: an untracked migration `database/migrations/2026_06_07_172030_normalize_existing_order_status_values.php` references `OrderStatus::Accepted`, which this scoped enum intentionally does not define.
