<?php

namespace App\Enums;

enum OrderEventType: string
{
    case Created = 'created';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';
    case Disputed = 'disputed';
    case TrackingUpdated = 'tracking_updated';
    case ShippingUpdated = 'shipping_updated';

    public static function fromOrderStatus(OrderStatus $status): self
    {
        return match ($status) {
            OrderStatus::Pending => self::Created,
            OrderStatus::Accepted => self::Accepted,
            OrderStatus::Rejected => self::Rejected,
            OrderStatus::Processing => self::Processing,
            OrderStatus::Shipped => self::Shipped,
            OrderStatus::Delivered => self::Delivered,
            OrderStatus::Completed => self::Completed,
            OrderStatus::Cancelled => self::Cancelled,
            OrderStatus::Refunded => self::Refunded,
            OrderStatus::Disputed => self::Disputed,
        };
    }

    public function titleKey(): string
    {
        return 'orders.timeline.'.$this->value.'.title';
    }

    public function descriptionKey(): string
    {
        return 'orders.timeline.'.$this->value.'.description';
    }

    public function icon(): string
    {
        return match ($this) {
            self::Created => 'o-shopping-bag',
            self::Accepted => 'o-check-badge',
            self::Rejected, self::Cancelled => 'o-x-circle',
            self::Processing => 'o-cog-6-tooth',
            self::Shipped, self::TrackingUpdated, self::ShippingUpdated => 'o-truck',
            self::Delivered => 'o-home',
            self::Completed => 'o-check-circle',
            self::Refunded => 'o-arrow-uturn-left',
            self::Disputed => 'o-exclamation-triangle',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Created, self::Accepted, self::Delivered, self::Completed => 'success',
            self::Rejected, self::Cancelled, self::Refunded => 'error',
            self::Processing, self::Shipped, self::TrackingUpdated, self::ShippingUpdated => 'info',
            self::Disputed => 'warning',
        };
    }
}
