<?php

namespace App\Actions\ProductQuestions;

use App\Actions\Notifications\SendProductQuestionNotificationAction;
use App\Models\Product;
use App\Models\ProductQuestion;
use App\Models\Users\Buyer;
use App\Policies\ProductQuestionPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class CreateProductQuestionAction
{
    public function __construct(
        private readonly ProductQuestionPolicy $policy,
        private readonly RecordProductQuestionAuditAction $audit,
        private readonly SendProductQuestionNotificationAction $notifications,
    ) {}

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function handle(
        Product $product,
        ?Buyer $buyer,
        string $question,
        ?string $guestName = null,
        ?string $guestEmail = null,
        string $source = 'product_questions_panel',
    ): ProductQuestion {
        $product = $product->fresh(['seller']) ?? $product;

        if (! $this->policy->create($buyer, $product)) {
            throw new AuthorizationException(__('products.questions.messages.unauthorized_create'));
        }

        $question = trim($question);
        $guestName = $this->cleanNullableText($guestName);
        $guestEmail = $this->cleanNullableText($guestEmail);

        if ($question === '') {
            throw ValidationException::withMessages([
                'question' => __('products.questions.validation.question_required'),
            ]);
        }

        if ($buyer === null && $guestName === null) {
            throw ValidationException::withMessages([
                'guestName' => __('products.questions.validation.guest_name_required'),
            ]);
        }

        $productQuestion = ProductQuestion::query()->create([
            'product_id' => $product->getKey(),
            'seller_id' => $product->seller_id,
            'buyer_id' => $buyer?->getKey(),
            'question' => $question,
            'guest_name' => $buyer === null ? $guestName : null,
            'guest_email' => $buyer === null ? $guestEmail : null,
        ]);

        $this->audit->created($buyer, $productQuestion, $source);
        $this->notifications->created($productQuestion);

        return $productQuestion;
    }

    private function cleanNullableText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
