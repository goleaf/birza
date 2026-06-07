---
quick_id: 260607-jze
description: Implement kettasoft/filterable for product catalog filters
date: 2026-06-07
status: completed
commit: uncommitted
---

# Quick Task 260607-jze Summary

Installed `kettasoft/filterable` v2.15.0 and registered its provider. Added an allowlisted `ProductFilter` for search, soft-delete status, category, seller, price, stock, organic, country, and exact attribute-value filtering.

Both buyer and admin product listings now use the shared filter while preserving their existing eager loading, URL state, sorting, and pagination. A reversible migration adds indexes matching the catalog predicates and expands the attribute pivot index.

Verification:

- `33` focused tests passed with `173` assertions.
- Filter tests passed with Laravel configuration cached.
- Pint, Blade cache compilation, package inspection, and scoped diff checks passed.
- Boost schema inspection confirmed the existing and new index targets.
- Composer audit reports 12 pre-existing advisories across 8 packages, including Laravel 12.56.0.

No commit was created because the affected files already contain unrelated staged user changes.
