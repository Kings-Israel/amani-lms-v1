<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('users', function(Blueprint $table)
        {
            $table->boolean('status')->default(true);
            $table->string('phone')->after('email');
            $table->integer('field_agent_id')->nullable();
            $table->unsignedBigInteger('branch_id')/*->default('1')*/;
           /* $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->onDelete('cascade');*/

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function(Blueprint $table)
        {
            //$table->dropForeign(['branch_id']); //
            $table->dropColumn('branch_id'); //
            $table->dropColumn('field_agent_id'); //
            $table->dropColumn('phone'); //

            $table->dropColumn('status'); //

        });

    }
}
