# Quick Task 260607-skz: Create Proper Image Pipeline

## Objective

Standardize marketplace product image handling with a reusable Laravel image pipeline, while preserving existing Livewire/Mary product image UI and legacy product fields during transition.

## Plan

1. Add central image configuration.
   - Define product image variants, validation limits, storage disk, directory pattern, quality, and fallback.
   - Keep future types listed only as config entries where no schema exists yet.

2. Add reusable image actions.
   - Generate variants with Intervention Image.
   - Upload images with safe filenames and relative paths.
   - Delete all paths/variants.
   - Replace only after successful upload.
   - Sync Livewire/Mary image library state into product image records.

3. Formalize product image storage.
   - Expand the existing `product_images` migration/model to store metadata and variant paths.
   - Keep legacy product fields and `image_library` synchronized for current UI/API compatibility.

4. Refactor product write flows.
   - Backend product create/edit: replace Mary `syncMedia()` persistence with project pipeline.
   - Seller product create/edit: remove duplicated `storeProductImage()` and use the same pipeline.
   - Ensure seller ownership checks remain intact.

5. Refactor display.
   - Add model helpers for product image variants and fallback URLs.
   - Update product grids/admin tables to use thumbnails/small variants.
   - Update product details/galleries to use larger variants.
   - Update search API to return a safe URL.

6. Add tests.
   - Cover validation config, generated variants, replacement safety, deletion, gallery ordering/main image, fallback URL, relative DB paths, seller ownership, and order history resilience.

7. Verify.
   - Run focused tests first.
   - Run Pint for changed PHP files.
   - Run migration-from-zero and seeders if schema changes.
   - Run full suite/build if practical in current dirty tree.

## Known Constraints

- The worktree already contains many unrelated modified/untracked files; only image-pipeline files should be staged for the requested commit.
- `product_images` exists in files but not in the current SQLite schema.
- No live category/user avatar/seller logo/banner schema exists, so product media is the implementation target.
