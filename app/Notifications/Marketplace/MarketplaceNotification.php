<?php

namespace App\Notifications\Marketplace;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

abstract class MarketplaceNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected bool $sendMail = false;

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->sendMail ? ['database', 'mail'] : ['database'];
    }

    abstract public function databaseType(object $notifiable): string;

    /**
     * @return array<string, mixed>
     */
    abstract protected function payload(object $notifiable): array;

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return $this->payload($notifiable);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $payload = $this->payload($notifiable);
        $title = __($payload['title_key'], $payload['title_params'] ?? []);
        $message = (new MailMessage)
            ->subject($title)
            ->greeting($title)
            ->line(__($payload['message_key'], $payload['message_params'] ?? []));

        if (filled($payload['url'] ?? null)) {
            $message->action(__('notifications.actions.view'), url($payload['url']));
        }

        return $message;
    }
}
