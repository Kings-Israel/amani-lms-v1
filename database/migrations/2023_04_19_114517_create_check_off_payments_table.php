<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCheckOffPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('check_off_payments', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('loan_id');
            $table->foreign('loan_id')->references('id')
                ->on('check_off_loans')->onDelete('restrict');

            $table->unsignedBigInteger('employer_id');
            $table->foreign('employer_id')->references('id')
                ->on('check_off_employers')->onDelete('restrict');

            $table->unsignedBigInteger('employee_id');
            $table->foreign('employee_id')->references('id')
                ->on('check_off_employees')->onDelete('restrict');

            $table->string('channel')->default('MPESA-PAYBILL');

            $table->string('TransID')->unique();
            $table->decimal('TransAmount');
            $table->string('TransTime');
            $table->string('BusinessShortCode');
            $table->string('BillRefNumber');
            $table->string('InvoiceNumber')->nullable();
            $table->decimal('OrgAccountBalance')->nullable();
            $table->string('MSISDN');
            $table->string('FirstName')->nullable();
            $table->string('MiddleName')->nullable();
            $table->string('LastName')->nullable();
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
        Schema::dropIfExists('check_off_payments');
    }
}
