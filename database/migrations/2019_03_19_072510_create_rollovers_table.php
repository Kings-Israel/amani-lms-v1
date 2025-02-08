<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRolloversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rollovers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('loan_id');
            $table->foreign('loan_id')
                ->references('id')
                ->on('loans')
                ->onDelete('cascade');
            $table->integer('amount');
            $table->integer('rollover_interest');
            $table->integer('rollover_due');
            $table->date('rollover_date');
            $table->timestamps();
        });

        Schema::create('installments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('loan_id');
            $table->foreign('loan_id')
                ->references('id')
                ->on('loans')
                ->onDelete('cascade');
            $table->integer('amount');
            $table->date('due_date');
            $table->boolean('current')->default(false);
            $table->timestamps();
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('rname');
            $table->text('description');
            $table->text('route');
            $table->timestamps();
        });
        Schema::create('arrears', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('installment_id');
            $table->foreign('installment_id')
                ->references('id')
                ->on('installments')
                ->onDelete('cascade');
            $table->integer('amount');
            $table->timestamps();
        });
        Schema::table('loans', function(Blueprint $table)
        {
            $table->boolean('rolled_over')->default(false);
            $table->date('disbursement_date')->change();
            $table->date('end_date')->change();
            $table->date('date_created')->change();
        });

        Schema::table('payments', function(Blueprint $table)
        {
            $table->boolean('for_rollover')->default(false);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function(Blueprint $table)
        {
            $table->dropColumn('for_rollover'); //

        });
        Schema::table('loans', function(Blueprint $table)
        {
            $table->dropColumn('rolled_over'); //

        });
        Schema::dropIfExists('arrears');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('installments');
        Schema::dropIfExists('rollovers');

    }
}
