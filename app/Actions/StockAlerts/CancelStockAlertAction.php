<?php

namespace App\Actions\StockAlerts;

use App\Actions\StockAlerts\Concerns\LogsStockAlertAudit;
use App\Enums\ProductStockAlertStatus;
use App\Models\ProductStockAlert;
use App\Models\Users\Buyer;
use Illuminate\Support\Facades\Gate;

class CancelStockAlertAction
{
    use LogsStockAlertAudit;

    public function handle(ProductStockAlert $alert, Buyer $buyer): ProductStockAlert
    {
        Gate::forUser($buyer)->authorize('cancel', $alert);

        if (! $alert->isActive()) {
            return $alert;
        }

        $oldStatus = $alert->status;
        $alert->cancel();

        $this->logStockAlertAudit(
            actor: $buyer,
            action: 'stock_alert.cancelled',
            auditable: $alert,
            oldValues: [
                'status' => $oldStatus?->value,
            ],
            newValues: [
                'status' => ProductStockAlertStatus::Cancelled->value,
            ],
            metadata: [
                'product_id' => $alert->product_id,
                'buyer_id' => $alert->buyer_id,
            ],
        );

        return $alert;
    }
}
