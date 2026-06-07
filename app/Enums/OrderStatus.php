<?php

namespace App\Enums;

use Illuminate\Support\Collection;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';
    case Disputed = 'disputed';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            fn (self $status): string => $status->value,
            self::cases()
        );
    }

    /**
     * @return array<int, array{id: string, name: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $status): array => [
                'id' => $status->value,
                'name' => $status->label(),
            ],
            self::cases()
        );
    }

    /**
     * @return array<string, int>
     */
    public static function countsFor(Collection $orders, string $attribute = 'status'): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status): array => [
                $status->value => self::countIn($orders, $status, $attribute),
            ])
            ->all();
    }

    public static function countIn(Collection $orders, self $status, string $attribute = 'status'): int
    {
        return $orders
            ->filter(fn (mixed $order): bool => self::fromValue($order->{$attribute}) === $status)
            ->count();
    }

    public static function fromValue(self|string $status): self
    {
        if ($status instanceof self) {
            return $status;
        }

        return self::from($status);
    }

    /**
     * @return array<int, string>
     */
    public static function revenueStatuses(): array
    {
        return array_map(
            fn (self $status): string => $status->value,
            array_filter(
                self::cases(),
                fn (self $status): bool => $status->isRevenueRecognized()
            )
        );
    }

    public function labelKey(): string
    {
        return 'orders.status.'.$this->value;
    }

    public function label(): string
    {
        return __($this->labelKey());
    }

    public function descriptionKey(): string
    {
        return 'orders.status.'.$this->value.'.description';
    }

    public function description(): string
    {
        return __($this->descriptionKey());
    }

    public function isPending(): bool
    {
        return $this === self::Pending;
    }

    public function isRevenueRecognized(): bool
    {
        return in_array($this, [
            self::Accepted,
            self::Processing,
            self::Shipped,
            self::Delivered,
            self::Completed,
            self::Disputed,
        ], true);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [
            self::Rejected,
            self::Completed,
            self::Cancelled,
            self::Refunded,
        ], true);
    }

    public function icon(): string
    {
        return match ($this) {
            self::Pending => 'o-clock',
            self::Accepted => 'o-check-badge',
            self::Rejected, self::Cancelled => 'o-x-circle',
            self::Processing => 'o-cog-6-tooth',
            self::Shipped => 'o-truck',
            self::Delivered => 'o-home',
            self::Completed => 'o-check-circle',
            self::Refunded => 'o-arrow-uturn-left',
            self::Disputed => 'o-exclamation-triangle',
        };
    }

    public function maryBadgeClass(): string
    {
        return match ($this) {
            self::Pending => 'badge-warning badge-outline',
            self::Accepted, self::Delivered, self::Completed => 'badge-success badge-outline',
            self::Rejected, self::Cancelled => 'badge-error badge-outline',
            self::Processing => 'badge-info badge-outline',
            self::Shipped => 'badge-secondary badge-outline',
            self::Refunded => 'badge-neutral badge-outline',
            self::Disputed => 'badge-warning',
        };
    }

    public function uiBadgeColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Accepted, self::Delivered, self::Completed => 'success',
            self::Rejected, self::Cancelled => 'error',
            self::Processing => 'info',
            self::Shipped => 'secondary',
            self::Refunded => 'neutral',
            self::Disputed => 'warning',
        };
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function htmlBadgeClasses(): array
    {
        return match ($this) {
            self::Pending => ['bg-yellow-100', 'text-yellow-800'],
            self::Accepted, self::Delivered, self::Completed => ['bg-green-100', 'text-green-800'],
            self::Rejected, self::Cancelled => ['bg-red-100', 'text-red-800'],
            self::Processing => ['bg-blue-100', 'text-blue-800'],
            self::Shipped => ['bg-indigo-100', 'text-indigo-800'],
            self::Refunded => ['bg-gray-100', 'text-gray-800'],
            self::Disputed => ['bg-amber-100', 'text-amber-800'],
        };
    }

    /**
     * @return array<int, self>
     */
    public function allowedNextStatuses(): array
    {
        return match ($this) {
            self::Pending => [self::Accepted, self::Rejected, self::Cancelled],
            self::Accepted => [self::Processing, self::Cancelled, self::Refunded, self::Disputed],
            self::Processing => [self::Shipped, self::Cancelled, self::Refunded, self::Disputed],
            self::Shipped => [self::Delivered, self::Refunded, self::Disputed],
            self::Delivered => [self::Completed, self::Refunded, self::Disputed],
            self::Disputed => [self::Completed, self::Refunded, self::Cancelled],
            self::Rejected,
            self::Completed,
            self::Cancelled,
            self::Refunded => [],
        };
    }

    /**
     * @return array<int, OrderStatusActorRole>
     */
    public function allowedActorRoles(): array
    {
        return match ($this) {
            self::Pending => [OrderStatusActorRole::System, OrderStatusActorRole::Admin],
            self::Accepted,
            self::Rejected,
            self::Processing,
            self::Shipped,
            self::Delivered => [OrderStatusActorRole::Seller, OrderStatusActorRole::Admin],
            self::Completed => [OrderStatusActorRole::Buyer, OrderStatusActorRole::Admin],
            self::Cancelled,
            self::Disputed => [OrderStatusActorRole::Buyer, OrderStatusActorRole::Seller, OrderStatusActorRole::Admin],
            self::Refunded => [OrderStatusActorRole::Admin],
        };
    }

    public function canTransitionTo(self $nextStatus): bool
    {
        return in_array($nextStatus, $this->allowedNextStatuses(), true);
    }

    public function canBeChangedBy(OrderStatusActorRole $role): bool
    {
        return in_array($role, $this->allowedActorRoles(), true);
    }

    public function lifecycleStep(): int
    {
        return match ($this) {
            self::Accepted, self::Rejected => 2,
            self::Processing => 3,
            self::Shipped => 4,
            self::Delivered, self::Completed, self::Refunded, self::Disputed => 5,
            default => 1,
        };
    }

    public function lifecycleStepsColor(): string
    {
        return match ($this) {
            self::Pending => 'step-warning',
            self::Delivered, self::Completed => 'step-success',
            self::Rejected, self::Cancelled, self::Refunded => 'step-error',
            default => 'step-primary',
        };
    }

    /**
     * @return array<int, array{step: int, label: string, icon: string}>
     */
    public static function lifecycleSteps(): array
    {
        return [
            ['step' => 1, 'label' => self::Pending->labelKey(), 'icon' => self::Pending->icon()],
            ['step' => 2, 'label' => self::Accepted->labelKey(), 'icon' => self::Accepted->icon()],
            ['step' => 3, 'label' => self::Processing->labelKey(), 'icon' => self::Processing->icon()],
            ['step' => 4, 'label' => self::Shipped->labelKey(), 'icon' => self::Shipped->icon()],
            ['step' => 5, 'label' => self::Delivered->labelKey(), 'icon' => self::Delivered->icon()],
        ];
    }

    /**
     * @return array{label: string, description: string, badgeColor: string, icon: string}
     */
    public function lifecyclePanel(): array
    {
        return [
            'label' => $this->labelKey(),
            'description' => $this->descriptionKey(),
            'badgeColor' => $this->uiBadgeColor(),
            'icon' => $this->icon(),
        ];
    }

    /**
     * @return array{title: string, description: string, icon: string}|null
     */
    public function nextMilestone(): ?array
    {
        return match ($this) {
            self::Accepted => [
                'title' => self::Processing->labelKey(),
                'description' => 'orders_timeline_processing_next_description',
                'icon' => self::Processing->icon(),
            ],
            self::Processing => [
                'title' => self::Shipped->labelKey(),
                'description' => 'orders_timeline_shipped_next_description',
                'icon' => self::Shipped->icon(),
            ],
            self::Shipped => [
                'title' => self::Delivered->labelKey(),
                'description' => 'orders_timeline_delivered_next_description',
                'icon' => self::Delivered->icon(),
            ],
            self::Delivered => [
                'title' => self::Completed->labelKey(),
                'description' => 'orders_timeline_completed_next_description',
                'icon' => self::Completed->icon(),
            ],
            default => null,
        };
    }

    public function calendarCssClass(): string
    {
        return match ($this) {
            self::Pending => 'order-calendar-event-pending',
            self::Accepted, self::Delivered, self::Completed => 'order-calendar-event-success',
            self::Processing => 'order-calendar-event-info',
            self::Shipped => 'order-calendar-event-shipped',
            default => 'order-calendar-event-error',
        };
    }
}
