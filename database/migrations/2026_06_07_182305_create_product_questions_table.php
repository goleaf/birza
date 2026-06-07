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
        Schema::create('product_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users_sellers')->cascadeOnDelete();
            $table->foreignId('buyer_id')->nullable()->constrained('users_buyers')->nullOnDelete();
            $table->foreignId('answered_by_seller_id')->nullable()->constrained('users_sellers')->nullOnDelete();
            $table->foreignId('moderated_by_admin_id')->nullable()->constrained('users_admins')->nullOnDelete();
            $table->text('question');
            $table->text('answer')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->string('status', 32)->default('pending');
            $table->boolean('is_public')->default(false);
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();
            $table->timestamp('moderated_at')->nullable();
            $table->string('moderation_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_id', 'status', 'is_public']);
            $table->index(['seller_id', 'status', 'created_at']);
            $table->index(['buyer_id', 'created_at']);
            $table->index(['answered_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_questions');
    }
};
