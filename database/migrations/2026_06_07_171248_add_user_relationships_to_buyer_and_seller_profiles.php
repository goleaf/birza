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
        Schema::table('users_buyers', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->unique()
                ->after('id')
                ->constrained()
                ->nullOnDelete();
        });

        Schema::table('users_sellers', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->unique()
                ->after('id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_sellers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::table('users_buyers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
