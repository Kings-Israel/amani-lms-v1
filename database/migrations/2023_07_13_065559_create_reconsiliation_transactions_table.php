<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReconsiliationTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reconsiliation_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')->references('id')
                ->on('customers')->onDelete('restrict');
            $table->unsignedBigInteger('reconsiled_by');
            $table->foreign('reconsiled_by')->references('id')
                ->on('users')->onDelete('restrict');
            $table->integer('amount');
            $table->string('transaction_id');
            $table->dateTime('date_paid');
            $table->string('phone_number');
            $table->string('channel');
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
        Schema::dropIfExists('reconsiliation_transactions');
    }
}
