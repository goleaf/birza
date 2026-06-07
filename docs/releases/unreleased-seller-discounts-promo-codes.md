# Seller Discounts And Promo Codes

## Summary

Adds seller-owned discounts and promo codes for the Birza marketplace. Sellers can manage their own promotions, buyers can apply valid promo codes in the cart, and checkout stores historical discount snapshots on orders and order items.

## Main Changes

- Added `discounts`, `promo_codes`, and `promo_code_redemptions`.
- Added backend promotion actions for create, update, archive, validate, apply, remove, calculate, and redemption recording.
- Added seller Livewire pages for discounts and promo codes.
- Added buyer cart promo code apply/remove controls.
- Updated cart totals and checkout order creation to recalculate promotions on the backend.
- Added discount snapshots to orders and order items.
- Added audit logs for seller promotion changes and buyer promo redemptions.
- Added demo promotion seeding, factories, translations, README documentation, and feature tests.

## Database Changes

- `discounts`: seller-owned automatic discounts with optional product/category scope, type, value, date limits, status, usage limit, and minimum order amount.
- `promo_codes`: seller-owned buyer-entered codes with globally unique `code`, type, value, date limits, status, usage limit, per-user limit, and minimum order amount.
- `promo_code_redemptions`: buyer/order redemption history.
- `orders`: promotion snapshot columns for total discount and promo code values.
- `order_items`: original price, discount amount, final price, discount id, and discount source snapshots.

## New Routes Or Pages

- `/seller/discounts`
- `/seller/promo-codes`
- `/buyer/cart` promo code controls.

## Permissions

- Sellers can manage only their own discounts and promo codes.
- Buyers can apply only currently valid promo codes.
- Promo codes are globally unique so one buyer-entered code cannot map to multiple sellers.
- Multi-seller carts remain supported; a promo code applies only to the seller order that owns the code.

## New Tests

```bash
php artisan test --compact tests/Feature/Marketplace/SellerDiscountPromoCodeFeatureTest.php
php artisan test --compact tests/Feature/Translations/TranslationFilesTest.php
```

## Breaking Changes

None expected. Existing orders keep their existing totals; new checkout orders include discount snapshot fields.

## Migration Steps

```bash
php artisan migrate
php artisan db:seed --class='Database\Seeders\Demo\DemoPromotionSeeder'
```

For a full local reset:

```bash
php artisan migrate:fresh --seed
```

## Known Issues

- Discount usage limits currently count discounted order lines.
- Payment processing is still simulated, so promotion redemption is tied to successful local order creation rather than external payment capture.
- Admin moderation for promo codes is policy-supported but does not yet have a dedicated admin UI.

## Manual Verification Checklist

- Seller can open `/seller/discounts` and create, edit, activate, deactivate, and archive a discount.
- Seller can open `/seller/promo-codes` and create, edit, activate, deactivate, and archive a promo code.
- Seller cannot access another seller's promotion records.
- Buyer can apply and remove a valid promo code in `/buyer/cart`.
- Expired, inactive, not-started, usage-limited, per-user-limited, and minimum-order promo codes show validation errors.
- Multi-seller cart applies a promo code only to the owning seller's products.
- Checkout stores order and order-item discount snapshots.
- Promo code redemption and audit log rows are created after successful checkout only.
- Cart page remains usable on mobile widths.
