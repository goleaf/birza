# Seller Analytics Dashboard Preflight Report

Date: 2026-06-07

Scope: analyze the current seller dashboard and marketplace data model before adding the requested seller analytics dashboard and marketplace insights feature.

No seller analytics feature code was implemented in this pass because the project foundation gate is not green.

## Stability Gate

The requested feature is explicitly gated on a stable foundation: role architecture, UI/design system, database architecture, factories and seeders, feature tests, security hardening, performance audit, documentation, and final cleanup.

Current repository state does not satisfy that gate.

- `.planning/STATE.md` says the active milestone is Phase 1, Platform Upgrade, with 0/3 plans complete.
- `.planning/PROJECT.md` marks "New marketplace business features" as out of scope for the current Livewire/Mary modernization effort.
- `.planning/stabilization/FOUNDATION-STABILIZATION-PLAN.md` says no large feature modules should be added until stabilization is complete.
- `git status --short` shows a heavily dirty tree across application code, migrations, docs, tests, and planning artifacts.
- `php artisan test --compact` currently fails: 524 passed, 1 failed. The failing test is `Tests\Feature\Marketplace\MessagingFeatureTest`, which receives a 500 on `/` because the in-memory SQLite test database has no `users_sellers` table when `BuildWelcomePageDataAction` runs seller counts.

Recommended GSD route before feature work: `$gsd-execute-phase 1`, because the current phase has 3 plans and 0 summaries.

## Current Seller Dashboard

Routes:

- Seller routes live in `routes/seller.php`.
- Private seller routes are grouped under prefix `seller`, name prefix `seller.`, and middleware `auth:seller`, `active.account:seller`, `verified.account:seller`, and `can:accessSellerCabinet`.
- The seller dashboard is declared as `Route::livewire('/dashboard', SellerDashboard::class)->name('dashboard')`.
- `php artisan route:list --except-vendor` currently only shows POST logout/notification routes for seller/buyer/admin, even though `Route::livewire()` declarations exist and tests use `route('seller.dashboard')`. This route-list mismatch should be resolved during foundation work.

Dashboard files:

- Component: `app/Livewire/Frontend/Seller/Dashboard.php`
- View: `resources/views/frontend/seller/dashboard/index.blade.php`
- Categories partial: `resources/views/frontend/seller/dashboard/partials/categories.blade.php`

What it shows now:

- Company ad placeholder text.
- Recent notifications through `x-notifications.recent-panel`.
- Seller categories and links to create products in assigned categories.
- Recent seller order items limited to 5 rows.
- A monthly sales chart built for the last 6 months.
- Seller profile card.
- Calendar UI generated in Blade.
- Market analysis, market sentiment, trading performance, risk management, portfolio/risk sections, and a fake recent orders table with hardcoded values. These are not real marketplace analytics.

Existing seller statistics:

- Total seller orders, via an order query scoped to orders containing the seller's `order_items`.
- Per-status order counts, using `OrderStatus::cases()` and one count query per status.
- Total revenue from seller order items whose parent order is `paid()`.
- Recent seller order items, eager-loaded with order, buyer, product, and product primary image.
- Recent seller notifications, limited to 5.
- Monthly revenue over the last 6 months.
- Monthly paid order counts over the last 6 months.

Missing seller statistics:

- Date range filtering on the dashboard.
- Completed, pending, cancelled, refunded, rejected counts as dashboard widgets with clear definitions.
- Sold item quantity.
- Average order value scoped to the selected range.
- Active, inactive/draft, low-stock, and out-of-stock product counts.
- Top products by sold quantity, revenue, and order count.
- Products with no sales.
- Products needing attention.
- Review count, average rating, latest reviews, and rating distribution.
- Stock insight section.
- Unread notification count widget.
- Product views, top viewed products, views over time, and conversion.
- Empty states for no orders/products/range results.
- Real backend-only replacement for the hardcoded trading/market blocks.

## Revenue And Orders

How revenue is calculated now:

- Seller dashboard revenue: `OrderItem::forSeller($seller)->whereHas('order', fn ($query) => $query->paid())->sum('total_price')`.
- Seller order list revenue: same seller item sum, filtered by date/status, also using `order->paid()`.
- This means current seller revenue is based on `orders.payment_status = paid`, not `OrderStatus::revenueStatuses()`.
- `OrderStatus::revenueStatuses()` exists and recognizes accepted, processing, shipped, delivered, completed, and disputed statuses, but the seller dashboard does not currently use that scope.
- Revenue uses `order_items.total_price` snapshots, which is correct for historical order accuracy.

Recommended revenue rule for the analytics feature:

