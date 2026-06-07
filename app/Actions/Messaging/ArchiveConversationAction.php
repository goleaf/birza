<?php

namespace App\Actions\Messaging;

use App\Models\Conversation;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;

class ArchiveConversationAction
{
    public function __construct(
        private readonly RecordMessagingAuditAction $audit,
    ) {}

    public function handle(Conversation $conversation, Authenticatable $actor, string $source = 'conversation_detail'): Conversation
    {
        Gate::forUser($actor)->authorize('archive', $conversation);

        if ($actor instanceof Buyer) {
            $conversation->buyer_archived_at = now();
        }

        if ($actor instanceof Seller) {
            $conversation->seller_archived_at = now();
        }

        $conversation->save();
        $this->audit->archived($actor, $conversation, $source);

        return $conversation->refresh();
    }
}
