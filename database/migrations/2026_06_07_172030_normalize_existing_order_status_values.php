<?php

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Order::query()
            ->where('status', 'paid')
            ->update(['status' => OrderStatus::Accepted->value]);

        Order::query()
            ->where('status', 'failed')
            ->update(['status' => OrderStatus::Rejected->value]);

        Order::query()
            ->whereIn('payment_status', [
                OrderStatus::Accepted->value,
                OrderStatus::Processing->value,
                OrderStatus::Shipped->value,
                OrderStatus::Delivered->value,
                OrderStatus::Completed->value,
            ])
            ->update(['payment_status' => OrderPaymentStatus::Paid->value]);

        Order::query()
            ->whereIn('payment_status', [
                OrderStatus::Rejected->value,
                OrderPaymentStatus::Failed->value,
            ])
            ->update(['payment_status' => OrderPaymentStatus::Failed->value]);

        Order::query()
            ->where('payment_status', OrderPaymentStatus::Cancelled->value)
            ->update(['payment_status' => OrderPaymentStatus::Cancelled->value]);

        Order::query()
            ->where('payment_status', OrderPaymentStatus::Refunded->value)
            ->update(['payment_status' => OrderPaymentStatus::Refunded->value]);
    }

    public function down(): void
    {
        Order::query()
            ->where('status', OrderStatus::Accepted->value)
            ->update(['status' => 'paid']);

        Order::query()
            ->where('status', OrderStatus::Rejected->value)
            ->update(['status' => 'failed']);
    }
};
