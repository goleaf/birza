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
        Schema::create('product_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reporter_id')->nullable()->constrained('users_buyers')->nullOnDelete();
            $table->string('reporter_email')->nullable();
            $table->string('reporter_fingerprint', 64)->nullable();
            $table->string('reason');
            $table->text('message')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users_admins')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
            $table->index(['reason', 'status']);
            $table->index(['product_id', 'status']);
            $table->index(['reporter_id', 'product_id', 'status'], 'product_reports_reporter_product_status_idx');
            $table->index(['reporter_email', 'product_id', 'status'], 'product_reports_guest_product_status_idx');
            $table->index(['reviewed_by', 'reviewed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_reports');
    }
};
