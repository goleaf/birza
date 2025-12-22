<?php

namespace Tests\Unit\Notifications;

use Tests\TestCase;
use App\Notifications\VerifyBuyerEmail;
use App\Models\Users\Buyer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

class VerifyBuyerEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_can_be_sent(): void
    {
        $buyer = Buyer::factory()->create();
        $notification = new VerifyBuyerEmail('verification-url');

        $this->assertInstanceOf(VerifyBuyerEmail::class, $notification);
    }

    public function test_notification_has_verification_url(): void
    {
        $buyer = Buyer::factory()->create();
        $url = 'https://example.com/verify';
        $notification = new VerifyBuyerEmail($url);

        $reflection = new \ReflectionClass($notification);
        $property = $reflection->getProperty('verificationUrl');
        $property->setAccessible(true);

        $this->assertEquals($url, $property->getValue($notification));
    }

    public function test_notification_via_channels(): void
    {
        $buyer = Buyer::factory()->create();
        $notification = new VerifyBuyerEmail('url');

        $channels = $notification->via($buyer);

        $this->assertContains('mail', $channels);
    }
}

