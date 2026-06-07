# Analysis Report: Complete Factories And Demo Seeders

Task: add complete factories and seeders for full project testing.

Status: this report was created before implementation. It is based on the current repository state, Laravel Boost application/schema inspection, existing migrations, models, factories, seeders, route files, and focused seeder tests.

## Application And Database Context

- Laravel 12.61.1, PHP 8.5, Livewire 4.3.1, PHPUnit 11.5.55, SQLite connection.
- Existing app languages: `lt` and `en`.
- Current live database has zero rows in the main marketplace tables that were inspected: admins, buyers, sellers, categories, products, orders, order items, carts, attributes, attribute values, seller transactions, buyer credit history, and activities.
- `tests/Feature/DatabaseSeederTest.php` currently passes a fresh SQLite `migrate:fresh --seed` smoke test, but it only asserts the legacy `admin@admin.com` record.
- `php artisan route:list --except-vendor` only reports a small route set because several app routes use `Route::livewire()` macros. Route files still show many admin, buyer, and seller pages that need seeded data.
- Several data-model migrations are present but pending in the developer database: generic `users`, `product_images`, `cart_items`, `reviews`, database `notifications`, `addresses`, buyer/seller `user_id` links, `order_status_histories`, wishlists, wishlist items, product questions, product stock alerts, product reports, order status normalization, and FK hardening.

## Current Factories

Existing factory files:

- `ActivityFactory`
- `AdminActionFactory`
- `AddressFactory`
- `AdminFactory`
- `AttributeFactory`
- `AttributeProductFactory`
- `AttributeValueFactory`
- `AuditLogFactory`
- `BuyerCreditHistoryFactory`
- `BuyerFactory`
- `CartFactory`
- `CartItemFactory`
- `CategoryFactory`
- `CountryFactory`
- `CreditAttachmentFactory`
- `GlobalSettingsFactory`
- `NotificationFactory`
- `OrderFactory`
- `OrderItemFactory`
- `OrderStatusHistoryFactory`
- `ProductFactory`
- `ProductAttributeValueFactory`
- `ProductImageFactory`
- `ProductQuestionFactory`
- `ProductReportFactory`
- `ProductStockAlertFactory`
- `ReviewFactory`
- `SellerFactory`
- `SellerTransactionFactory`
- `UserFactory`
- `WishlistFactory`
- `WishlistItemFactory`

Factory quality findings:

- Most main factories exist, but many only define a default state and one or two simple states.
- `UserFactory` supports only the shared generic user and `unverified()`.
- `BuyerFactory` and `SellerFactory` target `App\Models\Users\Buyer` and `App\Models\Users\Seller`, but only support `active()` and `inactive()`. They do not create linked generic `users` rows through `user_id`.
- `AdminFactory` targets `App\Models\Users\Admin`. The project also has `App\Models\Admin`, so the duplicate admin model should be normalized or avoided in tests.
- `ProductFactory` creates required products but does not create normalized product image rows, gallery images, review graphs, or status-like stock/image edge cases.
- `OrderFactory` has minimal status/payment coverage and does not create a complete graph of order items, status history, buyer/seller scenarios, or date ranges for dashboards.
- `OrderItemFactory` can create mismatched sellers because product and seller are generated independently.
- `CartFactory` is legacy-shaped around `product_id` and quantity, while the pending `cart_items` model/table represents the newer graph. Runtime cart UI currently uses LaraCart session data, not the database cart rows.
- `BuyerCreditHistoryFactory` uses `credit`/`debit`, while app methods create `add`/`deduct`; this mismatch can make tests unrealistic.
- `SellerTransactionFactory` uses `sale`/`refund`/`commission`, while seller transaction UI aggregates include `deduction` and `refund`; this mismatch should be aligned with runtime usage.
- `ProductImageFactory`, `AddressFactory`, `ReviewFactory`, `NotificationFactory`, and `OrderStatusHistoryFactory` exist but target pending migrations and need stronger states plus integration in seeders.

## Current Seeders

Existing seeder files:

- `DatabaseSeeder`
- `AdminSeeder`
- `database/seeders/test_information/AttributesSeeder.php`
- `database/seeders/test_information/CategorySeeder.php`
- `database/seeders/test_information/CountriesSeeder.php`
- `database/seeders/test_information/GlobalSettingsSeeder.php`
- `database/seeders/test_information/ProductAttributeSeeder.php`
- `database/seeders/test_information/ProductSeeder.php`
- `database/seeders/test_information/TestUsersSeeder.php`

Seeder quality findings:

