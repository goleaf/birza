# Image Pipeline Analysis

**Date:** 2026-06-07
**Scope:** models, migrations, Livewire components, Blade views, actions, config, storage, validation, factories, seeders, tests, and current SQLite schema.
**Mode:** analysis before refactor.

## Executive Summary

Birza image handling is currently product-only in the live database. Product media is split across legacy columns (`products.product_image`, `products.product_additional_image`), a JSON column (`products.image_library`), and an untracked-but-present `ProductImage` model/migration that has not been migrated into the current SQLite database. Categories, admins, buyers, and sellers do not currently have image/avatar/logo/banner columns in the live schema.

The highest-risk duplication is between admin product forms and seller product forms:

- Admin product create/edit uses Mary `WithMediaSync` through `InteractsWithProductImageLibrary`.
- Seller product create/edit manually validates, resizes to 500x500, WebP-encodes, stores under `products/`, deletes old files before the new image is written, and stores only basenames.

Display code rebuilds image URLs directly in multiple Blade views and one JavaScript search result renderer. Product grids load the same single legacy image path as detail views; there are no variants, no central fallback URL, and no existence-aware URL generation.

## Current Upload Flow

### Admin Product Images

Files:

- `app/Livewire/Backend/Products/Create.php`
- `app/Livewire/Backend/Products/Edit.php`
- `app/Livewire/Concerns/InteractsWithProductImageLibrary.php`
- `resources/views/backend/products/form.blade.php`

Flow:

- Livewire uses `WithFileUploads`.
- Blade uses `<x-mary-image-library wire:model="imageFiles" wire:library="imageLibrary">`.
- `InteractsWithProductImageLibrary::productImageLibraryRules()` validates `imageFiles.*` as nullable image files with `mimes:jpeg,png,jpg,gif,webp` and `max:15048`.
- `syncProductImageLibrary()` calls Mary `syncMedia()` with `storage_subpath: 'products'` and `model_field: 'image_library'`.
- Legacy columns are synced from the JSON library by `Product::syncLegacyImageColumnsFromLibrary()`.

Gaps:

- No project-owned resize/optimization/variant generation.
- No central image type config.
- No explicit rejection of animated GIFs.
- No variant metadata.
- Old file cleanup depends on Mary internals and `Product::deleteStoredImages()` only on force delete.

### Seller Product Images

Files:

- `app/Livewire/Frontend/Seller/Products/Create.php`
- `app/Livewire/Frontend/Seller/Products/Edit.php`
- `resources/views/frontend/seller/products/form.blade.php`

Flow:

- Livewire uses `WithFileUploads`.
- Main image uses `wire:model="product_image"` and additional image uses `wire:model="product_additional_image"`.
- Create requires `product_image`; edit makes both optional.
- Component-private `storeProductImage()` deletes the old file first, resizes via `Intervention\Image\Facades\Image::make()`, encodes WebP quality 80, writes `products/{uniqid()}.webp`, and returns only the basename.

Gaps:

- Upload, processing, naming, and deletion logic is duplicated in both seller create and edit.
- Old image is deleted before the replacement is safely saved.
- No gallery ordering, delete, replace, or choose-main behavior for seller.
- No WebP support in seller validation even though output is WebP.
- No backend validation for dimensions.
- No fallback helper.

### Buyer Credit Attachments

Files:

- `app/Livewire/Backend/Buyers/Credit.php`
- `app/Livewire/Backend/Buyers/CreditHistory.php`
- `resources/views/backend/buyers/credit.blade.php`

Flow:

- This is document upload, not marketplace image media.
- Validation allows `pdf,png,jpg,jpeg`, max `5120`.
- Stored under `credit-attachments` on the `public` disk.

Recommendation:

- Keep out of the image pipeline unless the project later wants shared file-attachment handling.

## Current Storage

Configured disks:

- `config/filesystems.php` default disk: `local`
- public disk root: `storage/app/public`
- public URL: `APP_URL/storage`
- symlink: `public/storage -> storage/app/public` exists

Current image locations:

- Product files: `storage/app/public/products/*`
- Seed product files: generated through `Storage::put('public/products/...')`, which writes to the default `local` disk at `storage/app/public/products/...`
- Livewire temporary uploads: `storage/app/livewire-tmp/*`
- Fallback placeholder: `public/images/admin-product-placeholder.svg`

Current storage risk:

- Product image paths are sometimes stored as basenames and sometimes as relative paths.
- Database stores no variants, dimensions, MIME, size, or original name for product legacy fields.
- There are 3500 files under `storage/app/public/products`, but the current SQLite `products` table is empty, so many local files are currently orphan-like fixture/development files.

