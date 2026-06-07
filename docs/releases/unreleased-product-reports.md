# Product Reports And Abuse Moderation Release Notes

## Metadata

- Version: Unreleased
- Release date: 2026-06-07
- Release type: minor
- Related commits: pending
- Related issues: none
- Git tag: none

## Summary

Adds product abuse reporting and admin moderation so guests and buyers can flag problematic products and admins can triage reports without exposing reporter identity to sellers.

## Added

- `product_reports` table with product, reporter, guest email, reason, status, reviewer, admin note, timestamps, and soft delete fields.
- `ProductReportReason` and `ProductReportStatus` enums.
- Product report model, factory, policy, demo seeder, and relationships on product, buyer, seller, and admin models.
- Public product detail report button and modal with reason, message, guest email, validation errors, and success message.
- Admin report list and detail pages with filters, pagination, status transitions, admin notes, and hide-product moderation.
- Report-specific admin and seller notifications.
- Audit logs and admin action records for report creation, review decisions, and product hiding.
- English and Lithuanian translation keys for product reports, report reasons, statuses, admin UI, and notifications.
- Feature tests for report submission, authorization, validation, duplicate prevention, admin moderation, seller privacy, notifications, audit logs, pagination, and translations.

## Changed

- Product detail pages now include an abuse-reporting entry point for active products.
- Admin navigation now includes product reports.
- Marketplace notification docs now distinguish product-report hiding from generic product rejection.

## Fixed

- None.

## Removed

- None.

## Security

- Guest reports are controlled by `MARKETPLACE_ALLOW_GUEST_PRODUCT_REPORTS`.
- Guest reports require email and duplicate open reports are blocked by guest email.
- Buyer duplicate open reports are blocked by buyer id.
- Blocked buyers and sellers reporting their own products are rejected.
- Admin moderation routes and Livewire actions are protected by `ProductReportPolicy`.
- Seller notifications do not expose reporter identity or reporter email.

## Deprecated

- None.

## Database Changes

- Migration: `2026_06_07_182705_create_product_reports_table.php`
- New table: `product_reports`
- New indexes: status/created date, reason/status, product/status, reporter/product/status, guest email/product/status, reviewer/date.
- Foreign keys: products, buyer reporters, admin reviewers.
- Seeder changes: `DemoProductReportSeeder` is included in `DemoScenarioSeeder`.

## Configuration Changes

- New `.env` keys:
  - `MARKETPLACE_ALLOW_GUEST_PRODUCT_REPORTS`
  - `MARKETPLACE_PRODUCT_REPORT_RATE_LIMIT_PER_HOUR`
- New config file: `config/marketplace.php`

## New Routes Or Pages

- Public: `/buyer/products/{product}` includes the report modal.
- Admin: `/admin/reports`
- Admin: `/admin/reports/{productReport}`
- API: none.

## New Permissions

- `ProductReportPolicy::viewAny` and `view` are admin-only.
- `ProductReportPolicy::create` allows guests when enabled and active buyers for active products.
- `ProductReportPolicy::review`, `resolve`, `reject`, `dismiss`, and `hideProduct` are admin-only.

## Testing Notes

- New tests: `tests/Feature/Marketplace/ProductReportFeatureTest.php`
- Updated tests: `tests/Feature/Translations/TranslationFilesTest.php`
- Test command: `php artisan test --compact tests/Feature/Marketplace/ProductReportFeatureTest.php tests/Feature/Translations/TranslationFilesTest.php`
- Coverage gaps: browser screenshot and mobile layout verification should still be performed before tagging.

## Deployment Notes

- Run `php artisan migrate`.
- Run `php artisan db:seed` for local/demo report scenarios.
- Run `npm run build` after deploying Blade/Livewire UI changes.
- Run `php artisan optimize:clear` after deployment if cached views/config/routes are present.

## Breaking Changes

- None expected.

## Migration Steps

1. Deploy code.
2. Run migrations.
3. Configure guest reports and hourly rate limit if the defaults are not suitable.
4. Rebuild frontend assets.
5. Clear optimized caches.
6. Verify product detail reporting and admin moderation.

## Known Issues

- Reporter update notifications are not implemented. Reports are submitted for admin review only.
- Product hiding currently deactivates the product; there is no separate hidden moderation status field yet.

## Rollback Notes

- Database rollback drops `product_reports`.
- Code rollback removes report routes, components, notifications, policy mapping, and report relationships.
- Audit log rows for report actions should be exported first if rollback must preserve moderation traceability.

## Manual Verification Checklist

- [ ] Product detail shows report button.
- [ ] Guest can submit a report when guest reports are enabled.
- [ ] Buyer can submit a report.
- [ ] Duplicate pending report is blocked.
- [ ] Admin can filter and open report detail.
- [ ] Admin can resolve, reject, dismiss, and hide from a report.
- [ ] Seller is notified when a reported product is hidden.
- [ ] Reporter identity is not visible to seller.
- [ ] Forbidden buyer/seller/admin URLs are checked.
- [ ] Mobile product detail and admin report layouts remain usable.
