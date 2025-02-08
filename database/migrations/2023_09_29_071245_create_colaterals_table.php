<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateColateralsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('collaterals', function (Blueprint $table) {
            $table->bigIncrements('id');
            //'item', 'description', 'serial_no', 'market_value', 'image_url'
            $table->unsignedBigInteger('loan_id');
            $table->foreign('loan_id')->references('id')
                ->on('loans')->onDelete('restrict');
            $table->string('item');
            $table->string('description');
            $table->string('serial_no')->nullable();
            $table->string('market_value');
            $table->string('image_url')->nullable();
            $table->timestamps();
        });
        Schema::create('leads', function (Blueprint $table) {
            $table->bigIncrements('id');
            //'item', 'description', 'serial_no', 'market_value', 'image_url'
            $table->string('name');
            $table->string('phone_number');
            $table->string('type_of_business');
            $table->string('estimated_amount');
            $table->string('location');
            $table->unsignedBigInteger('officer_id');
            $table->foreign('officer_id')->references('id')
                ->on('users')->onDelete('restrict');
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
        Schema::dropIfExists('leads');
        Schema::dropIfExists('collaterals');
    }
}
