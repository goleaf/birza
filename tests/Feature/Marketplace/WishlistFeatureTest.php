<?php

namespace Tests\Feature\Marketplace;

use App\Actions\Wishlists\AddProductToWishlistAction;
use App\Livewire\Frontend\Buyer\Products\Show as BuyerProductShow;
use App\Livewire\Frontend\Buyer\Wishlists\Index as BuyerWishlistsIndex;
use App\Livewire\Frontend\Buyer\Wishlists\Show as BuyerWishlistShow;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\Feature\Support\MarketplaceTestHelpers;
use Tests\TestCase;

class WishlistFeatureTest extends TestCase
{
    use MarketplaceTestHelpers;
    use RefreshDatabase;

    public function test_buyer_can_add_product_to_default_wishlist(): void
    {
        $buyer = $this->createBuyer();
        $product = $this->createProduct([
            'name' => 'Favorite Milk',
        ]);

        Livewire::actingAs($buyer, 'buyer')
            ->test(BuyerProductShow::class, ['product' => $product])
            ->call('addToWishlist')
            ->assertHasNoErrors();

        $wishlist = Wishlist::query()
            ->where('buyer_id', $buyer->id)
            ->where('is_default', true)
            ->firstOrFail();

        $this->assertDatabaseHas('wishlist_items', [
            'wishlist_id' => $wishlist->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_duplicate_favorite_is_not_created(): void
    {
        $buyer = $this->createBuyer();
        $product = $this->createProduct();

        app(AddProductToWishlistAction::class)->handle($buyer, $product);

        $this->expectException(ValidationException::class);

        try {
            app(AddProductToWishlistAction::class)->handle($buyer, $product);
        } finally {
            $this->assertDatabaseCount('wishlist_items', 1);
        }
    }

    public function test_wishlist_list_shows_only_current_buyers_lists(): void
    {
        $buyer = $this->createBuyer();
        $otherBuyer = $this->createBuyer();

        Wishlist::factory()->for($buyer, 'buyer')->create([
            'name' => 'Summer Picks',
        ]);
        Wishlist::factory()->for($otherBuyer, 'buyer')->create([
            'name' => 'Other Buyer Picks',
        ]);

        Livewire::actingAs($buyer, 'buyer')
            ->test(BuyerWishlistsIndex::class)
            ->assertSee('Summer Picks')
            ->assertDontSee('Other Buyer Picks');
    }

    public function test_buyer_cannot_view_another_buyers_private_wishlist(): void
    {
        $owner = $this->createBuyer();
        $otherBuyer = $this->createBuyer();
        $wishlist = Wishlist::factory()
            ->private()
            ->for($owner, 'buyer')
            ->create();

        Livewire::actingAs($otherBuyer, 'buyer')
            ->test(BuyerWishlistShow::class, ['wishlist' => $wishlist])
            ->assertForbidden();
    }

    public function test_buyer_can_remove_product_from_own_wishlist(): void
    {
        $buyer = $this->createBuyer();
        $product = $this->createProduct();
        $wishlist = Wishlist::factory()->for($buyer, 'buyer')->create();
        WishlistItem::factory()
            ->for($wishlist)
            ->forProduct($product)
            ->create();

        Livewire::actingAs($buyer, 'buyer')
            ->test(BuyerWishlistShow::class, ['wishlist' => $wishlist])
            ->call('removeProduct', $product->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('wishlist_items', [
            'wishlist_id' => $wishlist->id,
            'product_id' => $product->id,
        ]);
    }
}
