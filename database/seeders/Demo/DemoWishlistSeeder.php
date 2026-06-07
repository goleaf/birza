<?php

namespace Database\Seeders\Demo;

use App\Models\Product;
use App\Models\Users\Buyer;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DemoWishlistSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('wishlists') || ! Schema::hasTable('wishlist_items')) {
            return;
        }

        $emptyBuyer = Buyer::query()->where('email', 'demo-empty-buyer@example.com')->firstOrFail();
        $cartBuyer = Buyer::query()->where('email', 'demo-cart-buyer@example.com')->firstOrFail();
        $demoBuyer = Buyer::query()->where('email', 'buyer@example.com')->firstOrFail();

        $activeProduct = Product::query()->where('name', 'Demo Active Apples')->firstOrFail();
        $changedPriceProduct = Product::query()->where('name', 'Demo Changed Price Cheese')->firstOrFail();
        $inactiveProduct = Product::query()->where('name', 'Demo Inactive Honey')->firstOrFail();
        $outOfStockProduct = Product::query()->where('name', 'Demo Out Of Stock Milk')->firstOrFail();
        $sellerTwoProduct = Product::query()->where('name', 'Demo Seller Two Bread')->firstOrFail();

        $this->wishlist($emptyBuyer, __('wishlists.default_name'), true)
            ->items()
            ->delete();

        $defaultWishlist = $this->wishlist($cartBuyer, __('wishlists.default_name'), true);
        $defaultWishlist->items()->updateOrCreate(['product_id' => $activeProduct->id]);
        $defaultWishlist->items()->updateOrCreate(['product_id' => $changedPriceProduct->id]);

        $seasonalWishlist = $this->wishlist($cartBuyer, 'Demo Seasonal Order Ideas', false);
        $seasonalWishlist->items()->updateOrCreate(['product_id' => $sellerTwoProduct->id]);
        $seasonalWishlist->items()->updateOrCreate(['product_id' => $outOfStockProduct->id]);

        $unavailableWishlist = $this->wishlist($cartBuyer, 'Demo Unavailable Products', false);
        $unavailableWishlist->items()->updateOrCreate(['product_id' => $inactiveProduct->id]);

        $demoWishlist = $this->wishlist($demoBuyer, __('wishlists.default_name'), true);
        $demoWishlist->items()->updateOrCreate(['product_id' => $activeProduct->id]);
        $demoWishlist->items()->updateOrCreate(['product_id' => $outOfStockProduct->id]);
    }

    private function wishlist(Buyer $buyer, string $name, bool $isDefault): Wishlist
    {
        if ($isDefault) {
            $buyer->wishlists()->where('is_default', true)->update(['is_default' => false]);
        }

        return Wishlist::query()->updateOrCreate([
            'buyer_id' => $buyer->id,
            'name' => $name,
        ], [
            'slug' => Str::slug($name) ?: null,
            'description' => null,
            'is_default' => $isDefault,
            'is_private' => true,
        ]);
    }
}
