<?php

return [
    'product_reports' => [
        'allow_guest_reports' => (bool) env('MARKETPLACE_ALLOW_GUEST_PRODUCT_REPORTS', true),
        'rate_limit_per_hour' => (int) env('MARKETPLACE_PRODUCT_REPORT_RATE_LIMIT_PER_HOUR', 5),
    ],
];
