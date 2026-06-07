<?php

namespace App\Models;

use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS = [
        'PENDING' => 'pending',
        'PAID' => 'paid',
        'FAILED' => 'failed',
        'PROCESSING' => 'processing',
        'SHIPPED' => 'shipped',
        'DELIVERED' => 'delivered',
        'CANCELLED' => 'cancelled',
        'REFUNDED' => 'refunded',
    ];

    protected $fillable = [
        'order_total',
        'buyer_id',
        'payment_method',
        'payment_status',
        'status',
    ];

    protected $casts = [
        'order_total' => 'decimal:2',
        'buyer_id' => 'integer',
    ];

    /**
     * Get the buyer associated with the order
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class)->withTrashed();
    }

    /**
     * Get the sellers associated with the order through order items
     */
    public function sellers(): HasManyThrough
    {
        return $this->hasManyThrough(
            Seller::class,
            OrderItem::class,
            'order_id', // Foreign key on order_items table
            'id', // Foreign key on sellers table
            'id', // Local key on orders table
            'seller_id' // Local key on order_items table
        )->withTrashed();
    }

    /**
     * Get the order items for the order
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the total price of the order
     */
    public function getTotalAttribute(): float
    {
        return (float) $this->order_total;
    }

    /**
     * Scope a query to only include pending orders
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS['PENDING']);
    }

    /**
     * Scope a query to only include paid orders
     */
    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS['PAID']);
    }

    /**
     * Scope a query to only include failed orders
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS['FAILED']);
    }

    /**
     * Scope a query to include orders with full details
     */
    public function scopeWithFullDetails($query)
    {
        return $query->with(['buyer', 'orderItems.product', 'orderItems.seller']);
    }

    /**
     * Get the products associated with the order
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'order_items')
            ->withPivot(['quantity', 'unit_price', 'total_price', 'seller_id'])
            ->wherePivotNull('deleted_at')
            ->withTimestamps();
    }

    public function lifecycleStatus(): string
    {
        return (string) ($this->payment_status ?: $this->status ?: self::STATUS['PENDING']);
    }

    public function paymentStatusBadgeClass(): string
    {
        return match ($this->payment_status) {
            self::STATUS['PENDING'] => 'badge-warning badge-outline',
            self::STATUS['PAID'] => 'badge-success badge-outline',
            self::STATUS['PROCESSING'] => 'badge-info badge-outline',
            self::STATUS['SHIPPED'] => 'badge-secondary badge-outline',
            self::STATUS['DELIVERED'] => 'badge-success',
            self::STATUS['CANCELLED'], self::STATUS['FAILED'] => 'badge-error badge-outline',
            self::STATUS['REFUNDED'] => 'badge-neutral badge-outline',
            default => 'badge-neutral badge-outline',
        };
    }

    /**
     * @return array<int, array{step: int, label: string, icon: string}>
     */
    public function lifecycleSteps(): array
    {
        return [
            ['step' => 1, 'label' => 'orders_status_pending', 'icon' => 'o-clock'],
            ['step' => 2, 'label' => 'orders_status_paid', 'icon' => 'o-check-badge'],
            ['step' => 3, 'label' => 'orders_status_processing', 'icon' => 'o-cog-6-tooth'],
            ['step' => 4, 'label' => 'orders_status_shipped', 'icon' => 'o-truck'],
            ['step' => 5, 'label' => 'orders_status_delivered', 'icon' => 'o-home'],
        ];
    }

    public function lifecycleCurrentStep(): int
    {
        return match ($this->lifecycleStatus()) {
            self::STATUS['PAID'], self::STATUS['FAILED'] => 2,
            self::STATUS['PROCESSING'] => 3,
            self::STATUS['SHIPPED'] => 4,
            self::STATUS['DELIVERED'], self::STATUS['REFUNDED'] => 5,
            default => 1,
        };
    }

    public function lifecycleStepsColor(): string
    {
        return match ($this->lifecycleStatus()) {
            self::STATUS['PENDING'] => 'step-warning',
            self::STATUS['DELIVERED'] => 'step-success',
            self::STATUS['FAILED'], self::STATUS['CANCELLED'], self::STATUS['REFUNDED'] => 'step-error',
            default => 'step-primary',
        };
    }

    /**
     * @return array{label: string, description: string, badgeColor: string, icon: string}
     */
    public function lifecyclePanel(): array
    {
        return match ($this->lifecycleStatus()) {
            self::STATUS['PAID'] => [
                'label' => 'orders_status_paid',
                'description' => 'orders_steps_paid_description',
                'badgeColor' => 'success',
                'icon' => 'o-check-badge',
            ],
            self::STATUS['FAILED'] => [
                'label' => 'orders_status_failed',
                'description' => 'orders_steps_failed_description',
                'badgeColor' => 'error',
                'icon' => 'o-exclamation-circle',
            ],
            self::STATUS['PROCESSING'] => [
                'label' => 'orders_status_processing',
                'description' => 'orders_steps_processing_description',
                'badgeColor' => 'info',
                'icon' => 'o-cog-6-tooth',
            ],
            self::STATUS['SHIPPED'] => [
                'label' => 'orders_status_shipped',
                'description' => 'orders_steps_shipped_description',
                'badgeColor' => 'primary',
                'icon' => 'o-truck',
            ],
            self::STATUS['DELIVERED'] => [
                'label' => 'orders_status_delivered',
                'description' => 'orders_steps_delivered_description',
                'badgeColor' => 'success',
                'icon' => 'o-home',
            ],
            self::STATUS['CANCELLED'] => [
                'label' => 'orders_status_cancelled',
                'description' => 'orders_steps_cancelled_description',
                'badgeColor' => 'error',
                'icon' => 'o-x-circle',
            ],
            self::STATUS['REFUNDED'] => [
                'label' => 'orders_status_refunded',
                'description' => 'orders_steps_refunded_description',
                'badgeColor' => 'error',
                'icon' => 'o-arrow-uturn-left',
            ],
            default => [
                'label' => 'orders_status_pending',
                'description' => 'orders_steps_pending_description',
                'badgeColor' => 'warning',
                'icon' => 'o-clock',
            ],
        };
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
            'subtitle' => $this->created_at?->format('Y-m-d H:i'),
            'description' => 'orders_timeline_order_placed_description',
            'icon' => 'o-shopping-bag',
            'pending' => false,
            'tone' => 'success',
        ]];

        if ($this->lifecycleStatus() === self::STATUS['PENDING']) {
            $timeline[] = [
                'title' => 'orders_status_paid',
                'subtitle' => null,
                'description' => 'orders_timeline_waiting_confirmation_description',
                'icon' => 'o-credit-card',
                'pending' => true,
                'tone' => 'neutral',
            ];

            return $timeline;
        }

        $currentPanel = $this->lifecyclePanel();

        $timeline[] = [
            'title' => $currentPanel['label'],
            'subtitle' => $this->updated_at?->format('Y-m-d H:i'),
            'description' => $currentPanel['description'],
            'icon' => $currentPanel['icon'],
            'pending' => false,
            'tone' => $currentPanel['badgeColor'],
        ];

        $nextMilestone = match ($this->lifecycleStatus()) {
            self::STATUS['PAID'] => [
                'title' => 'orders_status_processing',
                'description' => 'orders_timeline_processing_next_description',
                'icon' => 'o-cog-6-tooth',
            ],
            self::STATUS['PROCESSING'] => [
                'title' => 'orders_status_shipped',
                'description' => 'orders_timeline_shipped_next_description',
                'icon' => 'o-truck',
            ],
            self::STATUS['SHIPPED'] => [
                'title' => 'orders_status_delivered',
                'description' => 'orders_timeline_delivered_next_description',
                'icon' => 'o-home',
            ],
            default => null,
        };

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
        $status = strtolower((string) $this->payment_status ?: self::STATUS['PENDING']);
        $total = $displayTotal ?? (float) $this->order_total;

        return [
            'label' => __('orders_order_number').' #'.$this->id,
            'description' => __('common_status').': '.__('orders_status_3_'.$status).'<br>'.__('orders_total').': '.number_format($total, 2).' €',
            'css' => $this->calendarEventCssClass($status),
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
            ->filter(fn ($order) => $order instanceof self)
            ->sortBy('created_at')
            ->map(fn (self $order) => $order->calendarEvent())
            ->values()
            ->all();
    }

    private function calendarEventCssClass(string $status): string
    {
        return match ($status) {
            self::STATUS['PENDING'] => 'order-calendar-event-pending',
            self::STATUS['PAID'], self::STATUS['DELIVERED'] => 'order-calendar-event-success',
            self::STATUS['PROCESSING'] => 'order-calendar-event-info',
            self::STATUS['SHIPPED'] => 'order-calendar-event-shipped',
            default => 'order-calendar-event-error',
        };
    }
}
