<?php

namespace Database\Seeders\Demo;

use App\Enums\ProductStockAlertStatus;
use App\Models\Product;
use App\Models\ProductStockAlert;
use App\Models\Users\Buyer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DemoStockAlertSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('product_stock_alerts')) {
            return;
        }

        $buyer = Buyer::query()->where('email', 'buyer@example.com')->first();
        $ordersBuyer = Buyer::query()->where('email', 'demo-orders-buyer@example.com')->first();
        $outOfStockProduct = Product::query()->where('name', 'Demo Out Of Stock Milk')->first();
        $availableProduct = Product::query()->where('name', 'Demo Active Apples')->first();

        if ($buyer && $outOfStockProduct) {
            ProductStockAlert::query()->updateOrCreate([
                'product_id' => $outOfStockProduct->id,
                'buyer_id' => $buyer->id,
                'status' => ProductStockAlertStatus::Active->value,
            ], [
                'notified_at' => null,
            ]);
        }

        if ($ordersBuyer && $availableProduct) {
            ProductStockAlert::query()->updateOrCreate([
                'product_id' => $availableProduct->id,
                'buyer_id' => $ordersBuyer->id,
                'status' => ProductStockAlertStatus::Notified->value,
            ], [
                'notified_at' => now()->subDay(),
            ]);
        }
    }
}
