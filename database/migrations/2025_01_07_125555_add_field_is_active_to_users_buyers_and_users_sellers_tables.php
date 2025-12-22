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
            $table->boolean('is_active')->default(false)->after('email_verified_at');
            $table->datetime('password_reset_at')->nullable()->after('updated_at');
        });

        Schema::table('users_sellers', function (Blueprint $table) {
            $table->boolean('is_active')->default(false)->after('email_verified_at');
            $table->datetime('password_reset_at')->nullable()->after('updated_at');
        });
    }

};