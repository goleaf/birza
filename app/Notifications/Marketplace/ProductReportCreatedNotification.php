<?php

namespace App\Notifications\Marketplace;

use App\Models\ProductReport;

class ProductReportCreatedNotification extends MarketplaceNotification
{
    public function __construct(public ProductReport $report) {}

    public function databaseType(object $notifiable): string
    {
        return 'marketplace.product_report.created';
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(object $notifiable): array
    {
        $this->report->loadMissing(['product', 'reporter']);

        return [
            'title_key' => 'notifications.reports.product_created.title',
            'message_key' => 'notifications.reports.product_created.message',
            'title_params' => ['product' => $this->report->product?->name],
            'message_params' => [
                'product' => $this->report->product?->name,
                'reason' => $this->report->reason->label(),
                'reporter' => $this->report->reporterLabel(),
            ],
            'related_type' => 'product_report',
            'related_id' => $this->report->id,
            'url' => $this->report->exists
                ? route('admin.reports.show', $this->report, false)
                : route('admin.notifications.index', absolute: false),
            'status' => $this->report->status->value,
            'icon' => 'shield-exclamation',
        ];
    }
}
