<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterGuarantorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guarantors', function (Blueprint $table) {
            $table->unsignedBigInteger('industry_id')->change();
            $table->unsignedBigInteger('business_id')->change();
            $table->foreign('industry_id')->references('id')->on('industries')->onDelete('cascade');
            $table->foreign('business_id')->references('id')->on('business_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('guarantors', function (Blueprint $table) {
            $table->string('industry_id')->change();
            $table->string('business_id')->change();

            // add drop foreign key logic here
        });
    }
}
