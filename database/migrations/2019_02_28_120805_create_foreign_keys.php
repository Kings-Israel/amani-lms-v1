<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForeignKeys extends Migration
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
            //$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('field_agent_id')
            //     ->references('id')
            //     ->on('field_agents')
            //     ->onDelete('cascade');

            $table->foreign('document_id')
                ->references('id')
                ->on('documents')
                ->onDelete('cascade');

            $table->foreign('guarantor_id')
                ->references('id')
                ->on('guarantors')
                ->onDelete('cascade');
        });
        Schema::table('next_of_kins', function(Blueprint $table)
        {
            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
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
        Schema::table('customers', function(Blueprint $table)
        {
            // $table->dropForeign(['field_agents']); //
            $table->dropForeign(['guarantor_id']); //
            $table->dropForeign(['document_id']); //

        });
        Schema::table('next_of_kins', function(Blueprint $table)
        {
            $table->dropForeign(['customer_id']); //
        });
    }
}
