<?php

namespace App\Enums;

enum ProductReportStatus: string
{
    case Pending = 'pending';
    case Reviewing = 'reviewing';
    case Resolved = 'resolved';
    case Rejected = 'rejected';
    case Dismissed = 'dismissed';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            fn (self $status): string => $status->value,
            self::cases(),
        );
    }

    /**
     * @return list<string>
     */
    public static function openValues(): array
    {
        return [
            self::Pending->value,
            self::Reviewing->value,
        ];
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $status): array => [
                'id' => $status->value,
                'name' => $status->label(),
            ],
            self::cases(),
        );
    }

    public function label(): string
    {
        return __($this->translationKey());
    }

    public function translationKey(): string
    {
        return 'reports.product.status.'.$this->value;
    }

    public function isOpen(): bool
    {
        return in_array($this, [self::Pending, self::Reviewing], true);
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'badge-warning badge-outline',
            self::Reviewing => 'badge-info badge-outline',
            self::Resolved => 'badge-success badge-outline',
            self::Rejected => 'badge-error badge-outline',
            self::Dismissed => 'badge-neutral badge-outline',
        };
    }
}
