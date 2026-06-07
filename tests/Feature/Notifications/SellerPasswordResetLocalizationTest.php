<?php

namespace Tests\Feature\Notifications;

use App\Models\Users\Seller;
use App\Notifications\ResetSellerPassword;
use Tests\TestCase;

class SellerPasswordResetLocalizationTest extends TestCase
{
    public function test_seller_password_reset_mail_uses_translation_keys(): void
    {
        $seller = new Seller([
            'email' => 'seller@example.com',
        ]);

        app()->setLocale('lt');

        $mail = (new ResetSellerPassword('reset-token'))->toMail($seller);
        $rendered = $mail->render();

        $this->assertSame(__('emails.seller.password_reset.subject'), $mail->subject);
        $this->assertStringContainsString(__('emails.seller.password_reset.title'), $rendered);
        $this->assertStringContainsString(__('emails.seller.password_reset.action'), $rendered);
        $this->assertStringContainsString(__('emails.seller.password_reset.expiry'), $rendered);
        $this->assertStringNotContainsString('Reset Seller Password', $rendered);
        $this->assertStringNotContainsString('You are receiving this email because we received a password reset request for your seller account.', $rendered);
    }
}
