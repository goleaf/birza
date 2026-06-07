# Performance Audit: Marketplace Lists and N+1 Risk

Date: 2026-06-07

## Scope Checked

- Public/home: `Frontend\HomeController`, `Frontend\Home`, `BuildWelcomePageDataAction`, `resources/views/frontend/welcome.blade.php`
- Buyer catalog: `Frontend\Buyer\Products\Index`, `Show`, product grid/detail Blade views
- Cart/checkout: `Frontend\Buyer\Cart\Index`, `App\Actions\Cart\*`
- Buyer dashboard/orders: `Frontend\Buyer\Dashboard`, `Orders\Index`, `Orders\Show`, dashboard/order Blade views
- Seller dashboard/products/orders/transactions: `Frontend\Seller\Dashboard`, `Products\Index`, `Orders\Index`, `Orders\Show`, `Transactions\Index`
- Backend/admin tables: dashboard, products, categories, orders, seller detail/orders, buyer orders, buyers, sellers
- Models/relationships: `Product`, `Category`, `Order`, `OrderItem`, `Cart`, `CartItem`, `Notification`, `Review`, buyer/seller models
- Schema/indexes: Boost schema inspection plus existing migrations for products, orders, order items, categories, product images, reviews, notifications
- Tests/factories/seeders: marketplace/controller tests, model factories, demo seeders
- Debugbar/config: `config/debugbar.php`, `config/app.php`, `.env.example`, Debugbar tests

## Repeated Query and N+1 Findings

### Product Lists

- Buyer catalog already paginates and eager-loads `primaryImage`, `seller`, `category`, and `category.parent`.
- Catalog filter reference data is queried on every render:
  - full root category tree with subcategory filter attributes/values
  - active European countries
- Backend product table paginates and eager-loads `category`, `seller`, `primaryImage`, but loads all sellers for a filter dropdown.
- Seller product list loads categories with nested `products` and `subcategories.products` as full collections. This is unpaginated and can load every product owned by a seller into one request.
- Product image helpers are safe when `primaryImage` or `images` is eager-loaded, but `imageLibraryPreview()` / `imageGalleryUrls()` call `images()->get()` if `images` was not loaded. Product grids should keep using `primaryImage`; detail pages should eager-load `images`.

### Order Lists

- Buyer order list loads all matching orders with `get()` and no pagination, then counts/sums in memory.
- Seller order list loads all matching seller order items, groups them by order in PHP, and returns all orders to the view.
- Seller dashboard loads all seller order items with `order`, `order.buyer`, and `product.primaryImage`, then builds totals, charts, recent rows, and all order groups in PHP.
- Buyer dashboard loads every buyer order with full details and all items/products/sellers/status history, then computes all counters and charts in memory.
- Backend seller orders page uses eager loading but returns `get()` with no pagination.
- Backend order table paginates, but uses `withFullDetails()` for the table. That loads `orderItems.product`, `orderItems.seller`, and `statusHistory` even though the index only displays buyer/status/total/date.

### Blade Loop Risks

- `resources/views/frontend/seller/dashboard/partials/categories.blade.php` calls `$seller->categories->isEmpty()` without the component eager-loading categories.
- `resources/views/frontend/seller/products/index.blade.php` loops nested category product collections; the component loads full product collections, not paginated result sets.
- `resources/views/frontend/buyer/products/index.blade.php` loops `$product->category->parent`, `$product->category`, and `$product->seller`; current component eager-loads those correctly.
- Order detail views loop items and seller/product relations; current detail components eager-load those relations.
- Backend category table loops `$category->attributes`; current component eager-loads `attributes` and `parent`.

### Livewire Reload Risks

- List components rerun full queries in `render()`, which is expected for Livewire, but high-risk components currently reload massive collections:
  - buyer dashboard
  - seller dashboard
  - buyer orders
  - seller orders
  - seller product list
- Search/filter properties use URL state but not debounced live inputs consistently. This is secondary because current form submit prevents per-keystroke reload in the audited order filters.
- Public properties do not store huge collections except seller order show `$orderItems`; the larger issue is building huge local collections inside render paths.

### Expensive Dashboard Counters

