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
            $table->decimal('discount_total', 10, 2)->default(0)->after('subtotal');
            $table->foreignId('promo_code_id')->nullable()->after('discount_total')->constrained('promo_codes')->nullOnDelete();
            $table->string('promo_code')->nullable()->after('promo_code_id');
            $table->decimal('promo_discount_amount', 10, 2)->default(0)->after('promo_code');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('discount_id')->nullable()->after('seller_id')->constrained('discounts')->nullOnDelete();
            $table->decimal('original_unit_price', 10, 2)->default(0)->after('unit_price');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('original_unit_price');
            $table->decimal('final_unit_price', 10, 2)->default(0)->after('discount_amount');
            $table->string('discount_source')->nullable()->after('seller_name_snapshot');

            $table->index(['discount_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['discount_id', 'created_at']);
            $table->dropConstrainedForeignId('discount_id');
            $table->dropColumn([
                'original_unit_price',
                'discount_amount',
                'final_unit_price',
                'discount_source',
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promo_code_id');
            $table->dropColumn([
                'discount_total',
                'promo_code',
                'promo_discount_amount',
            ]);
        });
    }
};
