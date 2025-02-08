<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCheckOffMpesaDisbursementTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('check_off_mpesa_disbursement_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('loan_id');
            $table->foreign('loan_id')->references('id')->on('check_off_loans')->onDelete('restrict');

            $table->string('transaction_receipt');
            $table->decimal('amount', 8, 2);
            $table->string('channel')->default('MPESA DISBURSEMENT API');
            $table->timestamp('disbursed_at');
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
        Schema::dropIfExists('check_off_mpesa_disbursement_transactions');
    }
}
