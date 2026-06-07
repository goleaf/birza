<?php

namespace App\Enums;

enum ConversationStatus: string
{
    case Active = 'active';
    case Closed = 'closed';
    case Blocked = 'blocked';
    case Archived = 'archived';

    public function labelKey(): string
    {
        return 'messages.status.'.$this->value;
    }

    public function label(): string
    {
        return __($this->labelKey());
    }

    public function canReceiveMessages(): bool
    {
        return $this === self::Active;
    }
}