- Buyer dashboard uses full order collection for counts, paid totals, average/highest order, recent orders, chart data, and most ordered products.
- Seller dashboard uses full order item collection for total orders, status counts, revenue, recent orders, all order groups, and chart data.
- Backend dashboard uses simple DB counts and a limited activity query. This is acceptable, though these counters could be cached later.
- Backend order table runs four aggregate queries for stats each render. Acceptable at current scope, but can be consolidated or cached if admin traffic grows.

### Slow Filters and Sorting

- Buyer catalog search uses `%like%` on product name/description, category translations, and seller name/company. This cannot use ordinary B-tree indexes well.
- Product attribute filters add one `whereHas` per selected attribute. This is logically correct but can become expensive with many selected attributes.
- Buyer/seller date filters use `whereDate()`, which may prevent efficient index use compared to datetime range bounds.
- Seller order filters use `whereHas('order')` from order items; this needs order item seller indexes plus order status/date indexes.
- Sorting by JSON translation fields such as `category_name->en` is acceptable for small reference data but should be cached for menus/filter options.

### Missing or Weak Indexes

Existing useful indexes:

- `products`: category/active, seller/active, country/active, active/created, active/organic, deleted/created, price, stock
- `orders`: buyer/status, buyer/payment_status, status/created_at
- `order_items`: order/seller, seller/created_at
- `categories`: parent/active
- `product_images`: product/sort, product/is_primary, product/type
- `reviews`: product/is_approved, user/created_at
- `user_notifications`: user/read_at, user/created_at

Gaps to fix:

- `orders(buyer_id, created_at)` for buyer order list/dashboard recent orders.
- `orders(buyer_id, status, created_at)` for buyer status/date filtered list.
- `orders(payment_status, created_at)` for paid aggregate/date chart queries.
- `order_items(seller_id, order_id)` for grouping seller orders by order from seller item rows.
- `categories(parent_category_id, order)` for menu/tree ordering.
- `cart_items(cart_id, product_id)` for cart validation and checkout batching.

### Pagination Gaps

- Buyer orders page needs pagination.
- Seller orders page needs pagination.
- Seller product list needs pagination or a category-first summary instead of full nested product collections.
- Backend seller orders page needs pagination.
- Notification full page/dropdown routes were not found; if added later, dropdown must limit latest rows and full page must paginate.

### Selected Column Gaps

- Backend order index uses full details relationship loading for table rows; it should select only order table columns and eager-load buyer only.
- Buyer/seller order lists should select summary columns and use counts/sums instead of loading full item/product relations.
- Seller dashboard recent orders should select only order item summary columns plus product thumbnail relation.
- Category/cache reference queries should select only columns used by filters and menus.

### Counts Instead of Full Relations

- Replace buyer/seller dashboard full collections with DB counts/sums/avg and limited recent rows.
- Replace seller order list all-order collection counts with aggregate queries.
- Replace seller product list nested full product collections with paginated product query and category summary counts.
- Use `withCount` / `withAvg` for reviews when reviews become visible in product lists; currently reviews are modeled but not displayed in catalog loops.

### Cache Candidates

- Category tree per locale: `categories.tree.locale.{locale}`
- Visible/filter categories per locale: `categories.filters.locale.{locale}`
- Active European countries for catalog filters
- Unit/status option maps are already cheap static arrays/enums.
- Do not globally cache buyer/seller/private counts. If cached later, keys must include user/seller ids.

### Debugbar / Production

- Debugbar is a dev dependency and explicitly opt-in locally.
- `.env.example` already sets `APP_DEBUG=false` and `DEBUGBAR_ENABLED=false`.
- `config/debugbar.php` only enables Debugbar when `APP_ENV=local` and `DEBUGBAR_ENABLED=true`.
- `config/app.php` conditionally registers Debugbar only in local opt-in mode and class existence is checked.
- Recent logs show Debugbar provider errors from local dependency/autoload churn, not production enablement.

## Refactor Priority

1. Buyer and seller order lists: pagination, aggregate counts, no full historical collections.
2. Seller and buyer dashboards: DB aggregates plus limited recent rows/chart data.
3. Seller product list: avoid loading all products grouped by category.
4. Cart checkout actions: batch lock/load products instead of one query per cart item.
5. Category/reference data cache and invalidation.
6. Backend order/seller order tables: trim eager loads and paginate.
7. Query-count tests for catalog, dashboards, order lists, and cart checkout.
