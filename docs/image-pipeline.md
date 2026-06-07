# Image Pipeline

Birza image handling is centralized in `App\Actions\Images` and configured in `config/images.php`.

## Storage

- Product images use the `public` disk.
- Stored database paths are relative storage paths, never absolute server paths.
- Product files live under `storage/app/public/images/products/{product_id}`.
- The public symlink must exist: `php artisan storage:link`.

Product variants are written under variant folders:

| Variant | Example path | Use |
| --- | --- | --- |
| `thumb` | `images/products/15/thumb/{uuid}.webp` | Admin tables, compact rows, order history |
| `small` | `images/products/15/small/{uuid}.webp` | Product cards and grids |
| `medium` | `images/products/15/medium/{uuid}.webp` | Form previews and legacy mirror fields |
| `large` | `images/products/15/large/{uuid}.webp` | Product detail galleries |
| `original` | `images/products/15/original/{uuid}.jpg` | Preserved source file when enabled |

## Database

Product galleries are stored in `product_images`.

Important columns:

- `product_id`
- `type`
- `disk`
- `path`
- `original_path`
- `variants`
- `original_name`
- `mime_type`
- `size`
- `width`
- `height`
- `alt_text`
- `caption`
- `sort_order`
- `is_primary`

The older `products.product_image`, `products.product_additional_image`, and `products.image_library` fields remain as compatibility mirrors. New code should prefer `ProductImage` records and `Product::imageUrl()` / `Product::imageGalleryUrls()`.

## Actions

- `ValidateImageUploadAction`: validates backend file rules from `config/images.php`.
- `UploadImageAction`: validates, generates a UUID filename, writes original and variants, and rolls back written files if processing fails.
- `GenerateImageVariantsAction`: resizes and converts images through Intervention Image.
- `ReplaceImageAction`: writes the new image first, then deletes old paths only after success.
- `DeleteImageAction`: deletes original and all variant paths for a `ProductImage`, path array, or path string.
- `SyncProductImageLibraryAction`: persists product gallery order, primary image, uploaded files, legacy paths, stale image deletion, and legacy mirror fields inside a database transaction.

Livewire components should call the shared trait `InteractsWithProductImageLibrary` and never process images directly.

## Validation

Validation is backend-only and does not trust the original file name or extension.

Current product rules:

- MIME types: `image/jpeg`, `image/png`, `image/webp`
- Max size: `8192 KB`
- Max dimensions: `6000x6000`
- Animated GIF/WebP rejected
- Output format: WebP
- Safe filename: generated UUID

To change sizes, quality, formats, or directories, update `config/images.php`. Do not hardcode image dimensions in controllers, Livewire components, or Blade.

## Display

Use these helpers:

```php
$product->imageUrl('thumb');
$product->imageUrl('small');
$product->imageUrl('large');
$product->imageGalleryUrls('large');
```

Display rules:

- Product cards use `small`.
- Product detail galleries use `large`.
- Admin tables, order rows, and cart rows use `thumb`.
- Missing files fall back to `config('images.fallbacks.product')`.
- Product alt text should use the product name unless custom `alt_text` exists.

## Safe Replacement And Deletion

For replacement:

1. Validate the new upload.
2. Save all new files.
3. Persist the database change.
4. Delete old files only after the new files and database state are safe.

For deletion:

- Use `DeleteImageAction`.
- Delete every path returned by `ProductImage::storedPaths()`.
- Product soft deletes do not remove images because the product can be restored.
- Backend force delete calls `Product::deleteStoredImages()` before removing the record.

## Seed Images

`Database\Seeders\test_information\ProductSeeder` generates local WebP seed uploads and sends them through `SyncProductImageLibraryAction`.

The demo seed includes:

- Products with a main image.
- Products with gallery images.
- Products without images, to verify fallback behavior.

Seed images do not depend on internet URLs.

## Adding Image Support To Another Model

1. Add or reuse an image type in `config/images.php`.
2. Add a model field or image relation only if the feature needs persistence.
3. Validate uploads with `ValidateImageUploadAction`.
4. Store with `UploadImageAction` or replace with `ReplaceImageAction`.
5. Persist only relative paths and metadata.
6. Delete old paths with `DeleteImageAction`.
7. Render through a model helper that checks storage existence and returns a fallback.
8. Add tests for valid upload, invalid MIME, large files, variants, replacement failure, deletion, and fallback.

## Tests

Focused pipeline tests:

```bash
php artisan test --compact tests/Feature/Images/ProductImagePipelineTest.php
```

Useful related tests:

```bash
php artisan test --compact tests/Feature/Controllers/Backend/ProductControllerTest.php
php artisan test --compact tests/Feature/Controllers/Frontend/Buyer/ProductControllerTest.php
php artisan test --compact tests/Feature/Controllers/Frontend/Seller/ProductControllerTest.php
php artisan test --compact tests/Unit/Backend/ProductImageLibraryMigrationTest.php
```
