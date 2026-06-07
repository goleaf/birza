<?php

namespace App\Actions\Messaging;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\AuditLogService;
use Illuminate\Contracts\Auth\Authenticatable;

class RecordMessagingAuditAction
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    public function conversationStarted(?Authenticatable $actor, Conversation $conversation, string $source): void
    {
        $this->auditLogService->log(
            actor: $actor,
            action: 'conversation.started',
            auditable: $conversation,
            oldValues: null,
            newValues: $this->conversationSnapshot($conversation),
            metadata: $this->conversationMetadata($conversation, $source),
        );
    }

    public function messageSent(Authenticatable $actor, Message $message, string $source): void
    {
        $message->loadMissing('conversation');

        $this->auditLogService->log(
            actor: $actor,
            action: 'conversation.message_sent',
            auditable: $message->conversation,
            oldValues: null,
            newValues: [
                'message_id' => $message->getKey(),
                'sender_id' => $message->sender_id,
                'sender_role' => $message->sender_role->value,
                'body_length' => mb_strlen((string) $message->body),
            ],
            metadata: $this->messageMetadata($message, $source),
        );
    }

    public function markedRead(Authenticatable $actor, Conversation $conversation, int $count, string $source): void
    {
        if ($count < 1) {
            return;
        }

        $this->auditLogService->log(
            actor: $actor,
            action: 'conversation.marked_read',
            auditable: $conversation,
            metadata: array_merge($this->conversationMetadata($conversation, $source), [
                'read_message_count' => $count,
            ]),
        );
    }

    public function archived(Authenticatable $actor, Conversation $conversation, string $source): void
    {
        $this->auditLogService->log(
            actor: $actor,
            action: 'conversation.archived',
            auditable: $conversation,
            metadata: $this->conversationMetadata($conversation, $source),
        );
    }

    public function closed(Authenticatable $actor, Conversation $conversation, string $source): void
    {
        $this->auditLogService->log(
            actor: $actor,
            action: 'conversation.closed',
            auditable: $conversation,
            newValues: $this->conversationSnapshot($conversation),
            metadata: $this->conversationMetadata($conversation, $source),
        );
    }

    public function adminViewed(Authenticatable $actor, Conversation $conversation, string $source): void
    {
        $this->auditLogService->log(
            actor: $actor,
            action: 'conversation.admin_viewed',
            auditable: $conversation,
            metadata: $this->conversationMetadata($conversation, $source),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function conversationSnapshot(Conversation $conversation): array
    {
        return [
            'conversation_id' => $conversation->getKey(),
            'buyer_id' => $conversation->buyer_id,
            'seller_id' => $conversation->seller_id,
            'product_id' => $conversation->product_id,
            'order_id' => $conversation->order_id,
            'status' => $conversation->status->value,
            'last_message_at' => $conversation->last_message_at?->toDateTimeString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function conversationMetadata(Conversation $conversation, string $source): array
    {
        return [
            'source' => $source,
            'conversation_id' => $conversation->getKey(),
            'buyer_id' => $conversation->buyer_id,
            'seller_id' => $conversation->seller_id,
            'product_id' => $conversation->product_id,
            'order_id' => $conversation->order_id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function messageMetadata(Message $message, string $source): array
    {
        $conversation = $message->conversation;

        return [
            'source' => $source,
            'conversation_id' => $conversation?->getKey(),
            'message_id' => $message->getKey(),
            'sender_id' => $message->sender_id,
            'sender_role' => $message->sender_role->value,
            'buyer_id' => $conversation?->buyer_id,
            'seller_id' => $conversation?->seller_id,
            'product_id' => $conversation?->product_id,
            'order_id' => $conversation?->order_id,
        ];
    }
}
