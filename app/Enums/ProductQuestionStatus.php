<?php

namespace App\Enums;

enum ProductQuestionStatus: string
{
    case Pending = 'pending';
    case Answered = 'answered';
    case Rejected = 'rejected';
    case Hidden = 'hidden';

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

    public static function fromValue(self|string|null $status): self
    {
        if ($status instanceof self) {
            return $status;
        }

        return self::tryFrom((string) $status) ?? self::Pending;
    }

    public function labelKey(): string
    {
        return 'products.questions.status.'.$this->value;
    }

    public function label(): string
    {
        return __($this->labelKey());
    }

    public function uiBadgeColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Answered => 'success',
            self::Rejected, self::Hidden => 'error',
        };
    }

    public function maryBadgeClass(): string
    {
        return match ($this) {
            self::Pending => 'badge-warning badge-outline',
            self::Answered => 'badge-success badge-outline',
            self::Rejected, self::Hidden => 'badge-error badge-outline',
        };
    }
}
