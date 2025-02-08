<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNullableColumnsLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_locations', function (Blueprint $table) {
            $table->string('home_coordinates')->nullable()->change();
            $table->string('business_coordinates')->nullable()->change();       
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_locations', function (Blueprint $table) {
            $table->string('home_coordinates')->change();
            $table->string('business_coordinates')->change();
        });
    }
}
