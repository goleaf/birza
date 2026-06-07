# Testing Report: Core Marketplace Flows

**Created:** 2026-06-07
**Scope:** Laravel 12 / Livewire 4 marketplace feature coverage
**Baseline command:** `php artisan test --compact --testsuite=Feature`

## Current Setup

- Test runner: PHPUnit 11 via `php artisan test`; no Pest setup was found.
- Test database: `phpunit.xml` uses SQLite in memory with `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:`.
- Reset strategy: existing feature tests consistently use `Illuminate\Foundation\Testing\RefreshDatabase`.
- Auth guards: `admin`, `buyer`, and `seller` session guards, backed by separate `users_admins`, `users_buyers`, and `users_sellers` providers.
- Main UI test style: HTTP route assertions plus Livewire component tests using `Livewire::test(...)`.
- Fakes in use: `Storage::fake()` for file uploads; mail/rate limiter fakes in auth tests; `Notification::fake()` for order, stock-alert, report, question, and moderation notification assertions.
- Baseline feature status after refreshing Composer autoload: **140 passed, 4 failed**.

## Tests That Exist Now

- **Feature tests:** 44 files, about 141 tests.
- **Unit tests:** 54 files, about 173 tests.
- **Authentication:** admin login/logout, buyer login/register, seller login/register, seller verification token/password reset, buyer/seller logout.
- **Admin pages:** dashboard, profile, products, categories, attributes, attribute values, countries, buyers, sellers, orders, settings.
- **Buyer pages:** dashboard, profile, product index/show, cart index, order index/show.
- **Seller pages:** dashboard, profile, products index/create/edit display, soft delete modal, orders index/show, transactions.
- **API/search:** product search JSON and localized category/product search.
- **Filters:** product filter allowlist, soft delete/status search, selected attribute-value filtering.
- **Seeders:** admin, attributes, categories, countries, global settings, product attributes, products, test users, full database seeder.
- **Translations:** supported JSON language files have matching keys and core dot keys.
- **Relationship/unit coverage:** models, relationships, middleware shells, providers, order status helper, safe Markdown, foreign key constraints.

## Current Baseline Failures

- `tests/Feature/Controllers/Backend/ProductControllerTest.php::test_product_create_persists_toggle_states` expects legacy image columns to store basenames, but the new image pipeline writes normalized medium variant paths.
- `tests/Feature/Controllers/Backend/ProductControllerTest.php::test_product_show_displays` does not see the expected localized attribute name; product attribute pivot data is not being loaded/rendered as the test expects.
- `tests/Feature/Controllers/Frontend/Seller/ProductControllerTest.php::test_product_edit_form_displays_gallery_for_existing_images` still expects PhotoSwipe assets that are not present in the current Mary image-library form.
- `tests/Feature/Support/SpotlightTest.php::test_spotlight_returns_quick_actions_and_orders` does not find the global-settings quick action for the translated settings label.

## Main Flows Not Tested or Weakly Tested

- Public catalog is not testable as requested because catalog routes are buyer-authenticated (`buyer.products.*`); there is no public catalog route.
- Buyer catalog has no direct tests for inactive/deleted product exclusion on the index, price filter, empty state, pagination, or inactive detail 404.
- Product search exists only on the API endpoint and product filters; the buyer catalog page does not expose a name search parameter.
- Sorting is not implemented on the buyer catalog page, so sorting tests would be artificial.
- Seller product create/edit success and validation are lightly covered; seller ownership denial for another seller's product is under-tested.
- Image upload is partially covered through backend product creation, but direct validation for invalid mime/oversized files and variant creation is missing.
- Cart checkout is under-tested: quantity update/remove exist in code but not tests; checkout order creation, order items, stock decrement, cart clearing, unavailable product handling, and client-side price manipulation are not covered.
- Orders have display tests, but dangerous ownership cases and deleted-product resilience are incomplete.
- Order status workflow has some buyer/seller happy-path tests, but status history fields, invalid transitions, admin reason requirements, unauthorized actor restrictions, and notifications are under-tested.
- Dashboard tests check rendering but not cross-user data isolation or exact counts.
- Admin action authorization is mostly route-auth based; non-admin cross-guard access is weakly tested.
- Notification domain model and marketplace notification classes are covered through order status, checkout, stock alert, question, and report flows.
- Multilingual coverage checks key parity, but raw-key absence and locale-specific validation/status/cart/checkout messages are incomplete.

