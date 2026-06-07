<?php

namespace Tests\Feature\Marketplace;

use App\Actions\Cart\CalculateCartTotalsAction;
use App\Actions\Cart\CreateOrdersFromCartAction;
use App\Actions\Promotions\ApplyPromoCodeAction;
use App\Actions\Promotions\CreatePromoCodeAction;
use App\Actions\Promotions\CreateSellerDiscountAction;
use App\Actions\Promotions\UpdatePromoCodeAction;
use App\Livewire\Frontend\Buyer\Cart\Index as BuyerCartIndex;
use App\Models\AuditLog;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Discount;
use App\Models\OrderItem;
use App\Models\PromoCode;
use App\Models\PromoCodeRedemption;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\Feature\Support\MarketplaceTestHelpers;
use Tests\TestCase;

class SellerDiscountPromoCodeFeatureTest extends TestCase
{
    use MarketplaceTestHelpers;
    use RefreshDatabase;

    public function test_seller_can_create_percentage_and_fixed_amount_discounts(): void
    {
        $seller = $this->createSeller();
        $product = $this->createProduct([
            'seller_id' => $seller->id,
            'price' => 50,
        ]);

        $percentageDiscount = app(CreateSellerDiscountAction::class)->handle($seller, [
            'product_id' => $product->id,
            'name' => 'Ten percent',
            'type' => Discount::TYPE_PERCENTAGE,
            'value' => 10,
            'status' => Discount::STATUS_ACTIVE,
        ]);

        $fixedDiscount = app(CreateSellerDiscountAction::class)->handle($seller, [
            'product_id' => $product->id,
            'name' => 'Five euros',
            'type' => Discount::TYPE_FIXED_AMOUNT,
            'value' => 5,
            'status' => Discount::STATUS_ACTIVE,
        ]);

        $this->assertSame(Discount::TYPE_PERCENTAGE, $percentageDiscount->type);
        $this->assertSame('10.00', $percentageDiscount->value);
        $this->assertSame(Discount::TYPE_FIXED_AMOUNT, $fixedDiscount->type);
        $this->assertSame('5.00', $fixedDiscount->value);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'discount.created',
            'actor_id' => $seller->id,
            'actor_role' => 'seller',
        ]);
    }

    public function test_seller_cannot_create_invalid_discount_values(): void
    {
        $seller = $this->createSeller();
        $product = $this->createProduct(['seller_id' => $seller->id]);
        $action = app(CreateSellerDiscountAction::class);

        $this->assertValidationFails(fn (): Discount => $action->handle($seller, [
            'product_id' => $product->id,
            'name' => 'Too much',
            'type' => Discount::TYPE_PERCENTAGE,
            'value' => 101,
            'status' => Discount::STATUS_ACTIVE,
        ]));

        $this->assertValidationFails(fn (): Discount => $action->handle($seller, [
            'product_id' => $product->id,
            'name' => 'Negative',
            'type' => Discount::TYPE_FIXED_AMOUNT,
            'value' => -1,
            'status' => Discount::STATUS_ACTIVE,
        ]));

        $this->assertDatabaseCount('discounts', 0);
    }

    public function test_seller_can_create_promo_code_and_code_is_globally_unique(): void
    {
        $firstSeller = $this->createSeller();
        $secondSeller = $this->createSeller();

        $promoCode = app(CreatePromoCodeAction::class)->handle($firstSeller, [
            'code' => 'save10',
            'type' => PromoCode::TYPE_PERCENTAGE,
            'value' => 10,
            'status' => PromoCode::STATUS_ACTIVE,
            'per_user_limit' => 1,
        ]);

        $this->assertSame('SAVE10', $promoCode->code);

        $this->assertValidationFails(fn (): PromoCode => app(CreatePromoCodeAction::class)->handle($secondSeller, [
            'code' => 'SAVE10',
            'type' => PromoCode::TYPE_FIXED_AMOUNT,
            'value' => 5,
            'status' => PromoCode::STATUS_ACTIVE,
            'per_user_limit' => 1,
        ]));

        $this->assertDatabaseCount('promo_codes', 1);
    }

    public function test_seller_cannot_edit_another_sellers_promo_code(): void
    {
        $owner = $this->createSeller();
        $otherSeller = $this->createSeller();
        $promoCode = PromoCode::factory()->for($owner, 'seller')->create([
            'code' => 'OWNERONLY',
        ]);

        $this->expectException(AuthorizationException::class);

        app(UpdatePromoCodeAction::class)->handle($otherSeller, $promoCode, [
            'code' => 'OWNERONLY',
            'type' => PromoCode::TYPE_PERCENTAGE,
            'value' => 20,
            'status' => PromoCode::STATUS_ACTIVE,
            'per_user_limit' => 1,
        ]);
    }

    public function test_buyer_can_apply_and_remove_valid_promo_code(): void
    {
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $product = $this->createProduct([
            'seller_id' => $seller->id,
            'price' => 40,
            'stock' => 5,
        ]);
        $this->createCartWithItem($buyer, $product, 1);
        PromoCode::factory()->for($seller, 'seller')->percentage(25)->code('CART25')->create();

        $this->actingAs($buyer, 'buyer');

        Livewire::test(BuyerCartIndex::class)
            ->set('promoCodeInput', 'cart25')
            ->call('applyPromoCode')
            ->assertHasNoErrors()
            ->assertSet('appliedPromoCode', 'CART25')
            ->call('removePromoCode')
            ->assertSet('appliedPromoCode', null)
            ->assertSet('promoCodeInput', '');
    }

    public function test_buyer_cannot_apply_expired_inactive_or_not_started_promo_code(): void
    {
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $product = $this->createProduct([
            'seller_id' => $seller->id,
            'price' => 25,
        ]);
        $cartItem = $this->createCartWithItem($buyer, $product, 1);

        PromoCode::factory()->for($seller, 'seller')->expired()->code('EXPIRED')->create();
        PromoCode::factory()->for($seller, 'seller')->inactive()->code('INACTIVE')->create();
        PromoCode::factory()->for($seller, 'seller')->notStarted()->code('SOON')->create();

        foreach (['EXPIRED', 'INACTIVE', 'SOON'] as $code) {
            $this->assertValidationFails(fn (): array => app(ApplyPromoCodeAction::class)->handle($cartItem->cart, $buyer, $code));
        }
    }

    public function test_buyer_cannot_exceed_usage_or_per_user_limits(): void
    {
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $product = $this->createProduct([
            'seller_id' => $seller->id,
            'price' => 25,
        ]);
        $cartItem = $this->createCartWithItem($buyer, $product, 1);

        PromoCode::factory()->for($seller, 'seller')->usageLimitReached()->code('USEDUP')->create();
        $perUserPromo = PromoCode::factory()->for($seller, 'seller')->code('ONCE')->create([
            'per_user_limit' => 1,
        ]);
        $order = $this->createOrderWithItem($buyer, $seller, $product);
        PromoCodeRedemption::factory()->for($perUserPromo)->for($order)->create([
            'user_id' => $buyer->id,
            'discount_amount' => 5,
        ]);

        $this->assertValidationFails(fn (): array => app(ApplyPromoCodeAction::class)->handle($cartItem->cart, $buyer, 'USEDUP'));
        $this->assertValidationFails(fn (): array => app(ApplyPromoCodeAction::class)->handle($cartItem->cart, $buyer, 'ONCE'));
    }

    public function test_promo_code_only_applies_to_the_owning_sellers_products_in_multi_seller_cart(): void
    {
        $buyer = $this->createBuyer();
        $sellerOne = $this->createSeller();
        $sellerTwo = $this->createSeller();
        $sellerOneProduct = $this->createProduct([
            'seller_id' => $sellerOne->id,
            'price' => 100,
        ]);
        $sellerTwoProduct = $this->createProduct([
            'seller_id' => $sellerTwo->id,
            'price' => 50,
        ]);
        $cart = Cart::factory()->for($buyer, 'buyer')->create();
        CartItem::factory()->for($cart)->forProduct($sellerOneProduct, 1)->create();
        CartItem::factory()->for($cart)->forProduct($sellerTwoProduct, 1)->create();
        PromoCode::factory()->for($sellerOne, 'seller')->fixedAmount(20)->code('SELLERONE')->create();

        $totals = app(CalculateCartTotalsAction::class)->handle($cart, $buyer, 'SELLERONE');

        $this->assertSame('20.00', $totals['promo_discount_amount']);
        $this->assertSame(80.0, $totals['seller_totals'][$sellerOne->id]['total']);
        $this->assertSame(50.0, $totals['seller_totals'][$sellerTwo->id]['total']);
        $this->assertSame('130.00', $totals['total']);
    }

    public function test_discount_and_promo_can_never_make_total_negative(): void
    {
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $product = $this->createProduct([
            'seller_id' => $seller->id,
            'price' => 5,
        ]);
        $cartItem = $this->createCartWithItem($buyer, $product, 1);
        Discount::factory()->for($seller, 'seller')->forProduct($product)->fixedAmount(500)->create();

        $discountTotals = app(CalculateCartTotalsAction::class)->handle($cartItem->cart, $buyer);

        $this->assertSame('0.00', $discountTotals['total']);
        $this->assertSame('5.00', $discountTotals['discount_total']);

        $secondProduct = $this->createProduct([
            'seller_id' => $seller->id,
            'price' => 5,
        ]);
        $secondCartItem = $this->createCartWithItem($buyer, $secondProduct, 1);
        PromoCode::factory()->for($seller, 'seller')->fixedAmount(500)->code('FREEISH')->create();

        $promoTotals = app(CalculateCartTotalsAction::class)->handle($secondCartItem->cart, $buyer, 'FREEISH');

        $this->assertSame('0.00', $promoTotals['total']);
        $this->assertSame('5.00', $promoTotals['promo_discount_amount']);
    }

    public function test_checkout_recalculates_backend_totals_records_redemption_and_stores_snapshots(): void
    {
        $buyer = $this->createBuyer([
            'address' => 'Buyer Street 20',
        ]);
        $seller = $this->createSeller();
        $product = $this->createProduct([
            'seller_id' => $seller->id,
            'price' => 50,
            'stock' => 10,
            'min_order_count' => 1,
        ]);
        $cartItem = $this->createCartWithItem($buyer, $product, 2, 0.01);
        $discount = Discount::factory()->for($seller, 'seller')->forProduct($product)->percentage(10)->create();
        $promoCode = PromoCode::factory()->for($seller, 'seller')->fixedAmount(5)->code('BACKEND')->create([
            'per_user_limit' => 2,
        ]);

        $orders = app(CreateOrdersFromCartAction::class)->handle($cartItem->cart, $buyer, [
            'shipping_address' => 'Buyer Street 20',
            'payment_method' => 'bank_transfer',
            'promo_code' => 'BACKEND',
        ]);

        $order = $orders->first();
        $item = OrderItem::query()->where('order_id', $order->id)->firstOrFail();

        $this->assertSame('100.00', $order->subtotal);
        $this->assertSame('15.00', $order->discount_total);
        $this->assertSame('BACKEND', $order->promo_code);
        $this->assertSame('5.00', $order->promo_discount_amount);
        $this->assertSame('85.00', $order->order_total);
        $this->assertSame('50.00', $item->unit_price);
        $this->assertSame('50.00', $item->original_unit_price);
        $this->assertSame('15.00', $item->discount_amount);
        $this->assertSame('42.50', $item->final_unit_price);
        $this->assertSame('85.00', $item->total_price);
        $this->assertStringContainsString('discount:'.$discount->id, (string) $item->discount_source);
        $this->assertStringContainsString('promo_code:'.$promoCode->id, (string) $item->discount_source);
        $this->assertSame(1, $promoCode->refresh()->used_count);
        $this->assertSame(1, $discount->refresh()->used_count);
        $this->assertDatabaseHas('promo_code_redemptions', [
            'promo_code_id' => $promoCode->id,
            'user_id' => $buyer->id,
            'order_id' => $order->id,
            'discount_amount' => '5.00',
        ]);
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
        $this->assertSame(8, $product->refresh()->stock);
    }

    public function test_failed_checkout_does_not_create_promo_redemption(): void
    {
        $buyer = $this->createBuyer([
            'address' => 'Buyer Street 21',
        ]);
        $seller = $this->createSeller();
        $product = $this->createProduct([
            'seller_id' => $seller->id,
            'stock' => 1,
            'min_order_count' => 1,
        ]);
        $cartItem = $this->createCartWithItem($buyer, $product, 2);
        PromoCode::factory()->for($seller, 'seller')->fixedAmount(5)->code('FAILSAFE')->create();

        $this->assertValidationFails(fn (): mixed => app(CreateOrdersFromCartAction::class)->handle($cartItem->cart, $buyer, [
            'shipping_address' => 'Buyer Street 21',
            'payment_method' => 'bank_transfer',
            'promo_code' => 'FAILSAFE',
        ]));

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('promo_code_redemptions', 0);
        $this->assertDatabaseHas('cart_items', ['id' => $cartItem->id]);
    }

    public function test_translation_keys_exist_for_discount_and_promo_surfaces(): void
    {
        foreach ((array) config('app.locales') as $locale) {
            $translations = json_decode((string) file_get_contents(lang_path("{$locale}.json")), true, 512, JSON_THROW_ON_ERROR);

            foreach ([
                'discounts.title',
                'discounts.type.percentage',
                'promo_codes.apply',
                'promo_codes.expired',
                'checkout.total_after_discount',
            ] as $key) {
                $this->assertArrayHasKey($key, $translations);
                $this->assertNotSame($key, $translations[$key]);
            }
        }
    }

    public function test_audit_log_is_created_for_promo_creation_and_redemption(): void
    {
        $buyer = $this->createBuyer([
            'address' => 'Buyer Street 22',
        ]);
        $seller = $this->createSeller();
        $product = $this->createProduct([
            'seller_id' => $seller->id,
            'price' => 30,
            'stock' => 5,
            'min_order_count' => 1,
        ]);
        $cartItem = $this->createCartWithItem($buyer, $product, 1);
        app(CreatePromoCodeAction::class)->handle($seller, [
            'code' => 'AUDITME',
            'type' => PromoCode::TYPE_FIXED_AMOUNT,
            'value' => 5,
            'status' => PromoCode::STATUS_ACTIVE,
            'per_user_limit' => 1,
        ]);

        app(CreateOrdersFromCartAction::class)->handle($cartItem->cart, $buyer, [
            'shipping_address' => 'Buyer Street 22',
            'payment_method' => 'bank_transfer',
            'promo_code' => 'AUDITME',
        ]);

        $this->assertTrue(AuditLog::query()->where('action', 'promo_code.created')->exists());
        $this->assertTrue(AuditLog::query()->where('action', 'promo_code.applied_at_checkout')->exists());
        $this->assertTrue(AuditLog::query()->where('action', 'promo_code.redemption_created')->exists());
    }

    private function assertValidationFails(callable $callback): void
    {
        try {
            $callback();
        } catch (ValidationException) {
            $this->assertTrue(true);

            return;
        }

        $this->fail('Expected validation to fail.');
    }
}
