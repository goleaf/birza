<?php

namespace App\Enums;

enum ProductStockAlertStatus: string
{
    case Active = 'active';
    case Notified = 'notified';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function labelKey(): string
    {
        return 'stock_alerts.status.'.$this->value;
    }
}
