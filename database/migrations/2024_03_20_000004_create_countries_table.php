<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('alpha2')->unique();
            $table->enum('region', ['Asia', 'Europe', 'Africa', 'Americas', 'Oceania']);
            $table->boolean('is_active')->default(true);
            $table->json('country_name')->unique();
            $table->json('description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
