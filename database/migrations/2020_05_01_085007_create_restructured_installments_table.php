<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRestructuredInstallmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('restructured_installments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('loan_id');
            $table->integer('principal_amount');
            $table->date('due_date');
            $table->boolean('current')->default(false);
            $table->integer('position');
            $table->boolean('for_rollover')->default(false);
            $table->integer('interest');
            $table->integer('total');
            $table->integer('amount_paid')->nullable();
            $table->date('start_date');
            $table->date('last_payment_date')->nullable();
            $table->date('interest_payment_date')->nullable();
            $table->boolean('completed')->default(false);
            $table->boolean('in_arrear')->default(false);
            $table->boolean('being_paid')->default(false);
            $table->timestamps();

            $table->foreign('loan_id')
                ->references('id')
                ->on('loans')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('restructured_installments');
    }
}
