<?php

namespace App\Notifications\Marketplace;

use App\Models\ProductReport;

class ProductHiddenDueToReportNotification extends MarketplaceNotification
{
    protected bool $sendMail = true;

    public function __construct(
        public ProductReport $report,
        public ?string $adminNote = null,
    ) {}

    public function databaseType(object $notifiable): string
    {
        return 'marketplace.product_report.product_hidden';
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(object $notifiable): array
    {
        $this->report->loadMissing('product');
        $product = $this->report->product;

        return [
            'title_key' => 'notifications.reports.product_hidden.title',
            'message_key' => filled($this->adminNote)
                ? 'notifications.reports.product_hidden.message_with_note'
                : 'notifications.reports.product_hidden.message',
            'title_params' => ['product' => $product?->name],
            'message_params' => [
                'product' => $product?->name,
                'reason' => $this->report->reason->label(),
                'note' => $this->adminNote,
            ],
            'related_type' => 'product',
            'related_id' => $product?->id,
            'url' => $product ? route('seller.products.edit', $product, false) : null,
            'status' => 'hidden',
            'icon' => 'eye-slash',
        ];
    }
}
