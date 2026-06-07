<?php

namespace Tests\Feature\Marketplace;

use App\Actions\ProductQuestions\CreateProductQuestionAction;
use App\Enums\ProductQuestionStatus;
use App\Livewire\Backend\ProductQuestions\Index as AdminProductQuestionsIndex;
use App\Livewire\Frontend\ProductQuestions\Panel as ProductQuestionPanel;
use App\Livewire\Frontend\Seller\ProductQuestions\Index as SellerProductQuestionsIndex;
use App\Models\Category;
use App\Models\Country;
use App\Models\Product;
use App\Models\ProductQuestion;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use App\Notifications\Marketplace\ProductQuestionAnsweredNotification;
use App\Notifications\Marketplace\ProductQuestionCreatedNotification;
use App\Notifications\Marketplace\ProductQuestionRejectedNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class ProductQuestionFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_question_with_contact_name(): void
    {
        Notification::fake();

        $product = $this->createProduct([
            'name' => 'Question Milk',
        ]);

        Livewire::test(ProductQuestionPanel::class, ['product' => $product])
            ->set('question', 'When will this product be available in larger boxes?')
            ->set('guestName', 'Guest Buyer')
            ->set('guestEmail', 'guest@example.com')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('question', '')
            ->assertSet('guestName', '')
            ->assertSet('guestEmail', '');

        $this->assertDatabaseHas('product_questions', [
            'product_id' => $product->id,
            'seller_id' => $product->seller_id,
            'buyer_id' => null,
            'guest_name' => 'Guest Buyer',
            'guest_email' => 'guest@example.com',
            'status' => ProductQuestionStatus::Pending->value,
            'is_public' => false,
        ]);

        Notification::assertSentTo(
            $product->seller()->firstOrFail(),
            ProductQuestionCreatedNotification::class,
        );
    }

    public function test_buyer_can_submit_question_about_active_product(): void
    {
        Notification::fake();

        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $product = $this->createProduct([
            'seller_id' => $seller->id,
        ]);

        Livewire::actingAs($buyer, 'buyer')
            ->test(ProductQuestionPanel::class, ['product' => $product])
            ->set('question', 'Can you confirm the harvest date for this product?')
            ->call('submit')
            ->assertHasNoErrors();

        $question = ProductQuestion::query()->firstOrFail();

        $this->assertSame($buyer->id, $question->buyer_id);
        $this->assertSame($seller->id, $question->seller_id);
        $this->assertSame(ProductQuestionStatus::Pending, $question->status);
        $this->assertFalse($question->is_public);

        Notification::assertSentTo($seller, ProductQuestionCreatedNotification::class);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'product_question.created',
            'actor_id' => $buyer->id,
            'actor_role' => 'buyer',
            'auditable_id' => $question->id,
        ]);
    }

    public function test_inactive_product_cannot_receive_questions(): void
    {
        $buyer = $this->createBuyer();
        $product = $this->createProduct(['is_active' => false]);

        $this->expectException(AuthorizationException::class);

        try {
            app(CreateProductQuestionAction::class)->handle(
                product: $product,
                buyer: $buyer,
                question: 'Can I ask about an inactive product?',
            );
        } finally {
            $this->assertDatabaseCount('product_questions', 0);
        }
    }

    public function test_buyer_question_validation_requires_meaningful_text(): void
    {
        $buyer = $this->createBuyer();
        $product = $this->createProduct();

        Livewire::actingAs($buyer, 'buyer')
            ->test(ProductQuestionPanel::class, ['product' => $product])
            ->set('question', 'short')
            ->call('submit')
            ->assertHasErrors(['question' => ['min']]);

        $this->assertDatabaseCount('product_questions', 0);
    }

    public function test_seller_can_answer_own_product_question(): void
    {
        Notification::fake();

        $seller = $this->createSeller();
        $buyer = $this->createBuyer();
        $product = $this->createProduct([
            'seller_id' => $seller->id,
        ]);
        $question = ProductQuestion::factory()
            ->forProduct($product)
            ->pending()
            ->create([
                'buyer_id' => $buyer->id,
                'question' => 'Can you pack this in smaller boxes?',
            ]);

        Livewire::actingAs($seller, 'seller')
            ->test(SellerProductQuestionsIndex::class)
            ->set("answers.{$question->id}", 'Yes, smaller boxes are available on request.')
            ->call('answer', $question->id)
            ->assertHasNoErrors();

        $question->refresh();

        $this->assertSame(ProductQuestionStatus::Answered, $question->status);
        $this->assertSame($seller->id, $question->answered_by_seller_id);
        $this->assertTrue($question->is_public);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'product_question.answered',
            'auditable_type' => ProductQuestion::class,
            'auditable_id' => $question->id,
        ]);

        Notification::assertSentTo($buyer, ProductQuestionAnsweredNotification::class);
    }

    public function test_seller_cannot_answer_another_sellers_question(): void
    {
        $owner = $this->createSeller();
        $otherSeller = $this->createSeller();
        $product = $this->createProduct([
            'seller_id' => $owner->id,
        ]);
        $question = ProductQuestion::factory()
            ->forProduct($product)
            ->pending()
            ->create();

        $this->expectException(ModelNotFoundException::class);

        try {
            Livewire::actingAs($otherSeller, 'seller')
                ->test(SellerProductQuestionsIndex::class)
                ->set("answers.{$question->id}", 'Trying to answer another seller question.')
                ->call('answer', $question->id);
        } finally {
            $this->assertSame(ProductQuestionStatus::Pending, $question->refresh()->status);
        }
    }

    public function test_public_panel_shows_answered_public_questions_only(): void
    {
        $product = $this->createProduct();
        ProductQuestion::factory()
            ->forProduct($product)
            ->answered('Yes, this can be delivered chilled.')
            ->create([
                'question' => 'Can this be delivered chilled?',
                'guest_name' => 'Visible Buyer',
            ]);
        ProductQuestion::factory()
            ->forProduct($product)
            ->pending()
            ->create([
                'question' => 'Hidden pending question',
            ]);
        ProductQuestion::factory()
            ->forProduct($product)
            ->rejected('Rejected question.')
            ->create([
                'question' => 'Hidden rejected question',
            ]);
        ProductQuestion::factory()
            ->forProduct($product)
            ->hidden('Hidden question.')
            ->create([
                'question' => 'Hidden moderated question',
            ]);

        Livewire::test(ProductQuestionPanel::class, ['product' => $product])
            ->assertSee('Can this be delivered chilled?')
            ->assertSee('Yes, this can be delivered chilled.')
            ->assertDontSee('Hidden pending question')
            ->assertDontSee('Hidden rejected question')
            ->assertDontSee('Hidden moderated question');
    }

    public function test_public_product_page_renders_answered_questions(): void
    {
        $product = $this->createProduct(['name' => 'Public Question Cheese']);

        ProductQuestion::factory()
            ->forProduct($product)
            ->answered('Yes, it is suitable for refrigerated delivery.')
            ->create([
                'question' => 'Is this cheese suitable for refrigerated delivery?',
            ]);

        $this->get(route('buyer.products.show', $product))
            ->assertOk()
            ->assertSee('Is this cheese suitable for refrigerated delivery?')
            ->assertSee('Yes, it is suitable for refrigerated delivery.');
    }

    public function test_seller_questions_page_renders_unanswered_questions(): void
    {
        $seller = $this->createSeller();
        $product = $this->createProduct(['seller_id' => $seller->id]);

        ProductQuestion::factory()
            ->forProduct($product)
            ->pending()
            ->create([
                'question' => 'Can this order be prepared before noon?',
            ]);

        $this->actingAs($seller, 'seller')
            ->get(route('seller.product-questions.index'))
            ->assertOk()
            ->assertSeeLivewire(SellerProductQuestionsIndex::class)
            ->assertSee('Can this order be prepared before noon?');
    }

    public function test_admin_questions_page_renders_pending_questions_for_moderation(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createProduct();

        ProductQuestion::factory()
            ->forProduct($product)
            ->pending()
            ->create([
                'question' => 'Does this question need moderation?',
            ]);

        $this->actingAs($admin, 'admin')
            ->get(route('backend.product-questions.index'))
            ->assertOk()
            ->assertSeeLivewire(AdminProductQuestionsIndex::class)
            ->assertSee('Does this question need moderation?');
    }

    public function test_admin_can_reject_question_and_non_admin_cannot_moderate(): void
    {
        Notification::fake();

        $admin = $this->createAdmin();
        $seller = $this->createSeller();
        $buyer = $this->createBuyer();
        $product = $this->createProduct(['seller_id' => $seller->id]);
        $question = ProductQuestion::factory()
            ->forProduct($product)
            ->pending()
            ->create([
                'buyer_id' => $buyer->id,
                'question' => 'This moderation question needs review.',
            ]);

        Livewire::actingAs($seller, 'seller')
            ->test(AdminProductQuestionsIndex::class)
            ->call('reject', $question->id)
            ->assertForbidden();

        Livewire::actingAs($admin, 'admin')
            ->test(AdminProductQuestionsIndex::class)
            ->set("reasons.{$question->id}", 'Abusive content.')
            ->call('reject', $question->id)
            ->assertHasNoErrors();

        $question->refresh();

        $this->assertSame(ProductQuestionStatus::Rejected, $question->status);
        $this->assertFalse($question->is_public);
        $this->assertSame($admin->id, $question->moderated_by_admin_id);
        $this->assertSame('Abusive content.', $question->moderation_reason);

        Notification::assertSentTo($buyer, ProductQuestionRejectedNotification::class);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'product_question.rejected',
            'actor_id' => $admin->id,
            'actor_role' => 'admin',
            'auditable_id' => $question->id,
        ]);
    }

    public function test_answer_validation_and_translation_keys_work(): void
    {
        $seller = $this->createSeller();
        $product = $this->createProduct(['seller_id' => $seller->id]);
        $question = ProductQuestion::factory()
            ->forProduct($product)
            ->pending()
            ->create();

        Livewire::actingAs($seller, 'seller')
            ->test(SellerProductQuestionsIndex::class)
            ->set("answers.{$question->id}", '')
            ->call('answer', $question->id)
            ->assertHasErrors(["answers.{$question->id}" => 'required']);

        foreach ([
            'products.questions.title',
            'products.questions.ask',
            'products.questions.question',
            'products.questions.answer',
            'products.questions.no_questions',
            'products.questions.submit',
            'products.questions.pending_moderation',
            'products.questions.answered_successfully',
            'products.questions.rejected',
            'products.questions.hidden',
            'products.questions.validation.question_required',
            'notifications.product_question.created.title',
            'notifications.product_question.answered.title',
        ] as $key) {
            $this->assertNotSame($key, __($key));
        }

        $this->assertSame('pending', ProductQuestionStatus::Pending->value);
        $this->assertSame('answered', ProductQuestionStatus::Answered->value);
        $this->assertSame('rejected', ProductQuestionStatus::Rejected->value);
        $this->assertSame('hidden', ProductQuestionStatus::Hidden->value);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createAdmin(array $attributes = []): Admin
    {
        return Admin::factory()->create(array_merge([
            'password' => Hash::make('password'),
            'is_active' => true,
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createBuyer(array $attributes = []): Buyer
    {
        return Buyer::factory()->create(array_merge([
            'password' => Hash::make('password'),
            'is_active' => true,
            'is_verified' => true,
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createSeller(array $attributes = []): Seller
    {
        return Seller::factory()->create(array_merge([
            'password' => Hash::make('password'),
            'is_active' => true,
            'is_verified' => true,
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createProduct(array $attributes = []): Product
    {
        $parent = Category::factory()->create([
            'category_name' => ['en' => 'Food', 'lt' => 'Maistas'],
        ]);

        $category = Category::factory()->create([
            'parent_category_id' => $parent->id,
            'category_name' => ['en' => 'Dairy', 'lt' => 'Pienas'],
        ]);

        $country = $this->createLithuanianCountry();

        return Product::factory()->active()->create(array_merge([
            'category_id' => $category->id,
            'country_of_origin' => $country->id,
            'seller_id' => $this->createSeller()->id,
            'price' => 10.00,
            'min_order_count' => 1,
            'stock' => 10,
            'unit' => 'kg',
            'product_image' => '',
            'product_additional_image' => '',
        ], $attributes));
    }

    private function createLithuanianCountry(): Country
    {
        return Country::query()->firstOrCreate(
            ['alpha2' => 'LT'],
            [
                'region' => 'Europe',
                'is_active' => true,
                'country_name' => ['en' => 'Lithuania', 'lt' => 'Lietuva'],
                'description' => [
                    'en' => 'Lithuanian marketplace origin.',
                    'lt' => 'Lietuvos turgavietes kilmes salis.',
                ],
            ],
        );
    }
}