Seller revenue should be calculated from `order_items.total_price` belonging to the seller, joined through parent orders in revenue-recognized statuses and paid payment state, excluding rejected, cancelled, and refunded orders. The implementation should document whether `disputed` stays revenue-recognized; the current enum includes it.

How orders are stored now:

- `orders` stores buyer, payment method/status, lifecycle status, subtotal, discount totals, order total, address snapshots, delivery method, timestamps, and soft delete.
- `order_items` stores order, product, seller, quantity, unit price, total price, product title snapshot, product price snapshot, seller name snapshot, discount fields, bundle link, timestamps, and soft delete.
- Multi-seller orders are represented by one parent order with many seller-scoped order items.
- `order_bundles` and linked order items exist for bundle checkout snapshots.
- `order_status_histories` stores status transitions.

Order status flow:

- `OrderStatus` values: pending, accepted, rejected, processing, shipped, delivered, completed, cancelled, refunded, disputed.
- `OrderPaymentStatus` values: pending, paid, failed, cancelled, refunded.
- `ChangeOrderStatusAction` is the controlled mutation path; `Order` blocks direct status mutation unless explicitly allowed.
- Sellers can change allowed lifecycle statuses through policy-backed checks in seller order show.
- Seller order analytics must distinguish lifecycle status from payment status.

## Products, Reviews, Stock, Views

Products:

- `products` belongs to `users_sellers`.
- Current product status is effectively `is_active` plus soft delete, with labels active, inactive, deleted, and draft translation keys.
- `stock` exists as an integer column and is used by checkout, cart validation, stock alerts, and buyer availability UI.
- Product primary images exist through `product_images`, while legacy product image fields still remain.

Reviews:

- `reviews` exists with `product_id`, `user_id`, `rating`, `title`, `body`, `is_approved`, timestamps, and soft delete.
- `Product::reviews()` and `User::reviews()` exist.
- Public comparison already uses approved review count and average rating.
- There is no seller-specific review analytics yet.
- Review stats must join through the seller's products and only use approved, non-deleted reviews unless a deliberate seller moderation policy says otherwise.

Stock:

- Low stock threshold exists at `config('notifications.low_stock_threshold')`, sourced from `MARKETPLACE_LOW_STOCK_THRESHOLD`, default 5.
- Seller low-stock notifications already use this threshold.
- Analytics should reuse that config value and should not duplicate a hardcoded threshold.

Product views and conversion:

- No `product_views`, `view_count`, analytics event, or visitor/session tracking table was found.
- Do not implement conversion analytics in this feature unless product view tracking is intentionally added as a separate approved feature.
- Documentation should state: product view tracking is required before conversion analytics can be accurate.

## Expensive Or Risky Queries

Current risks:

- Seller dashboard `statusCounts()` performs one count query per `OrderStatus`.
- Seller dashboard monthly chart performs two aggregate queries per month, so the default 6-month chart can issue at least 12 chart aggregate queries.
- Seller dashboard uses repeated `whereHas` subqueries on orders/order items.
- Seller dashboard view still calls `Auth::guard('seller')->user()` and Carbon date logic directly in Blade.
- The categories partial loops through categories and translations; current component eager-loads categories and parent, but this is not analytics-specific.
- Hardcoded fake dashboard sections create misleading non-database "analytics."
- `Order::placedBetween()` parses arbitrary date strings with `Carbon::parse()` and can throw on invalid input if not validated before use.
- `NotificationPolicy` references `user_id`, but database notifications use `notifiable_type` and `notifiable_id`; notification ownership is enforced by actions instead.

Indexes currently useful for analytics:

- `orders_status_created_at_idx`: status and created date.
- `orders_payment_created_at_idx`: payment status and created date.
- `order_items_seller_created_at_idx`: seller and order item created date.
- `order_items_seller_order_idx`: seller and order id.
- `order_items_order_product_idx`: order and product id.
- `products_seller_active_idx`: seller and active products.
- `products_stock_idx`: stock.
- `reviews_product_id_is_approved_index`: product and approval state.
- `notifications_notifiable_type_notifiable_id_read_at_index`: unread notification counts per actor.

Likely indexes to review when implementing:

- `products(seller_id, stock)` for seller low/out-of-stock counts.
- `products(seller_id, deleted_at, created_at)` for seller product analytics and recent products.
- `order_items(seller_id, product_id, created_at)` for top product analytics by seller/date.
- `reviews(product_id, is_approved, created_at)` already exists partially as product/approval; date-range review queries may need review only after real query evidence.
- Avoid adding indexes until the exact analytics query shapes are implemented and checked with schema/explain tools.

## Files That Would Need Changes

Recommended new classes after the gate opens:

