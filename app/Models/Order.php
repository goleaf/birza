<?php

namespace App\Models;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderStatusActorRole;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use App\Support\LocaleFormatter;
use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use LogicException;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected static bool $allowsStatusMutation = false;

    protected $fillable = [
        'payment_method',
        'promo_code',
        'shipping_address_snapshot',
        'billing_address_snapshot',
        'delivery_method',
        'tracking_number',
        'carrier_name',
        'estimated_delivery_date',
        'shipped_at',
        'delivered_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order_total' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'promo_code_id' => 'integer',
            'promo_discount_amount' => 'decimal:2',
            'buyer_id' => 'integer',
            'payment_status' => OrderPaymentStatus::class,
            'status' => OrderStatus::class,
            'estimated_delivery_date' => 'date',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $order): void {
            if (! $order->exists || self::$allowsStatusMutation || ! $order->isDirty('status')) {
                return;
            }

            throw new LogicException(__('orders.status.messages.direct_change_forbidden'));
        });
    }

    public static function allowStatusMutation(Closure $callback): mixed
    {
        $previous = self::$allowsStatusMutation;
        self::$allowsStatusMutation = true;

        try {
            return $callback();
        } finally {
            self::$allowsStatusMutation = $previous;
        }
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class)->withTrashed();
    }

    public function sellers(): HasManyThrough
    {
        return $this->hasManyThrough(
            Seller::class,
            OrderItem::class,
            'order_id',
            'id',
            'id',
            'seller_id'
        )->withTrashed();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_of_origin');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function orderBundles(): HasMany
    {
        return $this->hasMany(OrderBundle::class);
    }

    public function bundles(): HasMany
    {
        return $this->orderBundles();
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest('created_at');
    }

    public function events(): HasMany
    {
        return $this->hasMany(OrderEvent::class)->oldest('created_at');
    }

    public function publicEvents(): HasMany
    {
        return $this->events()->publicVisible();
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest('created_at');
    }

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'order_items')
            ->withPivot(['quantity', 'unit_price', 'total_price', 'seller_id'])
            ->wherePivotNull('deleted_at')
            ->withTimestamps();
    }

    public function getTotalAttribute(): float
    {
        return (float) $this->order_total;
    }

    public function scopeStatus(Builder $query, OrderStatus $status): Builder
    {
        return $query->where('status', $status->value);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->status(OrderStatus::Pending);
    }

    public function scopeAccepted(Builder $query): Builder
    {
        return $query->status(OrderStatus::Accepted);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('payment_status', OrderPaymentStatus::Paid->value);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('payment_status', OrderPaymentStatus::Failed->value);
    }

    public function scopeRevenueRecognized(Builder $query): Builder
    {
        return $query->whereIn('status', OrderStatus::revenueStatuses());
    }

    public function scopeForBuyer(Builder $query, Buyer|int $buyer): Builder
    {
        $buyerId = $buyer instanceof Buyer ? $buyer->id : $buyer;

        return $query->where('buyer_id', $buyerId);
    }

    public function scopeSummaryColumns(Builder $query): Builder
    {
        return $query->select([
            'id',
            'buyer_id',
            'payment_method',
            'payment_status',
            'status',
            'order_total',
            'created_at',
            'updated_at',
        ]);
    }

    public function scopePlacedBetween(Builder $query, ?string $dateFrom = null, ?string $dateTo = null): Builder
    {
        if (filled($dateFrom)) {
            $query->where('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
        }

        if (filled($dateTo)) {
            $query->where('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
        }

        return $query;
    }

    public function scopeWithFullDetails(Builder $query): Builder
    {
        return $query->with(['buyer', 'orderBundles.items.product', 'orderItems.product', 'orderItems.seller', 'statusHistory', 'events']);
    }

    public function lifecycleStatus(): OrderStatus
    {
        return $this->status ?? OrderStatus::Pending;
    }

    public function statusLabel(): string
    {
        return $this->lifecycleStatus()->label();
    }

    public function statusDescription(): string
    {
        return $this->lifecycleStatus()->description();
    }

    public function statusBadgeClass(): string
    {
        return $this->lifecycleStatus()->maryBadgeClass();
    }

    public function paymentStatusBadgeClass(): string
    {
        return $this->payment_status?->maryBadgeClass() ?? OrderPaymentStatus::Pending->maryBadgeClass();
    }

    public function paymentStatusLabel(): string
    {
        return $this->payment_status?->label() ?? OrderPaymentStatus::Pending->label();
    }

    public function paymentStatusUiColor(): string
    {
        return $this->payment_status?->uiBadgeColor() ?? OrderPaymentStatus::Pending->uiBadgeColor();
    }

    public function canBeCancelled(): bool
    {
        return $this->lifecycleStatus()->canTransitionTo(OrderStatus::Cancelled);
    }

    public function hasTrackingDetails(): bool
    {
        return filled($this->tracking_number)
            || filled($this->carrier_name)
            || $this->estimated_delivery_date !== null
            || $this->shipped_at !== null
            || $this->delivered_at !== null;
    }

    public function canReceiveShippingUpdate(): bool
    {
        return in_array($this->lifecycleStatus(), [
            OrderStatus::Accepted,
            OrderStatus::Processing,
            OrderStatus::Shipped,
        ], true);
    }

    /**
     * @return array<int, OrderStatus>
     */
    public function availableTransitionsFor(?Authenticatable $actor): array
    {
        $role = OrderStatusActorRole::fromActor($actor);

        return collect($this->lifecycleStatus()->allowedNextStatuses())
            ->filter(fn (OrderStatus $status): bool => $status->canBeChangedBy($role))
            ->filter(fn (): bool => $this->isManageableBy($actor, $role))
            ->values()
            ->all();
    }

    public function canTransitionTo(OrderStatus $status, ?Authenticatable $actor = null): bool
    {
        if ($actor === null) {
            return $this->lifecycleStatus()->canTransitionTo($status);
        }

        return in_array($status, $this->availableTransitionsFor($actor), true);
    }

    public function isManageableBy(?Authenticatable $actor, ?OrderStatusActorRole $role = null): bool
    {
        $role ??= OrderStatusActorRole::fromActor($actor);

        return match ($role) {
            OrderStatusActorRole::System => $actor === null,
            OrderStatusActorRole::Admin => $actor instanceof Admin,
            OrderStatusActorRole::Buyer => $actor instanceof Buyer
                && (int) $this->buyer_id === (int) $actor->getAuthIdentifier(),
            OrderStatusActorRole::Seller => $actor instanceof Seller
                && $this->hasSellerItems((int) $actor->getAuthIdentifier()),
        };
    }

    public function hasSellerItems(int $sellerId): bool
    {
        if ($this->relationLoaded('items')) {
            return $this->items->contains(fn (OrderItem $item): bool => (int) $item->seller_id === $sellerId);
        }

        return $this->items()->where('seller_id', $sellerId)->exists();
    }

    /**
     * @return array<int, array{step: int, label: string, icon: string}>
     */
    public function lifecycleSteps(): array
    {
        return OrderStatus::lifecycleSteps();
    }

    public function lifecycleCurrentStep(): int
    {
        return $this->lifecycleStatus()->lifecycleStep();
    }

    public function lifecycleStepsColor(): string
    {
        return $this->lifecycleStatus()->lifecycleStepsColor();
    }

    /**
     * @return array{label: string, description: string, badgeColor: string, icon: string}
     */
    public function lifecyclePanel(): array
    {
        return $this->lifecycleStatus()->lifecyclePanel();
    }

    /**
     * @return array<int, array{
     *     title: string,
     *     subtitle: ?string,
     *     description: string,
     *     icon: string,
     *     pending: bool,
     *     tone: string
     * }>
     */
    public function lifecycleTimeline(): array
    {
        $timeline = [[
            'title' => 'orders_timeline_order_placed_title',
            'subtitle' => LocaleFormatter::dateTime($this->created_at),
            'description' => 'orders_timeline_order_placed_description',
            'icon' => 'o-shopping-bag',
            'pending' => false,
            'tone' => 'success',
        ]];

        $currentStatus = $this->lifecycleStatus();

        if ($currentStatus === OrderStatus::Pending) {
            $timeline[] = [
                'title' => OrderStatus::Accepted->labelKey(),
                'subtitle' => null,
                'description' => 'orders_timeline_waiting_confirmation_description',
                'icon' => OrderStatus::Accepted->icon(),
                'pending' => true,
                'tone' => 'neutral',
            ];

            return $timeline;
        }

        $currentPanel = $this->lifecyclePanel();

        $timeline[] = [
            'title' => $currentPanel['label'],
            'subtitle' => LocaleFormatter::dateTime($this->updated_at),
            'description' => $currentPanel['description'],
            'icon' => $currentPanel['icon'],
            'pending' => false,
            'tone' => $currentPanel['badgeColor'],
        ];

        $nextMilestone = $currentStatus->nextMilestone();

        if ($nextMilestone) {
            $timeline[] = [
                'title' => $nextMilestone['title'],
                'subtitle' => null,
                'description' => $nextMilestone['description'],
                'icon' => $nextMilestone['icon'],
                'pending' => true,
                'tone' => 'neutral',
            ];
        }

        return $timeline;
    }

    /**
     * @return array{label: string, description: string, css: string, date: Carbon|null}
     */
    public function calendarEvent(?float $displayTotal = null): array
    {
        $status = $this->lifecycleStatus();
        $total = $displayTotal ?? (float) $this->order_total;

        return [
            'label' => __('orders_order_number').' #'.$this->id,
            'description' => __('common_status').': '.$status->label().'<br>'.__('orders_total').': '.LocaleFormatter::currency($total),
            'css' => $status->calendarCssClass(),
            'date' => $this->created_at,
        ];
    }

    /**
     * @param  iterable<self>  $orders
     * @return array<int, array{label: string, description: string, css: string, date: Carbon|null}>
     */
    public static function calendarEventsFrom(iterable $orders): array
    {
        return collect($orders)
            ->filter(fn (mixed $order): bool => $order instanceof self)
            ->sortBy('created_at')
            ->map(fn (self $order): array => $order->calendarEvent())
            ->values()
            ->all();
    }
}
