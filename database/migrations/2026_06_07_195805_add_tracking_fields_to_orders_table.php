<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('tracking_number', 120)->nullable()->after('delivery_method');
            $table->string('carrier_name', 120)->nullable()->after('tracking_number');
            $table->date('estimated_delivery_date')->nullable()->after('carrier_name');
            $table->timestamp('shipped_at')->nullable()->after('estimated_delivery_date');
            $table->timestamp('delivered_at')->nullable()->after('shipped_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'tracking_number',
                'carrier_name',
                'estimated_delivery_date',
                'shipped_at',
                'delivered_at',
            ]);
        });
    }
};
