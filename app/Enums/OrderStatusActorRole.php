<?php

namespace App\Enums;

use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Contracts\Auth\Authenticatable;
use InvalidArgumentException;

enum OrderStatusActorRole: string
{
    case Buyer = 'buyer';
    case Seller = 'seller';
    case Admin = 'admin';
    case System = 'system';

    public static function fromActor(?Authenticatable $actor): self
    {
        return match (true) {
            $actor instanceof Buyer => self::Buyer,
            $actor instanceof Seller => self::Seller,
            $actor instanceof Admin => self::Admin,
            $actor === null => self::System,
            default => throw new InvalidArgumentException(__('orders.status.messages.unknown_actor_role')),
        };
    }

    public function labelKey(): string
    {
        return 'orders.status.actor.'.$this->value;
    }

    public function label(): string
    {
        return __($this->labelKey());
    }
}
