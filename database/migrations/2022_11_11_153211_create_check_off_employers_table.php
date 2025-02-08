<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCheckOffEmployersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('check_off_employers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->index()->unique();
            $table->string('name');
            $table->string('location')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_phone_number')->nullable();
            $table->boolean('status')->default(true);
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
        Schema::dropIfExists('check_off_employers');
    }
}
