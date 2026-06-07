# 260607-idx Plan - Add Query-Driven Indexes

## Goal

Analyze common committed query paths and add missing database indexes for real filtering, sorting, relationship loading, and history screens.

## Scope

- Inspect existing indexes and query sites.
- Add only query-backed indexes.
- Avoid blanket indexing for unused fields.
- Document added and skipped index decisions.
- Add a focused schema test.

## Implementation

- Add a migration with named composite indexes for:
  - Order buyer/payment/date queries.
  - Seller order item lookups.
  - Buyer credit and seller transaction history filters.
  - Admin buyer/seller filters.
  - Category tree ordering.
  - Country region/status selectors.
  - Attribute value active filters.
- Add `QueryDrivenIndexTest`.
- Update documentation and changelog.

## Verification

- Focused schema test for expected index names and column order.
- Relationship/database tests.
- Full suite from the clean worktree before commit.
