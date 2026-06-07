<?php

namespace App\Actions\ProductQuestions;

use App\Actions\Notifications\SendProductQuestionNotificationAction;
use App\Enums\ProductQuestionStatus;
use App\Models\ProductQuestion;
use App\Models\Users\Admin;
use Illuminate\Auth\Access\AuthorizationException;

class RejectProductQuestionAction
{
    public function __construct(
        private readonly RecordProductQuestionAuditAction $audit,
        private readonly SendProductQuestionNotificationAction $notifications,
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

        if (! $admin->can('reject', $productQuestion)) {
            throw new AuthorizationException(__('products.questions.messages.unauthorized_moderate'));
        }

        $oldValues = $this->audit->snapshot($productQuestion);

        $productQuestion->forceFill([
            'status' => ProductQuestionStatus::Rejected,
            'is_public' => false,
            'moderated_by_admin_id' => $admin->getKey(),
            'moderated_at' => now(),
            'moderation_reason' => $this->cleanNullableText($reason),
        ])->save();

        $productQuestion->refresh();

        $this->audit->changed(
            actor: $admin,
            productQuestion: $productQuestion,
            oldValues: $oldValues,
            action: 'product_question.rejected',
            source: $source,
            reason: $reason,
        );

        $this->notifications->rejected($productQuestion);

        return $productQuestion;
    }

    private function cleanNullableText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