## Roles Not Fully Tested

- Inactive admin access.
- Inactive buyer direct access to protected buyer routes.
- Inactive seller direct access to protected seller routes.
- Cross-guard access where a buyer hits seller/admin pages or a seller hits buyer/admin pages.
- Generic `web` users attempting admin pages.
- Admin access to all admin surfaces is broadly tested, but admin status workflow actions are under-tested.

## Dangerous Access Cases Not Tested

- Manual URL access to another seller's product edit page.
- Livewire soft-delete action against another seller's product.
- Manual URL access to another buyer's order.
- Manual URL access to another seller's unrelated order.
- Checkout with inactive, deleted, or insufficient-stock products.
- Checkout with cart prices manipulated below the current product price.
- Direct order status mutation bypassing the status action is unit-tested only partially through model behavior, while the feature suite now covers the public status actions and denied cross-owner attempts.
- Livewire order-status action called by an unauthorized seller or buyer.

## Factories Missing or Weak

- Factories exist for the main current models: admins, buyers, sellers, products, product images, carts, cart items, orders, order items, order status history, reviews, notifications, countries, categories, attributes, global settings, addresses, transactions, wishlists, wishlist items, product questions, product reports, product stock alerts, discounts, and promo codes.
- Buyer/Seller factories have `active()` and `inactive()` states, but no `unverified()` state.
- Admin factory has no `inactive()` state even though `users_admins.is_active` exists.
- Product factory has `active()` and `inactive()` but no `outOfStock()`, `withRequiredCommerceFields()`, or image-library state.
- Cart and cart-item factories map to database cart tables, which now match the active buyer cart and checkout path.

## Seeders Missing or Weak

- Seeders exist for admin, categories, countries, attributes, global settings, product attributes, products, and test users.
- Demo seeders now cover complete buyer cart/order/product/seller graphs, product images, reviews, notifications, addresses, order status history, multi-seller checkout scenarios, stock alerts, product questions, product reports, wishlists, discounts, and promo codes.
- Feature tests should still create the minimum data they need with factories unless the scenario is explicitly a seeder test.

## Pages That Cannot Be Tested Because Data or Routes Are Missing

- Public catalog page: no public catalog route.
- Favorites as originally named are implemented as product wishlists; wishlist list/detail/remove/duplicate/ownership flows are now testable.
- Reviews UI: `Review` model and migration exist, but no review routes or page actions are exposed.
- Notification list/read UI exists in the current development tree; notification creation/read access is covered by marketplace notification tests outside the core scenario helper set.
- Address-based checkout: address model/migration exists, but current checkout component does not collect or snapshot addresses.
- Gallery reordering: product image library exists, but no explicit reorder action was found outside Mary media sync behavior.

## Duplicated or Weak Tests

- Many controller tests assert page chrome and translated labels but do not assert ownership, database effects, or authorization.
- Several old unit tests only assert provider/middleware classes exist; they add little marketplace safety.
- Some image-related tests still expect legacy assets/paths while the code has moved toward Mary image library and `product_images`.
- Dashboard tests assert visual fragments but not exact data isolation/counts.

## Tests to Create First

1. Authentication and role-access tests for cross-guard redirects, inactive account protection, invalid logins, and protected route denial.
2. Catalog tests for active/inactive/deleted product visibility and inactive detail 404.
3. Cart and checkout tests for backend totals, unavailable products, stock checks, order/item creation, and cart clearing.
4. Order ownership and status workflow tests for buyer/seller/admin permissions, invalid transitions, status history, and notifications.
5. Seller product ownership tests for another seller's edit/delete attempts.
6. Image upload validation/action tests for valid images, invalid files, oversized files, and generated variants.
7. Multilingual smoke tests for selected locale rendering and raw key absence on main pages.

## Implemented Coverage

