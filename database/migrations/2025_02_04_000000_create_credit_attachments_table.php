<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('credit_attachments');
        Schema::create('credit_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('credit_history_id');
            $table->foreign('credit_history_id')
                  ->references('id')
                  ->on('buyer_credit_history')
                  ->onDelete('cascade');
            $table->string('file_path');
            $table->string('original_name');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('credit_attachments');
    }
};
