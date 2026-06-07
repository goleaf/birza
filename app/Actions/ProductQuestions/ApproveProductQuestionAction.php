<?php

namespace App\Actions\ProductQuestions;

use App\Enums\ProductQuestionStatus;
use App\Models\ProductQuestion;
use App\Models\Users\Admin;
use Illuminate\Auth\Access\AuthorizationException;

class ApproveProductQuestionAction
{
    public function __construct(
        private readonly RecordProductQuestionAuditAction $audit,
    ) {}

    /**
     * @throws AuthorizationException
     */
    public function handle(
        ProductQuestion $productQuestion,
        Admin $admin,
        ?string $reason = null,
        string $source = 'admin_product_questions',
    ): ProductQuestion {
        $productQuestion = $productQuestion->fresh(['product', 'buyer', 'seller']) ?? $productQuestion;

        if (! $admin->can('approve', $productQuestion)) {
            throw new AuthorizationException(__('products.questions.messages.unauthorized_moderate'));
        }

        $oldValues = $this->audit->snapshot($productQuestion);
        $hasAnswer = filled($productQuestion->answer);

        $productQuestion->forceFill([
            'status' => $hasAnswer ? ProductQuestionStatus::Answered : ProductQuestionStatus::Pending,
            'is_public' => $hasAnswer,
            'moderated_by_admin_id' => $admin->getKey(),
            'moderated_at' => now(),
            'moderation_reason' => $this->cleanNullableText($reason),
        ])->save();

        $productQuestion->refresh();

        $this->audit->changed(
            actor: $admin,
            productQuestion: $productQuestion,
            oldValues: $oldValues,
            action: 'product_question.approved',
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
