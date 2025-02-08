<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateInteractions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_interaction_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('priority')->default(1);
            $table->timestamps();

        });

        Schema::table('customer_interactions', function (Blueprint $table) {
            $table->integer('model_id')->nullable();
            $table->unsignedBigInteger('interaction_category_id')->default(1);
            $table->integer('status')->default(1);
            $table->integer('followed_up')->default(1);
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->dateTime('closed_date')->nullable();
            $table->integer('target')->default(2);



        });

        Schema::create('pre_interactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('amount');
            $table->dateTime('due_date');
            $table->integer('model_id');
            $table->longText('system_remark');

            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('interaction_category_id');

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->onDelete('restrict');
            $table->foreign('interaction_category_id')
                ->references('id')
                ->on('customer_interaction_categories')
                ->onDelete('restrict');

            $table->timestamps();


        });

        Schema::create('customer_interaction_followups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('follow_up_id');
            $table->unsignedBigInteger('follow_by');
            $table->longText('remark');
            $table->integer('status')->default(1);
            $table->date('next_scheduled_interaction')->nullable();
            $table->foreign('follow_up_id')
                ->references('id')
                ->on('customer_interactions')
                ->onDelete('restrict');
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

        Schema::dropIfExists('customer_interaction_followups');
        Schema::dropIfExists('pre_interactions');

        Schema::table('customer_interactions', function (Blueprint $table) {
            $table->dropColumn('target');
            $table->dropColumn('closed_date');
            $table->dropColumn('closed_by');
            $table->dropColumn('followed_up');
            $table->dropColumn('status');
            $table->dropColumn('interaction_category_id');
            $table->dropColumn('model_id');


        });

        Schema::dropIfExists('customer_interaction_categories');


    }
}
