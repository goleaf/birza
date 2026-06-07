<?php

namespace App\Enums;

use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Contracts\Auth\Authenticatable;
use InvalidArgumentException;

enum MessageSenderRole: string
{
    case Buyer = 'buyer';
    case Seller = 'seller';
    case Admin = 'admin';

    public static function fromActor(Authenticatable $actor): self
    {
        return match (true) {
            $actor instanceof Buyer => self::Buyer,
            $actor instanceof Seller => self::Seller,
            $actor instanceof Admin => self::Admin,
            default => throw new InvalidArgumentException(__('messages.errors.unknown_sender_role')),
        };
    }

    public function labelKey(): string
    {
        return 'messages.sender_roles.'.$this->value;
    }

    public function label(): string
    {
        return __($this->labelKey());
    }
}