- Added `tests/Feature/Marketplace/AuthenticationFeatureTest.php` for public/auth pages, buyer login/logout, invalid login, inactive buyer/seller/admin restrictions, and unverified seller login denial.
- Added `tests/Feature/Marketplace/RoleAccessFeatureTest.php` for guest, buyer, seller, admin, generic web user, cross-order, and cross-seller product access.
- Added `tests/Feature/Marketplace/ProductCatalogFeatureTest.php` for active/inactive/deleted catalog visibility, category/price filters, empty state, and detail-page access rules.
- Added `tests/Feature/Marketplace/CartCheckoutFeatureTest.php` for database-backed add-to-cart, quantity update, removal, empty cart, backend price recalculation, snapshots, stock/deleted-product checkout failures, and cart ownership.
- Added `tests/Feature/Marketplace/OrderStatusWorkflowFeatureTest.php` for valid/invalid transitions, admin reason requirements, unauthorized actor restrictions, status history fields, and status-change notifications.
- Added `tests/Feature/Marketplace/AuditLoggingFeatureTest.php` for order status audit logs and specific cancellation audit events.
- Added `tests/Feature/Marketplace/ImageUploadFeatureTest.php` for seller product image upload, oversized image rejection, and missing-file fallback behavior.
- Added `tests/Feature/Marketplace/MultilingualFeatureTest.php` for locale switching, invalid locale fallback, guest/authenticated page locale rendering, translated status labels, raw-key absence, and required marketplace keys.
- Added `tests/Feature/Marketplace/ProductQuestionFeatureTest.php` for guest/buyer questions, seller answer authorization, public answered-only display, and validation.
- Added `tests/Feature/Marketplace/ProductReportFeatureTest.php` for guest/buyer reports, duplicates, disabled guest reports, moderation actions, product hiding, seller notification privacy, pagination, and translation keys.
- Added `tests/Feature/Marketplace/ProductStockAlertFeatureTest.php` for buyer subscriptions, duplicate prevention, inactive/deleted product restrictions, seller ownership denial, back-in-stock notifications, and low-stock seller alerts.
- Added `tests/Feature/Marketplace/WishlistFeatureTest.php` and `tests/Feature/Marketplace/ProductWishlistFeatureTest.php` for wishlist creation, duplicate favorites, list privacy, product removal, add-to-cart handoff, and inactive product restrictions.
- Added `tests/Feature/Marketplace/SellerDiscountPromoCodeFeatureTest.php` for seller discount and promo-code management plus checkout revalidation coverage.
- Added `tests/Feature/Marketplace/PerformanceQueryBudgetTest.php` for query-budget coverage around catalog, order, seller-product, dashboard, and checkout list loads.
- Added `tests/Feature/Support/MarketplaceTestHelpers.php` for role users, products, carts, orders, and acting-as helpers.

## Implementation Fixes Required by Tests

- Moved buyer product detail add-to-cart onto the database-backed `AddCartItemAction` so product detail, cart display, and checkout use the same cart storage.
- Added active-account middleware aliases to buyer, seller, and admin protected route groups and guarded inactive login paths.
- Added reusable seller product form `rules()` methods so Livewire media sync can validate uploads before save.
- Merged duplicate `ChangeOrderStatusAction` constructors so audit logging and marketplace notifications can be resolved together.
- Updated the legacy buyer cart controller test to seed database cart rows instead of LaraCart session state.

## Focused Verification

- `php artisan test --compact tests/Feature/Marketplace` -> **116 passed, 635 assertions**.
- `php artisan test --compact tests/Unit` -> **182 passed, 368 assertions**.
- `php artisan migrate:fresh --seed --no-interaction` -> passed after stale test processes holding `database/birza.sqlite` were allowed to clear.
- `php artisan view:clear && php artisan test --compact tests/Feature/Marketplace/ProductReportFeatureTest.php tests/Feature/Images/ProductImagePipelineTest.php` -> **26 passed, 236 assertions**.
- `php artisan test --compact tests/Feature/Images/ProductImagePipelineTest.php tests/Feature/Translations/TranslationFilesTest.php` -> **10 passed, 594 assertions**.
- A clean all-feature-suite rerun could not be isolated at the end of this pass because other Codex goals were repeatedly running `php artisan test --compact` in the same checkout. A prior broad run during that contention reported **311 passed, 5 failed, 2498 assertions**; the image/view failures reproduced as shared storage or compiled-view interference and passed when rerun in focused isolation.
