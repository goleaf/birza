<?php

namespace Tests\Feature\Controllers\Frontend\Seller;

use App\Livewire\Frontend\Seller\Products\Index as SellerProductsIndex;
use App\Models\Product;
use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_index_requires_authentication(): void
    {
        $response = $this->get(route('seller.products.index'));

        $response->assertRedirect(route('home'));
    }

    public function test_product_index_displays_for_authenticated_seller(): void
    {
        $seller = Seller::factory()->create();
        Product::factory()->count(3)->create(['seller_id' => $seller->id]);

        $response = $this->actingAs($seller, 'seller')
            ->get(route('seller.products.index'));

        $response->assertStatus(200)
            ->assertSeeLivewire(SellerProductsIndex::class);
    }

    public function test_product_soft_delete_confirmation_uses_modal_flow(): void
    {
        $seller = Seller::factory()->create();
        $product = Product::factory()->create([
            'seller_id' => $seller->id,
            'is_active' => true,
        ]);

        $this->actingAs($seller, 'seller');

        Livewire::test(SellerProductsIndex::class)
            ->call('confirmSoftDeleteProduct', $product->id)
            ->assertSet('confirmModal', true)
            ->assertSet('confirmModalMethod', 'softDeleteProduct')
            ->call('runConfirmedAction')
            ->assertSet('confirmModal', false);

        $product->refresh();

        $this->assertSoftDeleted('products', ['id' => $product->id]);
        $this->assertFalse($product->is_active);
    }
}
