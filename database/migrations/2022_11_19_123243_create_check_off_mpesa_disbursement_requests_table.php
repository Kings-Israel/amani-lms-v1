<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCheckOffMpesaDisbursementRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::create('check_off_mpesa_disbursement_requests', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('loan_id');
            $table->foreign('loan_id')->references('id')->on('check_off_loans')->onDelete('restrict');

            $table->unsignedBigInteger('requested_by');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('restrict');

            $table->string('ConversationID');
            $table->string('OriginatorConversationID');
            $table->string('ResponseCode');
            $table->string('ResponseDescription');
            $table->boolean('issued')->default(false);
            $table->json('response')->nullable();
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
        Schema::dropIfExists('check_off_mpesa_disbursement_requests');
    }
}
