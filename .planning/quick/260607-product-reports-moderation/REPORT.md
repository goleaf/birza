# Product Reports And Abuse Moderation - Pre-Implementation Report

Date: 2026-06-07

Feature scope: exactly one new marketplace feature - product reports and abuse moderation.

## Existing State

Reports already exist:

- No `product_reports` table exists in the current database schema.
- No `ProductReport` model, policy, factory, seeder, route, or admin Livewire page exists.
- Translation keys for product reports do not exist yet.

Product moderation already exists:

- Admin product routes already exist under `/admin/products`.
- Admin routes are protected by `auth:admin`, `active.account:admin`, and `can:accessAdminPanel`.
- `ProductPolicy` already has admin-only `changeStatus`, `approve`, `reject`, and `moderate` methods.
- Admin product list/edit flows can deactivate or delete products.
- `RecordProductAuditLogsAction`, `RecordAdminAction`, and `AuditLogService` already exist.
- Seller product moderation notifications already exist for approved/rejected/moderation-required products.

## Product Report Entry Point

Report button placement:

- Add the report button on the public product detail page:
  - Livewire class: `app/Livewire/Frontend/Buyer/Products/Show.php`
  - Blade view: `resources/views/frontend/buyer/products/show.blade.php`
- The button should live near the product actions, after compare/cart controls or in the product header actions.

Guest reporting:

- `buyer/products` and `buyer/products/{product}` routes are currently public.
- Guest reports should be allowed behind a config flag.
- Guests must provide a valid email address.
- Authenticated buyers should not need to provide an email.

Seller reporting:

- Sellers should not be able to report their own products.
- Sellers should not see reporter private data.

## Supported Reasons

Use these reasons for this project:

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

Reasons should be implemented as a backed enum and translated via `reports.product.reasons.*`.

## Supported Statuses

Use these statuses:

- `pending`
- `reviewing`
- `resolved`
- `rejected`
- `dismissed`

Statuses should be implemented as a backed enum and translated via `reports.product.status.*`.

Product status effect:

- A report does not automatically hide a product.
- Admin may explicitly hide/deactivate a reported product from the moderation detail page.
- Hiding from a report sets `products.is_active = false`, records audit logs, and notifies the seller.
- Dismissing or rejecting a report must not change product visibility.

## Admin Moderation Page

Admin page:

- Add `/admin/reports` for the paginated moderation list.
- Add `/admin/reports/{productReport}` for report detail and actions.
- Use Livewire backend components under `app/Livewire/Backend/ProductReports`.
- Use Mary-style backend UI because current admin pages use Mary components.
- Add navigation item in backend navigation.

Admin capabilities:

- Filter by status.
- Filter by reason.
- Filter by product name/id search.
- Filter by seller.
- Open report details.
- See product, seller, authenticated reporter if present, guest email if guest report, message, status, timestamps, reviewer, and admin note.
- Start reviewing.
- Resolve.
- Reject.
- Dismiss.
- Hide/deactivate reported product with confirmation and required admin note.

## Files Expected To Change

Database/model:

- `database/migrations/*_create_product_reports_table.php`
- `app/Models/ProductReport.php`
- `app/Enums/ProductReportReason.php`
- `app/Enums/ProductReportStatus.php`
- `app/Models/Product.php`
- `app/Models/Users/Buyer.php`
- `app/Models/Users/Admin.php`
- `app/Models/Users/Seller.php`

Actions and policy:

- `app/Actions/ProductReports/CreateProductReportAction.php`
- `app/Actions/ProductReports/ReviewProductReportAction.php`
- `app/Actions/ProductReports/ResolveProductReportAction.php`
- `app/Actions/ProductReports/RejectProductReportAction.php`
- `app/Actions/ProductReports/DismissProductReportAction.php`
- `app/Actions/ProductReports/HideReportedProductAction.php`
- `app/Policies/ProductReportPolicy.php`
- `app/Providers/AuthServiceProvider.php`

Notifications and audit:

- `app/Notifications/Marketplace/ProductReportCreatedNotification.php`
- `app/Notifications/Marketplace/ProductHiddenDueToReportNotification.php`
- `app/Actions/Notifications/SendProductReportNotificationAction.php`
- `app/Services/AuditLogService.php` only if a helper is needed.

Routes/UI:

- `routes/admin.php`
- `app/Livewire/Frontend/Buyer/Products/Show.php`
- `resources/views/frontend/buyer/products/show.blade.php`
- `app/Livewire/Backend/ProductReports/Index.php`
- `app/Livewire/Backend/ProductReports/Show.php`
- `resources/views/livewire/backend/product-reports/index.blade.php`
- `resources/views/livewire/backend/product-reports/show.blade.php`
- `resources/views/layouts/backend/navigation.blade.php`

Config/translations/seeders/docs:

- `config/marketplace.php`
- `.env.example`
- `lang/en.json`
- `lang/lt.json`
- `database/factories/ProductReportFactory.php`
- `database/seeders/Demo/DemoProductReportSeeder.php`
- `database/seeders/Demo/DemoScenarioSeeder.php`
- `README.md`
- `CHANGELOG.md`
- `docs/product-reports.md`
- `docs/releases/*` if release workflow requires a new note.

Tests:

- `tests/Feature/Marketplace/ProductReportFeatureTest.php`
- `tests/Feature/Translations/TranslationFilesTest.php`
- Relevant policy/model tests if needed.

## Required Tests

- Buyer can report active product.
- Guest can report product when guest reports are enabled.
- Guest cannot report product when guest reports are disabled.
- Buyer cannot report deleted product.
- Buyer cannot report inactive product.
- Invalid reason is rejected.
- Message max length is enforced.
- Duplicate pending report is blocked for the same buyer/product.
- Duplicate pending report is blocked for the same guest email/product.
- Blocked buyer cannot report.
- Admin receives notification after report.
- Admin can view paginated report list.
- Admin can open report detail.
- Admin can mark report reviewing.
- Admin can resolve report.
- Admin can reject report.
- Admin can dismiss report.
- Admin can hide product from report action.
- Non-admin cannot access report moderation.
- Seller cannot moderate report.
- Seller cannot see reporter private data.
- Audit log is created for report creation and admin decisions.
- Translation keys exist.
- Pagination works for many reports.

## Risky Edge Cases

- The codebase is currently dirty and the full test suite already has known failures. Feature tests must be focused and should not depend on unrelated red baseline tests.
- Notifications use Laravel database notifications, while local notification policy code may still have stale assumptions around `user_id` versus `notifiable_id`.
- Buyer product detail routes are public despite the `buyer.` route prefix, so guest report UX must be intentional and documented.
- Multiple auth guards can be active in one session. Report creation must explicitly resolve buyer/seller/admin actors and not treat a seller as a buyer.
- Product report duplicate checks must ignore dismissed/resolved/rejected reports but block pending/reviewing duplicates.
- Admin hide action must not force delete or soft delete the product unless explicitly selected; this feature should only deactivate the product.
- Reporter identity must never be exposed to sellers.
- Translation JSON already contains drift; new keys must be added in both `en` and `lt`.

