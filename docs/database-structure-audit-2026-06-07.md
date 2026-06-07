# Database Structure Audit - 2026-06-07

## Scope

This audit inspected the current Laravel database structure for users, roles, buyers, sellers, products, categories, orders, cart, favorites, reviews, messages, notifications, images, addresses, and statuses.

Sources checked:

- Live SQLite schema through Laravel Boost `database-schema`
- Laravel application info through Laravel Boost `application-info`
- Version-specific Laravel docs through Laravel Boost `search-docs`
- `php artisan migrate:status --no-interaction`
- `php artisan db:show --no-interaction`
- `php artisan config:show database.default --no-interaction`
- `php artisan config:show database.connections.sqlite.foreign_key_constraints --no-interaction`
- All files under `database/migrations`, `database/factories`, and `database/seeders`
- Models under `app/Models`
- Current database-heavy application logic in actions and Livewire components

No refactors or schema changes were made.

## Runtime Snapshot

- Framework: Laravel 12.61.1
- PHP: 8.5
- Database: SQLite 3.45.2
- Connection: `sqlite`
- Database file: `database/birza.sqlite`
- Foreign key constraints config: enabled
- Live tables: 27
- Migration status: all migrations are marked as ran

## Existing Tables

| Table | Fields | Indexes | Foreign keys |
| --- | --- | --- | --- |
| `activities` | `id`, `created_at`, `updated_at` | primary `id` | none |
| `attribute_product` | `id`, `attribute_id`, `product_id`, `selected_value_id` | primary `id`; unique `attribute_id, product_id, selected_value_id` | `attribute_id -> attributes.id`; `product_id -> products.id`; `selected_value_id -> attribute_values.id` |
| `attribute_values` | `id`, `attribute_id`, `value`, `is_active` | primary `id` | `attribute_id -> attributes.id` |
| `attributes` | `id`, `name`, `type`, `is_filterable`, `is_required`, `is_active` | primary `id`; `is_active, is_filterable` | none |
| `buyer_credit_history` | `id`, `buyer_id`, `amount`, `type`, `balance_after`, `admin_id`, `note`, `created_at`, `updated_at` | primary `id`; `buyer_id, created_at`; `admin_id` | `buyer_id -> users_buyers.id` |
| `cache` | `key`, `value`, `expiration` | primary `key` | none |
| `cache_locks` | `key`, `owner`, `expiration` | primary `key` | none |
| `carts` | `id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at` | primary `id`; `user_id, created_at` | `user_id -> users_buyers.id`; `product_id -> products.id` |
| `categories` | `id`, `category_name`, `parent_category_id`, `order`, `slug`, `is_active`, `created_at`, `updated_at`, `deleted_at` | primary `id`; `parent_category_id, is_active` | `parent_category_id -> categories.id` with cascade delete |
| `category_attribute` | `id`, `category_id`, `attribute_id` | primary `id`; unique `category_id, attribute_id` | `category_id -> categories.id`; `attribute_id -> attributes.id`, both cascade delete |
| `countries` | `id`, `alpha2`, `region`, `is_active`, `country_name`, `description` | primary `id`; unique `alpha2`; unique `country_name` | none |
| `credit_attachments` | `id`, `credit_history_id`, `file_path`, `original_name`, `created_at`, `updated_at` | primary `id` | `credit_history_id -> buyer_credit_history.id` with cascade delete |
| `failed_jobs` | `id`, `uuid`, `connection`, `queue`, `payload`, `exception`, `failed_at` | primary `id`; unique `uuid` | none |
| `global_settings` | `id`, `portal_additional_price`, `admin_primary_color`, `admin_accent_color`, `admin_surface_color`, `admin_spotlight_tags` | primary `id` | none |
| `migrations` | `id`, `migration`, `batch` | primary `id` | none |
| `order_items` | `id`, `order_id`, `product_id`, `seller_id`, `quantity`, `unit_price`, `total_price`, `deleted_at`, `created_at`, `updated_at` | primary `id`; `order_id, seller_id`; `seller_id, created_at` | `order_id -> orders.id`; `product_id -> products.id`; `seller_id -> users_sellers.id` |
| `orders` | `id`, `buyer_id`, `payment_method`, `payment_status`, `status`, `order_total`, `created_at`, `updated_at`, `deleted_at` | primary `id`; `buyer_id, payment_status`; `buyer_id, status`; `status, created_at` | `buyer_id -> users_buyers.id` |
| `password_reset_tokens` | `email`, `token`, `created_at` | primary `email` | none |
| `personal_access_tokens` | `id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at` | primary `id`; unique `token`; `tokenable_type, tokenable_id` | none, polymorphic |
| `product_attribute` | `id`, `product_id`, `attribute_id` | primary `id`; unique `product_id, attribute_id` | `product_id -> products.id`; `attribute_id -> attributes.id`, both cascade delete |
| `product_attribute_value` | `id`, `product_id`, `attribute_id`, `attribute_value_id`, `created_at`, `updated_at` | primary `id`; `product_id, attribute_id, attribute_value_id` | `product_id -> products.id`; `attribute_id -> attributes.id`; `attribute_value_id -> attribute_values.id` |
| `products` | `id`, `name`, `category_id`, `seller_id`, `price`, `min_order_price`, `min_order_count`, `stock`, `description`, `unit`, `package_weight`, `price_per_liter`, `is_organic`, `country_of_origin`, `product_image`, `product_additional_image`, `is_active`, `deleted_at`, `created_at`, `updated_at`, `temperature_conditions_from`, `temperature_conditions_to`, `use_until`, `total_shelf_life`, `pack_type`, `image_library` | primary `id`; `is_active, created_at`; `is_active, is_organic`; `category_id, is_active`; `country_of_origin, is_active`; `deleted_at, created_at`; `price`; `seller_id, is_active`; `stock` | `category_id -> categories.id`; `seller_id -> users_sellers.id`; `country_of_origin -> countries.id` |
| `seller_categories` | `id`, `seller_id`, `category_id`, `created_at`, `updated_at` | primary `id`; unique `seller_id, category_id` | `seller_id -> users_sellers.id`; `category_id -> categories.id`, both cascade delete |
| `seller_transactions` | `id`, `seller_id`, `order_id`, `amount`, `type`, `description`, `created_at`, `updated_at` | primary `id`; `seller_id, created_at`; `order_id, created_at` | `seller_id -> users_sellers.id`; `order_id -> orders.id` |
| `users_admins` | `id`, `name`, `email`, `email_verified_at`, `password`, `is_active`, `remember_token`, `created_at`, `updated_at` | primary `id`; unique `email` | none |
| `users_buyers` | `id`, `name`, `email`, `email_verified_at`, `password`, `is_verified`, `company_name`, `company_code`, `vat_code`, `address`, `phone`, `remember_token`, `created_at`, `updated_at`, `credit_balance`, `cart_session_id`, `is_active`, `password_reset_at`, `balance`, `bank_account`, `deleted_at` | primary `id`; unique `email` | none |
| `users_sellers` | `id`, `name`, `email`, `email_verified_at`, `password`, `is_verified`, `company_name`, `company_code`, `vat_code`, `address`, `phone`, `remember_token`, `created_at`, `updated_at`, `is_active`, `password_reset_at`, `bank_account`, `veterinary_certificate_number`, `deleted_at`, `balance` | primary `id`; unique `email` | none |

