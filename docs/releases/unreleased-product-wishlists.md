# Product Wishlists Release Notes

## Added

- Authenticated buyer product wishlists with named collections and an automatic default saved-products list.
- Catalog and product-detail save controls using the existing Blade/Livewire UI style.
- Buyer wishlist index and detail pages with remove, move, clear, availability, low-stock, and add-to-cart actions.
- Wishlist schema, Eloquent relationships, factories, demo seeding, authorization policy, translations, and focused PHPUnit coverage.

## Notes

- Guest wishlists are not supported.
- Existing favorites logic was not refactored because no shipped favorites model or UI flow exists.
- Price-change and back-in-stock wishlist notifications remain future work until real product-change detection events are available.

## Verification

Run:

```bash
php artisan test --compact tests/Feature/Marketplace/ProductWishlistFeatureTest.php
npm run build
```
