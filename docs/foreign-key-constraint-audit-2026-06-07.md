# Foreign Key Constraint Audit - 2026-06-07

## Scope

This audit checked the migration-defined relationship fields for users, buyers, sellers, products, categories, orders, carts, reviews, notifications, images, addresses, and related business history.

Fields checked included `user_id`, `buyer_id`, `seller_id`, `product_id`, `category_id`, `order_id`, `cart_id`, `address_id`, `imageable_id`, `parent_id`, `parent_category_id`, `created_by`, `updated_by`, and admin/actor fields.

## Changes Made

| Table | Field | Previous behavior | New behavior | Reason |
| --- | --- | --- | --- | --- |
| `buyer_credit_history` | `buyer_id` | FK to `users_buyers.id` with cascade delete | FK to `users_buyers.id` with restricted delete | Credit history is business history and should block hard-deleting a referenced buyer. Buyers already use soft deletes for normal archival. |
| `buyer_credit_history` | `admin_id` | Nullable integer with index, no FK | Nullable FK to `users_admins.id` with `nullOnDelete()` | Credit history should keep the row if the admin account is removed, while clearing the optional actor reference. |
| `categories` | `parent_category_id` | Self-FK with cascade delete | Self-FK with `nullOnDelete()` | Hard-deleting a parent category should not silently delete a full category subtree. Child categories can survive as roots. |
| `reviews` | `product_id` | Pending migration used cascade delete | Pending migration now uses nullable FK with `nullOnDelete()` | Reviews are historical user content and should not be erased by a product hard delete. Products use soft deletes for normal archival. |
| `reviews` | `user_id` | Pending migration used cascade delete | Pending migration now uses nullable FK with `nullOnDelete()` | Reviews should remain if a user is hard-deleted, with the actor reference cleared. |

## Existing Constraints Kept

| Table | Field(s) | Delete behavior | Decision |
| --- | --- | --- | --- |
| `seller_categories` | `seller_id`, `category_id` | cascade | Join rows are owned by seller/category membership and can be removed with either side. |
| `category_attribute` | `category_id`, `attribute_id` | cascade | Join rows are derived category metadata. |
| `product_attribute` | `product_id`, `attribute_id` | cascade | Join rows are derived product metadata. |
| `credit_attachments` | `credit_history_id` | cascade | Attachments are child files of the credit-history row. |
| `product_images` | `product_id` | cascade | Product images are child media rows. |
| `cart_items` | `cart_id`, `product_id` | cascade | Cart rows are temporary shopping state, not business history. |
| `user_notifications` | `user_id` | cascade | Persisted notifications are user-owned ephemeral records. |
| `addresses` | `user_id` | cascade | Address rows are user-owned profile data. |
| `orders` | `buyer_id` | restrict / no action | Orders are business history and should block hard-deleting a referenced buyer. |
| `order_items` | `order_id`, `product_id`, `seller_id` | restrict / no action | Order lines are business history and should block hard-deleting referenced order/product/seller rows. |
| `seller_transactions` | `seller_id`, `order_id` | restrict / no action | Seller transactions are financial history and should block hard deletes. |
| `products` | `category_id`, `seller_id`, `country_of_origin` | restrict / no action | Product references should remain valid unless changed intentionally. Products use soft deletes for archival. |

## Not Applicable Or Intentionally Unconstrained

- No committed migration currently defines `address_id`, `imageable_id`, `created_by`, or `updated_by` columns.
- `personal_access_tokens.tokenable_id` remains unconstrained because it is Laravel Sanctum's polymorphic token owner field.
- Generic `users` profile links on `users_buyers.user_id` and `users_sellers.user_id` already use nullable foreign keys with `nullOnDelete()`.

## Verification

- `php artisan test --compact tests/Unit/Database/ForeignKeyConstraintTest.php`
- The focused test covers restricted buyer credit-history deletion, admin reference nulling, category child preservation, and review reference nulling.
