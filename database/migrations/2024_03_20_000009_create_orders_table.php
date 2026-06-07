<?php

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users_buyers');
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->default(OrderPaymentStatus::Pending->value);
            $table->string('status')->default(OrderStatus::Pending->value);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('order_total', 10, 2);
            $table->string('shipping_address_snapshot')->nullable();
            $table->string('billing_address_snapshot')->nullable();
            $table->string('delivery_method')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
