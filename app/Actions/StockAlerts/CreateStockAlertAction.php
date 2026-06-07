<?php

namespace App\Actions\StockAlerts;

use App\Actions\StockAlerts\Concerns\LogsStockAlertAudit;
use App\Enums\ProductStockAlertStatus;
use App\Models\Product;
use App\Models\ProductStockAlert;
use App\Models\Users\Buyer;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class CreateStockAlertAction
{
    use LogsStockAlertAudit;

    public function handle(Product $product, Buyer $buyer): ProductStockAlert
    {
        $product->loadMissing('seller:id,is_active,deleted_at');

        Gate::forUser($buyer)->authorize('create', [ProductStockAlert::class, $product]);

        if ($product->isPurchasableByBuyers()) {
            throw ValidationException::withMessages([
                'stock_alert' => __('stock_alerts.product_available'),
            ]);
        }

        $alert = ProductStockAlert::query()->firstOrCreate([
            'product_id' => $product->id,
            'buyer_id' => $buyer->id,
            'status' => ProductStockAlertStatus::Active->value,
        ]);

        if ($alert->wasRecentlyCreated) {
            $this->logStockAlertAudit(
                actor: $buyer,
                action: 'stock_alert.created',
                auditable: $alert,
                newValues: [
                    'product_id' => $product->id,
                    'buyer_id' => $buyer->id,
                    'status' => ProductStockAlertStatus::Active->value,
                ],
                metadata: [
                    'product_id' => $product->id,
                ],
            );
        }

        return $alert;
    }
}
