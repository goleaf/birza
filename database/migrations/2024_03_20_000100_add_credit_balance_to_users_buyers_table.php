<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreditBalanceToUsersBuyersTable extends Migration
{
    public function up()
    {
        Schema::table('users_buyers', function (Blueprint $table) {
            $table->decimal('credit_balance', 10, 2)->default(0.00);
        });
    }

    public function down()
    {
        Schema::table('users_buyers', function (Blueprint $table) {
            $table->dropColumn('credit_balance');
        });
    }
}
