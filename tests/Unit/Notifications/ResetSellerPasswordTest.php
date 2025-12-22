<?php

namespace Tests\Unit\Notifications;

use Tests\TestCase;
use App\Notifications\ResetSellerPassword;
use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResetSellerPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_can_be_sent(): void
    {
        $seller = Seller::factory()->create();
        $notification = new ResetSellerPassword('reset-token');

        $this->assertInstanceOf(ResetSellerPassword::class, $notification);
    }

    public function test_notification_has_reset_token(): void
    {
        $seller = Seller::factory()->create();
        $token = 'reset-token-123';
        $notification = new ResetSellerPassword($token);

        $reflection = new \ReflectionClass($notification);
        $property = $reflection->getProperty('token');
        $property->setAccessible(true);

        $this->assertEquals($token, $property->getValue($notification));
    }

    public function test_notification_via_channels(): void
    {
        $seller = Seller::factory()->create();
        $notification = new ResetSellerPassword('token');

        $channels = $notification->via($seller);

        $this->assertContains('mail', $channels);
    }
}

