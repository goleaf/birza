<?php

use App\Enums\OrderStatus;

if (! function_exists('order_status_badge')) {
    function order_status_badge(OrderStatus|string $status): string
    {
        $status = OrderStatus::fromValue($status);
        [$background, $text] = $status->htmlBadgeClasses();

        return sprintf(
            '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium %s %s">%s</span>',
            e($background),
            e($text),
            e($status->label())
        );
    }
}
