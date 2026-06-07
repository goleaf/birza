<?php

namespace App\Actions\ProductReports;

use App\Actions\Audit\RecordAdminAction;
use App\Actions\Notifications\SendProductReportNotificationAction;
use App\Actions\Products\RecordProductAuditLogsAction;
use App\Enums\ProductReportStatus;
use App\Models\ProductReport;
use App\Models\Users\Admin;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

class HideReportedProductAction
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly RecordAdminAction $recordAdminAction,
        private readonly RecordProductAuditLogsAction $recordProductAuditLogs,
        private readonly SendProductReportNotificationAction $sendNotifications,
    ) {}

    public function handle(ProductReport $report, Admin $admin, ?string $adminNote = null): ProductReport
    {
        $resolvedReport = DB::transaction(function () use ($report, $admin, $adminNote): ProductReport {
            $report->loadMissing('product.seller');
            $product = $report->product;

            $oldReportValues = $this->reportSnapshot($report);
            $oldProductValues = $this->recordProductAuditLogs->snapshot($product);
            $oldImages = $this->recordProductAuditLogs->imagePaths($product);

            if ((bool) $product->is_active) {
                $product->forceFill(['is_active' => false])->save();
            }

            $report->forceFill([
                'status' => ProductReportStatus::Resolved,
                'reviewed_by' => $admin->getKey(),
                'reviewed_at' => now(),
                'admin_note' => $this->cleanText($adminNote),
            ])->save();

            $this->recordProductAuditLogs->updated(
                actor: $admin,
                product: $product,
                oldValues: $oldProductValues,
                oldImages: $oldImages,
                source: 'admin_product_report_moderation',
                reason: $adminNote,
            );

            $this->auditLogService->log(
                actor: $admin,
                action: 'product_report.product_hidden',
                auditable: $report,
                oldValues: $oldReportValues,
                newValues: $this->reportSnapshot($report),
                metadata: [
                    'product_id' => $product->getKey(),
                    'seller_id' => $product->seller_id,
                    'product_is_active' => false,
                ],
                reason: $adminNote,
            );

            $this->recordAdminAction->handle(
                actor: $admin,
                action: 'product_report.product_hidden',
                entity: $report,
                oldValues: [
                    'report' => $oldReportValues,
                    'product' => $oldProductValues,
                ],
                newValues: [
                    'report' => $this->reportSnapshot($report),
                    'product' => $this->recordProductAuditLogs->snapshot($product),
                ],
                metadata: [
                    'product_id' => $product->getKey(),
                    'seller_id' => $product->seller_id,
                ],
                reason: $adminNote,
            );

            return $report->refresh();
        });

        $this->sendNotifications->productHidden($resolvedReport, $adminNote);

        return $resolvedReport;
    }

    /**
     * @return array<string, mixed>
     */
    private function reportSnapshot(ProductReport $report): array
    {
        return [
            'status' => $report->status->value,
            'reason' => $report->reason->value,
            'product_id' => $report->product_id,
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
