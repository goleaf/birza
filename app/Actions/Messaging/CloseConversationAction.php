<?php

namespace App\Actions\Messaging;

use App\Enums\ConversationStatus;
use App\Models\Conversation;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;

class CloseConversationAction
{
    public function __construct(
        private readonly RecordMessagingAuditAction $audit,
    ) {}

    public function handle(Conversation $conversation, Authenticatable $actor, string $source = 'conversation_detail'): Conversation
    {
        Gate::forUser($actor)->authorize('close', $conversation);

        $conversation->forceFill([
            'status' => ConversationStatus::Closed,
        ])->save();

        $this->audit->closed($actor, $conversation, $source);

        return $conversation->refresh();
    }
}
