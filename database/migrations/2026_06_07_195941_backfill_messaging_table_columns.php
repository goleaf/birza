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
        $this->backfillConversations();
        $this->backfillMessages();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration repairs already-applied local skeleton tables. Fresh
        // databases get the full schema from the original create migrations.
    }

    private function backfillConversations(): void
    {
        if (! Schema::hasTable('conversations') || Schema::hasColumn('conversations', 'buyer_id')) {
            return;
        }

        Schema::table('conversations', function (Blueprint $table): void {
            $table->unsignedBigInteger('buyer_id')->index();
            $table->unsignedBigInteger('seller_id')->index();
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->string('status', 32)->index();
            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamp('buyer_archived_at')->nullable();
            $table->timestamp('seller_archived_at')->nullable();
            $table->softDeletes();

            $table->index(['buyer_id', 'last_message_at']);
            $table->index(['seller_id', 'last_message_at']);
            $table->unique(['buyer_id', 'seller_id', 'product_id'], 'conversations_buyer_seller_product_unique');
            $table->unique(['buyer_id', 'seller_id', 'order_id'], 'conversations_buyer_seller_order_unique');
        });
    }

    private function backfillMessages(): void
    {
        if (! Schema::hasTable('messages') || Schema::hasColumn('messages', 'conversation_id')) {
            return;
        }

        Schema::table('messages', function (Blueprint $table): void {
            $table->unsignedBigInteger('conversation_id')->index();
            $table->unsignedBigInteger('sender_id')->index();
            $table->string('sender_role', 32);
            $table->text('body');
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamp('edited_at')->nullable();
            $table->json('metadata')->nullable();
            $table->softDeletes();

            $table->index('created_at');
            $table->index(['conversation_id', 'created_at']);
            $table->index(['conversation_id', 'read_at']);
            $table->index(['sender_role', 'sender_id']);
        });
    }
};
