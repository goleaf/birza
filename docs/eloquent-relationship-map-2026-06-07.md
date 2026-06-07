# Eloquent Relationship Map - 2026-06-07

## Scope

This review standardized the Eloquent relationship surface for users, buyer and seller profiles, products, categories, orders, carts, reviews, notifications, images, and addresses.

It intentionally does not refactor controllers, Livewire workflows, or status handling beyond replacing stale relationship names that would otherwise break after alias removal.

## Canonical Relationships

| Model | Relationship | Type | Target |
| --- | --- | --- | --- |
| `User` | `buyerProfile()` | `hasOne` | `Users\Buyer` |
| `User` | `sellerProfile()` | `hasOne` | `Users\Seller` |
| `User` | `addresses()` | `hasMany` | `Address` |
| `User` | `notifications()` | `hasMany` | `Notification` on `user_notifications` |
| `User` | `reviews()` | `hasMany` | `Review` |
| `Users\Buyer` | `user()` | `belongsTo` | `User` |
| `Users\Buyer` | `orders()` | `hasMany` | `Order` |
| `Users\Seller` | `user()` | `belongsTo` | `User` |
| `Users\Seller` | `products()` | `hasMany` | `Product` |
| `Users\Seller` | `orders()` | `hasManyThrough` | `Order` through `OrderItem` |
| `Product` | `category()` | `belongsTo` | `Category` |
| `Product` | `seller()` | `belongsTo` | `Users\Seller` |
| `Product` | `images()` | `hasMany` | `ProductImage` |
| `Product` | `reviews()` | `hasMany` | `Review` |
| `ProductImage` | `product()` | `belongsTo` | `Product` |
| `Order` | `buyer()` | `belongsTo` | `Users\Buyer` |
| `Order` | `orderItems()` | `hasMany` | `OrderItem` |
| `Order` | `products()` | `belongsToMany` | `Product` through `order_items` |
| `OrderItem` | `order()` | `belongsTo` | `Order` |
| `OrderItem` | `product()` | `belongsTo` | `Product` |
| `OrderItem` | `seller()` | `belongsTo` | `Users\Seller` |
| `Cart` | `buyer()` | `belongsTo` | `Users\Buyer` by `user_id` |
| `Cart` | `cartItems()` | `hasMany` | `CartItem` |
| `CartItem` | `cart()` | `belongsTo` | `Cart` |
| `CartItem` | `product()` | `belongsTo` | `Product` |
| `Review` | `product()` | `belongsTo` | `Product` |
| `Review` | `user()` | `belongsTo` | `User` |
| `Notification` | `user()` | `belongsTo` | `User` |
| `Address` | `user()` | `belongsTo` | `User` |

## Removed Or Replaced Relationships

- Removed `Order::product()` because `orders.product_id` does not exist.
- Removed `Order::country()` because `orders.country_of_origin` does not exist.
- Removed duplicate `Order::items()` in favor of the canonical `Order::orderItems()`.
- Replaced `Cart::user()` with `Cart::buyer()` because `carts.user_id` points to `users_buyers`.
- Replaced `Cart::product()` with `Cart::cartItems()` for normalized cart line rows.
- Replaced ambiguous `Product::attributes()` mapping to `AttributeValue` with an `Attribute` relation through `product_attribute`.

## Schema Added For Missing Relationship Targets

- Added canonical `users` table for the new `User` model.
- Added nullable unique `user_id` foreign keys on `users_buyers` and `users_sellers`.
- Added `product_images` for `Product::images()`.
- Added `cart_items` for `Cart::cartItems()`.
- Added `reviews` for product/user reviews.
- Added `user_notifications` for persisted user notifications without colliding with Laravel notification conventions.
- Added `addresses` for reusable user addresses.

## Remaining Caveats

- `users_buyers` and `users_sellers` remain authenticatable profile tables. The generic `users` table is now available for shared profile relationships, but auth guards have not been consolidated.
- The legacy `carts` table still has `product_id` and `quantity`; normalized line items now live in `cart_items`, so cart feature work should decide whether to migrate existing rows or retire those columns.
- Product media still has legacy `product_image`, `product_additional_image`, and `image_library` columns alongside normalized `product_images`.
- Favorites and messages still do not have tables or models.
