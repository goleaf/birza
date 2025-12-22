<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('buyer_credit_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users_buyers')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('type'); // 'add' or 'deduct'
            $table->decimal('balance_after', 10, 2);
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('buyer_credit_history');
    }
};
