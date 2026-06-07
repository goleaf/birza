<?php

namespace App\Actions\Wishlists;

use App\Models\Users\Buyer;
use App\Models\Wishlist;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CreateWishlistAction
{
    public function __construct(
        private readonly RecordWishlistAuditLogAction $recordAuditLog,
    ) {}

    /**
     * @param  array{name?: string|null, description?: string|null, is_default?: bool|null, is_private?: bool|null}  $data
     */
    public function handle(Buyer $buyer, array $data): Wishlist
    {
        $validated = Validator::make($data, [
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('wishlists', 'name')->where(fn ($query) => $query->where('buyer_id', $buyer->id)),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_default' => ['nullable', 'boolean'],
            'is_private' => ['nullable', 'boolean'],
        ])->validate();

        return DB::transaction(function () use ($buyer, $validated): Wishlist {
            $isDefault = (bool) ($validated['is_default'] ?? false);

            if (! $buyer->wishlists()->exists()) {
                $isDefault = true;
            }

            if ($isDefault) {
                $buyer->wishlists()->where('is_default', true)->update(['is_default' => false]);
            }

            $wishlist = $buyer->wishlists()->create([
                'name' => trim((string) $validated['name']),
                'slug' => $this->slug($validated['name']),
                'description' => $this->nullableText($validated['description'] ?? null),
                'is_default' => $isDefault,
                'is_private' => (bool) ($validated['is_private'] ?? true),
            ]);

            $this->recordAuditLog->created($buyer, $wishlist);

            return $wishlist->fresh(['buyer']);
        });
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
