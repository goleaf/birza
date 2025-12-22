<?php

namespace Tests\Unit\Notifications;

use Tests\TestCase;
use App\Notifications\VerifySellerEmail;
use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VerifySellerEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_can_be_sent(): void
    {
        $seller = Seller::factory()->create();
        $notification = new VerifySellerEmail('verification-url');

        $this->assertInstanceOf(VerifySellerEmail::class, $notification);
    }

    public function test_notification_has_verification_url(): void
    {
        $seller = Seller::factory()->create();
        $url = 'https://example.com/verify';
        $notification = new VerifySellerEmail($url);

        $reflection = new \ReflectionClass($notification);
        $property = $reflection->getProperty('verificationUrl');
        $property->setAccessible(true);

        $this->assertEquals($url, $property->getValue($notification));
    }

    public function test_notification_via_channels(): void
    {
        $seller = Seller::factory()->create();
        $notification = new VerifySellerEmail('url');

        $channels = $notification->via($seller);

        $this->assertContains('mail', $channels);
    }
}

