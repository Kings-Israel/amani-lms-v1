<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCheckOffLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('check_off_loans', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('check_off_products')->onDelete('restrict');

            $table->unsignedBigInteger('employee_id');
            $table->foreign('employee_id')->references('id')->on('check_off_employees')->onDelete('restrict');

            $table->unsignedInteger('loan_amount');

            $table->date('end_date');
            $table->date('effective_date');

            $table->boolean('approved')->default(false);
            $table->string('approved_date')->nullable();

            $table->unsignedBigInteger('approved_by') ->nullable();
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');

            $table->boolean('settled')->default(false);
            $table->timestamp('settled_at')->nullable();
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
        Schema::dropIfExists('check_off_loans');
    }
}