## Requested Domain Coverage

| Domain | Current structure |
| --- | --- |
| Users | No generic `users` table. `2014_10_12_000000_create_users_table.php` has the default schema commented out. The app uses `users_admins`, `users_buyers`, and `users_sellers`. |
| Roles | No `roles`, `permissions`, `model_has_roles`, or equivalent role pivot tables. Roles are implicit through separate guards/tables. |
| Buyers | `users_buyers`, `buyer_credit_history`, `credit_attachments`, and `orders.buyer_id`. |
| Sellers | `users_sellers`, `seller_categories`, `products.seller_id`, `order_items.seller_id`, and `seller_transactions`. |
| Products | `products`, `product_attribute`, `attribute_product`, `product_attribute_value`, `attribute_values`, `attributes`, images stored as product columns/JSON. |
| Categories | `categories`, self parent relation, `category_attribute`, `seller_categories`. |
| Orders | `orders` plus `order_items`; order status and payment status are strings. |
| Cart | `carts` table exists, but inspected buyer cart flow uses LaraCart session state and writes to `orders`/`order_items` at checkout. |
| Favorites | No table, model, migration, factory, or seeder found. |
| Reviews | No table, model, migration, factory, or seeder found. |
| Messages | No marketplace message table/model found. Only translation/message keys and UI copy references exist. |
| Notifications | No Laravel `notifications` table found. Notifications are currently mail/view/UI notification code, not persisted database notifications. |
| Images | No normalized `images` table. Product images live in `products.product_image`, `products.product_additional_image`, and `products.image_library` JSON. Credit attachment files live in `credit_attachments`. |
| Addresses | No normalized `addresses` table. Buyer/seller addresses are single nullable strings. Orders do not store shipping/billing address snapshots. |
| Statuses | No `statuses` lookup table. `App\Enums\OrderStatus` now centrally defines and casts order lifecycle values for `orders.status` and `orders.payment_status`; buyer credit type and seller transaction type remain plain strings. |

