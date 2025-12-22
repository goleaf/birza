<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetSellerPassword extends ResetPassword
{
    protected function resetUrl($notifiable)
    {
        return url(route('seller.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Reset Seller Password')
            ->markdown('emails.seller.reset-password', [
                'resetUrl' => $this->resetUrl($notifiable),
            ]);
    }
} 