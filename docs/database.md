# Database Guide

This guide describes the current Birza database shape from migrations and the Laravel Boost schema snapshot on 2026-06-07.

## Local Database

Local default:

```env
DB_CONNECTION=sqlite
DB_DATABASE=birza.sqlite
DB_FOREIGN_KEYS=true
```

Useful commands:

```bash
php artisan migrate
php artisan migrate:status --no-interaction
php artisan db:seed
php artisan migrate:fresh --seed
```

`migrate:fresh --seed` deletes local data. Do not use it in production.

## Main Entities

| Area | Tables |
| --- | --- |
| Accounts | `users`, `users_admins`, `users_buyers`, `users_sellers`, `addresses` |
| Catalog | `products`, `product_images`, `categories`, `countries`, `attributes`, `attribute_values` |
| Product attributes | `product_attribute`, `attribute_product`, `product_attribute_value`, `category_attribute`, `seller_categories` |
| Cart | `carts`, `cart_items`, `cart_bundle_items` |
| Orders | `orders`, `order_items`, `order_bundles`, `order_status_histories` |
| Promotions | `discounts`, `promo_codes`, `promo_code_redemptions` |
| Buyer features | `wishlists`, `wishlist_items`, `product_stock_alerts` |
| Community/moderation | `reviews`, `product_questions`, `product_reports` |
| Messaging | `conversations`, `messages` |
| Notifications/audit | `notifications`, `admin_actions`, `audit_logs`, `activities` |
| Support | `global_settings`, `cache`, `cache_locks`, `failed_jobs`, `password_reset_tokens`, `personal_access_tokens`, `migrations` |

## Key Relationships

- Buyers have many carts, orders, wishlists, stock alerts, addresses, product questions, reports, conversations, and notifications.
- Sellers have many products, discounts, promo codes, product bundles, seller transactions, product questions, conversations, and notifications.
- Products belong to sellers, categories, and countries.
- Products have many product images, cart items, order items, reviews, reports, questions, wishlist items, stock alerts, discounts, and bundle items.
- Orders belong to buyers and have many order items, order bundles, status histories, promo redemptions, audit logs, and notifications.
- Order items keep product, seller, price, and discount snapshots.
- Promo codes belong to sellers and record redemptions.
- Conversations belong to one buyer and one seller, may link to a product or order, and contain messages from buyer, seller, or policy-authorized admin context.
- Messages belong to one conversation and store sender identity, sender role, read state, optional edit state, metadata, and escaped plain-text body content.
- Audit logs are polymorphic around actors and audited entities.
- Notifications are standard Laravel polymorphic database notifications.

## Money Fields

Money uses decimal columns. Keep money arithmetic on the backend and never trust frontend totals.

Important money fields:

- `products.price`
- `products.min_order_price`
- `carts.cart_items.unit_price`
- `orders.subtotal`
- `orders.discount_total`
- `orders.promo_discount_amount`
- `orders.order_total`
- `order_items.unit_price`
- `order_items.total_price`
- `order_items.original_unit_price`
- `order_items.discount_amount`
- `order_items.final_unit_price`
- `discounts.value`
- `promo_codes.value`
- `promo_code_redemptions.discount_amount`
- `buyer_credit_history.amount`
- `buyer_credit_history.balance_after`
- `seller_transactions.amount`

## Status Fields

Status fields are strings or enum-cast strings in PHP. Use enum/constants from models where they exist.

Examples:

- `orders.status`
- `orders.payment_status`
- `order_status_histories.old_status`
- `order_status_histories.new_status`
- `carts.status`
- `discounts.status`
- `promo_codes.status`
- `product_bundles.status`
- `product_questions.status`
- `product_reports.status`
- `product_stock_alerts.status`
- `conversations.status`

Do not display raw status values. Use translation keys and helper methods.

## Snapshot Rules

Orders must preserve historical checkout data even if products, buyers, sellers, or addresses later change.

Snapshot fields include:

- `orders.shipping_address_snapshot`
- `orders.billing_address_snapshot`
- `orders.promo_code`
- `orders.promo_discount_amount`
- `order_items.product_title_snapshot`
- `order_items.product_price_snapshot`
- `order_items.seller_name_snapshot`
- `order_items.original_unit_price`
- `order_items.discount_amount`
- `order_items.final_unit_price`
- `order_items.discount_source`
- `order_bundles.bundle_name_snapshot`
- `order_bundles.products_snapshot`

## Soft Deletes

Soft-delete tables include products, buyers, sellers, categories, reviews, discounts, promo codes, product bundles, product questions, product reports, wishlists, and orders where migrations/models define it.

Rules:

- Include trashed related records only when history needs them.
- Do not delete order history when products or users are soft-deleted.
- Do not delete product images on soft delete; force-delete paths only through controlled image deletion logic.

## Slugs

Slug fields exist on categories, wishlists, and product bundles. Keep slugs stable enough for route/model lookups. Avoid duplicate slug generation in controllers or views.

## Indexes

Current query-driven indexes cover common paths such as:

- buyer orders by buyer/status/date
- seller order items by seller/order
- category hierarchy ordering
- catalog filters by category/country/active/organic/stock/price
- product primary image lookup
- notification notifiable/read/latest lookups
- credit and transaction filters
- product questions/reports status filters
- conversation buyer/seller activity filters
- message conversation/read/date filters
- wishlists and wishlist item uniqueness
- promo code uniqueness and redemption lookups

See [query index audit](query-index-audit-2026-06-07.md) for historical detail.

## Factories And Seeders

Factories exist for the main marketplace models and should be used in tests. Seeders are split into production-safe minimal data and local demo data.

See [seeders guide](seeders.md) and [demo seeding guide](demo-seeding.md).

## Current Caveats

- Product attribute data still has multiple pivot tables. Future work should keep one canonical path and document it.
- Legacy product image fields still exist alongside `product_images`.
- Payment provider records are not modeled separately.
- No database constraints enforce every enum/status value; PHP enums/constants remain the main guard.