## Existing Model Relationships

### Account Models

- `App\Models\Users\Buyer`
  - `orders()` has many `Order` by `buyer_id`
  - `creditHistory()` has many `BuyerCreditHistory` by `buyer_id`
  - Uses `Notifiable`, `HasApiTokens`, and `SoftDeletes`
- `App\Models\Users\Seller`
  - `transactions()` has many `SellerTransaction`
  - `categories()` belongs to many `Category` through `seller_categories`
  - `products()` has many `Product`
  - `orders()` has many through `OrderItem`
  - Uses `Notifiable` and `SoftDeletes`
- `App\Models\Users\Admin`
  - No relationships declared
- `App\Models\Admin`
  - Duplicate admin model pointing at the same `users_admins` table
  - No relationships declared

### Catalog Models

- `Product`
  - `category()` belongs to `Category`
  - `seller()` belongs to `Users\Seller`
  - `country()` belongs to `Country` through `country_of_origin`
  - `attributeValues()` belongs to many `AttributeValue` through `product_attribute_value`
  - `attributes()` currently also returns a belongs-to-many `AttributeValue` relation through `product_attribute_value`
  - `orderItems()` has many `OrderItem`
- `Category`
  - `products()` has many `Product`
  - `parent()` belongs to `Category`
  - `subcategories()` has many `Category`
  - `attributes()` belongs to many `Attribute` through `category_attribute`
  - `sellers()` belongs to many `Users\Seller` through `seller_categories`
- `Attribute`
  - `values()` has many `AttributeValue`
  - `categories()` belongs to many `Category` through `category_attribute`
  - `products()` belongs to many `Product` through `product_attribute_value`
- `AttributeValue`
  - `attribute()` belongs to `Attribute`
  - `products()` belongs to many `Product` through `product_attribute_value`
- `Country`
  - `products()` has many `Product` through `country_of_origin`

### Commerce Models

- `Order`
  - `buyer()` belongs to `Users\Buyer` with trashed buyers included
  - `sellers()` has many through `OrderItem`
  - `orderItems()` has many `OrderItem`
  - `items()` duplicates `orderItems()`
  - `products()` belongs to many `Product` through `order_items`
  - `product()` belongs to `Product`, but `orders` has no `product_id` column
  - `country()` belongs to `Country`, but `orders` has no `country_of_origin` column
- `OrderItem`
  - `order()` belongs to `Order`
  - `product()` belongs to `Product` with trashed products included
  - `seller()` belongs to `Users\Seller`
- `Cart`
  - `user()` belongs to `Users\Buyer` by `user_id`
  - `product()` belongs to `Product`
- `SellerTransaction`
  - `seller()` belongs to `Users\Seller`
  - `order()` belongs to `Order`
- `BuyerCreditHistory`
  - `buyer()` belongs to `Users\Buyer`
  - `admin()` belongs to `Users\Admin`
  - `attachments()` has many `CreditAttachment`
- `CreditAttachment`
  - `creditHistory()` belongs to `BuyerCreditHistory`

## Factories And Seeders

Factories exist for:

- Admins, buyers, sellers
- Products, categories, countries
- Attributes and attribute values
- Orders and order items
- Carts
- Buyer credit history and credit attachments
- Seller transactions
- Global settings
- Activities

Seeders exist for:

- Admin account
- Countries
- Categories
- Global settings
- Test buyers and sellers
- Products
- Attributes and product attribute values
- Attribute-to-product selections

Seeder risks:

- `DatabaseSeeder` uses `DB::disableQueryLog()`. This is not raw SQL, but it is still direct DB facade usage in a seeder.
- `TestUsersSeeder` writes buyer and seller passwords as plain strings. The models have `password => hashed` casts, so this relies on casts working during seeding.
- `BuyerCreditHistoryFactory` uses `credit` and `debit`, while `Buyer::addCredit()` and `Buyer::deductCredit()` write `add` and `deduct`. The migration comment also says `add` or `deduct`.
- `SellerTransactionFactory` uses `sale`, `refund`, and `commission`, while seller transaction UI stats look for `deduction` and `refund`.
- `ProductAttributeSeeder` populates `attribute_product`, while `AttributesSeeder` populates `product_attribute_value`. Both appear active, so product attributes have split sources of truth.
- `UserFactory` still describes an `App\Models\User` default factory, but no `users` table and no `App\Models\User` model exist in the live app structure.

## Missing Tables And Fields

### Missing Tables

