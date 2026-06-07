<?php

namespace Tests\Unit\Models;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use App\Models\User;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EloquentRelationshipMapTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_buyer_and_seller_profiles(): void
    {
        $user = User::factory()->create();
        $buyer = Buyer::factory()->create(['user_id' => $user->id]);
        $seller = Seller::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(HasOne::class, $user->buyerProfile());
        $this->assertInstanceOf(HasOne::class, $user->sellerProfile());
        $this->assertInstanceOf(Buyer::class, $user->buyerProfile);
        $this->assertInstanceOf(Seller::class, $user->sellerProfile);
        $this->assertSame($buyer->id, $user->buyerProfile->id);
        $this->assertSame($seller->id, $user->sellerProfile->id);
    }

    public function test_profile_models_belong_to_user(): void
    {
        $user = User::factory()->create();
        $buyer = Buyer::factory()->create(['user_id' => $user->id]);
        $seller = Seller::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(BelongsTo::class, $buyer->user());
        $this->assertInstanceOf(BelongsTo::class, $seller->user());
        $this->assertSame($user->id, $buyer->user->id);
        $this->assertSame($user->id, $seller->user->id);
    }

    public function test_commerce_relationship_map_is_consistent(): void
    {
        $seller = Seller::factory()->create();
        $buyer = Buyer::factory()->create();
        $product = Product::factory()->for($seller, 'seller')->create();
        $order = Order::factory()->for($buyer, 'buyer')->create();
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'seller_id' => $seller->id,
        ]);

        $this->assertInstanceOf(HasMany::class, $seller->products());
        $this->assertInstanceOf(HasMany::class, $buyer->orders());
        $this->assertInstanceOf(HasMany::class, $order->orderItems());
        $this->assertInstanceOf(BelongsTo::class, $product->category());
        $this->assertInstanceOf(BelongsTo::class, $product->seller());
        $this->assertSame($product->id, $seller->products->sole()->id);
        $this->assertSame($order->id, $buyer->orders->sole()->id);
        $this->assertSame($orderItem->id, $order->orderItems->sole()->id);
        $this->assertSame($seller->id, $product->seller->id);
    }

    public function test_product_has_images_and_reviews(): void
    {
        $product = Product::factory()->create();
        $image = ProductImage::factory()->create(['product_id' => $product->id]);
        $review = Review::factory()->create(['product_id' => $product->id]);

        $this->assertInstanceOf(HasMany::class, $product->images());
        $this->assertInstanceOf(HasMany::class, $product->reviews());
        $this->assertSame($image->id, $product->images->sole()->id);
        $this->assertSame($review->id, $product->reviews->sole()->id);
    }

    public function test_cart_has_items_and_items_belong_to_product(): void
    {
        $cart = Cart::factory()->create();
        $item = CartItem::factory()->create(['cart_id' => $cart->id]);

        $this->assertInstanceOf(HasMany::class, $cart->cartItems());
        $this->assertInstanceOf(BelongsTo::class, $item->cart());
        $this->assertInstanceOf(BelongsTo::class, $item->product());
        $this->assertSame($item->id, $cart->cartItems->sole()->id);
    }

    public function test_review_notification_and_address_belong_to_user(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);
        $notification = Notification::factory()->create(['user_id' => $user->id]);
        $address = Address::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(BelongsTo::class, $review->user());
        $this->assertInstanceOf(BelongsTo::class, $notification->user());
        $this->assertInstanceOf(BelongsTo::class, $address->user());
        $this->assertInstanceOf(HasMany::class, $user->reviews());
        $this->assertInstanceOf(HasMany::class, $user->notifications());
        $this->assertInstanceOf(HasMany::class, $user->addresses());
        $this->assertSame($review->id, $user->reviews->sole()->id);
        $this->assertSame($notification->id, $user->notifications->sole()->id);
        $this->assertSame($address->id, $user->addresses->sole()->id);
    }
}
