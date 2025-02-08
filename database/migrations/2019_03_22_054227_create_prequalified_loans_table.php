<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrequalifiedLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prequalified_loans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->double('amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('prequalified_loans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->double('amount');
        });
    }
}
