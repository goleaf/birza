<?php

namespace App\Actions\StockAlerts;

use App\Actions\StockAlerts\Concerns\LogsStockAlertAudit;
use App\Models\Product;
use App\Models\ProductStockAlert;
use App\Notifications\Marketplace\BackInStockNotification;
use Illuminate\Support\Facades\DB;

class NotifyBackInStockAction
{
    use LogsStockAlertAudit;

    public function handle(Product $product): int
    {
        $product->loadMissing('seller:id,name,company_name,is_active,deleted_at');

        if (! $product->isPurchasableByBuyers()) {
            return 0;
        }

        return DB::transaction(function () use ($product): int {
            $alerts = ProductStockAlert::query()
                ->active()
                ->where('product_id', $product->id)
                ->whereHas('buyer', function ($query): void {
                    $query
                        ->where('is_active', true)
                        ->where('is_verified', true)
                        ->whereNull('deleted_at');
                })
                ->with(['buyer:id,name,email,is_active,is_verified,deleted_at'])
                ->lockForUpdate()
                ->get();

            $sent = 0;

            foreach ($alerts as $alert) {
                if (! $alert->buyer) {
                    continue;
                }

                $alert->markNotified();

                $alert->buyer->notify((new BackInStockNotification($product))->afterCommit());

                $this->logStockAlertAudit(
                    actor: null,
                    action: 'stock_alert.notification_sent',
                    auditable: $alert,
                    oldValues: [
                        'status' => 'active',
                    ],
                    newValues: [
                        'status' => 'notified',
                        'notified_at' => $alert->notified_at?->toISOString(),
                    ],
                    metadata: [
                        'product_id' => $product->id,
                        'buyer_id' => $alert->buyer_id,
                    ],
                );

                $sent++;
            }

            return $sent;
        });
    }
}
