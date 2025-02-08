<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('msettings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('paybill', 1000);
            $table->string('SecurityCredential', 2000);
            $table->string('InitiatorName', 1000);
            $table->string('Consumer_Key', 1000);
            $table->string('Consumer_Secret', 1000);
           /* $table->string('C2B_Consumer_Key', 1000);
            $table->string('C2B_Consumer Secret', 1000);*/
            $table->string('Utility_balance')->default('Utility Account|KES|0.00|0.00|0.00|0.00');
            $table->string('MMF_balance')->default('Working Account|KES|0.00|0.00|0.00|0.00');
            $table->dateTime('last_updated');
            $table->timestamps();
        });
        Schema::table('branches', function (Blueprint $table) {
            $table->string('C2B_Consumer_Key', 1000);
            $table->string('C2B_Consumer_Secret', 1000);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('C2B_Consumer_Key');
            $table->dropColumn('C2B_Consumer_Secret');

        });
        Schema::dropIfExists('msettings');
    }
}
