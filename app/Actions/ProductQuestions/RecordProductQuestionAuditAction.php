<?php

namespace App\Actions\ProductQuestions;

use App\Models\ProductQuestion;
use App\Services\AuditLogService;
use Illuminate\Contracts\Auth\Authenticatable;

class RecordProductQuestionAuditAction
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function snapshot(ProductQuestion $productQuestion): array
    {
        return $this->auditLogService->snapshot($productQuestion, [
            'product_id',
            'seller_id',
            'buyer_id',
            'answered_by_seller_id',
            'moderated_by_admin_id',
            'question',
            'answer',
            'answered_at',
            'status',
            'is_public',
            'guest_name',
            'moderated_at',
            'moderation_reason',
            'deleted_at',
        ]);
    }

    public function created(?Authenticatable $actor, ProductQuestion $productQuestion, string $source): void
    {
        $this->auditLogService->log(
            actor: $actor,
            action: 'product_question.created',
            auditable: $productQuestion,
            oldValues: null,
            newValues: $this->snapshot($productQuestion),
            metadata: $this->metadata($productQuestion, $source),
        );
    }

    /**
     * @param  array<string, mixed>  $oldValues
     */
    public function changed(
        ?Authenticatable $actor,
        ProductQuestion $productQuestion,
        array $oldValues,
        string $action,
        string $source,
        ?string $reason = null,
    ): void {
        $newValues = $this->snapshot($productQuestion);
        $changed = $this->auditLogService->changedValues($oldValues, $newValues);

        $this->auditLogService->log(
            actor: $actor,
            action: $action,
            auditable: $productQuestion,
            oldValues: $changed['old'],
            newValues: $changed['new'],
            metadata: $this->metadata($productQuestion, $source),
            reason: $reason,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function metadata(ProductQuestion $productQuestion, string $source): array
    {
        return [
            'source' => $source,
            'product_id' => $productQuestion->product_id,
            'seller_id' => $productQuestion->seller_id,
            'question_id' => $productQuestion->id,
            'guest_question' => $productQuestion->buyer_id === null,
        ];
    }
}
