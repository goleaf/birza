<?php

namespace App\Actions\Wishlists;

use App\Models\Users\Buyer;
use App\Models\Wishlist;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateWishlistAction
{
    public function __construct(
        private readonly RecordWishlistAuditLogAction $recordAuditLog,
    ) {}

    /**
     * @param  array{name?: string|null, description?: string|null, is_default?: bool|null, is_private?: bool|null}  $data
     */
    public function handle(Buyer $buyer, Wishlist $wishlist, array $data): Wishlist
    {
        $this->authorizeOwner($buyer, $wishlist);

        $validated = Validator::make($data, [
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('wishlists', 'name')
                    ->where(fn ($query) => $query->where('buyer_id', $buyer->id))
                    ->ignore($wishlist->id),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_default' => ['nullable', 'boolean'],
            'is_private' => ['nullable', 'boolean'],
        ])->validate();

        return DB::transaction(function () use ($buyer, $wishlist, $validated): Wishlist {
            $lockedWishlist = Wishlist::query()
                ->where('buyer_id', $buyer->id)
                ->lockForUpdate()
                ->findOrFail($wishlist->id);

            $oldValues = $this->recordAuditLog->snapshot($lockedWishlist);
            $isDefault = (bool) ($validated['is_default'] ?? $lockedWishlist->is_default);

            if ($isDefault) {
                $buyer->wishlists()
                    ->where('id', '!=', $lockedWishlist->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            } elseif ($lockedWishlist->is_default && ! $this->anotherDefaultExists($buyer, $lockedWishlist)) {
                $isDefault = true;
            }

            $lockedWishlist->forceFill([
                'name' => trim((string) $validated['name']),
                'slug' => $this->slug($validated['name']),
                'description' => $this->nullableText($validated['description'] ?? null),
                'is_default' => $isDefault,
                'is_private' => (bool) ($validated['is_private'] ?? true),
            ])->save();

            $this->recordAuditLog->updated($buyer, $lockedWishlist, $oldValues);

            return $lockedWishlist->fresh(['buyer']);
        });
    }

    private function authorizeOwner(Buyer $buyer, Wishlist $wishlist): void
    {
        abort_unless($wishlist->isOwnedBy($buyer), 403);
    }

    private function anotherDefaultExists(Buyer $buyer, Wishlist $wishlist): bool
    {
        return $buyer->wishlists()
            ->where('id', '!=', $wishlist->id)
            ->where('is_default', true)
            ->exists();
    }

    private function slug(string $name): ?string
    {
        $slug = Str::slug($name);

        return $slug === '' ? null : $slug;
    }

    private function nullableText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