- `users` if the product should use one canonical user table.
- `roles` and permission/role pivot tables if role assignment should become data-driven.
- `favorites`.
- `reviews`.
- `messages` or conversation/message tables.
- `notifications` if database notifications should be stored.
- `images` or media table for reusable uploaded media.
- `addresses` for reusable buyer/seller/shipping/billing addresses.
- `statuses` or equivalent lookup table if statuses should be configurable.

### Missing Or Weak Fields In Existing Tables

- `orders`
  - No order number/reference field separate from integer `id`.
  - No shipping address, billing address, company, phone, or VAT snapshot.
  - No currency, VAT amount, portal fee amount, subtotal, discount, or payment provider reference.
  - No cancelled/refunded/paid/shipped/delivered timestamps.
  - No status actor/audit fields.
- `order_items`
  - No product name, SKU, unit, tax, seller company, or other immutable line snapshot.
  - No currency or tax fields.
- `products`
  - No SKU/code/slug.
  - No normalized image relation.
  - No explicit owner status/review workflow fields.
  - No check constraints for non-negative `price`, `stock`, `min_order_count`, or shelf-life fields.
- `carts`
  - No session id or guard metadata in the table itself.
  - No unique constraint for one active row per buyer/product.
  - No product price/name snapshot.
  - Current inspected flow does not use this table as the active cart source.
- `users_buyers` and `users_sellers`
  - Address is a single nullable string.
  - No city, country, postal code, default billing/shipping address, or address relation.
  - No unique constraints on `company_code` or `vat_code`.
  - Buyer has both `credit_balance` and `balance`; only `credit_balance` is used by credit logic.
- `users_sellers`
  - `bank_account` exists in DB but is not in the Seller model fillable list.
- `buyer_credit_history`
  - `admin_id` is indexed but not constrained to `users_admins.id`.
  - No normalized type enum/constraint.
- `seller_transactions`
  - No normalized type enum/constraint.
  - No uniqueness/idempotency field for generated transaction entries.
- `categories`
  - `deleted_at` exists, but the model does not use `SoftDeletes`.
  - `is_active` exists in DB but is not fillable.
  - `created_at` and `updated_at` exist, but the model has `$timestamps = false`.
- `activities`
  - Only `id`, `created_at`, and `updated_at` exist. There is no actor, subject, event type, metadata, IP, or user-agent field.
- `global_settings`
  - Singleton shape is not enforced by a unique key or check.

## Missing Indexes

Recommended index candidates before adding more features:

- `users_buyers(is_active, created_at)` for welcome stats and admin buyer lists.
- `users_buyers(is_verified, is_active)` for buyer verification/activation filters.
- `users_buyers(credit_balance)` for admin balance range filters and sorting.
- `users_buyers(company_code)` and `users_buyers(vat_code)` if those identifiers must be unique or commonly searched.
- `users_sellers(is_active, created_at)` for welcome stats and admin seller lists.
- `users_sellers(company_code)` and `users_sellers(vat_code)` if those identifiers must be unique or commonly searched.
- `orders(payment_status, created_at)` for admin order filtering, stats, and date windows.
- `orders(created_at)` if date-only ordering/ranges are frequent without status filters.
- `order_items(product_id)` if product sales/history pages are added.
- `carts(user_id, product_id)` as a unique index if the `carts` table becomes authoritative.
- `categories(parent_category_id, order, id)` for category hierarchy ordering.
- `countries(region, is_active)` for backend country filters.
- `attribute_values(attribute_id, is_active)` for product detail/filter attribute loading.
- `seller_transactions(seller_id, type, created_at)` for seller transaction filter/stats pages.
- `credit_attachments(credit_history_id)` if attachment lists grow. The FK exists but no explicit non-primary index is reported by Boost for SQLite.

Normal B-tree indexes will not help much for leading-wildcard JSON/text searches such as `%term%` on translated JSON fields. If those searches become high traffic, evaluate full-text search or search-specific materialized columns.

## Missing Foreign Keys

The live schema has many core commerce constraints, but these are missing or weak:

- `buyer_credit_history.admin_id -> users_admins.id` is missing.
- `users_buyers`, `users_sellers`, and `users_admins` have no outbound FKs because they are root auth tables. That is acceptable, but it reinforces that roles are table/guard-based, not data-driven.
- `orders` has no FK to an address table because no address table exists.
- `products` has no FK to a normalized image/media table because no image table exists.
- `carts` has no session FK or unique active cart concept.
- `product_attribute_value` has nullable FK columns, allowing incomplete pivot rows.
- `attribute_product.selected_value_id` is constrained to `attribute_values.id`, but the schema does not enforce that the selected value belongs to the same `attribute_id`.

