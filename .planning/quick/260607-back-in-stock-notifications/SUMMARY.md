# Quick Task 260607: Back-in-stock notifications

## Analysis

- Product stock is stored on `products.stock`.
- Buyer-visible purchasability is now centralized through `Product::isVisibleToBuyers()`, `Product::isPurchasableByBuyers()`, and `Product::canReceiveStockAlerts()`.
- Existing dirty-tree low-stock seller notification work was present, but no buyer back-in-stock subscription flow existed.
- Guest alerts were intentionally not enabled because the project currently supports database notifications for authenticated users, not guest email notification delivery.

## Implementation

- Added `product_stock_alerts` with buyer/product/status uniqueness for duplicate prevention.
- Added stock alert enum, model, factory, policy, actions, product observer, database notification, buyer dashboard panel, buyer alert list, product-detail notify/cancel UI, demo seeder, translations, docs, and tests.
- Added audit events for alert creation, cancellation, and sent notifications when the audit system is present.

## Verification

- Passed: `php artisan migrate:fresh --seed --no-interaction`
- Passed: `php artisan test --compact tests/Feature/Marketplace/ProductStockAlertFeatureTest.php`
- Passed: `php artisan test --compact tests/Feature/Translations/TranslationFilesTest.php`
- Passed: `npm run build`
- Browser check: public out-of-stock product page renders the stock alert login CTA; authenticated buyer dashboard renders the stock alerts panel.

## Residual Risks

- Full feature suite currently has unrelated dirty-tree failures in product reports, admin-action security logging, demo image storage, and one concurrent sqlite readonly seeder run.
- Product detail and login pages currently emit existing Alpine/Mary compatibility errors from neighboring UI work, not from the stock alert block.
