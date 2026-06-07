<?php

return [
    'low_stock_threshold' => (int) env('MARKETPLACE_LOW_STOCK_THRESHOLD', 5),
    'retention_days' => 90,
];
