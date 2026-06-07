# Product Wishlists

Product wishlists let authenticated buyers save visible marketplace products into named lists and return to them before checkout.

## Decision

- Favorites did not exist as a shipped data model or UI flow when this feature was added.
- Wishlists are the primary saved-product system, not a replacement for an existing favorites feature.
- A default wishlist named by `wishlists.default_name` is created automatically when a buyer saves a product without choosing a list.
- Guest wishlists are intentionally not supported. Guests can browse public products and use the cart, but saving products requires an authenticated, active, verified buyer account.
- Sellers cannot manage buyer wishlists. Admin visibility is controlled by `WishlistPolicy` and should only be expanded if an admin wishlist screen is added.

## Data Model

- `wishlists`
  - `buyer_id`
  - `name`
  - `slug`
  - `description`
  - `is_default`
  - `is_private`
  - timestamps
- `wishlist_items`
  - `wishlist_id`
  - `product_id`
  - timestamps

The database prevents duplicate products inside the same wishlist with a unique `wishlist_id, product_id` constraint. Wishlist names are unique per buyer. The single-default-wishlist rule is enforced in the wishlist actions so the migrations stay database-portable and avoid raw partial-index SQL.

## UI Surfaces

- Catalog product cards expose a save button.
- Product detail pages expose a wishlist selector and save button for buyers.
- The frontend header shows a buyer wishlist link and item count.
- The buyer dashboard shows a short wishlist summary.
- `/buyer/wishlists` manages all buyer wishlists.
- `/buyer/wishlists/{wishlist}` shows wishlist items with product image, title, price, seller, category, availability, stock warning, remove, move, and add-to-cart controls.

## Component Standard

Wishlist screens reuse the existing Blade/Livewire UI wrappers:

- `x-ui.header`
- `x-ui.card`
- `x-ui.button`
- `x-ui.badge`

Do not introduce another wishlist-only button, card, modal, dropdown, or icon system.

## Business Logic

Wishlist logic lives in `App\Actions\Wishlists`:

- `CreateWishlistAction`
- `UpdateWishlistAction`
- `DeleteWishlistAction`
- `AddProductToWishlistAction`
- `RemoveProductFromWishlistAction`
- `MoveProductBetweenWishlistsAction`
- `ClearWishlistAction`
- `AddWishlistItemToCartAction`

Livewire components should call these actions rather than duplicating wishlist rules in component methods.

## Authorization And Validation

- Buyers can manage only their own wishlists.
- Private wishlists are hidden from other buyers.
- Public wishlist view support is policy-level only; no public sharing UI has been added.
- Inactive products, deleted products, products with inactive sellers, and otherwise non-visible products cannot be added.
- Duplicate products in the same wishlist are rejected before insert and also protected by the database constraint.

## Notifications

Wishlist price-change, back-in-stock, and unavailable-product notifications are not faked by this feature. They should be added only when the project has product price-change and availability detection events that can drive real notifications.

## Testing

Focused tests live in:

```bash
php artisan test --compact tests/Feature/Marketplace/ProductWishlistFeatureTest.php
```

The test file covers create, rename, delete, own/private access, active/inactive/deleted product saving, duplicate prevention, remove, move, clear, add-to-cart, guest/seller denial, empty state, and translation keys.
