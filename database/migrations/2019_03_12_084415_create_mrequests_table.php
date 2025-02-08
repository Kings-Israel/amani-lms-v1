<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMrequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mrequests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ConversationID');
            $table->string('OriginatorConversationID');
            $table->string('ResponseCode');
            $table->string('ResponseDescription');
            $table->boolean('settled')->default(false);
            $table->string('loan_id');
            $table->string('disburse_loan_ip');

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
        Schema::dropIfExists('mrequests');
    }
}
