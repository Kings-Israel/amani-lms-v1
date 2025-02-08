<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCheckOffEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('check_off_employees', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('referee_id');
            $table->foreign('referee_id')->references('id')->on('check_off_employee_referees')->onDelete('restrict');

            $table->unsignedBigInteger('next_of_kin_id');
            $table->foreign('next_of_kin_id')->references('id')->on('check_off_employee_next_of_kin')->onDelete('restrict');

            $table->unsignedBigInteger('employer_id');
            $table->foreign('employer_id')->references('id')->on('check_off_employers')->onDelete('restrict');

            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone_number')->unique();
            $table->string('id_number')->unique();
            $table->string('primary_email');
            $table->string('institution_email')->nullable();
            $table->date('dob');
            $table->enum('gender', ['Male', 'Female']);
            $table->enum('marital_status', ['Married', 'Single']);
            $table->date('date_of_employment');
            $table->enum('terms_of_employment', ['Permanent', 'Contract', 'Casual']);
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
        Schema::dropIfExists('check_off_employees');
    }
}
