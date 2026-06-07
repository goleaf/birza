<?php

namespace Tests\Unit\Policies;

use App\Models\Conversation;
use App\Models\Message;
use App\Policies\MessagePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Support\MarketplaceTestHelpers;
use Tests\TestCase;

class MessagePolicyTest extends TestCase
{
    use MarketplaceTestHelpers;
    use RefreshDatabase;

    public function test_participants_can_view_and_create_messages_in_active_conversation(): void
    {
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $otherBuyer = $this->createBuyer();
        $conversation = Conversation::factory()->create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);
        $message = Message::factory()->fromBuyer($conversation)->create();
        $policy = app(MessagePolicy::class);

        $this->assertTrue($policy->view($buyer, $message));
        $this->assertTrue($policy->view($seller, $message));
        $this->assertFalse($policy->view($otherBuyer, $message));
        $this->assertTrue($policy->create($buyer, $conversation));
        $this->assertTrue($policy->create($seller, $conversation));
        $this->assertFalse($policy->create($otherBuyer, $conversation));
    }

    public function test_closed_conversations_block_message_creation_and_admin_can_moderate_messages(): void
    {
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $admin = $this->createAdmin();
        $conversation = Conversation::factory()->closed()->create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);
        $message = Message::factory()->fromBuyer($conversation)->create();
        $policy = app(MessagePolicy::class);

        $this->assertFalse($policy->create($buyer, $conversation));
        $this->assertFalse($policy->delete($buyer, $message));
        $this->assertTrue($policy->delete($admin, $message));
        $this->assertFalse($policy->forceDelete($admin, $message));
    }
}
