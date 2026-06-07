# Product Reports And Abuse Moderation

Birza supports product abuse reports for suspicious, illegal, misleading, duplicate, scam, offensive, copyright, wrong-price, wrong-category, prohibited, and other policy-breaking products.

## Who Can Report

- Guests can report active public products when `MARKETPLACE_ALLOW_GUEST_PRODUCT_REPORTS=true`.
- Guest reports require an email address so duplicate open reports can be limited.
- Authenticated active buyers can report active public products.
- Blocked buyers cannot submit reports.
- Sellers cannot report their own products.
- Reporters cannot edit reports after submission.

Inactive, deleted, or otherwise non-public products are not reportable.

## Reasons

Supported reasons are defined in `App\Enums\ProductReportReason`:

- `scam`
- `fake_product`
- `wrong_price`
- `wrong_category`
- `prohibited_item`
- `offensive_content`
- `copyright_issue`
- `duplicate_product`
- `misleading_description`
- `other`

Visible reason labels are stored in `lang/en.json` and `lang/lt.json` under `reports.product.reasons.*`.

## Duplicate And Spam Protection

`CreateProductReportAction` blocks duplicate open reports for the same product from:

- the same authenticated buyer
- the same guest email

Open reports are `pending` and `reviewing`. The action also applies a per-product rate limit using Laravel's `RateLimiter`. Configure it with:

```env
MARKETPLACE_PRODUCT_REPORT_RATE_LIMIT_PER_HOUR=5
```

## Admin Review

Admins manage reports from:

```text
/admin/reports
/admin/reports/{productReport}
```

The report list is paginated and supports status, reason, seller, and product/reporter search filters. The detail page shows the reported product, seller, reporter, message, review metadata, and admin note.

Admin actions:

- start review
- resolve report without changing product visibility
- reject report without changing product visibility
- dismiss false or non-actionable report
- hide the reported product

Dangerous actions are policy-protected and use confirmation dialogs. Hiding a product requires an admin note, marks the report resolved, sets `products.is_active=false`, writes audit records, records an admin action, and notifies the seller.

## Seller Visibility

Sellers do not receive reporter identity or reporter email. A seller is notified only when an admin takes a product-level moderation action, such as hiding the reported product. The seller-facing notification includes the product, report reason, and admin note when provided.

## Audit Logging

The workflow records audit entries for:

- `product_report.created`
- `product_report.reviewing`
- `product_report.resolved`
- `product_report.rejected`
- `product_report.dismissed`
- `product_report.product_hidden`

The product-hidden action also writes product audit history so the product visibility change can be reviewed alongside other product changes.

## Test Coverage

Focused tests live in:

```text
tests/Feature/Marketplace/ProductReportFeatureTest.php
tests/Feature/Translations/TranslationFilesTest.php
```

Useful commands:

```bash
php artisan test --compact tests/Feature/Marketplace/ProductReportFeatureTest.php
php artisan test --compact tests/Feature/Translations/TranslationFilesTest.php
```

Coverage includes buyer and guest report submission, disabled guest reports, inactive/deleted product rejection, invalid reasons, message length validation, duplicate prevention, blocked buyers, seller own-product denial, admin moderation, product hiding, notification dispatch, reporter privacy, pagination, audit logs, and translation keys.

## Deployment Notes

Run:

```bash
php artisan migrate
npm run build
```

For local demo data, run:

```bash
php artisan db:seed --class='Database\\Seeders\\Demo\\DemoScenarioSeeder'
```

Before tagging a release, manually verify product detail report submission, duplicate report behavior, admin report list/detail, hide-product moderation, seller notification, audit log entries, forbidden access checks, and mobile layout.
