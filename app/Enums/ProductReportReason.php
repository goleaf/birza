<?php

namespace App\Enums;

enum ProductReportReason: string
{
    case Scam = 'scam';
    case FakeProduct = 'fake_product';
    case WrongPrice = 'wrong_price';
    case WrongCategory = 'wrong_category';
    case ProhibitedItem = 'prohibited_item';
    case OffensiveContent = 'offensive_content';
    case CopyrightIssue = 'copyright_issue';
    case DuplicateProduct = 'duplicate_product';
    case MisleadingDescription = 'misleading_description';
    case Other = 'other';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            fn (self $reason): string => $reason->value,
            self::cases(),
        );
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $reason): array => [
                'id' => $reason->value,
                'name' => $reason->label(),
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
        return 'reports.product.reasons.'.$this->value;
    }
}
