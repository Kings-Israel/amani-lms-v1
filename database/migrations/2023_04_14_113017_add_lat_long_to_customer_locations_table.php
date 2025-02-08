<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLatLongToCustomerLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_locations', function (Blueprint $table) {
            $table->decimal('business_longitude', 10, 7)->after('physical_address')->nullable();
            $table->decimal('business_latitude', 10, 7)->after('physical_address')->nullable();
            $table->string('business_address')->after('physical_address')->nullable();

            $table->decimal('longitude', 10, 7)->after('physical_address')->nullable();
            $table->decimal('latitude', 10, 7)->after('physical_address')->nullable();
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
            $table->dropColumn('business_longitude');
            $table->dropColumn('business_latitude');
            $table->dropColumn('business_address');
            $table->dropColumn('longitude');
            $table->dropColumn('latitude');
        });
    }
}
