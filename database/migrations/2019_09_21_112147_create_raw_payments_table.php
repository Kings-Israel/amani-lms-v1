<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRawPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('raw_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('amount');
            $table->string('mpesaReceiptNumber');
            $table->string('customer');
            $table->string('phoneNumber');
            $table->string('BusinessShortCode');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('raw_payments');
    }
}
