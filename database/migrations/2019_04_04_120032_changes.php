<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Changes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('installments', function (Blueprint $table) {
            $table->renameColumn('amount', 'principal_amount');
            $table->integer('interest');
            $table->integer('total');
            $table->integer('amount_paid')->nullable();
            $table->date('start_date');
            $table->date('last_payment_date')->nullable();
            $table->boolean('completed')->default(false);
            $table->boolean('in_arrear')->default(false);
            $table->boolean('being_paid')->default(false);


        });
        Schema::table('arrears', function (Blueprint $table) {
            $table->unsignedBigInteger('installment_id');
            $table->foreign('installment_id')->references('id')->on('installments')->onDelete('cascade');


        });

        Schema::table('branches', function (Blueprint $table) {
            $table->integer('paybill')->nullable();

        });

        Schema::table('ro_targets', function (Blueprint $table) {
            $table->date('date');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->integer('salary')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('salary');
        });

        Schema::table('ro_targets', function (Blueprint $table) {
            $table->dropColumn('date');
        });
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('paybill');

        });
        Schema::table('arrears', function (Blueprint $table) {
            $table->dropForeign(['installment_id']);
            $table->dropColumn('installment_id');


        });


        Schema::table('installments', function (Blueprint $table) {
            $table->dropColumn('being_paid');
            $table->dropColumn('in_arrear');
            $table->dropColumn('completed');
            $table->dropColumn('last_payment_date');
            $table->dropColumn('start_date');
            $table->dropColumn('amount_paid');
            $table->dropColumn('total');
            $table->dropColumn('interest');
            $table->renameColumn('principal_amount', 'amount');

        });

    }
}
