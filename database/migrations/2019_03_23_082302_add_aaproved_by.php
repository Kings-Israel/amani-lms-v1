<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAaprovedBy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->integer('approved_by')->nullable();
            $table->integer('disbursed_by')->nullable();
        });

        Schema::table('mrequests', function (Blueprint $table) {
            $table->integer('requested_by');
        });
        Schema::table('installments', function (Blueprint $table) {
            $table->integer('position');
        });
        Schema::table('arrears', function (Blueprint $table) {
            $table->dropForeign(['installment_id']);
            $table->renameColumn('installment_id', 'loan_id');
            $table->foreign('loan_id')->references('id')->on('loans')->onDelete('cascade');
        });
        Schema::create('settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('registration_fee');
            $table->integer('loan_processing_fee');
            $table->integer('rollover_interest');


        });

        Schema::create('repayment_mpesa_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('amount');
            $table->string('mpesaReceiptNumber');
            $table->dateTime('transactionDate');
            $table->string('phoneNumber');
            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');




        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('repayment_mpesa_transactions');

        Schema::dropIfExists('settings');

        Schema::table('arrears', function (Blueprint $table) {

            $table->dropForeign(['loan_id']);
            $table->renameColumn('loan_id', 'installment_id');
            $table->foreign('installment_id')->references('id')->on('installments')->onDelete('cascade');
        });
        Schema::table('installments', function (Blueprint $table) {
            $table->dropColumn('position');
        });
        Schema::table('mrequests', function (Blueprint $table) {
            $table->dropColumn('requested_by');
        });
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('disbursed_by');
            $table->dropColumn('approved_by');
        });
    }
}
