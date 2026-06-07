<?php

namespace App\Actions\ProductQuestions;

use App\Actions\Notifications\SendProductQuestionNotificationAction;
use App\Models\ProductQuestion;
use App\Models\Users\Seller;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class AnswerProductQuestionAction
{
    public function __construct(
        private readonly RecordProductQuestionAuditAction $audit,
        private readonly SendProductQuestionNotificationAction $notifications,
    ) {}

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function handle(
        ProductQuestion $productQuestion,
        Seller $seller,
        string $answer,
        string $source = 'seller_product_questions',
    ): ProductQuestion {
        $productQuestion = $productQuestion->fresh(['product', 'buyer', 'seller']) ?? $productQuestion;

        if (! $seller->can('answer', $productQuestion)) {
            throw new AuthorizationException(__('products.questions.messages.unauthorized_answer'));
        }

        $answer = trim($answer);

        if ($answer === '') {
            throw ValidationException::withMessages([
                'answer' => __('products.questions.validation.answer_required'),
            ]);
        }

        $oldValues = $this->audit->snapshot($productQuestion);

        $productQuestion->markAnswered($seller, $answer);
        $productQuestion->refresh();

        $this->audit->changed(
            actor: $seller,
            productQuestion: $productQuestion,
            oldValues: $oldValues,
            action: 'product_question.answered',
            source: $source,
        );

        $this->notifications->answered($productQuestion);

        return $productQuestion;
    }
}
