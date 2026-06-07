# Database Structure Audit

**Analysis Date:** 2026-06-07  
**Scope:** migrations, live SQLite schema, Eloquent models, relationships, factories, seeders, and database-facing logic.  
**Mode:** analysis only; no schema or application refactor was performed.

## Summary

Birza currently uses a custom marketplace schema with separate tables for admins, buyers, and sellers. There is no canonical `users` table in the live database, no role/permission schema, and no physical tables yet for favorites, reviews, messages, database notifications, normalized addresses, or normalized product images.

The strongest risks before adding more features are:

- Auth defaults still reference `App\Models\User`, but no `App\Models\User` class or live `users` table exists.
- Role behavior is encoded by separate guards/tables instead of a role/permission model.
- Buyer/seller address data is a single string, and orders do not snapshot shipping, billing, delivery, payment, tax, or contact data.
- Orders mix payment, fulfillment, buyer lifecycle, and seller settlement state on one parent row.
- Product attribute data is split across three overlapping pivot tables.
- Product images are split between legacy string columns and a JSON `image_library` column instead of a normalized image table.
- Cart persistence is inconsistent: a `carts` table exists, but runtime buyer cart logic uses LaraCart session state.
- Several important foreign keys, uniqueness constraints, and query indexes are missing or incomplete.

## What Tables Exist Now

### Domain Tables

| Table | Current purpose | Key fields |
| --- | --- | --- |
| `users_admins` | Admin auth users | `id`, `name`, `email`, `email_verified_at`, `password`, `is_active`, `remember_token`, timestamps |
| `users_buyers` | Buyer auth/business users | `id`, `name`, `email`, `email_verified_at`, `password`, `is_verified`, company fields, `address`, `phone`, `credit_balance`, `cart_session_id`, `is_active`, `password_reset_at`, `balance`, `bank_account`, `deleted_at`, timestamps |
| `users_sellers` | Seller auth/business users | `id`, `name`, `email`, `email_verified_at`, `password`, `is_verified`, company fields, `address`, `phone`, `is_active`, `password_reset_at`, `bank_account`, `veterinary_certificate_number`, `deleted_at`, `balance`, timestamps |
| `categories` | Parent/subcategory catalog tree | `id`, `category_name`, `parent_category_id`, `order`, `slug`, `is_active`, `deleted_at`, timestamps |
| `attributes` | Product attribute definitions | `id`, `name`, `type`, `is_filterable`, `is_required`, `is_active` |
| `attribute_values` | Values for product attributes | `id`, `attribute_id`, `value`, `is_active` |
| `countries` | Country list for product origin | `id`, `alpha2`, `region`, `is_active`, `country_name`, `description` |
| `products` | Marketplace products | `id`, `name`, `category_id`, `seller_id`, price/order/stock fields, origin, image fields, status, shelf-life fields, `image_library`, `deleted_at`, timestamps |
| `orders` | Buyer orders | `id`, `buyer_id`, `payment_method`, `payment_status`, `status`, `order_total`, `deleted_at`, timestamps |
| `order_items` | Product lines inside orders | `id`, `order_id`, `product_id`, `seller_id`, `quantity`, `unit_price`, `total_price`, `deleted_at`, timestamps |
| `carts` | Buyer/product cart rows | `id`, `user_id`, `product_id`, `quantity`, timestamps |
| `buyer_credit_history` | Buyer credit ledger | `id`, `buyer_id`, `amount`, `type`, `balance_after`, `admin_id`, `note`, timestamps |
| `credit_attachments` | Files attached to buyer credit history | `id`, `credit_history_id`, `file_path`, `original_name`, timestamps |
| `seller_transactions` | Seller finance ledger | `id`, `seller_id`, `order_id`, `amount`, `type`, `description`, timestamps |
| `global_settings` | Singleton-ish global settings | `id`, `portal_additional_price`, admin theme colors, `admin_spotlight_tags` |
| `activities` | Placeholder activity table | `id`, timestamps |

### Pivot Tables

| Table | Current purpose | Key fields |
| --- | --- | --- |
| `category_attribute` | Category-to-attribute assignment | `id`, `category_id`, `attribute_id` |
| `seller_categories` | Seller category access/interest | `id`, `seller_id`, `category_id`, timestamps |
| `product_attribute` | Product-to-attribute assignment | `id`, `product_id`, `attribute_id` |
| `attribute_product` | Product attribute selected value | `id`, `attribute_id`, `product_id`, `selected_value_id` |
| `product_attribute_value` | Product selected attribute values | `id`, `product_id`, `attribute_id`, `attribute_value_id`, timestamps |

### Framework/System Tables

