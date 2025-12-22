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
            $table->string('bank_account')->nullable()->after('phone');
        });

        Schema::table('users_sellers', function (Blueprint $table) {
            $table->string('bank_account')->nullable()->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */

};
