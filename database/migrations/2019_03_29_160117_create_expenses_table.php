<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expense_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('expense_name');
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('expense_type_id');
            $table->foreign('expense_type_id')
                ->references('id')
                ->on('expense_types')
                ->onDelete('cascade');
            $table->integer('amount');
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->onDelete('cascade');
            $table->dateTime('date_payed');
            $table->integer('paid_by');
            $table->text('description')->nullable();

            $table->timestamps();
        });

        Schema::create('user_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->unsignedBigInteger('expense_id');
            $table->foreign('expense_id')
                ->references('id')
                ->on('expenses')
                ->onDelete('cascade');
            $table->integer('amount');
            $table->dateTime('date_payed');
            $table->string('channel');
            $table->string('transaction_id');
            $table->timestamps();
        });

        Schema::create('investments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->integer('amount');
            $table->string('transaction_no');
            $table->string('channel')->nullable();
            $table->string('transaction_id')->nullable();
            $table->dateTime('date_payed');
            $table->timestamps();
        });

        Schema::create('customer_sms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('branch_id');

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->onDelete('cascade');
            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->onDelete('cascade');
            $table->text('sms');

            $table->timestamps();
        });
        Schema::create('user_sms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->onDelete('cascade');
            $table->text('sms');

            $table->timestamps();
        });

        Schema::create('settllement_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ConversationID');
            $table->string('OriginatorConversationID');
            $table->string('ResponseCode');
            $table->string('ResponseDescription');
            $table->boolean('settled')->default(false);
            $table->unsignedBigInteger('user_id');
            $table->integer('requested_by');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('mpesa_settlements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
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
        Schema::dropIfExists('mpesa_settlements');
        Schema::dropIfExists('settllement_requests');
        Schema::dropIfExists('user_sms');
        Schema::dropIfExists('customer_sms');
        Schema::dropIfExists('investments');
        Schema::dropIfExists('user_payments');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_types');
    }
}