| Table | Current purpose | Key fields |
| --- | --- | --- |
| `password_reset_tokens` | Shared Laravel password reset token table | `email`, `token`, `created_at` |
| `personal_access_tokens` | Sanctum tokens | `id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, timestamps |
| `failed_jobs` | Failed queue jobs | `id`, `uuid`, `connection`, `queue`, `payload`, `exception`, `failed_at` |
| `cache` | Database cache values | `key`, `value`, `expiration` |
| `cache_locks` | Database cache locks | `key`, `owner`, `expiration` |
| `migrations` | Migration history | `id`, `migration`, `batch` |

## Tables Not Present

| Domain area | Current state |
| --- | --- |
| `users` | Migration exists but is commented out; no live table and no `App\Models\User` class. |
| `roles`, `permissions` | Not present; role logic is handled by separate guards/tables. |
| favorites | Not present. |
| reviews/ratings | Not present. |
| messages/conversations | Not present. |
| database notifications | Not present; models use `Notifiable`, but there is no `notifications` table. |
| addresses | Not present; buyer/seller address is stored as one string. |
| product images/media | Not present; images live in `products.product_image`, `products.product_additional_image`, and `products.image_library`. |
| status history | Not present for orders, order items, products, buyers, or sellers. |

## Relationships That Exist Now

### Database Foreign Keys

- `categories.parent_category_id` -> `categories.id` with cascade delete.
- `attribute_values.attribute_id` -> `attributes.id`.
- `category_attribute.category_id` -> `categories.id` with cascade delete.
- `category_attribute.attribute_id` -> `attributes.id` with cascade delete.
- `products.category_id` -> `categories.id`.
- `products.seller_id` -> `users_sellers.id`.
- `products.country_of_origin` -> `countries.id`.
- `orders.buyer_id` -> `users_buyers.id`.
- `order_items.order_id` -> `orders.id`.
- `order_items.product_id` -> `products.id`.
- `order_items.seller_id` -> `users_sellers.id`.
- `carts.user_id` -> `users_buyers.id` with cascade delete.
- `carts.product_id` -> `products.id` with cascade delete.
- `buyer_credit_history.buyer_id` -> `users_buyers.id` with cascade delete.
- `credit_attachments.credit_history_id` -> `buyer_credit_history.id` with cascade delete.
- `seller_transactions.seller_id` -> `users_sellers.id`.
- `seller_transactions.order_id` -> `orders.id`.
- `seller_categories.seller_id` -> `users_sellers.id` with cascade delete.
- `seller_categories.category_id` -> `categories.id` with cascade delete.
- `product_attribute.product_id` -> `products.id` with cascade delete.
- `product_attribute.attribute_id` -> `attributes.id` with cascade delete.
- `attribute_product.attribute_id` -> `attributes.id`.
- `attribute_product.product_id` -> `products.id`.
- `attribute_product.selected_value_id` -> `attribute_values.id`.
- `product_attribute_value.product_id` -> `products.id`.
- `product_attribute_value.attribute_id` -> `attributes.id`.
- `product_attribute_value.attribute_value_id` -> `attribute_values.id`.

### Eloquent Relationships

- `Buyer` has many `orders` and `creditHistory`.
- `Seller` has many `transactions`, has many `products`, belongs to many `categories`, and has many `orders` through `order_items`.
- `Product` belongs to `category`, `seller`, and `country`; has many `orderItems`; belongs to many `AttributeValue` through `product_attribute_value`.
- `Category` belongs to parent category, has many subcategories, has many products, belongs to many attributes, and belongs to many sellers.
- `Attribute` has many values, belongs to many categories, and attempts to belong to many products through `product_attribute_value`.
- `AttributeValue` belongs to an attribute and belongs to many products through `product_attribute_value`.
- `Order` belongs to buyer, has many `orderItems`, has many `items`, belongs to many products through `order_items`, and has a seller traversal through `order_items`.
- `OrderItem` belongs to order, product, and seller.
- `Cart` belongs to buyer through `user_id` and belongs to product.
- `BuyerCreditHistory` belongs to buyer and admin, and has many attachments.
- `CreditAttachment` belongs to buyer credit history.
- `SellerTransaction` belongs to seller and order.
- `Country` has many products.

## What Fields Are Missing

### Users, Roles, Permissions

- A canonical `users` table/model, or removal of the stale `web` guard/provider.
- Role and permission tables if admins need more than one permission level.
- A clear cross-table identity strategy if a person can be both buyer and seller.
- Audit fields for activation/deactivation decisions.
- Unique constraints for company identity fields such as `company_code` and `vat_code`, if those must be unique.

### Buyers, Sellers, Addresses

- Normalized `addresses` table with owner type/id, type, country, city, postal code, street, company/contact fields, default flag, and timestamps.
- Buyer/seller profile fields are duplicated and drift-prone; no shared contact/address model exists.
- Seller verification metadata is minimal; no verification status history, document table, or rejection reason.

### Products, Categories, Images

- Normalized `product_images` table with product FK, disk/path, original name, alt text, sort order, primary flag, metadata, and timestamps.
- Product `slug`/SKU fields are missing from `products`.
- Product status is a boolean only; there is no draft/published/archived/rejected status model.
- Category model does not use/cast all table fields (`is_active`, timestamps, soft deletes) consistently.

### Orders, Cart, Statuses

- Order address snapshot fields are missing: billing/shipping address, buyer contact, delivery method, delivery price, payment reference, tax/VAT breakdown.
- Seller-level fulfillment/payment state is missing for multi-seller orders.
- Order status history is missing.
- `order_items` has no item-level fulfillment status.
- `carts` has no guest/session identifier and no price/options snapshot.
- Runtime checkout uses LaraCart session data, so the `carts` table is not the authoritative buyer cart.

### Favorites, Reviews, Messages, Notifications

- Favorites need at least buyer/product uniqueness and timestamps.
- Reviews need buyer/product/order linkage, rating, text, moderation status, and uniqueness rules.
- Messages need conversation, participants, messages, read states, and attachments.
- Database notifications need Laravel's `notifications` table if in-app notification history is expected.

## Indexes That Exist Now

- Auth: unique email indexes on `users_admins`, `users_buyers`, `users_sellers`.
- Category browsing: `categories(parent_category_id, is_active)`.
- Attribute filtering: `attributes(is_active, is_filterable)`.
- Catalog filtering: `products(category_id, is_active)`, `products(seller_id, is_active)`, `products(country_of_origin, is_active)`, `products(price)`, `products(stock)`, `products(is_active, is_organic)`, `products(is_active, created_at)`, `products(deleted_at, created_at)`.
- Order filtering: `orders(buyer_id, status)`, `orders(buyer_id, payment_status)`, `orders(status, created_at)`.
- Seller orders: `order_items(order_id, seller_id)`, `order_items(seller_id, created_at)`.
- Cart: `carts(user_id, created_at)`.
- Buyer credit: `buyer_credit_history(buyer_id, created_at)`, `buyer_credit_history(admin_id)`.
- Seller transactions: `seller_transactions(seller_id, created_at)`, `seller_transactions(order_id, created_at)`.
- Pivots: unique `category_attribute(category_id, attribute_id)`, unique `seller_categories(seller_id, category_id)`, unique `product_attribute(product_id, attribute_id)`, unique `attribute_product(attribute_id, product_id, selected_value_id)`, and non-unique `product_attribute_value(product_id, attribute_id, attribute_value_id)`.
- Framework: Sanctum token indexes, failed job UUID, cache primary keys.

## Indexes That Are Missing Or Weak

- `carts(user_id, product_id)` should be unique if the table remains a cart-items table.
- `product_attribute_value(product_id, attribute_id, attribute_value_id)` should likely be unique; the current index allows duplicates.
- Reverse pivot indexes are missing for `category_attribute(attribute_id, category_id)` and `seller_categories(category_id, seller_id)`.
- `attribute_values(attribute_id, is_active)` is missing for active value loading.
- `orders(buyer_id, created_at)` or `orders(buyer_id, payment_status, created_at)` is missing for buyer order history sorted by date.
- `order_items(product_id)` is missing for product sales/history lookups.
- `products(seller_id, deleted_at, created_at)` is missing for seller product management with archived products.
- `products(category_id, is_active, price)` and `products(category_id, is_active, stock)` may be useful if catalog range filters grow.
- `users_buyers(is_active, created_at)` and `users_sellers(is_active, created_at)` are missing for admin user lists.
- No search-oriented index exists for product/category autocomplete; current JSON and wildcard matching cannot use ordinary B-tree indexes well.

## Foreign Keys That Are Missing Or Weak

- `buyer_credit_history.admin_id` is indexed but not constrained to `users_admins.id`.
- `attribute_product` FKs have no cascade behavior, unlike `product_attribute`.
- `product_attribute_value` FKs are nullable and have no cascade behavior, so orphaned rows are easier to create.
- `order_items` FKs have no cascade behavior; this may be intentional with soft deletes, but it should be documented.
- `seller_transactions` FKs have no cascade behavior; this may be intentional for ledger integrity, but it should be documented.
- `password_reset_tokens.email` is shared across guards with no guard/provider discriminator.
- There are no FKs for the file paths stored in product images or credit attachments; file integrity is entirely application-managed.

## Dangerous Or Inconsistent Structure

### Authentication and Roles

- `config/auth.php` still defines default `web` auth using `App\Models\User`, but no such model or table exists.
- The base Laravel `users` migration is marked as ran but its table creation is commented out.
- There are two admin model classes: `App\Models\Admin` and `App\Models\Users\Admin`.
- There is no roles/permissions schema; admin access is all-or-nothing behind `auth:admin`.

### User Models

- `Buyer` and `Seller` define both `$fillable` and `$guarded`, which is confusing and can hide mass-assignment intent.
- Buyer has both `credit_balance` and `balance`; seller has `balance`; naming is inconsistent across ledger concepts.
- `remember_token` is used by auth flows beyond remember-me semantics.

### Product Attributes

- Three product-attribute tables exist: `product_attribute`, `attribute_product`, and `product_attribute_value`.
- Runtime product filtering uses `product_attribute_value`.
- `ProductAttributeSeeder` writes `attribute_product`.
- `product_attribute` appears to be a simpler legacy assignment table.
- `Product::attributes()` returns `AttributeValue` objects, not `Attribute` objects, despite the method name.

### Orders and Settlement

- `orders.status` and `orders.payment_status` are both free strings and can diverge.
- `Order::STATUS` is a PHP array, not a database enum/check constraint or PHP enum cast.
- One order can include items from multiple sellers, but seller screens update parent order status globally.
- `Order` defines `product()` and `country()` relationships for columns not present on `orders`.
- Seller ledger type is a free string and is not constrained.

### Cart

- The `carts` table exists and has a model/factory, but buyer cart and checkout logic currently use LaraCart session state.
- `users_buyers.cart_session_id` exists for LaraCart, reinforcing that the DB `carts` table is not the runtime source of truth.
- This split makes future guest/authenticated cart behavior unclear.

### Images and Files

- Products keep legacy image columns plus JSON `image_library`.
- There is no normalized image model/table for ordering, alt text, ownership, variants, or cleanup.
- Credit attachments store file paths without a private-storage schema distinction in the table.

### Migrations and Seeders

- Multiple migrations have no `down()` method.
- `password_reset_at` has duplicate migration history.
- `UserFactory` references `App\Models\User`, which does not exist.
- `BuyerCreditHistoryFactory` uses `credit`/`debit`, while runtime methods create `add`/`deduct`.
- `OrderFactory::paid()` updates `status` but not necessarily `payment_status`.
- `AttributesSeeder` assigns `$timestamp = now()` twice, which is harmless but indicates cleanup drift.

## What Should Be Improved Before Adding More Features

1. Decide the user identity strategy.
   - Either remove the stale default `web` user path or introduce a real canonical user model/table.
   - If admins need different abilities, add role/permission tables and policies before expanding the admin panel.

2. Normalize addresses before delivery, checkout, invoices, or B2B profile features.
   - Add address records and snapshot addresses onto orders at checkout.

3. Clarify order boundaries before payment, seller fulfillment, refunds, or multi-seller expansion.
   - Either enforce one seller per order or add seller-scoped order/fulfillment records.
   - Add status history and item/seller-level statuses.

4. Choose one cart persistence strategy.
   - Keep LaraCart behind a service and remove or repurpose `carts`, or make the DB cart table authoritative.
   - Add uniqueness and guest/session support if using the DB table.

5. Consolidate product attribute storage.
   - Pick either `product_attribute_value` or a replacement canonical table.
   - Migrate data from legacy pivots and remove unused models/tables in a planned cleanup.

6. Normalize product images.
   - Add a product images/media table before adding image galleries, variants, moderation, or CDN behavior.

7. Add missing constraints and indexes in additive migrations.
   - Prioritize unique cart rows, unique product attribute values, `buyer_credit_history.admin_id` FK, reverse pivot indexes, order date indexes, and admin list indexes.

8. Replace string statuses with enums or constrained values.
   - Use PHP enums/casts and database constraints where supported.
   - Keep `status` and `payment_status` meanings separate.

9. Repair factories and seeders before relying on generated data for new features.
   - Remove stale `UserFactory`, align ledger/status values, and stop seeding two different attribute pivot strategies.

10. Add migration integrity tests.
    - Verify clean migrate, seed, and representative relationship loading.
    - Add targeted tests for any future cleanup migration.

## Suggested Test Coverage For Future Schema Work

- Clean migrate + seed smoke test.
- Auth provider smoke tests for every configured guard.
- Relationship tests for buyer orders, seller orders, product attributes, product images, cart items, and ledger records.
- Constraint tests for duplicate cart rows, duplicate product attribute values, and invalid statuses.
- Checkout/order tests covering multi-seller orders, order snapshots, seller settlement, and status history.
- Seeder idempotency tests for all demo seeders.

