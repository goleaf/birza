<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetSellerPassword extends ResetPassword
{
    protected function resetUrl($notifiable)
    {
        return url(route('seller.password.reset', [
            'hash' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(__('emails.seller.password_reset.subject'))
            ->markdown('emails.seller.reset-password', [
                'resetUrl' => $this->resetUrl($notifiable),
            ]);
    }
}
