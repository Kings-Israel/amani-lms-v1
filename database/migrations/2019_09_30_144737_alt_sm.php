<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AltSm extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_sms', function (Blueprint $table) {
            $table->string('phone')->nullable();
        });
        Schema::table('user_sms', function (Blueprint $table) {
            $table->string('phone')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_sms', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
        Schema::table('customer_sms', function (Blueprint $table) {
            $table->dropColumn('phone');
        });


    }
}
