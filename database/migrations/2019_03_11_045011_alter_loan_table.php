<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterLoanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loans', function(Blueprint $table)
        {
            $table->integer('loan_amount')->after('id');
            $table->string('purpose');
            $table->boolean('settled')->default(false);
            $table->integer('total_amount')->nullable();
            $table->integer('total_amount_paid')->nullable();
            $table->string('source')->nullable()->default('branch');
        });

        Schema::table('customers', function(Blueprint $table)
        {
            $table->integer('prequalified_amount');
        });

        Schema::table('products', function(Blueprint $table)
        {
            $table->integer('duration');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function(Blueprint $table)
        {
            $table->dropColumn('duration'); //


        });

        Schema::table('customers', function(Blueprint $table)
        {
            $table->dropColumn('prequalified_amount'); //

        });
        Schema::table('loans', function(Blueprint $table)
        {
            $table->dropColumn('settled'); //
            $table->dropColumn('purpose'); //
            $table->dropColumn('loan_amount'); //


        });






    }
}
