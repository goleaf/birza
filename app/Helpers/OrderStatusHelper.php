<?php

if (!function_exists('order_status_badge')) {
    function order_status_badge(string $status): string
    {
        $config = [
            'completed' => ['bg-green-100', 'text-green-800'],
            'cancelled' => ['bg-red-100', 'text-red-800'],
            'processing' => ['bg-blue-100', 'text-blue-800'],
            'shipped' => ['bg-indigo-100', 'text-indigo-800'],
            'default' => ['bg-yellow-100', 'text-yellow-800']
        ];

        $style = $config[$status] ?? $config['default'];

        return sprintf(
            '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium %s %s">%s</span>',
            $style[0],
            $style[1],
            __('orders.status_' . $status)
        );
    }
}
