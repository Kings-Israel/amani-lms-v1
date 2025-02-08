<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCustomerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function(Blueprint $table)
        {
            $table->boolean('status')->default(true);
            $table->unsignedBigInteger('income_range_id')->nullable();
            $table->string('employment_date')->nullable();
            $table->string('employer')->nullable();
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->onDelete('cascade');

        });

        Schema::table('employers', function(Blueprint $table)
        {
            $table->string('ephone');
            $table->string('eemail');



        });
        Schema::table('field_agents', function(Blueprint $table)
        {
            $table->boolean('status')->default(true);
            $table->unsignedBigInteger('branch_id');



        });

        Schema::table('branches', function(Blueprint $table)
        {
            $table->boolean('status')->default(true);
            $table->string('bemail')->nullable();
            $table->string('bphone')->nullable();

        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('branches', function(Blueprint $table)
        {
            $table->dropColumn('bphone'); //
            $table->dropColumn('bemail'); //
            $table->dropColumn('status'); //


        });

        Schema::table('field_agents', function(Blueprint $table)
        {
            $table->dropColumn('branch_id'); //
            $table->dropColumn('status'); //

        });
        Schema::table('employers', function(Blueprint $table)
        {
            $table->dropColumn('eemail'); //
            $table->dropColumn('ephone'); //
        });

        Schema::table('customers', function(Blueprint $table)
        {
           $table->dropForeign(['branch_id']); //
            $table->dropColumn('branch_id'); //
            $table->dropColumn('employer_id'); //
            $table->dropColumn('employment_date'); //
            $table->dropColumn('income_range'); //
            $table->dropColumn('status'); //

        });

    }
}