## Entities Using Images

Live schema image fields:

- `products.product_image`
- `products.product_additional_image`
- `products.image_library`
- `credit_attachments.file_path` for non-product document/image attachments

Code-level image model present but not migrated in current DB:

- `app/Models/ProductImage.php`
- `database/migrations/2026_06_07_171224_create_product_images_table.php`
- `database/factories/ProductImageFactory.php`

Entities without live image fields:

- `categories`
- `users_admins`
- `users_buyers`
- `users_sellers`
- generic `users` from untracked relationship work

No current DB fields were found for category image, admin avatar, buyer avatar, seller logo, or seller banner.

## Upload Logic Files

Product upload/process:

- `app/Livewire/Concerns/InteractsWithProductImageLibrary.php`
- `app/Livewire/Backend/Products/Create.php`
- `app/Livewire/Backend/Products/Edit.php`
- `app/Livewire/Frontend/Seller/Products/Create.php`
- `app/Livewire/Frontend/Seller/Products/Edit.php`
- `database/seeders/test_information/ProductSeeder.php`

Attachment upload:

- `app/Livewire/Backend/Buyers/Credit.php`

## Resize Logic Files

- `app/Livewire/Frontend/Seller/Products/Create.php`
- `app/Livewire/Frontend/Seller/Products/Edit.php`
- `database/seeders/test_information/ProductSeeder.php`

Admin product upload currently relies on Mary media sync and does not apply project-owned resizing.

## Delete Logic Files

- `app/Livewire/Frontend/Seller/Products/Create.php` deletes an optional old image before storing, though create never passes an old image.
- `app/Livewire/Frontend/Seller/Products/Edit.php` deletes old image before storing the replacement.
- `app/Models/Product.php::deleteStoredImages()` deletes legacy/library paths.
- `app/Livewire/Backend/Products/Index.php::forceDeleteProduct()` calls `deleteStoredImages()` before force deleting.

Deletion gaps:

- Soft delete does not delete image files, which is probably correct.
- Force delete only deletes paths known in legacy columns or JSON library.
- Existing `product_images` records would cascade from the DB, but files would not be deleted unless handled explicitly.
- Replacement can lose the old image if the new write fails after the delete.
- Deleting a product image separately from the product is not supported in seller UI.

## Image Validations

Existing validations:

- Admin product library: `nullable`, `image`, `mimes:jpeg,png,jpg,gif,webp`, `max:15048`
- Seller create primary image: `required`, `image`, `mimes:jpeg,png,jpg,gif`, `max:15048`
- Seller create additional image: `nullable`, `image`, `mimes:jpeg,png,jpg,gif`, `max:15048`
- Seller edit primary/additional images: `nullable`, `image`, `mimes:jpeg,png,jpg,gif`, `max:15048`
- Buyer credit attachment: `nullable`, `file`, `mimes:pdf,png,jpg,jpeg`, `max:5120`

Missing validations:

- No central rules by image type.
- No dimensions constraints.
- No explicit MIME-type content whitelist with Laravel `File::image()->types(...)`.
- No animated image rejection.
- No configurable per-type maximum dimensions.
- Seller validation excludes WebP despite the rest of the system using WebP.
- Admin accepts GIF but generated variants/fallback behavior does not define GIF policy.
- No validation for gallery count.
- No validation that seller image actions target the authenticated seller's product beyond page mount/save ownership checks.

## Display Logic Files

Direct product URL construction exists in:

- `resources/views/backend/products/index.blade.php`
- `resources/views/backend/products/show.blade.php`
- `resources/views/backend/orders/show.blade.php`
- `resources/views/backend/sellers/show.blade.php`
- `resources/views/frontend/buyer/products/index.blade.php`
- `resources/views/frontend/buyer/products/show.blade.php`
- `resources/views/frontend/buyer/cart/index.blade.php`
- `resources/views/frontend/buyer/orders/show.blade.php`
- `resources/views/frontend/seller/dashboard/index.blade.php`
- `resources/views/frontend/seller/orders/show.blade.php`
- `resources/views/frontend/seller/products/partials/products_table.blade.php`
- `resources/views/frontend/seller/products/show.blade.php` uses `$product->image`, which does not match current `Product` fields and appears stale.
- `resources/views/frontend/buyer/products/index.blade.php` JavaScript search results hardcode `/storage/products/${item.product_image}`.

Display gaps:

