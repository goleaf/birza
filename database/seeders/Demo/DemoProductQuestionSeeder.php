<?php

namespace Database\Seeders\Demo;

use App\Enums\ProductQuestionStatus;
use App\Models\Product;
use App\Models\ProductQuestion;
use App\Models\Users\Buyer;
use Illuminate\Database\Seeder;

class DemoProductQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $buyer = Buyer::query()
            ->where('email', 'buyer@example.com')
            ->first()
            ?? Buyer::query()->where('email', 'buyer1@birza.lt')->first()
            ?? Buyer::query()->orderBy('id')->first();

        $fallbackProducts = Product::query()
            ->with('seller:id,name,email,company_name')
            ->where('is_active', true)
            ->orderBy('id')
            ->limit(4)
            ->get();

        $this->question(
            productName: 'Demo Active Apples',
            fallbackProduct: $fallbackProducts->shift(),
            question: 'Can these apples be packed in smaller boxes for office kitchens?',
            buyer: $buyer,
            answer: 'Yes, we can split the order into smaller labelled boxes if you add a note at checkout.',
            status: ProductQuestionStatus::Answered,
        );

        $this->question(
            productName: 'Demo Published Product',
            fallbackProduct: $fallbackProducts->shift(),
            question: 'Is the product suitable for next-day delivery to Vilnius?',
            guestName: 'Guest Buyer',
            guestEmail: 'guest-question@example.com',
            answer: 'Yes, next-day delivery is available for orders confirmed before noon.',
            status: ProductQuestionStatus::Answered,
        );

        $this->question(
            productName: 'Demo Low Stock Yogurt',
            fallbackProduct: $fallbackProducts->shift(),
            question: 'When will this product be restocked?',
            buyer: $buyer,
            status: ProductQuestionStatus::Pending,
        );

        $this->question(
            productName: 'Demo Seller Two Bread',
            fallbackProduct: $fallbackProducts->shift(),
            question: 'Can you send private contact details here?',
            guestName: 'Anonymous Guest',
            status: ProductQuestionStatus::Rejected,
            reason: 'Question requested private contact details.',
        );
    }

    private function question(
        string $productName,
        ?Product $fallbackProduct,
        string $question,
        ?Buyer $buyer = null,
        ?string $guestName = null,
        ?string $guestEmail = null,
        ?string $answer = null,
        ProductQuestionStatus $status = ProductQuestionStatus::Pending,
        ?string $reason = null,
    ): void {
        $product = Product::query()
            ->with('seller:id,name,email,company_name')
            ->where('name', $productName)
            ->first();

        $product ??= $fallbackProduct;

        if ($product === null || $product->seller_id === null) {
            return;
        }

        ProductQuestion::query()->updateOrCreate(
            [
                'product_id' => $product->id,
                'question' => $question,
            ],
            [
                'seller_id' => $product->seller_id,
                'buyer_id' => $buyer?->id,
                'guest_name' => $buyer === null ? $guestName : null,
                'guest_email' => $buyer === null ? $guestEmail : null,
                'answer' => $answer,
                'answered_by_seller_id' => filled($answer) ? $product->seller_id : null,
                'answered_at' => filled($answer) ? now()->subDays(2) : null,
                'status' => $status,
                'is_public' => $status === ProductQuestionStatus::Answered && filled($answer),
                'moderated_at' => $status === ProductQuestionStatus::Rejected ? now()->subDay() : null,
                'moderation_reason' => $reason,
            ],
        );
    }
}
