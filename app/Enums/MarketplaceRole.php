<?php

namespace App\Enums;

enum MarketplaceRole: string
{
    case Guest = 'guest';
    case Buyer = 'buyer';
    case Seller = 'seller';
    case Admin = 'admin';

    public function guard(): ?string
    {
        return match ($this) {
            self::Guest => null,
            self::Buyer => 'buyer',
            self::Seller => 'seller',
            self::Admin => 'admin',
        };
    }

    public function dashboardRoute(): ?string
    {
        return match ($this) {
            self::Guest => null,
            self::Buyer => 'buyer.dashboard',
            self::Seller => 'seller.dashboard',
            self::Admin => 'admin.dashboard',
        };
    }

    public function loginRoute(): ?string
    {
        return match ($this) {
            self::Guest => null,
            self::Buyer => 'buyer.login',
            self::Seller => 'seller.login',
            self::Admin => 'admin.login',
        };
    }

    public function accessGate(): ?string
    {
        return match ($this) {
            self::Guest => null,
            self::Buyer => 'accessBuyerCabinet',
            self::Seller => 'accessSellerCabinet',
            self::Admin => 'accessAdminPanel',
        };
    }

    public function requiresVerification(): bool
    {
        return $this === self::Buyer || $this === self::Seller;
    }

    /**
     * @return list<string>
     */
    public static function notificationGuards(): array
    {
        return [
            self::Buyer->value,
            self::Seller->value,
            self::Admin->value,
        ];
    }

    public static function fromGuard(?string $guard): ?self
    {
        return match ($guard) {
            'buyer' => self::Buyer,
            'seller' => self::Seller,
            'admin' => self::Admin,
            default => null,
        };
    }
}