- `app/Actions/SellerAnalytics/ResolveSellerAnalyticsDateRangeAction.php`
- `app/Actions/SellerAnalytics/GetSellerDashboardStatsAction.php`
- `app/Actions/SellerAnalytics/GetSellerRevenueSummaryAction.php`
- `app/Actions/SellerAnalytics/GetSellerOrderStatsAction.php`
- `app/Actions/SellerAnalytics/GetSellerProductPerformanceAction.php`
- `app/Actions/SellerAnalytics/GetSellerReviewStatsAction.php`
- `app/Actions/SellerAnalytics/GetSellerStockInsightsAction.php`

Likely existing files to update:

- `app/Livewire/Frontend/Seller/Dashboard.php`
- `resources/views/frontend/seller/dashboard/index.blade.php`
- `resources/views/frontend/seller/dashboard/partials/categories.blade.php` only if dashboard layout changes require it
- `routes/seller.php` only if a separate analytics route is preferred; otherwise keep the existing dashboard route
- `app/Models/Order.php`, `app/Models/OrderItem.php`, `app/Models/Product.php`, and `app/Models/Review.php` for focused scopes only if they remove real duplication
- `config/notifications.php` should remain the low-stock threshold source
- `lang/en.json` and `lang/lt.json`
- `database/seeders/Demo/*` for deterministic analytics scenarios
- `database/factories/*` only if new states are needed
- `tests/Feature/Controllers/Frontend/Seller/SellerDashboardControllerTest.php`
- `tests/Feature/Marketplace/PerformanceQueryBudgetTest.php`
- `tests/Feature/Translations/TranslationFilesTest.php`
- `README.md`, `CHANGELOG.md`, and relevant docs only after feature implementation is verified

UI components to reuse:

- `x-ui.header`
- `x-ui.card`
- `x-ui.statistic`
- `x-ui.chart`
- `x-ui.datepicker`
- `x-ui.badge`
- `x-ui.button`
- `x-notifications.recent-panel`
- Existing empty-state patterns from notification, wishlist, stock alert, and bundle pages

## Tests Needed

Add focused PHPUnit coverage after the gate opens:

- Guest cannot access seller analytics dashboard.
- Buyer cannot access seller analytics dashboard.
- Seller can access own analytics dashboard.
- Unverified/inactive seller follows existing seller cabinet gate behavior.
- Seller revenue includes only own order items.
- Seller revenue excludes another seller's order items from the same parent order.
- Revenue excludes cancelled, rejected, and refunded orders.
- Revenue uses `order_items.total_price` snapshots, not current product prices.
- Date range defaults to last 30 days.
- Date range filters revenue, orders, sold items, top products, reviews, and charts.
- Invalid custom dates fall back safely and do not throw.
- Pending/completed/cancelled counts are correct.
- Active/inactive product counts are correct.
- Low-stock and out-of-stock counts use `config('notifications.low_stock_threshold')`.
- Top products show only current seller products.
- Products with no sales are included only from the current seller.
- Recent orders show only current seller order data.
- Review stats use only approved reviews for current seller products.
- Unread notification count uses current seller notifications only.
- Empty seller with no products/orders renders clear empty states.
- Translation keys exist in both `en` and `lt`.
- Query budget remains bounded with many products/orders/order items/reviews/notifications.

Recommended focused commands:

```bash
php artisan test --compact tests/Feature/Controllers/Frontend/Seller/SellerDashboardControllerTest.php
php artisan test --compact tests/Feature/Marketplace/PerformanceQueryBudgetTest.php --filter=seller
php artisan test --compact tests/Feature/Translations/TranslationFilesTest.php
php artisan test --compact
npm run build
```

## Implementation Shape After Gate Opens

- Keep seller analytics on the existing seller dashboard unless product direction wants a separate `seller.analytics` route.
- Add validated Livewire URL state for `range`, `date_from`, and `date_to`; default to last 30 days.
- Resolve date ranges in a dedicated action and return immutable start/end Carbon values.
- Use backend aggregate queries only.
- Use order item snapshots for revenue and product performance.
- Use per-seller query scopes/actions so another seller's rows cannot leak.
- Replace hardcoded fake dashboard sections with real analytics cards/tables or remove them.
- Keep charts because `x-ui.chart` already exists; do not add a chart dependency.
- Do not audit normal seller dashboard views.
- Document that conversion analytics is unavailable until product view tracking exists.

## Recommendation

Do not implement `feat: add seller analytics dashboard` yet.

Next safe step is to execute the current GSD platform/foundation work: `$gsd-execute-phase 1`. After the final stabilization checklist is green, this report can become the implementation brief for a focused seller analytics feature.
