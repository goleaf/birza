# Marketplace Performance Guide

This guide documents the 2026-06-07 marketplace performance audit and the rules to keep product, order, dashboard, cart, category, notification, and admin pages stable with large data volumes.

The detailed working audit lives at `.planning/quick/260607-perf-fix-n1-marketplace-lists/REPORT.md`.

## Pages Audited

- Buyer catalog, product search results, category filters, and product detail pages.
- Seller products, seller dashboard, seller order list, and seller order detail pages.
- Buyer dashboard, buyer order list, buyer order detail, cart, and checkout.
- Backend product, order, buyer order, seller order, category, buyer, and seller tables.
- Category menus, country/category filter data, product images, reviews, notifications, seeders, factories, tests, and indexes.

## Optimized Areas

- Buyer catalog now uses cached category/country reference data, selected product columns, eager-loaded seller/category/main image relations, and pagination.
- Buyer order list now paginates and uses summary order columns plus database counts/sums instead of loading all historical orders.
- Seller order list now paginates order summaries, computes seller subtotals with database aggregates, and avoids grouping every order item in PHP.
- Buyer and seller dashboards now use database aggregates, limited recent rows, and bounded chart queries instead of full order collections.
- Seller product list now uses a paginated product table and a lightweight cached category tree instead of loading nested category product collections.
- Backend order tables now avoid full order detail eager loads when the table only needs order summary data.
- Checkout now batch-loads locked products for cart validation and updates in-memory stock after atomic decrements instead of refreshing each product row.
- Categories and active European country filter options are cached and invalidated on create, update, and delete.

## Common Rules

- Paginate every potentially large list: catalog, seller products, buyer orders, seller orders, backend products/orders/users, notifications, reviews, favorites, audit logs, and messages.
- Eager-load every relation read inside Blade loops. Product grids must not lazy-load seller, category, main image, reviews, favorites, or image counts.
- Use selected columns for large lists and include foreign keys needed by eager-loaded relationships.
- Use `withCount`, `withExists`, `withAvg`, `withSum`, `count()`, `sum()`, and `avg()` instead of loading full relation collections for counters.
- Prefer `withExists()` for boolean relationship checks.
- Keep expensive logic out of Blade and Livewire public properties. Livewire render methods should build paginated builders or bounded aggregate queries, not large collections.
- Use scopes for repeated query rules such as active products, buyer orders, seller order items, placed-between date filters, and summary columns.
- Cache stable reference data only. User-private counters must use per-user or per-seller keys if cached later.
- Never query in Blade templates, and never call aggregate methods inside loops.
- Avoid `Model::all()` for user-facing or unbounded data. Use scopes, limits, or pagination.

## Caching

Current reference cache keys:

- `categories.tree.locale.{locale}`
- `categories.visible.locale.{locale}`
- `categories.filters.locale.{locale}`
- `countries.active.european`
- `countries.active.name_map.alpha2`

Category and country model events invalidate their own reference caches. Avoid cache tags unless the configured store supports them.

## Indexes

Indexes must support real query paths, not every possible column. The current audit added or uses query-backed indexes for:

- Buyer orders by buyer/date and buyer/status/date.
- Orders by payment status/date.
- Seller order items by seller/order.
- Category trees by parent/order.
- Cart items by cart/product through the unique cart item key.
- Product catalog filters and product image primary lookup.
- Notification notifiable/read/latest lookups.

See `docs/query-index-audit-2026-06-07.md` for the broader index evidence.

## Query Count Tests

`tests/Feature/Marketplace/PerformanceQueryBudgetTest.php` covers:

- Buyer catalog with many products.
- Buyer order list with many orders.
- Seller order list with many orders.
- Seller product list pagination.
- Checkout batched product loading for many cart items.

The tests use generous maximum query counts and product-table query checks so they catch obvious N+1 regressions without depending on exact framework query noise.

## Debugbar

Debugbar is local investigation tooling only. Production must use:

```env
APP_DEBUG=false
DEBUGBAR_ENABLED=false
```

For production-like checks, use query-count tests, logs, query listeners, and Telescope only when intentionally installed.

## Query Delta Documentation

When a task is query-related, document the expected query delta in the PR notes, release notes, or relevant docs:

- before behavior, such as one query per product row
- after behavior, such as one paginated query plus eager-loaded relationships
- affected page or component
- index or cache dependency
- focused test command

Exact counts can vary by framework boot noise. Prefer stable test assertions that catch N+1 regressions without depending on one brittle number.

## Future Feature Checklist

- Does the page paginate?
- Does it eager-load every relationship used by Blade or Livewire loops?
- Does it select only the columns needed for the list?
- Does it avoid lazy loading in loops and computed properties?
- Does it use database counts/sums/exists checks instead of full collections?
- Do filters and sorting columns have query-backed indexes?
- Does it cache stable reference data without leaking private user data?
- Does checkout or batch processing avoid one query per item where batched loading is possible?
- Does it have a focused query-count or regression test for important paths?
- Is Debugbar disabled outside local opt-in environments?
- Is cached data invalidated by model events or explicit action logic?
- Are private cache keys scoped to the buyer, seller, admin, or tenant-equivalent owner?

## Related Docs

- [Database guide](database.md)
- [Testing guide](testing.md)
- [Query index audit](query-index-audit-2026-06-07.md)
