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
        Schema::table('products', function (Blueprint $table) {
            $table->integer('temperature_conditions_from')->nullable()->after('stock');
            $table->integer('temperature_conditions_to')->nullable()->after('temperature_conditions_from');
            $table->date('use_until')->nullable()->after('temperature_conditions_to');
            $table->integer('total_shelf_life')->nullable()->after('use_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'temperature_conditions_from',
                'temperature_conditions_to',
                'use_until',
                'total_shelf_life'
            ]);
        });
    }
};
