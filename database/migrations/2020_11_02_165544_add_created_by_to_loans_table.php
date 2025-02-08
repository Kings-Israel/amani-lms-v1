<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCreatedByToLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')
                ->nullable()
                ->default(null)
                ->after('disbursed_by');

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
            $table->string('create_loan_ip')->nullable();
            $table->string('approve_loan_ip')->nullable();
            $table->string('disburse_loan_ip')->nullable();



        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('created_by');
            $table->dropColumn('create_loan_ip');
            $table->dropColumn('approve_loan_ip');
            $table->dropColumn('disburse_loan_ip');



        });
    }
}
