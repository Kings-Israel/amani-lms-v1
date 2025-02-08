<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCheckOffMpesaDisbursementResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('check_off_mpesa_disbursement_responses', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('loan_id');
            $table->foreign('loan_id')->references('id')->on('check_off_loans')->onDelete('restrict');

            $table->string('OriginatorConversationID')->nullable();
            $table->string('ConversationID')->nullable();
            $table->string('TransactionID')->nullable();
            $table->decimal('TransactionAmount', 8, 2)->nullable();
            $table->string('TransactionReceipt')->nullable();
            $table->string('B2CRecipientIsRegisteredCustomer')->nullable();
            $table->boolean('issued')->default(false);
            $table->json('response')->nullable();
            $table->string('ResultCode')->nullable();
            $table->string('ResultDesc')->nullable();
            $table->string('B2CChargesPaidAccountAvailableFunds')->nullable();
            $table->string('ReceiverPartyPublicName')->nullable();
            $table->timestamp('TransactionCompletedDateTime')->nullable();
            $table->string('B2CUtilityAccountAvailableFunds')->nullable();
            $table->string('B2CWorkingAccountAvailableFunds')->nullable();
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
        Schema::dropIfExists('check_off_mpesa_disbursement_responses');
    }
}
