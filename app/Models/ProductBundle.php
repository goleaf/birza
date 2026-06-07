<?php

namespace App\Models;

use App\Models\Users\Seller;
use Database\Factories\ProductBundleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ProductBundle extends Model
{
    /** @use HasFactory<ProductBundleFactory> */
    use HasFactory, SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_ARCHIVED = 'archived';

    public const DISCOUNT_TYPE_PERCENTAGE = 'percentage';

    public const DISCOUNT_TYPE_FIXED_AMOUNT = 'fixed_amount';

    protected $fillable = [
        'seller_id',
        'name',
        'slug',
        'description',
        'status',
        'discount_type',
        'discount_value',
        'starts_at',
        'ends_at',
        'published_at',
        'image_path',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'seller_id' => 'integer',
            'discount_value' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductBundleItem::class)->orderBy('sort_order');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_bundle_items')
            ->withPivot(['quantity', 'sort_order'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartBundleItem::class);
    }

    public function orderBundles(): HasMany
    {
        return $this->hasMany(OrderBundle::class);
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest('created_at');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeForSeller(Builder $query, Seller|int $seller): Builder
    {
        $sellerId = $seller instanceof Seller ? $seller->id : $seller;

        return $query->where('seller_id', $sellerId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeCurrentlyAvailable(Builder $query, ?Carbon $date = null): Builder
    {
        $date ??= now();

        return $query
            ->active()
            ->published()
            ->where(function (Builder $query) use ($date): void {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $date);
            })
            ->where(function (Builder $query) use ($date): void {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $date);
            });
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query
            ->currentlyAvailable()
            ->whereHas('seller', fn (Builder $query): Builder => $query->where('is_active', true))
            ->whereHas('items.product', fn (Builder $query): Builder => $query
                ->active()
                ->where('stock', '>', 0), '>=', self::minimumProducts())
            ->whereDoesntHave('items.product', fn (Builder $query): Builder => $query
                ->where('is_active', false)
                ->orWhereNotNull('deleted_at')
                ->orWhereColumn('products.stock', '<', 'product_bundle_items.quantity'))
            ->has('items', '>=', self::minimumProducts());
    }

    public function scopeWithActiveProducts(Builder $query): Builder
    {
        return $query->with([
            'seller:id,name,company_name,is_active,deleted_at',
            'items.product' => fn ($query) => $query
                ->select(['id', 'seller_id', 'name', 'price', 'stock', 'is_active', 'deleted_at', 'product_image'])
                ->with(['seller:id,name,company_name,is_active,deleted_at', 'primaryImage:id,product_id,disk,path,variants,is_primary,sort_order']),
        ]);
    }

    public function isCurrentlyAvailable(?Carbon $date = null): bool
    {
        $date ??= now();

        if ($this->status !== self::STATUS_ACTIVE || $this->published_at === null || $this->published_at->isAfter($date)) {
            return false;
        }

        if ($this->starts_at !== null && $this->starts_at->isAfter($date)) {
            return false;
        }

        if ($this->ends_at !== null && $this->ends_at->isBefore($date)) {
            return false;
        }

        return true;
    }

    public function isVisibleToBuyers(): bool
    {
        if ($this->trashed() || ! $this->isCurrentlyAvailable()) {
            return false;
        }

        $seller = $this->relationLoaded('seller')
            ? $this->seller
            : $this->seller()->select(['id', 'is_active', 'deleted_at'])->first();

        return $seller !== null
            && ! $seller->trashed()
            && (bool) $seller->is_active
            && $this->availableItems()->count() >= self::minimumProducts();
    }

    public function basePrice(): float
    {
        return round((float) $this->availableItems()->sum(
            fn (ProductBundleItem $item): float => (float) $item->product->price * (int) $item->quantity,
        ), 2);
    }

    public function discountAmount(?float $basePrice = null): float
    {
        $basePrice ??= $this->basePrice();

        if ($this->discount_type === null || $this->discount_value === null) {
            return 0.0;
        }

        $discount = match ($this->discount_type) {
            self::DISCOUNT_TYPE_PERCENTAGE => $basePrice * ((float) $this->discount_value / 100),
            self::DISCOUNT_TYPE_FIXED_AMOUNT => (float) $this->discount_value,
            default => 0.0,
        };

        return round(min($basePrice, max(0, $discount)), 2);
    }

    public function finalPrice(): float
    {
        $basePrice = $this->basePrice();

        return round(max(0, $basePrice - $this->discountAmount($basePrice)), 2);
    }

    public function imageUrl(): string
    {
        if (filled($this->image_path) && Storage::disk('public')->exists($this->image_path)) {
            return Storage::disk('public')->url($this->image_path);
        }

        $item = $this->availableItems()->first();

        return $item?->product?->imageUrl('medium') ?? asset((string) config('images.fallbacks.product'));
    }

    public function statusLabel(): string
    {
        return __('bundles.status.'.$this->status);
    }

    /**
     * @return list<string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE,
            self::STATUS_EXPIRED,
            self::STATUS_ARCHIVED,
        ];
    }

    /**
     * @return list<string>
     */
    public static function discountTypes(): array
    {
        return [
            self::DISCOUNT_TYPE_PERCENTAGE,
            self::DISCOUNT_TYPE_FIXED_AMOUNT,
        ];
    }

    public static function minimumProducts(): int
    {
        return 2;
    }

    /**
     * @return Collection<int, ProductBundleItem>
     */
    private function availableItems(): Collection
    {
        $items = $this->relationLoaded('items')
            ? $this->items
            : $this->items()->with('product.seller')->get();

        return $items->filter(fn (ProductBundleItem $item): bool => $item->product instanceof Product
            && $item->product->isPurchasableByBuyers()
            && (int) $item->product->stock >= (int) $item->quantity);
    }
}
