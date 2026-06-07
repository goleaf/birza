<?php

namespace Tests\Feature\Marketplace;

use App\Actions\Wishlists\AddProductToWishlistAction;
use App\Livewire\Frontend\Buyer\Products\Show as BuyerProductShow;
use App\Livewire\Frontend\Buyer\Wishlists\Index as BuyerWishlistsIndex;
use App\Livewire\Frontend\Buyer\Wishlists\Show as BuyerWishlistsShow;
use App\Models\CartItem;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use JsonException;
use Livewire\Livewire;
use Tests\Feature\Support\MarketplaceTestHelpers;
use Tests\TestCase;

class ProductWishlistFeatureTest extends TestCase
{
    use MarketplaceTestHelpers;
    use RefreshDatabase;

    public function test_buyer_can_create_wishlist(): void
    {
        $buyer = $this->createBuyer();
        $this->actingAs($buyer, 'buyer');

        Livewire::test(BuyerWishlistsIndex::class)
            ->set('name', 'Weekend market')
            ->set('description', 'Products to compare before checkout.')
            ->set('isPrivate', true)
            ->call('createWishlist')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wishlists', [
            'buyer_id' => $buyer->id,
            'name' => 'Weekend market',
            'is_default' => true,
            'is_private' => true,
        ]);
    }

    public function test_buyer_can_rename_own_wishlist(): void
    {
        $buyer = $this->createBuyer();
        $wishlist = Wishlist::factory()->for($buyer, 'buyer')->default()->create();
        $this->actingAs($buyer, 'buyer');

        Livewire::test(BuyerWishlistsIndex::class)
            ->call('startEditing', $wishlist->id)
            ->set('editName', 'Holiday orders')
            ->set('editDescription', 'Products for December.')
            ->set('editIsPrivate', false)
            ->set('editIsDefault', true)
            ->call('updateWishlist')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wishlists', [
            'id' => $wishlist->id,
            'name' => 'Holiday orders',
            'description' => 'Products for December.',
            'is_private' => false,
        ]);
    }

    public function test_buyer_can_delete_own_wishlist(): void
    {
        $buyer = $this->createBuyer();
        $wishlist = Wishlist::factory()->for($buyer, 'buyer')->create([
            'name' => 'Delete me',
        ]);
        $this->actingAs($buyer, 'buyer');

        Livewire::test(BuyerWishlistsIndex::class)
            ->call('deleteWishlist', $wishlist->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('wishlists', [
            'id' => $wishlist->id,
        ]);
    }

    public function test_buyer_can_view_own_wishlist_and_empty_state(): void
    {
        $buyer = $this->createBuyer();
        $wishlist = Wishlist::factory()->for($buyer, 'buyer')->create([
            'name' => 'Empty list',
        ]);

        $this->actingAs($buyer, 'buyer')
            ->get(route('buyer.wishlists.show', $wishlist))
            ->assertOk()
            ->assertSeeLivewire(BuyerWishlistsShow::class)
            ->assertSee('Empty list')
            ->assertSee(__('wishlists.items_empty'));
    }

    public function test_buyer_cannot_view_another_buyers_private_wishlist(): void
    {
        $owner = $this->createBuyer();
        $otherBuyer = $this->createBuyer();
        $wishlist = Wishlist::factory()->for($owner, 'buyer')->private()->create();

        $this->actingAs($otherBuyer, 'buyer')
            ->get(route('buyer.wishlists.show', $wishlist))
            ->assertForbidden();
    }

    public function test_buyer_can_add_active_product_to_wishlist_from_product_page(): void
    {
        $buyer = $this->createBuyer();
        $product = $this->createProduct([
            'name' => 'Wishlist Apples',
        ]);
        $this->actingAs($buyer, 'buyer');

        Livewire::test(BuyerProductShow::class, ['product' => $product])
            ->call('addToWishlist')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wishlist_items', [
            'product_id' => $product->id,
        ]);
        $this->assertDatabaseHas('wishlists', [
            'buyer_id' => $buyer->id,
            'name' => __('wishlists.default_name'),
            'is_default' => true,
        ]);
    }

    public function test_buyer_cannot_add_inactive_product_to_wishlist(): void
    {
        $buyer = $this->createBuyer();
        $product = $this->createProduct([
            'is_active' => false,
        ]);

        $this->expectException(ValidationException::class);

        try {
            app(AddProductToWishlistAction::class)->handle($buyer, $product);
        } finally {
            $this->assertDatabaseMissing('wishlist_items', [
                'product_id' => $product->id,
            ]);
        }
    }

    public function test_buyer_cannot_add_deleted_product_to_wishlist(): void
    {
        $buyer = $this->createBuyer();
        $product = $this->createProduct();
        $product->delete();

        $this->expectException(ValidationException::class);

        try {
            app(AddProductToWishlistAction::class)->handle($buyer, $product);
        } finally {
            $this->assertDatabaseMissing('wishlist_items', [
                'product_id' => $product->id,
            ]);
        }
    }

    public function test_buyer_cannot_add_duplicate_product_to_same_wishlist(): void
    {
        $buyer = $this->createBuyer();
        $product = $this->createProduct();
        $wishlist = Wishlist::factory()->for($buyer, 'buyer')->default()->create();

        app(AddProductToWishlistAction::class)->handle($buyer, $product, $wishlist);

        $this->expectException(ValidationException::class);

        try {
            app(AddProductToWishlistAction::class)->handle($buyer, $product, $wishlist);
        } finally {
            $this->assertSame(1, WishlistItem::query()->where('wishlist_id', $wishlist->id)->count());
        }
    }

    public function test_buyer_can_remove_product_from_wishlist(): void
    {
        $buyer = $this->createBuyer();
        $wishlist = Wishlist::factory()->for($buyer, 'buyer')->create();
        $product = $this->createProduct();
        WishlistItem::factory()->for($wishlist)->for($product)->create();
        $this->actingAs($buyer, 'buyer');

        Livewire::test(BuyerWishlistsShow::class, ['wishlist' => $wishlist])
            ->call('removeProduct', $product->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('wishlist_items', [
            'wishlist_id' => $wishlist->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_buyer_can_move_product_between_wishlists(): void
    {
        $buyer = $this->createBuyer();
        $sourceWishlist = Wishlist::factory()->for($buyer, 'buyer')->create([
            'name' => 'Source list',
        ]);
        $targetWishlist = Wishlist::factory()->for($buyer, 'buyer')->create([
            'name' => 'Target list',
        ]);
        $product = $this->createProduct();
        WishlistItem::factory()->for($sourceWishlist)->for($product)->create();
        $this->actingAs($buyer, 'buyer');

        Livewire::test(BuyerWishlistsShow::class, ['wishlist' => $sourceWishlist])
            ->set("moveTargetWishlistIds.{$product->id}", $targetWishlist->id)
            ->call('moveProduct', $product->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('wishlist_items', [
            'wishlist_id' => $sourceWishlist->id,
            'product_id' => $product->id,
        ]);
        $this->assertDatabaseHas('wishlist_items', [
            'wishlist_id' => $targetWishlist->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_buyer_can_clear_wishlist(): void
    {
        $buyer = $this->createBuyer();
        $wishlist = Wishlist::factory()->for($buyer, 'buyer')->create();
        WishlistItem::factory()->count(2)->for($wishlist)->create();
        $this->actingAs($buyer, 'buyer');

        Livewire::test(BuyerWishlistsShow::class, ['wishlist' => $wishlist])
            ->call('clearWishlist')
            ->assertHasNoErrors();

        $this->assertSame(0, WishlistItem::query()->where('wishlist_id', $wishlist->id)->count());
    }

    public function test_wishlist_item_can_be_added_to_cart(): void
    {
        $buyer = $this->createBuyer();
        $product = $this->createProduct([
            'min_order_count' => 2,
            'stock' => 10,
            'price' => 12.50,
        ]);
        $wishlist = Wishlist::factory()->for($buyer, 'buyer')->create();
        $wishlistItem = WishlistItem::factory()->for($wishlist)->for($product)->create();
        $this->actingAs($buyer, 'buyer');

        Livewire::test(BuyerWishlistsShow::class, ['wishlist' => $wishlist])
            ->call('addItemToCart', $wishlistItem->id)
            ->assertHasNoErrors();

        $cartItem = CartItem::query()
            ->whereHas('cart', fn ($query) => $query->where('user_id', $buyer->id))
            ->where('product_id', $product->id)
            ->first();

        $this->assertNotNull($cartItem);
        $this->assertSame(2, $cartItem->quantity);
    }

    public function test_seller_and_guest_cannot_manage_buyer_wishlists(): void
    {
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $wishlist = Wishlist::factory()->for($buyer, 'buyer')->create();

        $this->assertFalse(Gate::forUser($seller)->allows('update', $wishlist));

        $this->actingAs($seller, 'seller')
            ->get(route('buyer.wishlists.index'))
            ->assertRedirect(route('home'));

        auth('seller')->logout();

        $this->get(route('buyer.wishlists.index'))
            ->assertRedirect(route('home'));
    }

    /**
     * @throws JsonException
     */
    public function test_wishlist_translation_keys_exist(): void
    {
        $keys = [
            'wishlists.title',
            'wishlists.default_name',
            'wishlists.create',
            'wishlists.edit',
            'wishlists.delete',
            'wishlists.clear',
            'wishlists.empty',
            'wishlists.items_empty',
            'wishlists.actions.add_product',
            'wishlists.actions.remove_product',
            'wishlists.actions.move_product',
            'wishlists.actions.add_to_cart',
            'wishlists.messages.created',
            'wishlists.messages.updated',
            'wishlists.messages.deleted',
            'wishlists.messages.product_added',
            'wishlists.messages.product_removed',
            'wishlists.messages.already_exists',
            'wishlists.messages.product_unavailable',
        ];

        $en = $this->translationFile('en');
        $lt = $this->translationFile('lt');

        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $en);
            $this->assertArrayHasKey($key, $lt);
        }
    }

    /**
     * @return array<string, string>
     *
     * @throws JsonException
     */
    private function translationFile(string $locale): array
    {
        return json_decode(
            (string) file_get_contents(lang_path("$locale.json")),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
    }
}
