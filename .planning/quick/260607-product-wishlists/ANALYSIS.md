# Product Wishlists Analysis - 2026-06-07

## Scope

Add exactly one marketplace feature: buyer product wishlists.

## Current Findings

- Favorites do not currently exist. Repository grep found only documentation references saying favorites are missing or future work.
- Existing cart logic is database-backed through `carts`, `cart_items`, and Actions under `App\Actions\Cart`.
- Buyers use the `buyer` guard and `App\Models\Users\Buyer`; buyer routes live in `routes/buyer.php`.
- Product visibility is represented by `products.is_active`, `products.deleted_at`, and seller activity.
- Product catalog and product detail pages are Livewire components:
  - `App\Livewire\Frontend\Buyer\Products\Index`
  - `App\Livewire\Frontend\Buyer\Products\Show`
- Buyer dashboard is `App\Livewire\Frontend\Buyer\Dashboard`.
- UI should reuse the Mary-first wrapper layer: `x-ui.button`, `x-ui.card`, `x-ui.badge`, `x-ui.header`, and `x-ui.icon`.
- Audit logging exists through `App\Services\AuditLogService`; wishlist lifecycle events can be logged there.
- Notifications exist, but no price-change/back-in-stock event source exists yet.

## Decision

Wishlists will not replace favorites because no favorites implementation exists.

Wishlists will be named buyer-owned collections. A default wishlist will be created automatically when a buyer saves a product from a catalog card or product detail page without choosing another list.

Guest wishlists will not be implemented in this feature. Guests can browse public product pages and use the cart, but wishlists require an authenticated, active, verified buyer because named collections need persistent ownership and authorization.

## Database Tables

Add:

- `wishlists`
  - `id`
  - `buyer_id`
  - `name`
  - `slug` nullable
  - `description` nullable
  - `is_default`
  - `is_private`
  - timestamps
  - unique `buyer_id`, `name`
  - indexed `buyer_id`, `is_default`
- `wishlist_items`
  - `id`
  - `wishlist_id`
  - `product_id`
  - timestamps
  - unique `wishlist_id`, `product_id`

One default wishlist per buyer will be enforced by Actions in a transaction. A portable partial unique index for only default rows would require database-specific SQL, which this project forbids.

## UI Placement

- Product card/catalog grid: one-click save to default wishlist for authenticated buyers.
- Product detail page: wishlist selector plus save button.
- Buyer header: wishlist link/count next to cart.
- Buyer dashboard: compact wishlist panel/link.
- Buyer wishlists index: list, create, rename, delete, clear.
- Buyer wishlist detail: products with image, title, price, seller, category, availability, stock warning, remove, move, add to cart.

## Files To Change

- Migrations for `wishlists` and `wishlist_items`.
- Models: `Wishlist`, `WishlistItem`, `Buyer`, `Product`.
- Factories and demo seeder.
- Actions under `App\Actions\Wishlists`.
- Policy: `WishlistPolicy`, registered in `AuthServiceProvider`.
- Buyer routes.
- Livewire components/views for wishlist index and detail.
- Product catalog/detail Livewire components and Blade views.
- Buyer dashboard and frontend header/view composer.
- Translation files: `lang/en.json`, `lang/lt.json`.
- README, changelog, and release note.
- Feature tests for policy, routes, actions, Livewire flows, cart integration, translations, and seed data.

## Tests Needed

- Buyer can create, rename, delete, view, clear wishlist.
- Buyer cannot view another buyer's private wishlist.
- Seller and guest cannot manage buyer wishlists.
- Buyer can add active product to wishlist.
- Buyer cannot add inactive or deleted product.
- Buyer cannot duplicate a product in the same wishlist.
- Buyer can remove and move wishlist items.
- Wishlist item can be added to cart.
- Empty state renders.
- Translation keys exist.
- Demo seeder creates wishlist states.

## Notifications

Price-change, back-in-stock, and unavailable-product wishlist notifications are useful but not implemented here because the project does not yet have product-change watchers for buyer wishlist subscribers. This should be a follow-up once product inventory/price events are formalized.