- No central product image URL method with variant selection.
- No central fallback image.
- Product grids do not request smaller variants.
- Admin tables do not request thumbnails.
- Several image tags lack `loading="lazy"`, dimensions/aspect-ratio, or fallback behavior.
- Search API returns only `product_image` basename, forcing frontend JavaScript to hardcode the storage path.
- Order history depends on mutable product image state and can break if product image is missing or product is deleted.

## Broken Path Risks

Broken paths can appear when:

- A product has `product_image` set but the file is missing from `storage/app/public/products`.
- `image_library` contains absolute or stale `url` values rather than regenerating from disk/path.
- A seller replacement deletes the old image and then fails before writing the new image.
- Search results return null/empty `product_image` and JavaScript still sets `/storage/products/null` or `/storage/products/`.
- `resources/views/frontend/seller/products/show.blade.php` checks `$product->image`, a non-current field.
- Force deleting a product removes files, then existing order pages still try to render `$item->product->product_image`.
- Factories generate random filenames without corresponding files.

## Missing Tests

Current tests cover:

- Admin product Mary upload persists `image_library` and legacy columns.
- Legacy columns can be converted into an `image_library` preview.
- Some frontend product/gallery render expectations.
- Buyer credit attachment storage exists.

Missing tests for the requested pipeline:

- Valid product image upload through project-owned pipeline.
- Invalid file rejected.
- Too-large image rejected.
- Wrong MIME type rejected.
- Animated image rejected.
- Variants generated: thumbnail, small, medium, large.
- Old image deleted after successful replacement.
- Old image retained if replacement fails.
- Gallery image added.
- Gallery image deleted.
- Gallery image reordered.
- Main image selected.
- Missing image uses fallback.
- Database stores relative paths only.
- Seller cannot upload/delete image for another seller's product.
- Deleting one image removes all variants.
- Checkout/order history still renders if product image is later deleted.
- API search returns a safe image URL/fallback rather than a basename requiring hardcoded JS.

## Recommended First Refactor

Refactor product media first, because it is the only marketplace image type currently represented in the live schema and it has real duplication, deletion risk, and display breakage.

Recommended direction:

1. Introduce a central `config/images.php` with image types and variants.
2. Introduce a small service/action set:
   - `GenerateImageVariantsAction`
   - `UploadImageAction`
   - `DeleteImageAction`
   - `ReplaceImageAction`
   - product-specific gallery helpers only where needed.
3. Formalize the existing `product_images` table by expanding it for variants/metadata and migrating from legacy columns/JSON.
4. Keep `products.product_image` and `products.product_additional_image` synchronized during the refactor for compatibility with existing code/tests.
5. Add Product model methods for:
   - `primaryImage()`
   - `orderedImages()`
   - `imageUrl($variant = 'medium')`
   - `imageUrls()`
   - `fallbackImageUrl()`
6. Update seller product create/edit to use the same reusable pipeline as admin product forms.
7. Update display paths to use the model/helper URLs and requested variants.
8. Add focused tests around the pipeline before broad UI cleanup.

## Proposed Product Image Configuration

Initial product image type:

- allowed MIME/extensions: JPEG, PNG, WebP
- reject GIF/animated files for now
- max file size: 8 MB
- max dimensions: 6000x6000
- disk: `public`
- directory: `images/products/{product_id}`
- keep original: true for product images
- variants:
  - `thumb`: 160x160 cover, quality 78
  - `small`: 320x240 cover, quality 80
  - `medium`: 640x480 cover, quality 82
  - `large`: 1200x900 contain/scale down, quality 84
  - `original`: normalized original copy if kept
- database stores relative paths, never server absolute paths

Future image types should be configured but not added to schema until used:

- category image
- user avatar
- seller logo
- seller banner
- buyer avatar
- admin upload

## Transaction and Deletion Policy

- Write new files first.
- Create/update DB records inside a transaction after files are generated.
- Delete old files only after the transaction succeeds.
- If DB save fails, delete newly generated files as cleanup.
- Deleting an image deletes all variant paths recorded in metadata.
- Replacing primary image keeps the old image until the new image and DB state are committed.

## Documentation Sources Checked

- Laravel Boost application info: Laravel 12.61.1, Livewire 4.3.1, PHPUnit 11.5.55, PHP 8.5, sqlite.
- Laravel 12 filesystem docs: public disk, `Storage::url()`, delete/deleteDirectory, `Storage::fake()`.
- Laravel 12 validation docs: `File::image()`, MIME sniffing, dimensions.
- Livewire 4 uploads docs: temporary image preview URLs and file upload testing.
- Intervention Image docs/local package context: project currently uses `intervention/image` 2.7 through the Laravel facade.
