<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCheckoffEmployerSmsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('check_off_loans', function (Blueprint $table) {
            $table->unsignedBigInteger('employer_approval_id')->nullable();
            $table->foreign('employer_approval_id')
                ->references('id')
                ->on('check_off_employers')
                ->onDelete('restrict');

        });
        Schema::table('check_off_employers', function (Blueprint $table) {
            $table->string('otp')->nullable();
            $table->string('password')->nullable();

        });

        Schema::create('checkoff_employer_smses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employer_id');
            $table->foreign('employer_id')
                ->references('id')
                ->on('check_off_employers')
                ->onDelete('restrict');
            $table->string('message');
            $table->string('phone');
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
        Schema::dropIfExists('checkoff_employer_smses');
        Schema::table('check_off_employers', function (Blueprint $table) {
            $table->dropColumn('otp');
            $table->dropColumn('password');


        });
        Schema::table('check_off_loans', function (Blueprint $table) {
            $table->dropForeign(['employer_approval_id']);
            $table->dropColumn('employer_approval_id');

        });
    }
}