- `DatabaseSeeder` calls countries, categories, settings, test users, products, attributes, product attributes, and admin.
- `AdminSeeder` is idempotent and creates `admin@admin.com`.
- `TestUsersSeeder` creates 10 buyers and 10 sellers, all active and verified. It does not create the requested stable demo credentials, inactive users, blocked users, unverified users, or a shared buyer-and-seller identity.
- `CategorySeeder` seeds a large translated category tree and is idempotent, but does not seed inactive, empty, image-backed, or edge-case categories.
- `CountriesSeeder` seeds active countries with translated names. Descriptions are not seeded.
- `ProductSeeder` seeds active products across subcategories and sellers with generated local WebP files. It does not seed draft/pending/rejected equivalents, inactive products, out-of-stock products, products without images, featured products, product gallery rows, or moderation edge cases.
- `AttributesSeeder` seeds translated attributes and values, then fills `product_attribute_value` for products. It does not attach category attributes, so some category-aware filter scenarios may remain uncovered.
- `ProductAttributeSeeder` depends on category attribute assignments. If categories have no attached attributes, it creates little or no useful `attribute_product` coverage.
- No seeder currently creates orders, order items, status histories, carts/cart items, reviews, addresses, notifications, activities, seller transactions, buyer credit history, or credit attachments.

## Models Without Complete Factory Coverage

Models with no dedicated factory file at the initial scan:

- `App\Models\AttributeProduct`
- `App\Models\ProductAttributeValue`

Additional supported models discovered during implementation:

- `App\Models\Wishlist`
- `App\Models\WishlistItem`
- `App\Models\ProductQuestion`
- `App\Models\ProductStockAlert`
- `App\Models\ProductReport`

Models with ambiguous or weak factory coverage:

- `App\Models\Admin` exists separately from `App\Models\Users\Admin`. `AdminFactory` maps to `App\Models\Users\Admin`.
- `App\Models\User` has a factory, but seeders do not create linked buyer/seller profile records through the pending `user_id` columns.
- `ActivityFactory` exists, but the table has only `id` and timestamps, so it cannot represent useful activity details without schema support.
- `AdminActionFactory` exists for a timestamp-only table. `AuditLogFactory` exists and the current pending audit log schema supports detailed actor/action/auditable metadata.

## Existing Models Not To Invent Around

The prompt mentions several marketplace concepts that are not currently supported by project models/tables:

- Favorites are represented by the supported wishlist models (`Wishlist` and `WishlistItem`); there is no separate favorites table.
- Messages: no message/conversation model/table found.
- Delivery methods: no delivery method model/table found.
- Static pages: no static page model/table found.
- Separate payments: no payment model/table found. Payment data is currently stored as fields on `orders`.
- Roles/permissions: no role/permission tables or package found. The app uses separate admin, buyer, seller guards/tables plus a pending shared `users` table.

Implementation should not invent these models. The seed report and docs should mark them as unsupported until schema exists.

## Relationships Not Seeded Today

Missing seeded relationship coverage:

- Generic `User` to buyer profile through `users_buyers.user_id`.
- Generic `User` to seller profile through `users_sellers.user_id`.
- Generic `User` to addresses, notifications, and reviews.
- Buyer to orders.
- Buyer to cart/cart items.
- Buyer to buyer credit history.
- Seller to products across several dashboard scenarios.
- Seller to categories through `seller_categories` beyond simple random sync.
- Seller to order items and seller transactions.
- Product to normalized product images.
- Product to reviews.
- Product to wishlists through wishlist items.
- Product to product reports.
- Product to product questions.
- Product to stock alerts.
- Product to attributes through category-aware `attribute_product` coverage.
- Product to soft-deleted order item scenarios.
- Order to order items.
- Order to order status history.
- Order to buyer and seller dashboard date/status scenarios.
- Notification records for buyer/seller/admin-like testing.
- Address records for buyer checkout/profile tests.
- Activity records for the backend dashboard recent activity section.
- Admin action and audit log rows for backend activity pages, limited to timestamps until those schemas gain detail columns.
- Credit attachments tied to buyer credit history.
- Buyer to wishlists and wishlist items.
- Buyer to product reports, product questions, and stock alerts.

## Statuses Not Seeded Today

Supported order statuses from `App\Enums\OrderStatus`:

- `pending`
- `accepted`
- `rejected`
- `processing`
- `shipped`
- `delivered`
- `completed`
- `cancelled`
- `refunded`
- `disputed`

Current seeders create no orders, so none of these statuses are represented by seeded data.

Supported payment statuses from `App\Enums\OrderPaymentStatus`:

- `pending`
- `paid`
- `failed`
- `cancelled`
- `refunded`

Current seeders create no orders, so none of these payment statuses are represented by seeded data.

Other status-like fields missing from current seed scenarios:

