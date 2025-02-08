<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMpesaTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mpesa_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('loan_id');
            $table->string('ResultCode');
            $table->string('ResultDesc');
            $table->string('OriginatorConversationID');
            $table->string('ConversationID');
            $table->string('TransactionID');
            $table->string('TransactionAmount');
            $table->string('TransactionReceipt');
            $table->string('B2CRecipientIsRegisteredCustomer');
            $table->string('B2CChargesPaidAccountAvailableFunds');
            $table->string('ReceiverPartyPublicName');
            $table->string('TransactionCompletedDateTime');
            $table->string('B2CUtilityAccountAvailableFunds');
            $table->string('B2CWorkingAccountAvailableFunds');
            $table->boolean('status')->default(false);
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
        Schema::dropIfExists('mpesa_transactions');
    }
}
