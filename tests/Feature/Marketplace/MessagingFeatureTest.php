<?php

namespace Tests\Feature\Marketplace;

use App\Actions\Messaging\SendMessageAction;
use App\Actions\Messaging\StartConversationAction;
use App\Enums\ConversationStatus;
use App\Enums\MessageSenderRole;
use App\Livewire\Backend\Messages\Show as AdminMessageShow;
use App\Livewire\Frontend\Buyer\Messages\Show as BuyerMessageShow;
use App\Livewire\Frontend\Buyer\Products\Show as BuyerProductShow;
use App\Livewire\Frontend\Seller\Messages\Show as SellerMessageShow;
use App\Models\Conversation;
use App\Models\Message;
use App\Notifications\Marketplace\NewConversationMessageNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\Feature\Support\MarketplaceTestHelpers;
use Tests\TestCase;

class MessagingFeatureTest extends TestCase
{
    use MarketplaceTestHelpers;
    use RefreshDatabase;

    public function test_buyer_can_start_conversation_from_active_product_page(): void
    {
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $product = $this->createProduct(['seller_id' => $seller->id]);

        Livewire::actingAs($buyer, 'buyer')
            ->test(BuyerProductShow::class, ['product' => $product])
            ->call('contactSeller')
            ->assertRedirect();

        $conversation = Conversation::query()->firstOrFail();

        $this->assertSame($buyer->id, $conversation->buyer_id);
        $this->assertSame($seller->id, $conversation->seller_id);
        $this->assertSame($product->id, $conversation->product_id);
        $this->assertSame(ConversationStatus::Active, $conversation->status);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'conversation.started',
            'actor_id' => $buyer->id,
            'actor_role' => 'buyer',
            'auditable_id' => $conversation->id,
        ]);
    }

    public function test_buyer_cannot_start_product_conversation_for_inactive_product_or_seller(): void
    {
        $buyer = $this->createBuyer();
        $inactiveProduct = $this->createProduct(['is_active' => false]);
        $inactiveSeller = $this->createSeller(['is_active' => false]);
        $sellerProduct = $this->createProduct(['seller_id' => $inactiveSeller->id]);

        foreach ([$inactiveProduct, $sellerProduct] as $product) {
            try {
                app(StartConversationAction::class)->forProduct($buyer, $product);
                $this->fail('Inactive products or sellers must not start conversations.');
            } catch (AuthorizationException) {
                $this->assertDatabaseMissing('conversations', [
                    'buyer_id' => $buyer->id,
                    'product_id' => $product->id,
                ]);
            }
        }
    }

    public function test_buyer_and_seller_can_only_view_their_own_conversations(): void
    {
        $buyer = $this->createBuyer();
        $otherBuyer = $this->createBuyer();
        $seller = $this->createSeller();
        $otherSeller = $this->createSeller();
        $conversation = Conversation::factory()->create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        Livewire::actingAs($buyer, 'buyer')
            ->test(BuyerMessageShow::class, ['conversation' => $conversation])
            ->assertOk();

        Livewire::actingAs($otherBuyer, 'buyer')
            ->test(BuyerMessageShow::class, ['conversation' => $conversation])
            ->assertForbidden();

        Livewire::actingAs($seller, 'seller')
            ->test(SellerMessageShow::class, ['conversation' => $conversation])
            ->assertOk();

        Livewire::actingAs($otherSeller, 'seller')
            ->test(SellerMessageShow::class, ['conversation' => $conversation])
            ->assertForbidden();
    }

    public function test_buyer_and_seller_can_send_messages_and_only_recipient_is_notified(): void
    {
        Notification::fake();

        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $conversation = Conversation::factory()->create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        Livewire::actingAs($buyer, 'buyer')
            ->test(BuyerMessageShow::class, ['conversation' => $conversation])
            ->set('body', 'Can you confirm the delivery window?')
            ->call('sendMessage')
            ->assertHasNoErrors();

        $buyerMessage = Message::query()->firstOrFail();

        $this->assertSame(MessageSenderRole::Buyer, $buyerMessage->sender_role);
        Notification::assertSentTo($seller, NewConversationMessageNotification::class);
        Notification::assertNotSentTo($buyer, NewConversationMessageNotification::class);

        Livewire::actingAs($seller, 'seller')
            ->test(SellerMessageShow::class, ['conversation' => $conversation->refresh()])
            ->set('body', 'Yes, Friday before noon works.')
            ->call('sendMessage')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('messages', 2);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'conversation.message_sent',
            'auditable_id' => $conversation->id,
        ]);
    }

    public function test_message_validation_and_closed_conversation_rules_are_enforced(): void
    {
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $conversation = Conversation::factory()->create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        $this->expectException(ValidationException::class);

        try {
            app(SendMessageAction::class)->handle($conversation, $buyer, '   ');
        } finally {
            $this->assertDatabaseCount('messages', 0);
        }
    }

    public function test_closed_conversation_blocks_new_messages(): void
    {
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $conversation = Conversation::factory()->closed()->create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        $this->expectException(AuthorizationException::class);

        try {
            app(SendMessageAction::class)->handle($conversation, $buyer, 'Can this closed conversation reopen itself?');
        } finally {
            $this->assertDatabaseCount('messages', 0);
        }
    }

    public function test_opening_conversation_marks_other_participant_messages_as_read_and_counts_unread(): void
    {
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $conversation = Conversation::factory()->create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);
        $message = Message::factory()->fromSeller($conversation)->unread()->create([
            'body' => 'Unread seller message.',
        ]);

        $this->assertSame(1, Conversation::query()->forBuyer($buyer)->unreadFor($buyer)->count());
        $this->assertSame(0, Conversation::query()->forSeller($seller)->unreadFor($seller)->count());

        Livewire::actingAs($buyer, 'buyer')
            ->test(BuyerMessageShow::class, ['conversation' => $conversation])
            ->assertOk();

        $this->assertNotNull($message->refresh()->read_at);
        $this->assertSame(0, Conversation::query()->forBuyer($buyer)->unreadFor($buyer)->count());
    }

    public function test_message_body_is_escaped_in_conversation_ui(): void
    {
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $conversation = Conversation::factory()->create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);
        $body = '<script>alert("x")</script>';

        Message::factory()->fromSeller($conversation)->create([
            'body' => $body,
        ]);

        Livewire::actingAs($buyer, 'buyer')
            ->test(BuyerMessageShow::class, ['conversation' => $conversation])
            ->assertSee(e($body), false)
            ->assertDontSee($body, false);
    }

    public function test_order_related_conversation_respects_buyer_and_seller_ownership(): void
    {
        $buyer = $this->createBuyer();
        $otherBuyer = $this->createBuyer();
        $seller = $this->createSeller();
        $otherSeller = $this->createSeller();
        $order = $this->createOrderWithItem($buyer, $seller);

        $buyerConversation = app(StartConversationAction::class)->forOrder($buyer, $order, $seller);
        $sellerConversation = app(StartConversationAction::class)->forOrder($seller, $order, $seller);

        $this->assertSame($buyerConversation->id, $sellerConversation->id);
        $this->assertSame($order->id, $buyerConversation->order_id);

        foreach ([[$otherBuyer, $seller], [$buyer, $otherSeller], [$otherSeller, $otherSeller]] as $case) {
            try {
                app(StartConversationAction::class)->forOrder($case[0], $order, $case[1]);
                $this->fail('Foreign order conversations must not be allowed.');
            } catch (AuthorizationException) {
                $this->assertDatabaseCount('conversations', 1);
            }
        }
    }

    public function test_admin_moderation_view_is_policy_controlled_and_audited(): void
    {
        $admin = $this->createAdmin();
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $conversation = Conversation::factory()->create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        Livewire::actingAs($admin, 'admin')
            ->test(AdminMessageShow::class, ['conversation' => $conversation])
            ->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'conversation.admin_viewed',
            'actor_id' => $admin->id,
            'actor_role' => 'admin',
            'auditable_id' => $conversation->id,
        ]);

        Livewire::actingAs($buyer, 'buyer')
            ->test(AdminMessageShow::class, ['conversation' => $conversation])
            ->assertForbidden();
    }

    public function test_messaging_translation_keys_exist(): void
    {
        foreach ([
            'messages.title',
            'messages.inbox',
            'messages.write_message',
            'messages.contact_seller',
            'messages.errors.not_allowed',
            'messages.errors.conversation_closed',
            'notifications.messages.new.title',
            'notifications.messages.new.message',
        ] as $key) {
            $this->assertNotSame($key, __($key));
        }
    }
}
