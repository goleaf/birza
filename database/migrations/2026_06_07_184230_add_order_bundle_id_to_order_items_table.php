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
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('order_bundle_id')
                ->nullable()
                ->after('order_id')
                ->constrained('order_bundles')
                ->nullOnDelete();

            $table->index(['order_bundle_id', 'seller_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['order_bundle_id', 'seller_id']);
            $table->dropConstrainedForeignId('order_bundle_id');
        });
    }
};
