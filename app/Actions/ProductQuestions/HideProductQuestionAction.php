<?php

namespace App\Actions\ProductQuestions;

use App\Enums\ProductQuestionStatus;
use App\Models\ProductQuestion;
use App\Models\Users\Admin;
use App\Models\Users\Seller;
use Illuminate\Auth\Access\AuthorizationException;

class HideProductQuestionAction
{
    public function __construct(
        private readonly RecordProductQuestionAuditAction $audit,
    ) {}

    /**
     * @throws AuthorizationException
     */
    public function handle(
        ProductQuestion $productQuestion,
        Admin|Seller $actor,
        ?string $reason = null,
        string $source = 'product_questions',
    ): ProductQuestion {
        $productQuestion = $productQuestion->fresh(['product', 'buyer', 'seller']) ?? $productQuestion;

        if (! $actor->can('hide', $productQuestion)) {
            throw new AuthorizationException(__('products.questions.messages.unauthorized_moderate'));
        }

        $oldValues = $this->audit->snapshot($productQuestion);

        $productQuestion->forceFill([
            'status' => ProductQuestionStatus::Hidden,
            'is_public' => false,
            'moderated_by_admin_id' => $actor instanceof Admin ? $actor->getKey() : $productQuestion->moderated_by_admin_id,
            'moderated_at' => now(),
            'moderation_reason' => $this->cleanNullableText($reason),
        ])->save();

        $productQuestion->refresh();

        $this->audit->changed(
            actor: $actor,
            productQuestion: $productQuestion,
            oldValues: $oldValues,
            action: 'product_question.hidden',
            source: $source,
            reason: $reason,
        );

        return $productQuestion;
    }

    private function cleanNullableText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
