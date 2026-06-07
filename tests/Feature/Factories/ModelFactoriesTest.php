<?php

namespace Tests\Feature\Factories;

use App\Enums\OrderStatus;
use App\Models\Activity;
use App\Models\Address;
use App\Models\AdminAction;
use App\Models\Attribute;
use App\Models\AttributeProduct;
use App\Models\AttributeValue;
use App\Models\AuditLog;
use App\Models\BuyerCreditHistory;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Country;
use App\Models\CreditAttachment;
use App\Models\GlobalSettings;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductImage;
use App\Models\ProductQuestion;
use App\Models\ProductReport;
use App\Models\ProductStockAlert;
use App\Models\Review;
use App\Models\SellerTransaction;
use App\Models\User;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelFactoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_main_factories_create_valid_marketplace_records(): void
    {
        $admin = Admin::factory()->active()->create();
        $sharedUser = User::factory()->asBuyerAndSeller()->create();
        $buyer = Buyer::factory()->verified()->withUser()->create();
        $seller = Seller::factory()->verified()->withUser()->withCategories()->create();
        $country = Country::factory()->active()->create();
        $category = Category::factory()->active()->create();
        $attribute = Attribute::factory()->active()->filterable()->required()->create();
        $attributeValue = AttributeValue::factory()->active()->for($attribute)->create();

        $product = Product::factory()
            ->for($seller, 'seller')
            ->for($country, 'country')
            ->for($category, 'category')
            ->withGallery()
            ->create();

        $order = Order::factory()
            ->for($buyer, 'buyer')
            ->completed()
            ->withItems(2, $seller)
            ->withStatusHistory()
            ->create();

        $orderItem = OrderItem::factory()
            ->for($order)
            ->forProduct($product)
            ->create();

        $cart = Cart::factory()
            ->for($buyer, 'buyer')
            ->withItems(2)
            ->create();

        $cartItem = CartItem::factory()
            ->for($cart)
            ->forProduct($product)
            ->create();

        $review = Review::factory()
            ->approved()
            ->for($product)
            ->for(User::factory())
            ->create();

        $address = Address::factory()
            ->default()
            ->lithuanian()
            ->for(User::factory())
            ->create();

        $notification = Notification::factory()
            ->forNotifiable($buyer)
            ->orderStatusChanged(OrderStatus::Processing->value)
            ->create();

        $creditHistory = BuyerCreditHistory::factory()
            ->add()
            ->withAttachment()
            ->for($buyer, 'buyer')
            ->for($admin, 'admin')
            ->create();

        $sellerTransaction = SellerTransaction::factory()
            ->forSellerOrder($seller, $order)
            ->addition()
            ->create();

        $productImage = ProductImage::factory()
            ->primary()
            ->forProductPath($product)
            ->create();

        $attributeProduct = AttributeProduct::factory()
            ->forProductAttribute($product, $attribute, $attributeValue)
            ->create();

        $productAttributeValue = ProductAttributeValue::factory()
            ->forProductAttribute($product, $attribute, $attributeValue)
            ->create();

        $wishlist = Wishlist::factory()
            ->default()
            ->for($buyer, 'buyer')
            ->withItems()
            ->create();

        $wishlistItem = WishlistItem::factory()
            ->for($wishlist)
            ->forProduct($product)
            ->create();

        $productQuestion = ProductQuestion::factory()
            ->forProduct($product)
            ->answered()
            ->create([
                'buyer_id' => $buyer->id,
            ]);

        $stockAlert = ProductStockAlert::factory()
            ->notified()
            ->create([
                'product_id' => $product->id,
                'buyer_id' => $buyer->id,
            ]);

        $productReport = ProductReport::factory()
            ->resolved($admin)
            ->create([
                'product_id' => $product->id,
                'reporter_id' => $buyer->id,
            ]);

        $statusHistory = OrderStatusHistory::factory()
            ->for($order)
            ->transition(OrderStatus::Pending, OrderStatus::Completed)
            ->byAdmin($admin->id)
            ->create();

        $auditLog = AuditLog::factory()
            ->byAdmin($admin)
            ->forAuditable($product)
            ->create();

        $activity = Activity::factory()->create();
        $adminAction = AdminAction::factory()->create();
        $settings = GlobalSettings::factory()->create();
        $attachment = CreditAttachment::factory()->for($creditHistory, 'creditHistory')->create();

        $this->assertTrue($sharedUser->buyerProfile()->exists());
        $this->assertTrue($sharedUser->sellerProfile()->exists());
        $this->assertSame($seller->id, $orderItem->seller_id);
        $this->assertSame($product->id, $cartItem->product_id);
        $this->assertTrue($cart->items()->exists());
        $this->assertTrue($product->images()->exists());
        $this->assertSame($product->id, $review->product_id);
        $this->assertSame($address->user_id, $address->user->id);
        $this->assertSame($buyer->id, $notification->notifiable_id);
        $this->assertSame('add', $creditHistory->type);
        $this->assertSame('addition', $sellerTransaction->type);
        $this->assertTrue($productImage->is_primary);
        $this->assertSame($attributeValue->id, $attributeProduct->selected_value_id);
        $this->assertSame($attributeValue->id, $productAttributeValue->attribute_value_id);
        $this->assertTrue($wishlist->items()->exists());
        $this->assertSame($product->id, $wishlistItem->product_id);
        $this->assertSame($seller->id, $productQuestion->seller_id);
        $this->assertSame($buyer->id, $stockAlert->buyer_id);
        $this->assertSame($product->id, $productReport->product_id);
        $this->assertSame(OrderStatus::Completed, $statusHistory->new_status);
        $this->assertSame($product->id, $auditLog->auditable_id);
        $this->assertNotNull($activity->id);
        $this->assertNotNull($adminAction->id);
        $this->assertNotNull($settings->id);
        $this->assertSame($creditHistory->id, $attachment->credit_history_id);
    }
}
