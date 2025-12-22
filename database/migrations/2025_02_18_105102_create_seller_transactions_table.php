<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users_sellers');
            $table->foreignId('order_id')->constrained('orders');
            $table->decimal('amount', 10, 2);
            $table->string('type');
            $table->string('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_transactions');
    }
};
