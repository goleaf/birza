# Product Bundles Release Notes

## Metadata

- Version: Unreleased
- Release type: feature block
- Git tag: not created

## Summary

Sellers can create product bundles that group several seller-owned products into one purchasable set. Buyers can view active bundles publicly, add bundles to cart, and checkout with backend-recalculated bundle pricing.

## Main Changes

- Added seller bundle list/create/edit screens with product selection, quantities, sort order, discount fields, availability dates, image upload, and publish/archive actions.
- Added public buyer bundle detail pages and related bundle cards on product detail pages.
- Added backend bundle list/detail screens for moderation-style status changes.
- Added bundle cart rows, quantity updates, removal, and checkout integration.
- Added order bundle snapshots so old orders remain readable after bundle or product changes.

## Database Changes

- Added `product_bundles`.
- Added `product_bundle_items`.
- Added `cart_bundle_items`.
- Added `order_bundles`.
- Added nullable `order_bundle_id` to `order_items`.

Bundle price is calculated dynamically from current product prices and quantities. Checkout persists historical `order_bundles.products_snapshot` and linked `order_items` rows.

## New Routes Or Pages

- Seller: `seller.bundles.index`, `seller.bundles.create`, `seller.bundles.edit`.
- Buyer: `buyer.bundles.show`.
- Admin: `admin.bundles.index`, `admin.bundles.show`.

## New Permissions

- Added `ProductBundlePolicy`.
- Sellers manage only their own bundles.
- Guests and buyers can view/add active public bundles.
- Admins can manage bundle status through backend screens.

## Tests

- `tests/Feature/ProductBundleFeatureTest.php`
- `tests/Feature/BundleCartCheckoutTest.php`

Run:

```bash
php artisan test --compact tests/Feature/ProductBundleFeatureTest.php tests/Feature/BundleCartCheckoutTest.php
```

## Migration Steps

1. Run `php artisan migrate`.
2. Run `php artisan db:seed` in local/demo environments to create bundle examples.
3. Run `npm run build` for frontend assets.

## Manual Verification Checklist

- [ ] Seller can list, create, edit, publish, unpublish, archive, and delete own bundles.
- [ ] Seller cannot add another seller's product.
- [ ] Public bundle page hides draft/inactive/archived bundles.
- [ ] Buyer can add an active bundle to cart.
- [ ] Cart shows included products, discount, and final bundle price.
- [ ] Checkout creates an order bundle snapshot and decrements stock.
- [ ] Order detail pages show bundle snapshots after bundle deletion.
- [ ] Backend bundle list/detail status actions work.