## Dangerous Or Inconsistent Structure

1. `users` migration is a no-op.
   The default users migration is present but fully commented out. This is okay if the three auth tables are intentional, but it is confusing because `UserFactory` still exists.

2. Admin model is duplicated.
   `App\Models\Admin` and `App\Models\Users\Admin` both target `users_admins`. The factory uses `Users\Admin`; `AdminSeeder` uses `App\Models\Admin`.

3. Product attribute storage is split.
   `product_attribute`, `attribute_product`, and `product_attribute_value` all exist. Current model and seeder behavior uses more than one, creating drift risk.

4. `Product::attributes()` is misnamed or incorrect.
   It returns `AttributeValue` through `product_attribute_value`, not `Attribute`.

5. `Product::syncAttributeValues()` can create incomplete rows.
   The relation sync only has attribute value IDs, while `product_attribute_value.attribute_id` is nullable. This can bypass the intended product/attribute/value triplet.

6. Order has relationships to missing columns.
   `Order::product()` expects `product_id`, and `Order::country()` expects `country_of_origin`; neither column exists on `orders`.

7. Order status is duplicated.
   Both `orders.status` and `orders.payment_status` exist and are populated with the same `OrderStatus` enum values in checkout. This still blurs lifecycle status and payment status even though the allowed PHP values are centralized.

8. Statuses are unconstrained strings.
   Order status now has PHP enum casts, but there are still no database constraints for order status, buyer credit type, or seller transaction type.

9. Category soft delete columns are not active in the model.
   The table has `deleted_at`, but `Category` does not use `SoftDeletes`, and it disables timestamps despite timestamp columns existing.

10. Buyer balance fields are ambiguous.
    `users_buyers` has both `credit_balance` and `balance`. Credit logic uses `credit_balance`.

11. Seller bank account is not mass assignable.
    `users_sellers.bank_account` exists, but `Seller::$fillable` does not include it.

12. The `carts` table is not the inspected cart source of truth.
    Buyer cart pages use LaraCart session data and create orders directly at checkout. The table/model may be legacy or unused.

13. Activity table is not useful as an audit log.
    It has no actor, subject, event, or payload fields.

14. Images are denormalized.
    Product image data is split between legacy filename columns and `image_library` JSON. This makes reuse, ordering, metadata, cleanup, and ownership harder.

15. Addresses are denormalized and not snapshotted.
    Buyer/seller addresses are mutable strings, and orders do not preserve checkout address state.

16. Database notifications are absent.
    The app has notification classes/views and UI notifications, but no persisted notification table.

## Improvements Before Adding More Features

1. Decide the account model strategy.
   Keep separate auth tables intentionally, or introduce a single `users` table with roles. Do not mix both.

2. Remove or retire the unused default user path.
   If separate auth tables remain, remove the stale `UserFactory` and document the three-table guard architecture.

3. Consolidate product attribute storage.
   Pick one source of truth for product attributes and values, then remove or migrate away from duplicate pivot tables.

4. Normalize order lifecycle.
   Separate payment status from fulfillment status, add database constraints if needed, and decide whether a status lookup table is needed.

5. Add order snapshots.
   Orders and order items should preserve buyer/company/address/tax/product/seller details as they existed at checkout.

6. Normalize addresses before shipping features.
   Add address tables or embedded immutable order address snapshots before adding shipping, invoices, or delivery tracking.

7. Normalize images before media-heavy features.
   Add a product/media image table if galleries, ordering, alt text, ownership, or reuse will grow.

8. Add database-backed favorites, reviews, messages, and notifications only after the account/order/product boundaries are cleaned up.
   These features depend on clear user roles, product ownership, order ownership, and notification targets.

9. Add the missing FK for buyer credit admins.
   `buyer_credit_history.admin_id` should reference `users_admins.id` or become nullable without a relationship.

10. Add query-matched indexes.
    Prioritize buyer/seller active filters, order payment status/date filters, category ordering, country filters, and seller transaction type/date filters.

11. Add model casts/fillable cleanup.
    Align fillable/casts with the live columns before expanding forms and factories.

12. Add tests for schema invariants.
    Cover relationship integrity, checkout snapshots, status values, product attribute storage, and model/factory consistency after any schema cleanup.

## Suggested Next Schema Work Order

1. Account/role decision and stale user cleanup.
2. Product attribute pivot consolidation.
3. Status database constraint cleanup for orders and transaction type enum cleanup.
4. Order and order item snapshot fields.
5. Address and image normalization.
6. Favorites, reviews, messages, and database notifications.
