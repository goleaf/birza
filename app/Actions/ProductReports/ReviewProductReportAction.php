<?php

namespace App\Actions\ProductReports;

use App\Enums\ProductReportStatus;
use App\Models\ProductReport;
use App\Models\Users\Admin;
use App\Services\AuditLogService;

class ReviewProductReportAction
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    public function handle(ProductReport $report, Admin $admin, ?string $adminNote = null): ProductReport
    {
        $oldValues = $this->snapshot($report);

        $report->forceFill([
            'status' => ProductReportStatus::Reviewing,
            'reviewed_by' => $admin->getKey(),
            'reviewed_at' => now(),
            'admin_note' => $this->cleanText($adminNote),
        ])->save();

        $this->auditLogService->log(
            actor: $admin,
            action: 'product_report.reviewing',
            auditable: $report,
            oldValues: $oldValues,
            newValues: $this->snapshot($report),
            metadata: ['product_id' => $report->product_id],
            reason: $adminNote,
        );

        return $report->refresh();
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(ProductReport $report): array
    {
        return [
            'status' => $report->status->value,
            'reviewed_by' => $report->reviewed_by,
            'reviewed_at' => $report->reviewed_at?->toISOString(),
            'admin_note' => $report->admin_note,
        ];
    }

    private function cleanText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
