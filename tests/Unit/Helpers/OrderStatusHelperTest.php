<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;
use Illuminate\Support\Facades\Lang;

class OrderStatusHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock the translator to return simple strings
        Lang::shouldReceive('get')
            ->andReturnUsing(function ($key, $replace = [], $locale = null) {
                // Return a simple string based on the key
                return str_replace('orders.status_', '', $key);
            });
    }

    public function test_order_status_badge_completed(): void
    {
        $result = order_status_badge('completed');

        $this->assertStringContainsString('bg-green-100', $result);
        $this->assertStringContainsString('text-green-800', $result);
    }

    public function test_order_status_badge_cancelled(): void
    {
        $result = order_status_badge('cancelled');

        $this->assertStringContainsString('bg-red-100', $result);
        $this->assertStringContainsString('text-red-800', $result);
    }

    public function test_order_status_badge_processing(): void
    {
        $result = order_status_badge('processing');

        $this->assertStringContainsString('bg-blue-100', $result);
        $this->assertStringContainsString('text-blue-800', $result);
    }

    public function test_order_status_badge_shipped(): void
    {
        $result = order_status_badge('shipped');

        $this->assertStringContainsString('bg-indigo-100', $result);
        $this->assertStringContainsString('text-indigo-800', $result);
    }

    public function test_order_status_badge_default(): void
    {
        $result = order_status_badge('unknown_status');

        $this->assertStringContainsString('bg-yellow-100', $result);
        $this->assertStringContainsString('text-yellow-800', $result);
    }

    public function test_order_status_badge_returns_html(): void
    {
        $result = order_status_badge('completed');

        $this->assertStringContainsString('<span', $result);
        $this->assertStringContainsString('</span>', $result);
    }
}

