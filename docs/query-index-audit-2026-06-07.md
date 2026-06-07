# Query Index Audit - 2026-06-07

## Scope

This audit reviewed committed query usage in Livewire pages, filters, model scopes, support classes, migrations, and existing index definitions.

The requested fields checked were `status`, `slug`, `user_id`, `seller_id`, `buyer_id`, `product_id`, `category_id`, `order_id`, `price`, `created_at`, `published_at`, `deleted_at`, `is_active`, `is_featured`, `city`, and `country`.

## Existing Index Coverage Kept

| Area | Existing useful indexes |
| --- | --- |
| Product catalog | `products_category_active_idx`, `products_seller_active_idx`, `products_country_active_idx`, `products_price_idx`, `products_stock_idx`, `products_active_organic_idx`, `products_active_created_at_idx`, `products_deleted_created_at_idx` |
| Orders | `orders_buyer_status_idx`, `orders_buyer_payment_status_idx`, `orders_status_created_at_idx` |
| Order items | `order_items_order_seller_idx`, `order_items_seller_created_at_idx` |
| Cart and normalized cart items | `carts_user_created_at_idx`, `cart_items_cart_id_product_id_unique` |
| Product images, reviews, notifications, addresses | Existing relationship and user/date indexes from the relationship-standardization migrations |

## Indexes Added

| Table | Index | Columns | Query evidence |
| --- | --- | --- | --- |
| `orders` | `orders_buyer_created_at_idx` | `buyer_id`, `created_at` | Buyer order lists and dashboards load a buyer's orders ordered by newest date, sometimes without a status filter. |
| `orders` | `orders_buyer_payment_created_at_idx` | `buyer_id`, `payment_status`, `created_at` | Buyer/admin buyer order screens filter by buyer, payment status, date range, and newest order. |
| `orders` | `orders_payment_created_at_idx` | `payment_status`, `created_at` | Admin orders filter by payment status and date range, and dashboard stats aggregate by paid/pending payment status. |
| `order_items` | `order_items_seller_order_idx` | `seller_id`, `order_id` | Seller order authorization and seller order item lookups filter by seller plus order. |
| `order_items` | `order_items_order_product_idx` | `order_id`, `product_id` | Order item relationship loading and order/product line lookups need the order/product pair. |
| `buyer_credit_history` | `buyer_credit_history_buyer_type_created_idx` | `buyer_id`, `type`, `created_at` | Buyer credit history pages and CSV export filter by buyer, type, and date range before ordering by newest date. |
| `seller_transactions` | `seller_transactions_seller_type_created_idx` | `seller_id`, `type`, `created_at` | Seller transaction history filters by seller, type, date range, and newest transaction; stats aggregate seller/type totals. |
| `users_buyers` | `users_buyers_active_created_at_idx` | `is_active`, `created_at` | Admin buyer list and home stats filter active buyers; default list sorting is newest created. |
| `users_buyers` | `users_buyers_verified_created_at_idx` | `is_verified`, `created_at` | Admin buyer list filters verified/unverified buyers and sorts by created date. |
| `users_buyers` | `users_buyers_credit_balance_idx` | `credit_balance` | Admin buyer list filters and sorts credit balance ranges. |
| `users_sellers` | `users_sellers_active_created_at_idx` | `is_active`, `created_at` | Admin seller list and home stats filter active sellers; default list sorting is newest created. |
| `categories` | `categories_parent_order_idx` | `parent_category_id`, `order`, `id` | Category index and selector screens filter root/child categories and order by parent, display order, and id. |
| `countries` | `countries_region_active_alpha2_idx` | `region`, `is_active`, `alpha2` | Product forms and country selectors repeatedly load active European countries ordered by code. |
| `attribute_values` | `attribute_values_attribute_active_idx` | `attribute_id`, `is_active` | Product filters and attribute value admin pages filter values by attribute and active status. |

## Intentionally Skipped

- `slug`: only stored on categories; no committed route, controller, Livewire page, or scope queries by slug.
- `published_at`: no committed column found.
- `is_featured`: no committed column found.
- `city` and address `country_code`: address factories/migrations define them, but committed application query paths do not filter or sort addresses by city/country yet.
- Text contains searches such as `LIKE "%term%"` on names, translated JSON, email, company, and phone fields: ordinary b-tree indexes will not help leading-wildcard searches. Full-text/search-specific work should be designed separately.
- Product filter columns that already have usable indexes were not duplicated with every possible composite permutation.

## Verification

- `php artisan test --compact tests/Unit/Database/QueryDrivenIndexTest.php`
- The focused test uses Laravel's schema inspection API to assert every added index name and column order.
