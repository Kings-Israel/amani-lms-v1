<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterMpesaTrans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mpesa_transactions', function (Blueprint $table) {
            $table->string('TransactionAmount')->nullable()->change();
            $table->string('TransactionReceipt')->nullable()->change();
            $table->string('B2CRecipientIsRegisteredCustomer')->nullable()->change();
            $table->string('B2CChargesPaidAccountAvailableFunds')->nullable()->change();
            $table->string('ReceiverPartyPublicName')->nullable()->change();
            $table->string('TransactionCompletedDateTime')->nullable()->change();
            $table->string('B2CUtilityAccountAvailableFunds')->nullable()->change();
            $table->string('B2CWorkingAccountAvailableFunds')->nullable()->change();



        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
