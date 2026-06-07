<?php

namespace App\Actions\Messaging;

use App\Enums\MessageSenderRole;
use App\Models\Conversation;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;

class MarkConversationAsReadAction
{
    public function __construct(
        private readonly RecordMessagingAuditAction $audit,
    ) {}

    public function handle(Conversation $conversation, Authenticatable $actor, string $source = 'conversation_detail'): int
    {
        Gate::forUser($actor)->authorize('view', $conversation);

        if (! $actor instanceof Buyer && ! $actor instanceof Seller) {
            return 0;
        }

        $role = MessageSenderRole::fromActor($actor);

        $count = $conversation->messages()
            ->visible()
            ->where('sender_role', '!=', $role->value)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        $this->audit->markedRead($actor, $conversation, $count, $source);

        return $count;
    }
}
