<?php

namespace App\Enums;

enum OrderPaymentStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

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

    public static function fromValue(self|string|null $status): self
    {
        if ($status instanceof self) {
            return $status;
        }

        return self::tryFrom((string) $status) ?? self::Pending;
    }

    public function isPaid(): bool
    {
        return $this === self::Paid;
    }

    public function labelKey(): string
    {
        return 'orders.payment_status.'.$this->value;
    }

    public function label(): string
    {
        return __($this->labelKey());
    }

    public function uiBadgeColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Paid => 'success',
            self::Failed, self::Cancelled => 'error',
            self::Refunded => 'neutral',
        };
    }

    public function maryBadgeClass(): string
    {
        return match ($this) {
            self::Pending => 'badge-warning badge-outline',
            self::Paid => 'badge-success badge-outline',
            self::Failed, self::Cancelled => 'badge-error badge-outline',
            self::Refunded => 'badge-neutral badge-outline',
        };
    }
}
