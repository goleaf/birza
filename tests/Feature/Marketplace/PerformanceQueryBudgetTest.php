<?php

namespace Tests\Feature\Marketplace;

use App\Actions\Cart\CreateOrdersFromCartAction;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Support\MarketplaceTestHelpers;
use Tests\TestCase;

class PerformanceQueryBudgetTest extends TestCase
{
    use MarketplaceTestHelpers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        Storage::fake('public');
    }

    public function test_buyer_catalog_keeps_query_count_bounded_with_many_products(): void
    {
        $buyer = $this->createBuyer();
        $this->createCatalogProducts(36);

        Model::preventLazyLoading();

        try {
            [$queryCount, $response] = $this->countQueries(
                fn () => $this->actingAs($buyer, 'buyer')->get(route('buyer.products.index'))
            );
        } finally {
            Model::preventLazyLoading(false);
        }

        $response->assertOk();
        $this->assertLessThanOrEqual(30, $queryCount);
    }

    public function test_buyer_order_list_keeps_query_count_bounded_with_many_orders(): void
    {
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $product = $this->createProduct(['seller_id' => $seller->id]);

        $this->createOrdersWithItems($buyer, $seller, $product, 32);

        [$queryCount, $response] = $this->countQueries(
            fn () => $this->actingAs($buyer, 'buyer')->get(route('buyer.orders.index'))
        );

        $response->assertOk();
        $this->assertLessThanOrEqual(35, $queryCount);
    }

    public function test_seller_order_list_keeps_query_count_bounded_with_many_orders(): void
    {
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $product = $this->createProduct(['seller_id' => $seller->id]);

        $this->createOrdersWithItems($buyer, $seller, $product, 32);

        [$queryCount, $response] = $this->countQueries(
            fn () => $this->actingAs($seller, 'seller')->get(route('seller.orders.index'))
        );

        $response->assertOk();
        $this->assertLessThanOrEqual(40, $queryCount);
    }

    public function test_seller_product_list_paginates_without_loading_category_product_collections(): void
    {
        $seller = $this->createSeller();
        $category = Category::factory()->active()->create([
            'category_name' => ['en' => 'Root', 'lt' => 'Saknis'],
        ]);
        $subcategory = Category::factory()->active()->create([
            'parent_category_id' => $category->id,
            'category_name' => ['en' => 'Leaf', 'lt' => 'Lapas'],
        ]);
        $seller->categories()->attach($subcategory);

        Product::factory()
            ->active()
            ->count(34)
            ->for($seller, 'seller')
            ->create([
                'category_id' => $subcategory->id,
                'country_of_origin' => $this->createLithuanianCountry()->id,
            ]);

        [$queryCount, $response] = $this->countQueries(
            fn () => $this->actingAs($seller, 'seller')->get(route('seller.products.index'))
        );

        $response->assertOk()
            ->assertSee($category->getTranslation('category_name', app()->getLocale()))
            ->assertSee($subcategory->getTranslation('category_name', app()->getLocale()));
        $this->assertLessThanOrEqual(25, $queryCount);
    }

    public function test_checkout_uses_batched_product_loading_for_many_cart_items(): void
    {
        Notification::fake();

        $buyer = $this->createBuyer([
            'address' => 'Buyer Street 20',
        ]);
        $seller = $this->createSeller();
        $cart = Cart::factory()->create([
            'user_id' => $buyer->id,
            'guest_token' => null,
            'status' => Cart::STATUS_ACTIVE,
        ]);

        $this->createCatalogProducts(8, $seller)
            ->each(fn (Product $product): CartItem => CartItem::factory()
                ->for($cart)
                ->for($product)
                ->create([
                    'quantity' => 2,
                    'unit_price' => $product->price,
                ]));

        $queries = [];

        $printedProductLookupTraces = 0;

        DB::listen(function ($query) use (&$queries, &$printedProductLookupTraces): void {
            $queries[] = strtolower($query->sql);

            if ($printedProductLookupTraces < 2
                && str_starts_with(strtolower($query->sql), 'select')
                && str_contains(strtolower($query->sql), 'from "products"')
                && str_contains(strtolower($query->sql), 'where "id" = ?')) {
                $printedProductLookupTraces++;
                fwrite(STDERR, collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 16))
                    ->map(fn (array $frame): string => ($frame['class'] ?? '').($frame['type'] ?? '').($frame['function'] ?? '').':'.($frame['line'] ?? ''))
                    ->implode(PHP_EOL).PHP_EOL.PHP_EOL);
            }
        });

        app(CreateOrdersFromCartAction::class)->handle($cart, $buyer, [
            'shipping_address' => 'Buyer Street 20',
            'payment_method' => 'bank_transfer',
        ]);

        $productQueries = collect($queries)
            ->filter(fn (string $query): bool => str_starts_with($query, 'select')
                && str_contains($query, 'from "products"'))
            ->values();

        $this->assertLessThanOrEqual(3, $productQueries->count(), $productQueries->implode(PHP_EOL));
    }

    /**
     * @return Collection<int, Product>
     */
    private function createCatalogProducts(int $count, ?Seller $seller = null): Collection
    {
        $seller ??= $this->createSeller();
        $country = $this->createLithuanianCountry();
        $parentCategory = Category::factory()->active()->create([
            'category_name' => ['en' => 'Food', 'lt' => 'Maistas'],
        ]);
        $category = Category::factory()->active()->create([
            'parent_category_id' => $parentCategory->id,
            'category_name' => ['en' => 'Dairy', 'lt' => 'Pienas'],
        ]);

        return Product::factory()
            ->active()
            ->count($count)
            ->for($seller, 'seller')
            ->create([
                'category_id' => $category->id,
                'country_of_origin' => $country->id,
                'stock' => 50,
                'min_order_count' => 1,
            ])
            ->each(fn (Product $product): ProductImage => ProductImage::factory()
                ->primary()
                ->for($product)
                ->create());
    }

    private function createOrdersWithItems(
        Buyer $buyer,
        Seller $seller,
        Product $product,
        int $count,
    ): void {
        Order::factory()
            ->count($count)
            ->for($buyer, 'buyer')
            ->sequence(
                ['status' => OrderStatus::Pending, 'payment_status' => OrderPaymentStatus::Pending],
                ['status' => OrderStatus::Accepted, 'payment_status' => OrderPaymentStatus::Paid],
                ['status' => OrderStatus::Completed, 'payment_status' => OrderPaymentStatus::Paid],
            )
            ->create()
            ->each(fn (Order $order): OrderItem => OrderItem::factory()
                ->for($order)
                ->forProduct($product, 2)
                ->create([
                    'seller_id' => $seller->id,
                    'total_price' => 20,
                ]));
    }

    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return array{0: int, 1: TReturn}
     */
    private function countQueries(callable $callback): array
    {
        $queryCount = 0;

        DB::listen(function () use (&$queryCount): void {
            $queryCount++;
        });

        $result = $callback();

        return [$queryCount, $result];
    }
}
