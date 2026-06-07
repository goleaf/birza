<?php

namespace App\Actions\Messaging;

use App\Actions\Notifications\SendMarketplaceNotificationAction;
use App\Enums\MessageSenderRole;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use App\Notifications\Marketplace\NewConversationMessageNotification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class SendMessageAction
{
    public const MAX_BODY_LENGTH = 2000;

    public function __construct(
        private readonly SendMarketplaceNotificationAction $sendNotification,
        private readonly RecordMessagingAuditAction $audit,
    ) {}

    public function handle(Conversation $conversation, Authenticatable $sender, string $body, string $source = 'conversation_detail'): Message
    {
        Gate::forUser($sender)->authorize('create', [Message::class, $conversation]);

        $validated = Validator::make([
            'body' => trim($body),
        ], [
            'body' => ['required', 'string', 'max:'.self::MAX_BODY_LENGTH],
        ], [
            'body.required' => __('messages.validation.body_required'),
            'body.max' => __('messages.validation.body_too_long', ['max' => self::MAX_BODY_LENGTH]),
        ], attributes: [
            'body' => __('messages.body'),
        ])->validate();

        $senderRole = MessageSenderRole::fromActor($sender);

        $message = DB::transaction(function () use ($conversation, $sender, $senderRole, $validated): Message {
            $message = Message::query()->forceCreate([
                'conversation_id' => $conversation->getKey(),
                'sender_id' => $sender->getAuthIdentifier(),
                'sender_role' => $senderRole,
                'body' => $validated['body'],
                'metadata' => null,
            ]);

            $conversation->forceFill([
                'last_message_at' => $message->created_at,
                'buyer_archived_at' => null,
                'seller_archived_at' => null,
            ])->save();

            return $message;
        });

        $this->audit->messageSent($sender, $message, $source);
        $this->notifyRecipient($conversation->fresh(['buyer', 'seller']) ?? $conversation, $message, $senderRole);

        return $message;
    }

    private function notifyRecipient(Conversation $conversation, Message $message, MessageSenderRole $senderRole): void
    {
        $recipient = match ($senderRole) {
            MessageSenderRole::Buyer => $conversation->seller,
            MessageSenderRole::Seller => $conversation->buyer,
            MessageSenderRole::Admin => null,
        };

        if (! $recipient instanceof Buyer && ! $recipient instanceof Seller) {
            return;
        }

        $this->sendNotification->handle(
            $recipient,
            new NewConversationMessageNotification($message),
        );
    }
}
