---
quick_id: 260607-jze
description: Implement kettasoft/filterable for product catalog filters
date: 2026-06-07
status: completed
---

# Quick Task 260607-jze Plan

## Task 1: Install and wire Filterable

- Add `kettasoft/filterable` and register its service provider.
- Add the package trait and filter class binding to `Product`.
- Create an allowlisted `ProductFilter` with isolated array input for Livewire.

## Task 2: Migrate catalog queries

- Replace duplicated buyer and admin product predicates with `ProductFilter`.
- Preserve eager loading, sorting, pagination, hierarchy, and attribute-value behavior.
- Add indexes for the catalog predicates and pivot lookup.

## Task 3: Verify behavior

- Add direct filter tests and an admin Livewire regression test.
- Run buyer, admin, filter, and product model tests.
- Run Pint, Blade compilation, config-cache verification, schema inspection, and Composer audit.
