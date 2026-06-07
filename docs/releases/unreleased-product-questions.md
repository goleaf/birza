# Product Questions And Answers Release Notes

## Metadata

- Version: Unreleased
- Release date: 2026-06-07
- Release type: minor
- Related commits: pending
- Related issues: none
- Git tag: none

## Summary

Adds a public product Q&A workflow so guests and buyers can ask questions on active products and sellers can answer them publicly.

## Added

- `product_questions` table with product, seller, buyer/guest, answer, status, visibility, moderation, timestamps, and soft delete fields.
- Product detail Q&A section with public answered questions, empty state, validation errors, loading state, and ask form.
- Seller question inbox for filtering and answering own product questions.
- Admin product-question moderation page for approve, reject, and hide actions.
- Database notifications for new questions, answered questions, and rejected questions.
- Audit logs for created, answered, approved, rejected, and hidden question events.
- Demo product-question seed data covering answered, pending, rejected, buyer-authored, and guest-authored questions.
- PHPUnit coverage for visibility, creation, notifications, authorization, moderation, validation, translations, status changes, and audit logs.

## Changed

- Product catalog/detail routes are guest-accessible while inactive or deleted products still return 404 from the product detail component.
- README role notes now document product Q&A capabilities for guests, buyers, sellers, and admins.

## Fixed

- None.

## Removed

- None.

## Security

- Pending, rejected, hidden, and unanswered questions are not shown publicly.
- Sellers can only answer or hide questions for their own products.
- Buyers cannot answer product questions as sellers.
- Admin moderation actions are protected by the admin guard and `ProductQuestionPolicy`.
- Guest email is optional and is not included in audit snapshots.

## Deprecated

- None.

## Database Changes

- Migration: `2026_06_07_182305_create_product_questions_table.php`
- New table: `product_questions`
- New indexes: product/status/visibility, seller/status/created, buyer/created, answered date.
- Foreign keys: products, seller profile, buyer profile, answering seller, moderating admin.
- Seeder changes: `DemoProductQuestionSeeder` is included in `DemoScenarioSeeder`.

## New Routes Or Pages

- Public: `/buyer/products/{product}` now includes the Q&A panel.
- Seller: `/seller/product-questions`
- Admin: `/admin/product-questions`
- API: none.

## New Permissions

- `ProductQuestionPolicy::create` allows guests and active buyers to ask about active products.
- `ProductQuestionPolicy::answer` allows approved sellers to answer own product questions.
- `ProductQuestionPolicy::hide` allows sellers to hide own product questions and admins to hide any question.
- `ProductQuestionPolicy::approve`, `reject`, and `moderate` are admin-only.

## Testing Notes

- New tests: `tests/Feature/Marketplace/ProductQuestionFeatureTest.php`
- Test command: `php artisan test --compact tests/Feature/Marketplace/ProductQuestionFeatureTest.php tests/Feature/Translations/TranslationFilesTest.php`
- Coverage gaps: browser screenshot verification should still be performed before tagging a release.

## Deployment Notes

- Run `php artisan migrate`.
- Run `php artisan db:seed` for local/demo Q&A examples.
- Run `npm run build` after deploying the Blade/Livewire UI changes.
- Run `php artisan optimize:clear` after deployment if cached views/config/routes are present.

## Breaking Changes

- None expected.

## Migration Steps

1. Deploy code.
2. Run migrations.
3. Rebuild frontend assets.
4. Clear optimized caches.
5. Verify product detail, seller question inbox, and admin moderation pages.

## Known Issues

- Guest answer notifications require a guest email; guest questions without email are public-only once answered.

## Rollback Notes

- Database rollback drops `product_questions`.
- Code rollback removes the Q&A routes, components, notifications, and policy mapping.
- Public answered Q&A data should be exported first if rollback needs to preserve content.

## Manual Verification Checklist

- [ ] Product detail shows answered public questions only.
- [ ] Guest can submit a valid question.
- [ ] Buyer can submit a valid question.
- [ ] Seller can answer own pending question.
- [ ] Seller cannot answer another seller's question.
- [ ] Admin can reject and hide questions.
- [ ] Mobile product detail and seller/admin layouts remain usable.
