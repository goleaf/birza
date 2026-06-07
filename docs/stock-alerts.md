# Product Stock Alerts

Product stock alerts let authenticated buyers subscribe to visible out-of-stock products and receive an in-app notification when the product becomes purchasable again.

## Who Can Subscribe

- Authenticated, active, verified buyers can create alerts.
- Guests cannot subscribe in this implementation; the public product page links guests to buyer login.
- Sellers cannot view buyer alert lists or buyer alert email data.
- Admins may view alert records through policy-level access if an admin surface is added later.

## Availability Rules

An alert can be created only when the product is visible to buyers and not currently purchasable:

- product is not soft deleted
- product is active
- seller is active and not soft deleted
- stock is `0` or lower

The product is considered back in stock when the same visibility rules are true and `stock > 0`.

## Duplicate Prevention

The `product_stock_alerts` table stores `product_id`, `buyer_id`, `status`, and `notified_at`. A unique index on `(product_id, buyer_id, status)` prevents duplicate active alerts for the same buyer and product while still allowing a buyer to subscribe again after an older alert was notified or cancelled.

## Notifications

Back-in-stock notifications use Laravel database notifications. The notification is sent after the product update commits and only active alerts are processed.

When a product becomes purchasable again:

- active alerts are marked `notified`
- `notified_at` is set
- buyers receive `notifications.stock_alert.back_in_stock.*`
- audit logs record `stock_alert.notification_sent`

## Audit Logs

The audit system records:

- `stock_alert.created`
- `stock_alert.cancelled`
- `stock_alert.notification_sent`

No guest email or private buyer email value is stored in stock-alert audit metadata.

## Demo Data

`DemoStockAlertSeeder` creates:

- an active alert for `Demo Out Of Stock Milk`
- a notified alert for `Demo Active Apples`

The demo catalog already includes active, out-of-stock, inactive, blocked-seller, and available products.

## Testing

Focused commands:

```bash
php artisan test --compact tests/Feature/Marketplace/ProductStockAlertFeatureTest.php
php artisan test --compact tests/Feature/Translations/TranslationFilesTest.php
```

Full verification for a release should also run migrations, seeders, the full test suite, and the frontend build.