- Active and inactive admins.
- Active, inactive, blocked-equivalent, verified, and unverified buyers.
- Active, inactive, blocked-equivalent, verified, and unverified sellers.
- Active and inactive categories.
- Active and inactive countries.
- Active and inactive attributes and attribute values.
- In-stock, low-stock, and out-of-stock products.
- Approved and unapproved reviews.
- Read and unread notifications.
- Pending, reviewing, resolved, rejected, and dismissed product reports.
- Default and non-default addresses.
- Add/deduct buyer credit history types.
- Deduction/refund seller transaction types.

Project note: products do not currently have a real `draft`, `published`, `pending`, `approved`, `rejected`, `cancelled`, `completed`, `refunded`, or `disputed` status column. Product seeders can only express product-like scenarios through fields that exist, primarily `is_active`, stock, image presence, category, seller, price, and soft deletes.

## Roles Not Seeded Today

Actual role model:

- There is no roles table. Roles are represented by separate guards/tables: `users_admins`, `users_buyers`, and `users_sellers`.

Current seeded identities:

- One admin: `admin@admin.com`.
- Ten buyers: `buyer1@birza.lt` through `buyer10@birza.lt`.
- Ten sellers: `seller1@birza.lt` through `seller10@birza.lt`.

Missing required demo identities:

- `admin@example.com`.
- `buyer@example.com`.
- `seller@example.com`.
- One generic user linked to both buyer and seller profiles if the pending shared `users` relationship is intended to support it.
- One inactive user.
- One blocked-equivalent user. There is no explicit `blocked` column, so this should be modeled as inactive unless a blocked field exists later.
- One unverified buyer/seller where verification fields exist.
- One seller with no products.
- One buyer with no orders.

## Pages That Cannot Be Fully Tested Because Data Is Missing

Public and buyer-facing:

- Public catalog/search can test active products after current seeders, but cannot test no-image products, out-of-stock products, inactive category behavior, empty category behavior, normalized image fallback, pagination edge rows, or product moderation-like states.
- Public/product detail can test a basic product but not gallery images, reviews, no-image fallback, long title layout, soft-deleted product safety, or stale order snapshots.
- Buyer dashboard is mostly empty because no orders, addresses, wishlists, notifications, reviews, stock alerts, or cart scenarios are seeded.
- Buyer order history cannot test every status, date grouping, filtering, cancelled/completed/active order states, or deleted product display.
- Buyer cart cannot be seeded directly for the current UI because the runtime cart uses LaraCart session data, not database cart rows. Database cart model tests can still be seeded.
- Buyer profile/address views cannot be fully tested until addresses are seeded.

Seller-facing:

- Seller dashboard lacks realistic products, order status distribution, recent/old orders, low-stock products, reviews, notifications, and seller transaction coverage.
- Seller product index can test basic active products, but not no-products seller, many-products seller, inactive products, no-image products, low-stock/out-of-stock products, pagination, and search/filter edge cases.
- Seller order pages cannot test status tabs/actions, buyer relationships, historical dates, rejected/refunded/disputed orders, or deleted product safety because no orders are seeded.
- Seller transaction pages cannot test deduction/refund totals without seller transaction data.

Admin-facing:

- Admin dashboard has categories/products after current seeders but no orders, activities, user-status mix, moderation-like product/report queues, review queues, notifications, or dispute-like statuses.
- Admin users/buyers/sellers pages cannot test inactive, blocked-equivalent, unverified, and pagination/search edge cases well.
- Admin products pages cannot test inactive/no-image/out-of-stock/soft-deleted/long-title/high-price/min-price edge cases.
- Admin orders pages are empty.
- Admin attributes/categories/countries have base rows but need inactive/empty/filter edge rows.

## Dashboards That Look Empty Today

- Backend dashboard: recent activity list is empty; orders/revenue widgets are empty without orders.
- Buyer dashboard: order metrics, monthly charts, recent orders, addresses, notifications, and reviews are empty.
- Seller dashboard: orders, recent orders, low stock, transactions, reviews, and notification-like data are empty; a seller without products is not represented.
- Admin order dashboards/pages: order tables and status widgets are empty.

## Filters That Cannot Be Fully Tested Today

- Catalog category filters: categories exist, but inactive/empty category scenarios are missing.
- Catalog product filters: cannot test no-image, out-of-stock, low-stock, min-price, high-price, long-title, soft-deleted, and inactive-product behavior.
- Attribute filters: attributes/values exist, but category-aware attribute/product filter coverage may be incomplete because category attributes are not consistently attached.
- Country/location filters: countries exist, but product location variety and inactive country scenarios are limited.
- Admin user filters: missing inactive, blocked-equivalent, unverified, and shared buyer/seller identity rows.
- Admin product filters: missing inactive, no-image, out-of-stock, low-stock, soft-deleted, and extreme price rows.
- Admin/seller/buyer order filters: no seeded orders, so status/payment/date filters cannot be tested.
- Review filters: no seeded reviews, so approved/unapproved filters cannot be tested.
- Product report filters: no seeded reports, so pending/reviewing/resolved/rejected/dismissed filters cannot be tested.
- Wishlist filters/lists: no seeded wishlist rows or wishlist items.
- Notification filters: no seeded notification rows.
- Cart filters/validation: no database cart scenarios, and runtime cart is session-based.

