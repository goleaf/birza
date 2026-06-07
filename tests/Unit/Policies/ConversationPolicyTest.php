<?php

namespace Tests\Unit\Policies;

use App\Models\Conversation;
use App\Policies\ConversationPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Support\MarketplaceTestHelpers;
use Tests\TestCase;

class ConversationPolicyTest extends TestCase
{
    use MarketplaceTestHelpers;
    use RefreshDatabase;

    public function test_participants_and_admin_can_view_conversation(): void
    {
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $otherBuyer = $this->createBuyer();
        $otherSeller = $this->createSeller();
        $admin = $this->createAdmin();
        $conversation = Conversation::factory()->create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);
        $policy = app(ConversationPolicy::class);

        $this->assertTrue($policy->view($buyer, $conversation));
        $this->assertTrue($policy->view($seller, $conversation));
        $this->assertTrue($policy->view($admin, $conversation));
        $this->assertFalse($policy->view($otherBuyer, $conversation));
        $this->assertFalse($policy->view($otherSeller, $conversation));
    }

    public function test_product_conversation_creation_requires_active_verified_seller_and_not_self_profile(): void
    {
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $inactiveSeller = $this->createSeller(['is_active' => false]);
        $sameEmailSeller = $this->createSeller(['email' => $buyer->email]);
        $activeProduct = $this->createProduct(['seller_id' => $seller->id]);
        $inactiveSellerProduct = $this->createProduct(['seller_id' => $inactiveSeller->id]);
        $selfProduct = $this->createProduct(['seller_id' => $sameEmailSeller->id]);
        $policy = app(ConversationPolicy::class);

        $activeProduct->load('seller');
        $inactiveSellerProduct->load('seller');
        $selfProduct->load('seller');

        $this->assertTrue($policy->createFromProduct($buyer, $activeProduct));
        $this->assertFalse($policy->createFromProduct($buyer, $inactiveSellerProduct));
        $this->assertFalse($policy->createFromProduct($buyer, $selfProduct));
    }
}