## Edge Cases Missing Today

Missing but supported by current/pending schema:

- Product without image or with missing-image fallback.
- Product with gallery images.
- Product with very long title.
- Product with long description.
- Product with minimum valid price.
- Product with high valid price.
- Product with zero stock.
- Low-stock product.
- Seller with no products.
- Seller with many products.
- Buyer with no orders.
- Buyer with empty cart.
- Buyer with several cart items.
- Cart item for unavailable/inactive product.
- Cart item for out-of-stock product.
- Cart item where product price changed after the cart snapshot.
- Order in every supported status.
- Order in every supported payment status.
- Order item where product was soft-deleted after ordering.
- Order status history across several actor roles.
- Review without optional comment if nullable.
- Approved and unapproved reviews.
- Read and unread notifications.
- Default shipping/billing addresses.
- Inactive category with products.
- Empty category.
- Blocked-equivalent seller with products.
- Inactive buyer/seller.
- Unverified buyer/seller.
- Old and recent orders for charts.
- Seller refunds/deductions.
- Buyer credit add/deduct history.
- Credit attachments.
- Soft-deleted product.

Missing because no schema/model exists:

- Messages/conversations.
- Delivery method choices.
- Static pages.
- Separate payment records.
- Product reports/disputes outside order status.

## Files That Need To Be Created Or Updated

Likely updates:

- `database/factories/UserFactory.php`
- `database/factories/AdminFactory.php`
- `database/factories/BuyerFactory.php`
- `database/factories/SellerFactory.php`
- `database/factories/CategoryFactory.php`
- `database/factories/CountryFactory.php`
- `database/factories/AttributeFactory.php`
- `database/factories/AttributeValueFactory.php`
- `database/factories/ProductFactory.php`
- `database/factories/ProductImageFactory.php`
- `database/factories/OrderFactory.php`
- `database/factories/OrderItemFactory.php`
- `database/factories/OrderStatusHistoryFactory.php`
- `database/factories/CartFactory.php`
- `database/factories/CartItemFactory.php`
- `database/factories/ReviewFactory.php`
- `database/factories/AddressFactory.php`
- `database/factories/NotificationFactory.php`
- `database/factories/BuyerCreditHistoryFactory.php`
- `database/factories/SellerTransactionFactory.php`
- `database/factories/CreditAttachmentFactory.php`
- `database/factories/GlobalSettingsFactory.php`
- `database/seeders/DatabaseSeeder.php`
- `database/seeders/AdminSeeder.php`
- Existing `database/seeders/test_information/*` seeders where reuse is safer than duplicating.

Likely new seeders:

- `database/seeders/Minimal/MinimalAdminSeeder.php` or equivalent production-safe required records.
- `database/seeders/Demo/DemoUserSeeder.php`
- `database/seeders/Demo/DemoCatalogSeeder.php`
- `database/seeders/Demo/DemoProductImageSeeder.php`
- `database/seeders/Demo/DemoCartSeeder.php`
- `database/seeders/Demo/DemoOrderSeeder.php`
- `database/seeders/Demo/DemoReviewSeeder.php`
- `database/seeders/Demo/DemoNotificationSeeder.php`
- `database/seeders/Demo/DemoCreditSeeder.php`
- `database/seeders/Demo/DemoScenarioSeeder.php`

Likely tests:

- `tests/Feature/Factories/ModelFactoriesTest.php`
- `tests/Feature/Seeders/DemoScenarioSeederTest.php`
- Update `tests/Feature/DatabaseSeederTest.php`
- Update existing seeder tests if changed behavior affects their assertions.

Likely documentation:

- `docs/demo-seeding.md`
- `CHANGELOG.md`

## Implementation Guardrails

- Use only existing models and columns.
- Keep demo seeders disabled in production.
- Keep stable records idempotent with `updateOrCreate`/`firstOrCreate`.
- Use factories for volume data instead of manually creating large row sets.
- Preserve old seeder tests where possible by keeping legacy demo users/admins or updating tests intentionally.
- Seed local image paths only; do not use external URLs.
- Keep order rows valid against `OrderStatus` and `OrderPaymentStatus`.
- Do not rely on live product fields for old order display more than the schema allows; current schema has price snapshots but no product title/address snapshot fields.
- Treat `blocked` as inactive unless a real blocked field is added by a future migration.
- Treat `draft`, `published`, `pending moderation`, `approved`, and `rejected` product scenarios as unsupported unless a product status/moderation column exists.
- Document unsupported requested concepts instead of inventing tables.
